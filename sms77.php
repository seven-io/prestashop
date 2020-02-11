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

use libphonenumber\PhoneNumberUtil;

require_once __DIR__ . "/Constants.php";

class Sms77 extends Module
{
    protected $config;
    protected $errors = [];

    public function __construct()
    {
        $this->config = Constants::$configuration;

        $this->name = 'sms77';
        $this->version = '1.4.0';
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

        $this->config['SMS77_FROM'] = Configuration::get('PS_SHOP_NAME'); // defaults to the shop name

        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
    }

    private function dbQuery($select, $from, $where)
    {
        $sql = new DbQuery();
        $sql->select($select);
        $sql->from($from, 'q');
        $sql->where($where);
        return Db::getInstance()->executeS($sql);
    }

    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit' . $this->name)) {
            foreach (Tools::getValue('config') as $k => $v) {
                if ('SMS77_API_KEY' === $k && 0 === Tools::strlen($v)) {
                    $output .=
                        $this->displayError(
                            $this->l('An API key is required in order to send SMS. Get yours at http://sms77.io.')
                        );
                }

                if ('SMS77_BULK' === $k && Tools::strlen($v)) {
                    if (0 === Tools::strlen($this->getSetting('API_KEY'))) {
                        $output .= $this->displayError(
                            $this->l('An API key is required in order to send SMS. Get yours at http://sms77.io.')
                        );
                    } else {
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
                                    return $this->phoneNumberUtil->isValidNumber($numberProto);
                                } catch (NumberParseException $e) {
                                    return false;
                                }
                            });

                            return count($numbers) ? true : false;
                        });

                        $hasPlaceholder = preg_match('{0}', $v) || preg_match('{1}', $v);
                        if ($hasPlaceholder) { // this is a personalized message
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
                } else {
                    Configuration::updateValue($k, $v);
                }
            }

            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output . (new Form($this->name))->generate();
    }

    private function getSetting($key)
    {
        return Configuration::get("SMS77_$key");
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
            $action = Configuration::get("SMS77_ON_$action");
            $personalized = $this->personalize($action, (array)$address, $data['id_order']);
            $recipient = Tools::strlen($address->phone_mobile) ? $address->phone_mobile : $address->phone;
            $this->validateAndSend($personalized, $recipient);
        }
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

    private function personalize($msg, $data, $orderId = null)
    {
        $hasFirstName = false !== strpos($msg, '{0}');
        $hasLastName = false !== strpos($msg, '{1}');
        $hasOrderId = $orderId ? false !== strpos($msg, '{2}') : false;

        if ($hasFirstName || $hasLastName || $hasOrderId) { // this is a personalized message
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

    public function uninstall()
    {
        foreach ($this->config as $k => $v) {
            Configuration::deleteByName($k);
        }

        return parent::uninstall();
    }

    private function validateAndSend($msg, $number)
    {
        if (!Tools::strlen($number)) {
            return null;
        }

        $apiKey = $this->getSetting('API_KEY');
        if (!Tools::strlen($apiKey)) {
            return null;
        }

        $signature = $this->getSetting('SIGNATURE');
        if (Tools::strlen($signature)) {
            if ('append' === $this->getSetting('SIGNATURE_POSITION')) {
                $msg .= $signature;
            } else {
                $msg = $signature . $msg;
            }
        }

        $api = new Client($apiKey, 'prestashop');
        $api->sms($number, $msg, [
            'from' => $this->getSetting('FROM')
        ]);
    }
}
