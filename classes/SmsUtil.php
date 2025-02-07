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

use Sms77\Api\Client;
use Sms77\Api\Constant\SmsOptions;
use Sms77\Api\Exception\InvalidRequiredArgumentException;

class SmsUtil {
    /**
     * @return array|mixed|null
     * @throws PrestaShopDatabaseException|ReflectionException|InvalidRequiredArgumentException
     */
    public static function sendBulk(): mixed {
        $form = Tools::getAllValues();
        $cfg = Util::parseFormByClass(
            SmsOptions::class,
            static function (&$smsConfig, $k) use ($form) {
                $optionValue = isset($form[$k]) ? $form[$k] : false;

                if (false === $optionValue) unset($smsConfig[$k]);
                else {
                    if ('1' === $optionValue || '0' === $optionValue)
                        $optionValue = (bool)$optionValue;

                    $smsConfig[$k] = $optionValue;
                }
            }
        );
        $countries = Tools::getValue(Constants::BULK_COUNTRIES, []);
        $groups = Tools::getValue(Constants::BULK_GROUPS, []);
        $ignoreSignature = (boolean)$form['ignore_signature'];
        $activeCustomerAddresses =
            TableWrapper::getActiveCustomerAddressesByGroupsAndCountries(
                $groups, $countries);

        if ((new Personalizer($cfg[SmsOptions::Text]))->hasPlaceholders()) {
            $res = [];

            foreach ($activeCustomerAddresses as $address) {
                $cfg[SmsOptions::Text] =
                    (new Personalizer($cfg[SmsOptions::Text], compact('address')))
                        ->getTransformed();
                $cfg[SmsOptions::To] = Util::getRecipient($address);
                $res[] = self::validateAndSend($cfg, $ignoreSignature);
            }

            return self::insert($res, 'bulk_personalized', $cfg, $groups, $countries)
                ? $res : null;
        }

        $phoneNumbers = array_map(static function ($d) {
            return Util::getRecipient($d);
        }, $activeCustomerAddresses);

        if (count($phoneNumbers)) {
            $res = self::validateAndSend([
                SmsOptions::Text => $cfg[SmsOptions::Text],
                SmsOptions::To => $phoneNumbers,
                $ignoreSignature,
            ]);

            return self::insert($res, 'bulk', $cfg, $groups, $countries)
                ? $res : null;
        }

        PrestaShopLogger::addLog('Seven: No phone numbers to send bulk SMS.');

        return null;
    }

    /**
     * @return mixed|null
     * @throws InvalidRequiredArgumentException
     */
    public static function validateAndSend(array $cfg, bool $ignoreSignature = false): mixed {
        $to = $cfg[SmsOptions::To];
        $text = $cfg[SmsOptions::Text];
        unset($cfg[SmsOptions::Text], $cfg[SmsOptions::To]);

        if (is_array($to)) $to = Util::stringifyUniqueList($to);

        if (!Tools::strlen($to)) {
            PrestaShopLogger::addLog('Seven: Cannot send - no recipient given.');
            return null;
        }

        $apiKey = Configuration::get(Constants::API_KEY);

        if (!Tools::strlen($apiKey)) {
            PrestaShopLogger::addLog('Seven: Cannot send - no API key.');
            return null;
        }

        if (!$ignoreSignature) $text = self::addSignature($text);

        if (!array_key_exists(SmsOptions::From, $cfg))
            $cfg[SmsOptions::From] = Configuration::get(Constants::FROM);

        if (!array_key_exists(SmsOptions::Json, $cfg)) $cfg[SmsOptions::Json] = true;

        PrestaShopLogger::addLog('Seven: Send SMS to ' . $to . ' with text: ' . $text);

        $cfg['type'] = 'direct'; // sms77/api#php5.6 fix
        if (isset($cfg['ttl']) && '' === $cfg['ttl']) unset($cfg['ttl']); // sms77/api#php5.6 fix

        return json_decode((new Client($apiKey, 'prestashop'))
            ->sms($to, $text, $cfg), true);
    }

    private static function addSignature(string $msg): string {
        $signature = Tools::getValue(
            Constants::SIGNATURE, Configuration::get(Constants::SIGNATURE));

        if (Tools::strlen($signature)) {
            $signaturePosition = Tools::getValue(
                Constants::SIGNATURE_POSITION,
                Configuration::get(Constants::SIGNATURE_POSITION)
            );

            if ('append' === $signaturePosition) $msg .= $signature;
            else $msg = $signature . $msg;
        }

        return $msg;
    }

    /**
     * @throws PrestaShopDatabaseException
     */
    public static function insert(
        array|string|null $res,
        string            $type,
        array             $cfg = [],
        array             $groups = [],
        array             $countries = []
    ): bool {
        if (!$res) return false;

        $data = [
            TableWrapper::RESPONSE => json_encode($res),
            TableWrapper::TYPE => $type,
            TableWrapper::CONFIG => json_encode($cfg),
        ];

        if (count($groups))
            $data[TableWrapper::GROUPS] = is_array($groups)
                ? Util::stringifyUniqueList($groups) : $groups;

        if (count($countries))
            $data[TableWrapper::COUNTRIES] = is_array($countries)
                ? Util::stringifyUniqueList($countries) : $countries;

        return TableWrapper::insert($data);
    }

    /**
     * @throws InvalidRequiredArgumentException
     * @throws PrestaShopDatabaseException
     */
    public static function sendEventSMS(Order|int $order, string $action, array $placeholders = []): bool {
        if (!($order instanceof Order)) $order = new Order($order);
        $address = Util::getAddressForOrder($order);
        $placeholders = array_merge(compact('address', 'order'), $placeholders);
        $text = Configuration::get('SEVEN_ON_' . $action);
        $text = (new Personalizer($text, $placeholders))->getTransformed();
        $to = Util::getRecipient($address);
        $res = SmsUtil::validateAndSend(compact('text', 'to'));
        $type = 'on_' . Tools::strtolower($action);
        return SmsUtil::insert($res, $type);
    }
}
