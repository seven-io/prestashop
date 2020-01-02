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
        $this->version = '1.2.0';
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

        $this->config['SMS77_FROM'] = Configuration::get('PS_SHOP_NAME');
        $this->config['SMS77_ON_INVOICE'] = 'An invoice has been generated for your order.';
        $this->config['SMS77_ON_INVOICE'] .=
            ' Log in to your customer account in order to have a look at it. Best regards!';

        $this->config['SMS77_ON_PAYMENT'] = 'A payment has been made for your order.';
        $this->config['SMS77_ON_PAYMENT'] .= ' Log in to your customer account for more information. Best regards!';

        $this->config['SMS77_ON_SHIPMENT'] = 'Your order has been shipped.';
        $this->config['SMS77_ON_SHIPMENT'] .= ' Log in to your customer account for more information. Best regards!';

        $this->config['SMS77_ON_DELIVERY'] = 'Your order has been delivered. Enjoy your goods!';
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

    public function hookActionOrderStatusPostUpdate(array $data)
    {
        $validateAndSend = static function ($configKey, $number) {
            if (Tools::strlen($number)) {
                $apiKey = Configuration::get('SMS77_API_KEY');

                if (0 !== Tools::strlen($apiKey)) {
                    $api = new Client($apiKey, 'prestashop');
                    $api->sms($number, Configuration::get($configKey), [
                        'from' => Configuration::get('SMS77_FROM')
                    ]);
                }
            }
        };

        $getToPhoneNumber = static function (array $data) {
            $order = isset($data['Order']) ? $data['Order'] : $data['cart'];
            $addressId = Tools::strlen($order->id_address_delivery)
                ? $order->id_address_delivery
                : $order->id_address_invoice;
            $address = new Address((int)$addressId);
            return Tools::strlen($address->phone_mobile) ? $address->phone_mobile : $address->phone;
        };

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
            $validateAndSend("SMS77_ON_$action", $getToPhoneNumber($data));
        }
    }

    public function uninstall()
    {
        foreach ($this->config as $k) {
            Configuration::deleteByName($k);
        }

        return parent::uninstall();
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

                Configuration::updateValue($k, $v);
            }
        }

        $output .= $this->displayConfirmation($this->l('Settings updated'));

        return $output . (new BackendHelperForm($this->name))->generate();
    }
}
