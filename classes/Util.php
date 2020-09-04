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

class Util {
    /**
     * @param string $class
     * @param callable $cb
     * @return array
     * @throws ReflectionException
     */
    public static function parseFormByClass($class, $cb) {
        $constants = (new ReflectionClass($class))->getConstants();
        $array = array_flip($constants);

        foreach (array_keys($array) as $key) {
            $cb($array, $key);
        }

        return $array;
    }

    /**
     * @param array $customer
     * @return mixed
     */
    public static function getRecipient($customer) {
        return '' === $customer['phone_mobile']
            ? $customer['phone'] : $customer['phone_mobile'];
    }

    /**
     * @param OrderState $orderState
     * @return string|null
     */
    public static function getOrderStateAction(OrderState $orderState) {
        $isRefunded = 7 === $orderState->id;
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
        } elseif ($isRefunded) {
            $action = 'REFUND';
        }

        return $action;
    }

    /**
     * @return bool
     */
    public static function hasApiKey() {
        return (bool)Tools::strlen(Configuration::get(Constants::API_KEY));
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
     * @param string $url
     */
    public static function redirect($url) {
        die(header("Location: $url"));
    }

    /**
     * @param array $items
     * @return string
     */
    public static function stringifyUniqueList($items) {
        return implode(',', array_unique($items));
    }
}