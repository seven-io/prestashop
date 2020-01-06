<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    sms77.io
 * @copyright 2019-present sms77 e.K.
 * @license   LICENSE
 */

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use PrestaShop\PrestaShop\Adapter\Entity\Tab;
use Sms77\Api\Client;
use Sms77\Domain\Reviewer\Command\UpdateIsAllowedToReviewCommand;
use Sms77\Domain\Reviewer\Exception\CannotCreateReviewerException;
use Sms77\Domain\Reviewer\Exception\CannotToggleAllowedToReviewStatusException;
use Sms77\Domain\Reviewer\Exception\ReviewerException;
use Sms77\Domain\Reviewer\Query\GetReviewerSettingsForForm;
use Sms77\Domain\Reviewer\QueryResult\ReviewerSettingsForForm;

class Sms77 extends Module
{
    protected $errors = [];

    protected $config = [
        'SMS77_API_KEY' => '',
        'SMS77_MSG_ON_SHIPMENT' => false,
        'SMS77_MSG_ON_DELIVERY' => false,
        'SMS77_MSG_ON_PAYMENT' => false,
        'SMS77_MSG_ON_INVOICE' => false,
    ];

    public function __construct()
    {
        $this->name = 'sms77';
        $this->version = '1.3.0';
        $this->author = 'sms77 e.K.';
        $this->need_instance = 0;
        $this->module_key = '7c33461cc60fc57e9746c6d288b6487e';

        parent::__construct();

        $this->__moduleDir = dirname(__FILE__);
        $this->bootstrap = true;
        $this->displayName = 'sms77';

        $this->description =
            $this->l('sms77.io module to programmatically send text messages.');

        $this->tab = 'advertising_marketing';
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_,
        ];

        $this->config['SMS77_FROM'] = Configuration::get('PS_SHOP_NAME'); //defaults to the shop name

        $this->config['SMS77_ON_INVOICE'] = 'Dear {0} {1}. An invoice has been generated for your order #{2}.';
        $this->config['SMS77_ON_INVOICE'] .=
            ' Log in to your account in order to have a look at it. Best regards!';

        $this->config['SMS77_ON_PAYMENT'] = 'Dear {0} {1}. A payment has been made for your order #{2}.';
        $this->config['SMS77_ON_PAYMENT'] .= ' Log in to your account for more information. Best regards!';

        $this->config['SMS77_ON_SHIPMENT'] = 'Dear {0} {1}. Your order #{2} has been shipped.';
        $this->config['SMS77_ON_SHIPMENT'] .= ' Log in to your customer account for more information. Best regards!';

        $this->config['SMS77_ON_DELIVERY'] = 'Dear {0} {1}. Your order #{2} has been delivered. Enjoy your goods!';

        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        foreach ($this->config as $k => $v) {
            Configuration::updateValue($k, $v);
        }

