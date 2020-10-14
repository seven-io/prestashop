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
use Sms77\Api\SmsOptions;

class SmsUtil
{
    /**
     * @return array|mixed|null
     * @throws PrestaShopDatabaseException|ReflectionException
     */
    public static function sendBulk()
    {
        $form = Tools::getAllValues();
        $cfg = Util::parseFormByClass(
            SmsOptions::class,
            static function (&$smsConfig, $k) use ($form) {
                $optionValue = isset($form[$k]) ? $form[$k] : false;

                if (false === $optionValue) {
                    unset($smsConfig[$k]);
                } else {
                    if ('1' === $optionValue || '0' === $optionValue) {
                        $optionValue = (bool)$optionValue;
                    }

                    $smsConfig[$k] = $optionValue;
                }
            }
        );
        $countries = Tools::getValue(Constants::BULK_COUNTRIES, []);
        $groups = Tools::getValue(Constants::BULK_GROUPS, []);
        $ignoreSignature = (boolean)$form['ignore_signature'];
        $activeCustomers = TableWrapper::getActiveCustomerAddressesByGroupsAndCountries(
            $groups,
            $countries
        );

        if ((new Personalizer($cfg[SmsOptions::Text]))->fillPlaceholders()->getHasPlaceholder()) {
            $res = [];

            foreach ($activeCustomers as $activeCustomer) {
                $cfg[SmsOptions::Text] = (new Personalizer($cfg[SmsOptions::Text]))
                    ->addAddress($activeCustomer)
                    ->fillPlaceholders()->getTransformed();
                $cfg[SmsOptions::To] = Util::getRecipient($activeCustomer);
                $res[] = self::validateAndSend($cfg, $ignoreSignature);
            }

            return self::insert($res, 'bulk_personalized', $cfg, $groups, $countries)
                ? $res : null;
        }

        $phoneNumbers = array_map(static function ($d) {
            return Util::getRecipient($d);
        }, $activeCustomers);

        if (count($phoneNumbers)) {
            $res = self::validateAndSend([
                SmsOptions::Text => $cfg[SmsOptions::Text],
                SmsOptions::To => $phoneNumbers,
                $ignoreSignature,
            ]);

            return self::insert($res, 'bulk', $cfg, $groups, $countries)
                ? $res : null;
        }

        return null;
    }

    /**
     * @param array $cfg
     * @param boolean $ignoreSignature
     * @return mixed|null
     */
    public static function validateAndSend($cfg, $ignoreSignature = false)
    {
        $to = $cfg[SmsOptions::To];
        $text = $cfg[SmsOptions::Text];

        if (is_array($to)) {
            $to = Util::stringifyUniqueList($to);
        }

        if (!Tools::strlen($to)) {
            return null;
        }

        $apiKey = Configuration::get(Constants::API_KEY);

        if (!Tools::strlen($apiKey)) {
            return null;
        }

        if (!$ignoreSignature) {
            $text = self::addSignature($text);
        }

        if (!array_key_exists(SmsOptions::From, $cfg)) {
            $cfg[SmsOptions::From] = Configuration::get(Constants::FROM);
        }

        if (!array_key_exists(SmsOptions::Json, $cfg)) {
            $cfg[SmsOptions::Json] = true;
        }

        unset($cfg[SmsOptions::Text], $cfg[SmsOptions::To]);

        return json_decode((new Client($apiKey, 'prestashop'))
            ->sms($to, $text, $cfg), true);
    }

    /**
     * @param string $msg
     * @return string
     */
    private static function addSignature($msg)
    {
        $signature = Tools::getValue(
            Constants::SIGNATURE,
            Configuration::get(Constants::SIGNATURE)
        );

        if (Tools::strlen($signature)) {
            $signaturePosition = Tools::getValue(
                Constants::SIGNATURE_POSITION,
                Configuration::get(Constants::SIGNATURE_POSITION)
            );

            if ('append' === $signaturePosition) {
                $msg .= $signature;
            } else {
                $msg = $signature . $msg;
            }
        }

        return $msg;
    }

    /**
     * @param array | string | null $res
     * @param string $type
     * @param array $cfg
     * @param array $groups
     * @param array $countries
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public static function insert($res, $type, $cfg, $groups = [], $countries = [])
    {
        if (!$res) {
            return false;
        }

        $data = [
            TableWrapper::RESPONSE => json_encode($res),
            TableWrapper::TYPE => $type,
            TableWrapper::CONFIG => json_encode($cfg),
        ];

        if (count($groups)) {
            $data[TableWrapper::GROUPS] = is_array($groups)
                ? Util::stringifyUniqueList($groups) : $groups;
        }

        if (count($countries)) {
            $data[TableWrapper::COUNTRIES] = is_array($countries)
                ? Util::stringifyUniqueList($countries) : $countries;
        }

        return TableWrapper::insert($data);
    }
}
