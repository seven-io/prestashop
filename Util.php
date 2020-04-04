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
use libphonenumber\NumberParseException;

class Util {
    static function getAction(OrderState $orderState) {
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

     static function dbQuery($select, $from, $where)
    {
        $sql = new DbQuery();
        $sql->select($select);
        $sql->from($from, 'q');
        $sql->where($where);
        return Db::getInstance()->executeS($sql);
    }

     static function validateAndSend($msg, $number)
    {
        $apiKey = Configuration::get(Constants::API_KEY);
        if (!Tools::strlen($number) || !Tools::strlen($apiKey)) {
            return null;
        }

        $signature = Configuration::get(Constants::SIGNATURE);
        if (Tools::strlen($signature)) {
            if ('append' === Configuration::get(Constants::SIGNATURE_POSITION)) {
                $msg .= $signature;
            } else {
                $msg = $signature . $msg;
            }
        }

        $res = (new Client($apiKey, 'prestashop'))->sms($number, $msg, [
            'from' => Configuration::get(Constants::FROM),
            'json' => true,
        ]);

        TableWrapper::insert(['response' => json_decode($res)]);
    }

    static function addWhereIfSet($where, $key, $field, $config) {
        if (isset($config[$key])) {
            $list = implode(',', $config[$key]);

            $where .= " AND $field IN ($list)";
        }

        return $where;
    }

    static function sendBulk($config, $v) {
        $addresses = Util::dbQuery(
            'id_country, id_customer, phone, phone_mobile','address', self::addWhereIfSet(
            "q.active = 1 AND q.deleted = 0 AND q.id_customer != 0 AND q.phone_mobile<>'0000000000'",
            Constants::BULK_COUNTRIES, "q.id_country", $config));

        $merged = array_map(static function ($address) use($config) {
            $customer = Util::dbQuery('*','customer', self::addWhereIfSet(
                'q.active = 1 AND q.deleted = 0 AND q.id_customer = ' . $address['id_customer'],
                Constants::BULK_GROUPS, "q.id_default_group", $config));
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
                    $isoCode = Util::dbQuery(
                        'iso_code',
                        'country',
                        'q.id_country = ' . $d['id_country']
                    );
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
                Util::validateAndSend(
                    (new Personalizer($msg, $valid))->toString(),
                    '' === $valid['phone'] ? $valid['phone_mobile'] : $valid['phone']
                );
            }
        } else {
            $phoneNumbers = array_map(static function ($d) {
                return '' === $d['phone_mobile'] ? $d['phone'] : $d['phone_mobile'];
            }, $valids);

            Util::validateAndSend($v, implode(',', array_unique($phoneNumbers)));
        }
    }
}