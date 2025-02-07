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

class FormUtil {
    public static function signaturePosition(string $label, string $hintDesc, string $tab, string $toName): array {
        return [
            'desc' => $hintDesc,
            'hint' => $hintDesc,
            'label' => $label,
            'name' => $toName
                ? self::toName(Constants::SIGNATURE_POSITION)
                : Constants::SIGNATURE_POSITION,
            'tab' => $tab,
            'type' => 'radio',
            'values' => array_map(static function ($pos) {
                return [
                    'id' => 'seven_config_signature_position_' . $pos,
                    'label' => $pos,
                    'value' => $pos,
                ];
            }, Constants::SIGNATURE_POSITIONS),
        ];
    }

    public static function toName(string $key): string {
        return 'config[' . $key . ']';
    }

    public static function makeTextarea(string $name, string $text, ?string $tab): array {
        return [
            'desc' => $text,
            'hint' => $text,
            'label' => $text,
            'name' => $name,
            'tab' => $tab,
            'type' => 'textarea',
        ];
    }

    public static function makeSwitch(
        string $name,
        string $label,
        string $shortAction = null,
        bool   $isBool = false,
        string $tab = null,
        string $desc = null
    ): array {
        if (!$shortAction) $shortAction = $name;

        $values = [
            ['id' => $shortAction . '_on', 'value' => 1,],
            ['id' => $shortAction . '_off', 'value' => 0,],
        ];

        return [
            'desc' => $desc,
            'hint' => $desc,
            'is_bool' => $isBool,
            'label' => $label,
            'name' => $name,
            'tab' => $tab,
            'type' => 'switch',
            'values' => $values,
        ];
    }
}
