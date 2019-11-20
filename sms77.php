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
 * @copyright 2019-present sms77.io
 * @license   LICENSE
 */

use Sms77\Domain\Reviewer\Command\UpdateIsAllowedToReviewCommand;
use Sms77\Domain\Reviewer\Exception\CannotCreateReviewerException;
use Sms77\Domain\Reviewer\Exception\CannotToggleAllowedToReviewStatusException;
use Sms77\Domain\Reviewer\Exception\ReviewerException;
use Sms77\Domain\Reviewer\Query\GetReviewerSettingsForForm;
use Sms77\Domain\Reviewer\QueryResult\ReviewerSettingsForForm;
use PrestaShop\PrestaShop\Adapter\Entity\Tab;
use Sms77\Api\Client;

class Sms77 extends Module
{
    protected $errors = [];

    protected $config = [
        'SMS77_API_KEY' => '',
        'SMS77_MSG_ON_SHIPMENT' => true,
        'SMS77_MSG_ON_DELIVERY' => true,
        'SMS77_MSG_ON_PAYMENT' => true,
        'SMS77_MSG_ON_INVOICE' => true,
    ];

    public function __construct()
    {
        $this->name = 'sms77';
        $this->version = '1.0.0';
        $this->author = 'sms77.io';
        $this->need_instance = 0;

        parent::__construct();

        $this->__moduleDir = dirname(__FILE__);
        $this->bootstrap = true;
        $this->displayName = "sms77";

        $this->description =
            $this->l('sms77.io module to programatically send text messages.');

        $this->tab = 'advertising_marketing';
        $this->ps_versions_compliancy = [
            'min' => '1.7.6.0',
            'max' => _PS_VERSION_,
        ];

        $this->config["SMS77_ON_INVOICE"] = "An invoice has been generated for your order.";
        $this->config["SMS77_ON_INVOICE"] .=
            " Log in to your customer account in order to have a look at it. Best regards!";
        $this->config["SMS77_ON_INVOICE"] = trim($this->config["SMS77_ON_INVOICE"]);

        $this->config["SMS77_ON_PAYMENT"] = "A payment has been made for your order.";
        $this->config["SMS77_ON_PAYMENT"] .= " Log in to your customer account for more information. Best regards!";
        $this->config["SMS77_ON_PAYMENT"] = trim($this->config["SMS77_ON_PAYMENT"]);

        $this->config["SMS77_ON_SHIPMENT"] = "Your order has been shipped.";
        $this->config["SMS77_ON_SHIPMENT"] .= " Log in to your customer account for more information. Best regards!";
        $this->config["SMS77_ON_SHIPMENT"] = trim($this->config["SMS77_ON_SHIPMENT"]);

        $this->config["SMS77_ON_DELIVERY"] = "Your order has been delivered. Enjoy your goods!";
        $this->config["SMS77_ON_DELIVERY"] = trim($this->config["SMS77_ON_DELIVERY"]);
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
            && $this->registerHook("actionOrderStatusPostUpdate");
    }

    public function hookActionOrderStatusPostUpdate(array $data)
    {
        $validateAndSend = function (string $configKey, string $number) {
            if (Tools::strlen($number)) {
                $apiKey = Configuration::get("SMS77_API_KEY");

                if (0 !== Tools::strlen($apiKey)) {
                    $api = new Client($apiKey);
                    $api->sms($number, Configuration::get($configKey));
                }
            }
        };

        $getToPhoneNumber = function (array $data) {
            $order = isset($data["Order"]) ? $data["Order"] : $data["cart"];
            $addressId = Tools::strlen($order->id_address_delivery) ? $order->id_address_delivery : $order->id_address_invoice;
            $address = new Address((int)$addressId);
            return Tools::strlen($address->phone_mobile) ? $address->phone_mobile : $address->phone;
        };

        $orderState = $data["newOrderStatus"];
        $awaitingPayment = in_array($orderState->id, [1, 10, 13]);
        $isShipping = 4 === $orderState->id;
        $awaitingDelivery = 5 === $orderState->id;
        $isPaid = in_array($orderState->id, [2, 11]);

        $action = null;

        if ($awaitingPayment) {
            $action = "INVOICE";
        } elseif ($isPaid) {
            $action = "PAYMENT";
        } elseif ($isShipping) {
            $action = "SHIPMENT";
        } elseif ($awaitingDelivery) {
            $action = "DELIVERY";
        }

        if (null !== $action) {
            if (1 == Configuration::get("SMS77_MSG_ON_$action")) {
                $validateAndSend("SMS77_ON_$action", $getToPhoneNumber($data));
            }
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

        if (Tools::isSubmit('submit' . $this->name)) {
            foreach (Tools::getValue('config') as $k => $v) {
                Configuration::updateValue($k, $v);
            }
        }

        return $this->errors
            ? $this->displayError(implode($this->errors, '<br>'))
            : $this->displayConfirmation($this->l('Settings updated'))
            . (new BackendHelperForm($this->name))->generate();
    }
}
