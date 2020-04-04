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

require_once dirname(__FILE__) . "/Constants.php";
require_once dirname(__FILE__) . "/Form.php";
require_once dirname(__FILE__) . "/Personalizer.php";
require_once dirname(__FILE__) . "/Util.php";
require_once dirname(__FILE__) . "/TableWrapper.php";

/**
 * @property PhoneNumberUtil phoneNumberUtil
 */
class Sms77 extends Module
{
    protected $config;

    public function __construct()
    {
        $this->config = Constants::CONFIGURATION;
        $this->name = 'sms77';
        $this->version = '1.5.0';
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
        $this->config[Constants::FROM] = Configuration::get('PS_SHOP_NAME'); // defaults to the shop name
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
    }

    public function getContent()
    {
        $output = null;

        $missingApiKey = function () use ($output) {
            return $this->displayError(
                $this->l('An API key is required in order to send SMS. Get yours at http://sms77.io.')
            );
        };

        if (Tools::isSubmit('submit' . $this->name)) {
            $config = Tools::getValue('config');

            foreach ($config as $k => $v) {
                if (Constants::API_KEY === $k && 0 === Tools::strlen($v)) {
                    $output .= $missingApiKey();
                }

                if (Constants::BULK === $k && Tools::strlen($v)) {
                    if (0 === Tools::strlen(Configuration::get(Constants::API_KEY))) {
                        $output .= $missingApiKey();
                    } else {
                        Util::sendBulk($config, $v);
                    }
                } elseif (!in_array($k, Constants::NON_PERSISTED_KEYS)) {
                    Configuration::updateValue($k, $v);
                }
            }

            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output . (new Form($this->name))->generate();
    }

    public function hookActionOrderStatusPostUpdate(array $data)
    {
        $order = isset($data['Order']) ? $data['Order'] : $data['cart'];

        $address = (array) new Address((int)(Tools::strlen($order->id_address_delivery)
            ? $order->id_address_delivery
            : $order->id_address_invoice));

        $action = Util::getAction($data['newOrderStatus']);

        if (null !== $action && 1 === (int)Configuration::get("SMS77_MSG_ON_$action")) {
            Util::validateAndSend(
                (new Personalizer(Configuration::get("SMS77_ON_$action"), $address))->toString($data['id_order']),
                Tools::strlen($address['phone_mobile']) ? $address['phone_mobile'] : $address['phone']);
        }
    }

    public function install()
    {
        TableWrapper::create();

        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        foreach ($this->config as $k => $v) {
            if (!in_array($k, Constants::NON_PERSISTED_KEYS)) {
                Configuration::updateValue($k, $v);
            }
        }

        return parent::install()
            && $this->registerHook('actionOrderStatusPostUpdate');
    }

    public function uninstall()
    {
        TableWrapper::drop();

        foreach (array_keys($this->config) as $k) {
            Configuration::deleteByName($k);
        }

        return parent::uninstall();
    }
}