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

class TableWrapper {
    const _ACTIVE_AND_NOT_DELETED = 'q.active = 1 AND q.deleted = 0';
    const BASE_NAME = 'seven_message';
    const CONFIG = 'config';
    const COUNTRIES = 'countries';
    const GROUPS = 'groups';
    const ID = 'id_seven_message';
    const NAME = _DB_PREFIX_ . self::BASE_NAME;
    const RESPONSE = 'response';
    const TIMESTAMP = 'timestamp';
    const TYPE = 'type';

    public static function create() {
        self::execute('
            CREATE TABLE IF NOT EXISTS ' . self::NAME . ' (
                `' . self::ID . '` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `' . self::TIMESTAMP . '` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `' . self::RESPONSE . '` TEXT NOT NULL,
                `' . self::TYPE . '` VARCHAR(12) NOT NULL,
                `' . self::GROUPS . '` VARCHAR(255),
                `' . self::COUNTRIES . '` VARCHAR(255),
                `' . self::CONFIG . '` TEXT,
                PRIMARY KEY (id_seven_message)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
        ');
    }

    /**
     * @param string $sql
     * @return bool
     */
    public static function execute($sql) {
        return Db::getInstance()->execute($sql);
    }

    /** @return bool */
    public static function drop() {
        return self::execute('DROP TABLE ' . self::NAME);
    }

    /**
     * @param string $fields
     * @param string|null $where
     * @return array|bool|object|null
     */
    public static function get($fields = '*', $where = null) {
        $sql = "SELECT $fields FROM " . self::NAME;

        if (is_array($where)) $sql .= " WHERE $where[0] = $where[1]";

        return Db::getInstance()->getRow($sql);
    }

    /**
     * @param array $groups
     * @param array $countries
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public static function getActiveCustomerAddressesByGroupsAndCountries($groups, $countries) {
        return array_map(static function ($address) use ($groups) {
            $customerId = $address['id_customer'];
            $customer = TableWrapper::getActiveCustomerByGroups($customerId, $groups);
            $customer = $customer ?: [];

            return $address + $customer;
        }, self::getActiveCustomerAddressesByCountries($countries));
    }

    /**
     * @param int $id_customer
     * @param array $groups
     * @return mixed
     * @throws PrestaShopDatabaseException
     */
    public static function getActiveCustomerByGroups($id_customer, $groups) {
        $customer = self::dbQuery('*', 'customer',
            self::addWhereActiveAndNotDeletedIfSet(
                "AND q.id_customer = $id_customer",
                'q.id_default_group',
                $groups
            )
        );

        return array_shift($customer);
    }

    /**
     * @param string $select
     * @param string $from
     * @param string $where
     * @return array|bool|mysqli_result|PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     */
    public static function dbQuery($select, $from, $where) {
        $sql = new DbQuery;

        $sql->select($select);
        $sql->from($from, 'q');
        $sql->where($where);

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param string $where
     * @param string $field
     * @param array $array
     * @return string
     */
    public static function addWhereActiveAndNotDeletedIfSet($where, $field, $array) {
        $where = self::_ACTIVE_AND_NOT_DELETED . " $where";

        if (count($array)) {
            $list = implode(',', $array);

            $where .= " AND $field IN ($list)";
        }

        return $where;
    }

    /**
     * @param array $countries
     * @return array|bool|mysqli_result|PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     */
    public static function getActiveCustomerAddressesByCountries($countries) {
        return self::dbQuery('id_country, id_customer, phone, phone_mobile', 'address',
            self::addWhereActiveAndNotDeletedIfSet(
                "AND q.id_customer != 0 AND q.phone_mobile<>'0000000000'",
                'q.id_country',
                $countries
            )
        );
    }

    /**
     * @param array $data
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public static function insert($data = []) {
        $db = Db::getInstance();

        foreach ($data as $k => $v) $data[$k] = $db->escape($v);

        return $db->insert(self::BASE_NAME, $data, true);
    }
}
