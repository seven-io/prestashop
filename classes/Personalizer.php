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

/**
 * @property string $msg
 * @property array $address
 * @property bool $hasFirstName
 * @property bool $hasLastName
 * @property bool $hasOrderId
 */
class Personalizer
{
    public function __construct($msg, $address) {
        $this->msg = $msg;
        $this->address = $address;

        $this->hasFirstName = false !== strpos($this->msg, '{0}');
        $this->hasLastName = false !== strpos($this->msg, '{1}');
        $this->hasOrderId = false !== strpos($this->msg, '{2}');
    }

    function toString($orderId = null) {
        $msg = $this->msg;

        if ($this->hasFirstName) {
            $msg = str_replace('{0}', $this->address['firstname'], $msg);
        }

        if ($this->hasLastName) {
            $msg = str_replace('{1}', $this->address['lastname'], $msg);
        }

        if ($this->hasOrderId && $orderId) {
            $msg = str_replace('{2}', $orderId, $msg);
        }

        return $msg;
    }
}