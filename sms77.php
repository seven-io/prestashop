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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . "/classes/Constants.php";
require_once dirname(__FILE__) . "/classes/Form.php";
require_once dirname(__FILE__) . "/classes/Personalizer.php";
require_once dirname(__FILE__) . "/classes/Util.php";
require_once dirname(__FILE__) . "/classes/TableWrapper.php";

class Sms77 extends Module
{
    protected $config;

    public function __construct() {
        $this->config = Constants::CONFIGURATION;
        $this->name = 'sms77';
        $this->version = '1.6.0';
        $this->author = 'sms77 e.K.';
        $this->need_instance = 0;
        $this->module_key = '7c33461cc60fc57e9746c6d288b6487e';
        parent::__construct();

        $this->__moduleDir = dirname(__FILE__);
        $this->bootstrap = true;
        $this->displayName = 'sms77';
        $this->description = $this->l('sms77.io module to programmatically send text messages.');
        $this->tab = 'advertising_marketing';
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_,
        ];
        $this->config[Constants::FROM] = Configuration::get('PS_SHOP_NAME'); // defaults to the shop name
    }

    private function addErrorOnMissingApiKey() {
        return $this->displayError(
            $this->l('An API key is required in order to send SMS. Get yours at http://sms77.io.')
        );
    }

    public function getContent() {
        $output = null;

        if (Tools::isSubmit('submit' . $this->name)) {
            $config = Tools::getValue('config');

            foreach ($config as $k => $v) {
                if (Constants::API_KEY === $k && 0 === Tools::strlen($v)) {
                    $output .= $this->addErrorOnMissingApiKey();
                }

                Configuration::updateValue($k, $v);
            }

            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output . (new Form($this->name))->generate();
    }

    public function hookActionOrderStatusPostUpdate(array $data) {
        $order = isset($data['Order']) ? $data['Order'] : $data['cart'];

        $address = (array)new Address((int)(Tools::strlen($order->id_address_delivery)
            ? $order->id_address_delivery
            : $order->id_address_invoice));

        $action = getAction($data['newOrderStatus']);

        if (null !== $action && 1 === (int)Configuration::get("SMS77_MSG_ON_$action")) {
            $res = Util::validateAndSend(
                (new Personalizer(Configuration::get("SMS77_ON_$action"), $address))->toString($data['id_order']),
                Tools::strlen($address['phone_mobile']) ? $address['phone_mobile'] : $address['phone']);

            Util::insert($res, strtolower("on_$action"));
        }

        function getAction(OrderState $orderState) {
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
        }

    }

    public function install() {
        TableWrapper::create();

        $tab = new Tab();
        $tab->name[$this->context->language->id] = $this->l('sms77io Bulk SMS'); // Need a foreach for the language TODO???
        $tab->class_name = 'Sms77Admin';
        $tab->id_parent = Tab::getIdFromClassName('AdminParentCustomerThreads');
        $tab->module = $this->name;
        $tab->add();

        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        foreach ($this->config as $k => $v) {
            Configuration::updateValue($k, $v);
        }

        return parent::install()
            && $this->registerHook('actionOrderStatusPostUpdate');
    }

    public function uninstall() {
        TableWrapper::drop();

        foreach (Tab::getCollectionFromModule($this->name) as $moduleTab) {
            $moduleTab->delete();
        }

        foreach (array_keys($this->config) as $k) {
            Configuration::deleteByName($k);
        }

        return parent::uninstall();
    }
}