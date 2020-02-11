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
    public static $configuration = [
        'SMS77_API_KEY' => '',
        'SMS77_BULK' => '',
        'SMS77_FROM' => '',
        'SMS77_MSG_ON_DELIVERY' => false,
        'SMS77_MSG_ON_INVOICE' => false,
        'SMS77_MSG_ON_PAYMENT' => false,
        'SMS77_MSG_ON_SHIPMENT' => false,
        'SMS77_ON_DELIVERY' => 'Dear {0} {1}. Your order #{2} has been delivered. Enjoy your goods!',
        'SMS77_ON_INVOICE' => 'Dear {0} {1}. An invoice has been generated for your order #{2}.  Log in to your account in order to have a look at it. Best regards!',
        'SMS77_ON_PAYMENT' => 'Dear {0} {1}. A payment has been made for your order #{2}. Log in to your account for more information. Best regards!',
        'SMS77_ON_SHIPMENT' => 'Dear {0} {1}. Your order #{2} has been shipped. Log in to your customer account for more information. Best regards!',
        'SMS77_SIGNATURE' => '',
        'SMS77_SIGNATURE_POSITION' => 'append',
    ];

    public static $signature_positions = ['append', 'prepend',];
}