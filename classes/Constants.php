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

abstract class Constants
{
    const API_KEY = 'SMS77_API_KEY';

    const BULK = 'SMS77_BULK';
    const BULK_COUNTRIES = 'SMS77_BULK_COUNTRIES';
    const BULK_GROUPS = 'SMS77_BULK_GROUPS';

    const CONFIGURATION = [
        self::API_KEY => '',
        self::BULK => '',
        self::FROM => '',
        self::MSG_ON_DELIVERY => false,
        self::MSG_ON_INVOICE => false,
        self::MSG_ON_PAYMENT => false,
        self::MSG_ON_REFUND => false,
        self::MSG_ON_SHIPMENT => false,
        self::ON_DELIVERY => 'Dear {{address.firstname}} {{address.lastname}}. Your order #{{order.id}} has been delivered. Enjoy your goods!',
        self::ON_INVOICE => 'Dear {{address.firstname}} {{address.lastname}}. An invoice with number {{invoice.number}} has been generated for your order #{{order.id}}. Log in to your account in order to have a look at it. Best regards!',
        self::ON_PAYMENT => 'Dear {{address.firstname}} {{address.lastname}}. Your order #{{order.id}} has been marked as being fully paid. Log in to your account for more information. Best regards!',
        self::ON_REFUND => 'Dear {{address.firstname}} {{address.lastname}}. Your order #{{order.id}} has been marked as being refunded. Log in to your account for more information. Best regards!',
        self::ON_SHIPMENT => 'Dear {{address.firstname}} {{address.lastname}}. Your order #{{order.id}} has been shipped. Log in to your customer account for more information. Best regards!',
        self::SIGNATURE => '',
        self::SIGNATURE_POSITION => 'append',
    ];

    const FROM = 'SMS77_FROM';

    const MSG_ON_DELIVERY = 'SMS77_MSG_ON_DELIVERY';
    const MSG_ON_INVOICE = 'SMS77_MSG_ON_INVOICE';
    const MSG_ON_PAYMENT = 'SMS77_MSG_ON_PAYMENT';
    const MSG_ON_REFUND = 'SMS77_MSG_ON_REFUND';
    const MSG_ON_SHIPMENT = 'SMS77_MSG_ON_SHIPMENT';

    const ON_DELIVERY = 'SMS77_ON_DELIVERY';
    const ON_INVOICE = 'SMS77_ON_INVOICE';
    const ON_PAYMENT = 'SMS77_ON_PAYMENT';
    const ON_REFUND = 'SMS77_ON_REFUND';
    const ON_SHIPMENT = 'SMS77_ON_SHIPMENT';

    const ORDER_ACTION_DELIVERY = 'DELIVERY';
    const ORDER_ACTION_INVOICE = 'INVOICE';
    const ORDER_ACTION_PAYMENT = 'PAYMENT';
    const ORDER_ACTION_REFUND = 'REFUND';
    const ORDER_ACTION_SHIPMENT = 'SHIPMENT';

    const SIGNATURE = 'SMS77_SIGNATURE';
    const SIGNATURE_POSITION = 'SMS77_SIGNATURE_POSITION';
    const SIGNATURE_POSITIONS = ['append', 'prepend',];
}
