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

class Constants
{
    const API_KEY = 'SMS77_API_KEY';
    const BULK = 'SMS77_BULK';
    const BULK_COUNTRIES = 'SMS77_BULK_COUNTRIES';
    const FROM = 'SMS77_FROM';
    const MSG_ON_DELIVERY = 'SMS77_MSG_ON_DELIVERY';
    const MSG_ON_INVOICE = 'SMS77_MSG_ON_INVOICE';
    const MSG_ON_PAYMENT = 'SMS77_MSG_ON_PAYMENT';
    const MSG_ON_SHIPMENT = 'SMS77_MSG_ON_SHIPMENT';
    const ON_DELIVERY = 'SMS77_ON_DELIVERY';
    const ON_INVOICE = 'SMS77_ON_INVOICE';
    const ON_PAYMENT = 'SMS77_ON_PAYMENT';
    const ON_SHIPMENT = 'SMS77_ON_SHIPMENT';
    const SIGNATURE = 'SMS77_SIGNATURE';
    const SIGNATURE_POSITION = 'SMS77_SIGNATURE_POSITION';

    public static $configuration = [
        self::API_KEY => '',
        self::BULK => '',
        self::FROM => '',
        self::MSG_ON_DELIVERY => false,
        self::MSG_ON_INVOICE => false,
        self::MSG_ON_PAYMENT => false,
        self::MSG_ON_SHIPMENT => false,
        self::ON_DELIVERY => 'Dear {0} {1}. Your order #{2} has been delivered. Enjoy your goods!',
        self::ON_INVOICE => 'Dear {0} {1}. An invoice has been generated for your order #{2}. 
        Log in to your account in order to have a look at it. Best regards!',
        self::ON_PAYMENT => 'Dear {0} {1}. A payment has been made for your order #{2}. 
        Log in to your account for more information. Best regards!',
        self::ON_SHIPMENT => 'Dear {0} {1}. Your order #{2} has been shipped. 
        Log in to your customer account for more information. Best regards!',
        self::SIGNATURE => '',
        self::SIGNATURE_POSITION => 'append',
    ];

    public static $signature_positions = ['append', 'prepend',];
}