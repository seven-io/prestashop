<?php
/**
 * NOTICE OF LICENSE
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 * You must not modify, adapt or create derivative works of this source code
 * @author    seven.io
 * @copyright 2019-present seven communications GmbH & Co. KG
 * @license   LICENSE
 */

class Util {
    /**
     * @throws ReflectionException
     */
    public static function parseFormByClass(string $class, callable $cb): array {
        $array = array_flip((new ReflectionClass($class))->getConstants());

        foreach (array_keys($array) as $key) $cb($array, $key);

        return $array;
    }

    public static function isEventEnabled(string $action): bool {
        return 1 === (int)Configuration::get($action);
    }

    public static function getAddressForOrder(Order $order): array {
        return (array)new Address((int)(Tools::strlen($order->id_address_delivery)
            ? $order->id_address_delivery
            : $order->id_address_invoice));
    }

    public static function getRecipient(array $customer): mixed {
        return '' === $customer['phone_mobile']
            ? $customer['phone'] : $customer['phone_mobile'];
    }

    public static function getOrderStateAction(OrderState $orderState): ?string {
        if (4 === $orderState->id) return Constants::ORDER_ACTION_SHIPMENT; // works
        if (5 === $orderState->id) return Constants::ORDER_ACTION_DELIVERY; // works
        if (7 === $orderState->id) return Constants::ORDER_ACTION_REFUND; // works

        return null;
    }

    public static function hasApiKey(): bool {
        return (bool)Tools::strlen(Configuration::get(Constants::API_KEY));
    }

    public static function log(mixed $data): void {
        $data = json_encode($data);
        $logger = new FileLogger(0);
        $logger->setFilename(_PS_ROOT_DIR_ . '/var/logs/seven.log');
        $logger->logDebug($data);

        PrestaShopLogger::addLog('SEVEN:' . PHP_EOL);
        PrestaShopLogger::addLog($data);
    }

    /**
     * @throws PrestaShopException
     */
    public static function pluginConfigLink(): string {
        return Context::getContext()->link->getAdminLink('seven', true, [
            'route' => 'admin_module_configure_action',
            'module_name' => 'seven']);
    }

    public static function stringifyUniqueList(array $items): string {
        return implode(',', array_unique($items));
    }
}
