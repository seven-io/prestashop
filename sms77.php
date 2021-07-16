<?php
/**
 * NOTICE OF LICENSE
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 * You must not modify, adapt or create derivative works of this source code
 * @author    sms77.io
 * @copyright 2019-present sms77 e.K.
 * @license   LICENSE
 */

if (!defined('_PS_VERSION_')) exit;

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php'))
    require_once dirname(__FILE__) . '/vendor/autoload.php';

class Sms77 extends Module {
    protected $config;

    public function __construct() {
        $this->__moduleDir = dirname(__FILE__);
        $this->author = 'sms77 e.K.';
        $this->bootstrap = true;
        $this->config = Constants::CONFIGURATION;
        $this->config[Constants::FROM] = Configuration::get('PS_SHOP_NAME');
        $this->description =
            $this->l('sms77.io module to programmatically send text messages.');
        $this->displayName = 'sms77';
        $this->module_key = '597145e6fdfc3580abe1afc34f7f3971';
        $this->name = 'sms77';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_,
        ];
        $this->tab = 'advertising_marketing';
        $this->version = '2.0.0';
        parent::__construct();
    }

    /**
     * @return string
     * @throws PrestaShopException
     */
    public function getContent() {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            foreach (Tools::getValue('config') as $k => $v) {
                if (Constants::API_KEY === $k && 0 === Tools::strlen($v))
                    $output .= $this->displayError(
                        $this->l('An API key is required in order to send SMS. 
                        Get yours now at https://sms77.io.')
                    );

                Configuration::updateValue($k, $v);

                if (Constants::MSG_ON_INVOICE === $k)
                    $this->toggleHookRegistration('actionSetInvoice', $v);

                if (Constants::MSG_ON_PAYMENT === $k)
                    $this->toggleHookRegistration('actionPaymentConfirmation', $v);

                if (in_array($k, [
                    Constants::MSG_ON_DELIVERY,
                    Constants::MSG_ON_REFUND,
                    Constants::MSG_ON_SHIPMENT,
                ])) $this->toggleHookRegistration('actionOrderStatusPostUpdate', $v);
            }

            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output . (new Form($this->name))->generate();
    }

    /**
     * @param string $hook
     * @param string $value
     */
    private function toggleHookRegistration($hook, $value) {
        $isRegistered = $this->isRegisteredInHook($hook);
        if ('1' === $value && !$isRegistered) $this->registerHook($hook);
        elseif ('0' === $value && $isRegistered) $this->unregisterHook($hook);
    }

    /**
     * @param array $data
     * @throws PrestaShopDatabaseException
     * @throws \Sms77\Api\Exception\InvalidRequiredArgumentException
     */
    public function hookActionSetInvoice(array $data) {
        if (!Util::isEventEnabled(Constants::MSG_ON_INVOICE)) return;

        SmsUtil::sendEventSMS($data['Order'], Constants::ORDER_ACTION_INVOICE,
            ['invoice' => $data['OrderInvoice']]);
    }

    /**
     * @param array $data
     * @throws PrestaShopDatabaseException
     * @throws \Sms77\Api\Exception\InvalidRequiredArgumentException
     */
    public function hookActionPaymentConfirmation(array $data) {
        if (!Util::isEventEnabled(Constants::MSG_ON_PAYMENT)) return;

        SmsUtil::sendEventSMS($data['id_order'], Constants::ORDER_ACTION_PAYMENT);
    }

    /**
     * @param array $data
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws \Sms77\Api\Exception\InvalidRequiredArgumentException
     */
    public function hookActionOrderStatusPostUpdate(array $data) {
        /** @var Order|Cart $order */
        $order = isset($data['Order']) ? $data['Order'] : $data['cart'];
        $order = $order ?: new Order($data['id_order']);

        $action = Util::getOrderStateAction($data['newOrderStatus']);
        if (!$action || !Util::isEventEnabled('SMS77_MSG_ON_' . $action)) return;

        SmsUtil::sendEventSMS($order, $action);
    }

    /**
     * @return bool
     * @throws PrestaShopException
     */
    public function install() {
        TableWrapper::create();

        $tab = new Tab;
        $tab->class_name = 'Sms77Admin';
        $tab->id_parent = Tab::getIdFromClassName('AdminParentCustomerThreads');
        $tab->module = $this->name;
        $tab->name[$this->context->language->id] = $this->l('Sms77 Bulk SMS');
        $tab->add();

        if (Shop::isFeatureActive()) Shop::setContext(Shop::CONTEXT_ALL);

        foreach ($this->config as $k => $v) Configuration::updateValue($k, $v);

        return parent::install();
    }

    /**
     * @return bool
     */
    public function uninstall() {
        TableWrapper::drop();

        foreach (Tab::getCollectionFromModule($this->name) as $tab) $tab->delete();

        foreach (array_keys($this->config) as $k) Configuration::deleteByName($k);

        return parent::uninstall();
    }
}
