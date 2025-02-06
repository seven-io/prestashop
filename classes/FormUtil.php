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
    /**
     * @param string $label
     * @param string $hintDesc
     * @param string $tab
     * @param string $toName
     * @return array
     */
    public static function signaturePosition($label, $hintDesc, $tab, $toName) {
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

    /**
     * @param string $key
     * @return string
     */
    public static function toName($key) {
        return 'config[' . $key . ']';
    }

    /**
     * @param string $name
     * @param string $text
     * @param string | null $tab
     * @return array
     */
    public static function makeTextarea($name, $text, $tab) {
        return [
            'desc' => $text,
            'hint' => $text,
            'label' => $text,
            'name' => $name,
            'tab' => $tab,
            'type' => 'textarea',
        ];
    }

    /**
     * @param string $name
     * @param string $label
     * @param string $shortAction
     * @param boolean $isBool
     * @param string | null $tab
     * @param string|null $desc
     * @return array
     */
    public static function makeSwitch(
        $name,
        $label,
        $shortAction = null,
        $isBool = false,
        $tab = null,
        $desc = null
    ) {
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
