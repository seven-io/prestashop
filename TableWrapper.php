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

class TableWrapper
{
     const NAME = 'sms77_message';

     static function execute($sql) {
         Db::getInstance()->execute($sql);
     }

     static function create() {
         self::execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . self::NAME . '` (
                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `response` TEXT NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE `SMS77_ID` (`sms77_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
        ');
     }

     static function drop() {
         self::execute('DROP TABLE `' . _DB_PREFIX_ . self::NAME . '`;');
     }

    static function insert($data = []) {
        foreach ($data as $k => $v) {
            $data[$k] = Db::getInstance()->escape($v);
        }

        Db::getInstance()->insert(self::NAME, $data, true);
    }
}