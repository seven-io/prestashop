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

use Sms77\Api\Client;

require_once __DIR__ . '/TableWrapper.php';
require_once __DIR__ . '/Constants.php';

class Util
{
    private static function addSignature($msg) {
        $signature = Configuration::get(Constants::SIGNATURE);

        if (Tools::strlen($signature)) {
            if ('append' === Configuration::get(Constants::SIGNATURE_POSITION)) {
                $msg .= $signature;
            } else {
                $msg = $signature . $msg;
            }
        }

        return $msg;
    }

    private static function toString($items) {
        return implode(',', array_unique($items));
    }

    static function getRecipient($address) {
        return '' === $address['phone'] ? $address['phone_mobile'] : $address['phone'];
    }

    static function insert($res, $type, $groups = [], $countries = []) {
        if (!$res) {
            return false;
        }

        $array = [TableWrapper::RESPONSE => json_encode($res), TableWrapper::TYPE => $type,];

        if (count($groups)) {
            $array[TableWrapper::GROUPS] = is_array($groups) ? self::toString($groups) : $groups;
        }
        if (count($countries)) {
            $array[TableWrapper::COUNTRIES] = is_array($countries) ? self::toString($countries) : $countries;
        }

        return TableWrapper::insert($array);
    }

    static function validateAndSend($msg, $number) {
        if (is_array($number)) {
            $number = self::toString($number);
        }

        if (!Tools::strlen($number)) {
            return null;
        }

        $apiKey = Configuration::get(Constants::API_KEY);

        if (!Tools::strlen($apiKey)) {
            return null;
        }

        return json_decode((new Client($apiKey, 'prestashop'))->sms($number, self::addSignature($msg), [
            'from' => Configuration::get(Constants::FROM),
            'json' => true,
        ]), true);
    }
}