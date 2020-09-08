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

class OrderPersonalizer extends Personalizer {
    /**
     * OrderPersonalizer constructor.
     * @param string $action
     * @param array $address
     * @param int $orderId
     */
    public function __construct($action, $address, $orderId) {
        parent::__construct(Configuration::get("SMS77_ON_$action"));

        $this->addAddress($address);

        $this->addPlaceholders([$orderId]);

        $this->fillPlaceholders();
    }
}