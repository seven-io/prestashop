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
 * @author    seven.io
 * @copyright 2019-present seven communications GmbH & Co. KG
 * @license   LICENSE
 */

class SevenMessage extends ObjectModel
{
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'fields' => [
            'response' => [
                'type' => self::TYPE_STRING,
                'required' => true,
            ],
        ],
        'primary' => 'id_seven_message',
        'table' => 'seven_message',
    ];
    /** @var string $response */
    public $response;
}
