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

class Util {
    /**
     * @param string $class
     * @param callable $cb
     * @return array
     * @throws ReflectionException
     */
    public static function parseFormByClass($class, callable $cb) {
        $array = array_flip((new ReflectionClass($class))->getConstants());

        foreach (array_keys($array) as $key) $cb($array, $key);

        return $array;
    }

    /**
     * @param $action
     * @return bool
     */
    public static function isEventEnabled($action) {
        return 1 === (int)Configuration::get($action);
    }

    /**
     * @param Order $order
     * @return array
     */
    public static function getAddressForOrder(Order $order) {
        return (array)new Address((int)(Tools::strlen($order->id_address_delivery)
            ? $order->id_address_delivery
            : $order->id_address_invoice));
    }

    /**
     * @param array $customer
     * @return mixed
     */
    public static function getRecipient(array $customer) {
        return '' === $customer['phone_mobile']
            ? $customer['phone'] : $customer['phone_mobile'];
    }

    /**
     * @param OrderState $orderState
     * @return string|null
     */
    public static function getOrderStateAction(OrderState $orderState) {
        if (4 === $orderState->id) return Constants::ORDER_ACTION_SHIPMENT; // works
        if (5 === $orderState->id) return Constants::ORDER_ACTION_DELIVERY; // works
        if (7 === $orderState->id) return Constants::ORDER_ACTION_REFUND; // works

        return null;
    }

    /**
     * @return bool
     */
    public static function hasApiKey() {
        return (bool)Tools::strlen(Configuration::get(Constants::API_KEY));
    }

    /**
     * @param $data
     */
    public static function log($data) {
        $data = json_encode($data);
        $logger = new FileLogger(0);
        $logger->setFilename(_PS_ROOT_DIR_ . '/var/logs/sms77.log');
        $logger->logDebug($data);

        PrestaShopLogger::addLog('SMS77:' . PHP_EOL);
        PrestaShopLogger::addLog($data);
    }

    /**
     * @return string
     * @throws PrestaShopException
     */
    public static function pluginConfigLink() {
        return Context::getContext()->link->getAdminLink('sms77', true, [
            'route' => 'admin_module_configure_action',
            'module_name' => 'sms77']);
    }

    /**
     * @param array $items
     * @return string
     */
    public static function stringifyUniqueList($items) {
        return implode(',', array_unique($items));
    }
}
