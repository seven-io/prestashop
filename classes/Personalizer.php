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

class Personalizer
{
    /** @var string $msg */
    private $msg;
    /** @var string|string[] */
    private $transformed;
    /** @var array $placeholders */
    private $placeholders = [];
    /** @var boolean $hasPlaceholder */
    private $hasPlaceholder = false;
    /** @var boolean $hasAddress */
    private $hasAddress = false;

    /**
     * Personalizer constructor.
     * @param string $msg
     */
    public function __construct($msg)
    {
        $this->msg = $msg;
        $this->transformed = $msg;
    }

    /**
     * @param array $placeholders
     * @return $this
     */
    public function addPlaceholders($placeholders)
    {
        foreach ($placeholders as $placeholder) {
            $this->placeholders[] = $placeholder;
        }

        return $this;
    }

    /**
     * @param array $address
     * @return $this
     */
    public function addAddress($address)
    {
        if (!$this->hasAddress) {
            $placeholders = [$address['firstname'], $address['lastname']];

            foreach ($this->placeholders as $placeholder) {
                $placeholders[] = $placeholder;
            }

            $this->placeholders = $placeholders;

            $this->hasAddress = true;
        }

        return $this;
    }

    /** @return array */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /** @return bool */
    public function getHasPlaceholder()
    {
        return $this->hasPlaceholder;
    }

    /** @return $this */
    public function fillPlaceholders()
    {
        $n = 0;

        foreach ($this->placeholders as $replace) {
            $this->replace($n, $replace);

            $n++;
        }

        return $this;
    }

    /**
     * @param $search
     * @param $replace
     * @return $this
     */
    private function replace($search, $replace)
    {
        $search = '{' . $search . '}';

        if (false !== strpos($this->transformed, $search)) {
            if (!$this->hasPlaceholder) {
                $this->hasPlaceholder = true;
            }

            $this->transformed = str_replace($search, $replace, $this->transformed);
        }

        return $this;
    }

    /** @return string */
    public function getMsg()
    {
        return $this->msg;
    }

    /** @return string|string[] */
    public function getTransformed()
    {
        return $this->transformed;
    }
}
