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

class Personalizer {
    /** @var boolean $hasPlaceholder */
    private $hasPlaceholder = false;

    /** @var string $msg */
    private $msg;

    /** @var array $placeholders */
    private $placeholders = [];

    /** @var string|string[] */
    private $transformed;

    /**
     * AbstractPersonalizer constructor.
     * @param string $msg
     * @param array $address
     * @param array $extraPlaceholders
     */
    public function __construct($msg, array $placeholders = []) {
        $this->msg = $msg;
        $this->transformed = $msg;
        $this->setPlaceholders($placeholders);

        //die(var_dump($this->placeholders));

        $this->fillPlaceholders();
    }

    /**
     * @param array $placeholders
     * @return $this
     */
    private function setPlaceholders(array $placeholders) {
        foreach ($placeholders as $k => $v)
            $this->placeholders[$k] = json_decode(json_encode($v), true);

        return $this;
    }

    /** @return $this */
    private function fillPlaceholders() {
        $matches = [];
        preg_match_all('{{{[a-z]+\.+[a-z]+}}}', $this->transformed, $matches);
        //die(var_dump(['transformed' => $this->transformed, 'matches' => $matches]));
        $this->hasPlaceholder = is_array($matches) && !empty($matches[0]);

        PrestaShopLogger::addLog('Seven: $this->hasPlaceholder => ' . $this->hasPlaceholder);

        if ($this->hasPlaceholder) foreach ($matches[0] as $match) {
            $parts = explode('.', $match);
            if (!$parts || empty($parts)) continue;
            $o = str_replace('{{', '', $parts[0]);
            $k = str_replace('}}', '', $parts[1]);

            PrestaShopLogger::addLog('Seven: $o => ' . $o);
            PrestaShopLogger::addLog('Seven: $k => ' . $k);

            if (!isset($this->placeholders[$o][$k])) {
                PrestaShopLogger::addLog('Seven: !isset($this->placeholders[$o][$k]) => ' . $o . ' . ' .$k);
                continue;
            }

            PrestaShopLogger::addLog('Seven: $match => ' . $match);

            $this->transformed = str_replace(
                $match, $this->placeholders[$o][$k], $this->transformed);
        }

        return $this;
    }

    /** @return array */
    public function getPlaceholders() {
        return $this->placeholders;
    }

    /** @return bool */
    public function hasPlaceholders() {
        return $this->hasPlaceholder;
    }

    /** @return string|string[] */
    public function getTransformed() {
        return $this->transformed;
    }
}
