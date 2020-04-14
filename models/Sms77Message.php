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

class Sms77Message extends ObjectModel
{
    /** @var string $response */
    public $response;

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
        'primary' => 'id_sms77_message',
        'table' => 'sms77_message',
    ];
}