        return parent::install()
            && $this->registerHook('actionOrderStatusPostUpdate');
    }

    private function validateAndSend($msg, $number)
    {
        if (!Tools::strlen($number)) {
            return null;
        }

        $apiKey = Configuration::get('SMS77_API_KEY');
        if (!Tools::strlen($apiKey)) {
            return null;
        }

        $api = new Client($apiKey, 'prestashop');
        $api->sms($number, $msg, [
            'from' => Configuration::get('SMS77_FROM')
        ]);
    }

    public function hookActionOrderStatusPostUpdate(array $data)
    {
        $getAddress = static function (array $data) {
            $order = isset($data['Order']) ? $data['Order'] : $data['cart'];

            $id = (int)(Tools::strlen($order->id_address_delivery)
                ? $order->id_address_delivery
                : $order->id_address_invoice);

            return new Address($id);
        };

        $address = $getAddress($data);

        $getAction = static function () use ($data) {
            $orderState = $data['newOrderStatus'];
            $awaitingPayment = in_array($orderState->id, [1, 10, 13], true);
            $isShipping = 4 === $orderState->id;
            $awaitingDelivery = 5 === $orderState->id;
            $isPaid = in_array($orderState->id, [2, 11], true);

            $action = null;

            if ($awaitingPayment) {
                $action = 'INVOICE';
            } elseif ($isPaid) {
                $action = 'PAYMENT';
            } elseif ($isShipping) {
                $action = 'SHIPMENT';
            } elseif ($awaitingDelivery) {
                $action = 'DELIVERY';
            }

            return $action;
        };

        $action = $getAction();

        if (null !== $action && 1 === (int)Configuration::get("SMS77_MSG_ON_$action")) {
            $this->validateAndSend(
                $this->personalize(Configuration::get("SMS77_ON_$action"), (array)$address, $data['id_order']),
                Tools::strlen($address->phone_mobile) ? $address->phone_mobile : $address->phone);
        }
    }

    public function uninstall()
    {
        foreach ($this->config as $k) {
            Configuration::deleteByName($k);
        }

        return parent::uninstall();
    }

    private static function dbQuery($select, $from, $where)
    {
        $sql = new DbQuery();
        $sql->select($select);
        $sql->from($from, 'q');
        $sql->where($where);
        return Db::getInstance()->executeS($sql);
    }

    public function getContent()
    {
        require_once $this->__moduleDir . '/forms/BackendHelperForm.php';

        $output = null;

        if (Tools::isSubmit('submit' . $this->name)) {
            foreach (Tools::getValue('config') as $k => $v) {
                if ('SMS77_API_KEY' === $k && 0 === Tools::strlen($v)) {
                    $this->errors[] =
                        Tools::displayError($this->l(
                            'An API key is required in order to send SMS. Get yours at sms77.io.'
                        ));
                }

                if ('SMS77_ON_GENERIC' === $k) {
                    $addresses = self::dbQuery(
                        'id_country, id_customer, phone, phone_mobile',
                        'address',
                        "q.active = 1 AND q.deleted = 0 AND q.id_customer != 0 AND q.phone_mobile<>'0000000000'");

                    $merged = array_map(static function ($address) {
                        $customer = self::dbQuery(
                            '*',
                            'customer',
                            'q.active = 1 AND q.deleted = 0 AND q.id_customer = ' . $address['id_customer']);
                        return $address + array_shift($customer);
                    }, $addresses);

                    $valids = array_filter($merged, function ($d) {
                        $numbers = [];
                        if (isset($d['phone'])) {
                            $numbers[] = $d['phone'];
                        }
                        if (isset($d['phone_mobile'])) {
                            $numbers[] = $d['phone_mobile'];
                        }


                        $numbers = array_filter($numbers, function ($number) use ($d) {
                            try {
                                $isoCode = self::dbQuery(
                                    'iso_code',
                                    'country',
                                    'q.id_country = ' . $d['id_country']);
                                $isoCode = array_shift($isoCode)['iso_code'];
                                $numberProto = $this->phoneNumberUtil->parse($number, $isoCode);
                                return $this->phoneNumberUtil->isValidNumberForRegion($numberProto, $isoCode);
                            } catch (NumberParseException $e) {
                                return false;
                            }
                        });

                        return count($numbers) ? true : false;
                    });

                    if (preg_match('{0}', $v) || preg_match('{1}', $v)) { //this is a personalized message
                        $msg = $v;

                        foreach ($valids as $valid) {
                            $this->validateAndSend(
                                $this->personalize($msg, $valid),
                                '' === $valid['phone'] ? $valid['phone_mobile'] : $valid['phone']
                            );
                        }
                    } else {
                        $phoneNumbers = array_map(static function ($d) {
                            return '' === $d['phone_mobile'] ? $d['phone'] : $d['phone_mobile'];
                        }, $valids);
                        $this->validateAndSend($k, implode(',', array_unique($phoneNumbers)));
                    }
                }

                Configuration::updateValue($k, $v);
            }
        }

        $output .= $this->displayConfirmation($this->l('Settings updated'));

        return $output . (new BackendHelperForm($this->name))->generate();
    }

    private function personalize($msg, $data, $orderId = null)
    {
        $hasFirstName = false !== strpos($msg, '{0}');
        $hasLastName = false !== strpos($msg, '{1}');
        $hasOrderId = $orderId ? false !== strpos($msg, '{2}') : false;

        if ($hasFirstName || $hasLastName || $hasOrderId) { //this is a personalized message
            if ($hasFirstName) {
                $msg = str_replace('{0}', $data['firstname'], $msg);
            }

            if ($hasLastName) {
                $msg = str_replace('{1}', $data['lastname'], $msg);
            }

            if ($hasOrderId) {
                $msg = str_replace('{2}', $orderId, $msg);
            }
        }

        return $msg;
    }
}
