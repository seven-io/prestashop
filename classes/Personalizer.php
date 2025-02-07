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
    private bool $hasPlaceholder = false;
    private string $msg;
    private array $placeholders = [];
    /** @var string|string[] */
    private $transformed;

    public function __construct(string $msg, array $placeholders = []) {
        $this->msg = $msg;
        $this->transformed = $msg;
        $this->setPlaceholders($placeholders);
        $this->fillPlaceholders();
    }

    private function setPlaceholders(array $placeholders): void {
        foreach ($placeholders as $k => $v)
            $this->placeholders[$k] = json_decode(json_encode($v), true);
    }

    private function fillPlaceholders(): void {
        $matches = [];
        preg_match_all('{{{[a-z]+\.+[a-z]+}}}', $this->transformed, $matches);
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

            $this->transformed = str_replace($match, $this->placeholders[$o][$k], $this->transformed);
        }
    }

    public function getPlaceholders(): array {
        return $this->placeholders;
    }

    public function hasPlaceholders(): bool {
        return $this->hasPlaceholder;
    }

    /** @return string|string[] */
    public function getTransformed(): array|string {
        return $this->transformed;
    }
}
