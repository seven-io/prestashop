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
    const BASE_NAME = 'sms77_message';
    const NAME = _DB_PREFIX_ . self::BASE_NAME;
    const ID = 'id_sms77_message';
    const TIMESTAMP = 'timestamp';
    const RESPONSE = 'response';
    const TYPE = 'type';
    const GROUPS = 'groups';
    const COUNTRIES = 'countries';
    const _ACTIVE_AND_NOT_DELETED = 'q.active = 1 AND q.deleted = 0';

    static function addWhereIfSet($where, $field, $array) {
        if (count($array)) {
            $list = implode(',', $array);

            $where .= " AND $field IN ($list)";
        }

        return $where;
    }

    static function addWhereActiveAndNotDeletedIfSet($where, $field, $array) {
        return self::addWhereIfSet(self::_ACTIVE_AND_NOT_DELETED . " $where", $field, $array);
    }

    static function create() {
        self::execute('
            CREATE TABLE IF NOT EXISTS ' . self::NAME . ' (
                ' . self::ID . ' INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                ' . self::TIMESTAMP . ' DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                ' . self::RESPONSE . ' TEXT NOT NULL,
                ' . self::TYPE . ' VARCHAR(12) NOT NULL,
                ' . self::GROUPS . ' VARCHAR(255),
                ' . self::COUNTRIES . ' VARCHAR(255),
                PRIMARY KEY (id_sms77_message)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
        ');
    }

    static function dbQuery($select, $from, $where) {
        $sql = new DbQuery();

        $sql->select($select);
        $sql->from($from, 'q');
        $sql->where($where);

        return Db::getInstance()->executeS($sql);
    }

    static function drop() {
        return self::execute('DROP TABLE ' . self::NAME);
    }

    static function execute($sql) {
        return Db::getInstance()->execute($sql);
    }

    static function get($fields = '*', $where = null) {
        $sql = "SELECT $fields FROM " . self::NAME;

        if (is_array($where)) {
            $sql .= " WHERE $where[0] = $where[1]";
        }

        return Db::getInstance()->getRow($sql);
    }

    static function getActiveCustomerAddressesByGroupsAndCountries($groups, $countries) {
        return array_map(static function ($address) use ($groups) {
            return $address + TableWrapper::getActiveCustomerByGroups($address['id_customer'], $groups);
        }, TableWrapper::getActiveCustomerAddressesByCountries($countries));
    }

    static function getActiveCustomerByGroups($id_customer, $groups) {
        $customer = self::dbQuery(
            '*',
            'customer',
            self::addWhereActiveAndNotDeletedIfSet(
                "AND q.id_customer = $id_customer", 'q.id_default_group', $groups));

        return array_shift($customer);
    }

    static function getActiveCustomerAddressesByCountries($countries) {
        return self::dbQuery(
            'id_country, id_customer, phone, phone_mobile',
            'address',
            self::addWhereActiveAndNotDeletedIfSet(
                "AND q.id_customer != 0 AND q.phone_mobile<>'0000000000'",
                'q.id_country',
                $countries));
    }

    static function insert($data = []) {
        $db = Db::getInstance();

        foreach ($data as $k => $v) {
            $data[$k] = $db->escape($v);
        }

        return $db->insert(self::BASE_NAME, $data, true);
    }
}