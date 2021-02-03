<?php
/**
 * 2018 elPresta
 *
 * NOTICE OF LICENSE
 *
 * This source file is a property of elPresta.
 * Redistribution or republication of any part of this code is prohibited.
 * A single module license strictly limits the usage of this module
 * to one (1) shop / domain / website.
 * If you want to use this module in more than one shop / domain / website
 * you must purchase additional licenses.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * this module to newer versions in the future.
 *
 * @author    elPresta <info@elPresta.eu>
 * @copyright 2018 elPresta
 * @license   Commercial License
 * Property of elPresta
 */

class VenipakDatabaseHelper
{
    private static $tables = array(
        'venipak_carrier_addresses' => 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'venipak_carrier_addresses` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `id_shop` INT(11) NOT NULL,
                  `address_title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
                  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
                  `company` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
                  `address` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
                  `city` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
                  `country` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
                  `postcode` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
                  `phone_number` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
                  `person` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
                  `is_default` tinyint(1) NOT NULL,
                  `type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',

        'venipak_carrier_call' => 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'venipak_carrier_call` (
                  `call_id` int(11) NOT NULL AUTO_INCREMENT,
                  `id_shop` int(11) NOT NULL,
                  `warehouse_id` int(11) NOT NULL,
                  `call_date` datetime NOT NULL,
                  `call_data` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                  PRIMARY KEY (`call_id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',

        'venipak_manifest' => 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'venipak_manifest` (
                  `manifest_no` varchar(14) NOT NULL,
                  `app_id` int(11) NOT NULL,
                  `manifest_date` date NOT NULL,
                  `warehouse_id` int(11) NOT NULL,
                  `status` tinyint(2) NOT NULL DEFAULT "1",
                  PRIMARY KEY (`manifest_no`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',

        'venipak_order_info' => 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'venipak_order_info` (
                  `order_id` int(11) NOT NULL,
                  `packs` int(10) unsigned NOT NULL,
                  `weight` double(10,2) unsigned NOT NULL,
                  `is_cod` tinyint(1) NOT NULL,
                  `cod_amount` decimal(10,2) NOT NULL,
                  `warehouse_id` int(10) unsigned NOT NULL,
                  `delivery_time` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
                  `return_docs` tinyint(1) NOT NULL,
                  `check_docs` tinyint(1) NOT NULL,
                  `show_sender` int(11) DEFAULT NULL,
                  `manifest_no` varchar(14) COLLATE utf8_unicode_ci NOT NULL,
                  `dlr_code` varchar(14) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `comment_door_code` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `comment_office_no` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `comment_warehous_no` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `comment_call` tinyint(1) DEFAULT NULL,
                  `id_pickup_point` int(10) unsigned NOT NULL DEFAULT "0",
                  PRIMARY KEY (`order_id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;',

        'venipak_order_pack' => 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'venipak_order_pack` (
                  `pack_no` int(11) NOT NULL,
                  `api_id` int(11) NOT NULL,
                  `order_id` int(10) unsigned NOT NULL,
                  `weight` decimal(8,3) NOT NULL,
                  `sent` tinyint(1) NOT NULL DEFAULT "0",
                  PRIMARY KEY (`pack_no`,`api_id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',

        /*'venipak_credentials' => 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'venipak_credentials` (
                  `api_id` int(11) NOT NULL,
                  `username` varchar(100) NOT NULL,
                  `password` varchar(100) NOT NULL,
                  PRIMARY KEY (`api_id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',*/

        'venipak_cart_comments' => 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'venipak_cart_comments` (
                  `cart_id` int(10) unsigned NOT NULL,
                  `delivery_type` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
                  `comment_door_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
                  `comment_office_no` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
                  `comment_warehous_no` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
                  `comment_call` tinyint(1) NOT NULL DEFAULT "0",
                  `id_pickup_point` int(10) unsigned NOT NULL DEFAULT "0",
                  PRIMARY KEY (`cart_id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',

        'venipak_pickup_points' => 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'venipak_pickup_points` (
                  `id` int(11) NOT NULL,
                  `code` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
                  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
                  `address` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
                  `city` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
                  `zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
                  `country` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
                  `working_hours` text COLLATE utf8_unicode_ci NOT NULL,
                  `contact_t` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
                  `lat` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
                  `lng` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
                  `pick_up_enabled` int(1) NOT NULL,
                  `cod_enabled` int(1) NOT NULL,
                  `ldg_enabled` int(1) NOT NULL,
                  `pickup_point_enabled` int(1) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `IDX_code` (`code`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;',
    );


    // Install database tables
    public static function installTables() {

        $sql = self::$tables;

        foreach ($sql as $query) {
            try {
               $res_query = Db::getInstance()->execute($query);

                if ($res_query === false) {
                    return false;
                }

            } catch(Exception $e) {
              return false;
            }
        }

        return true;
    }

    /*
     * Uninstall database schema
     */
    public static function uninstallTables() {

        $sql[] = 'DROP TABLE `'._DB_PREFIX_.'venipak_carrier_addresses`';
        $sql[] = 'DROP TABLE `'._DB_PREFIX_.'venipak_carrier_call`';
        $sql[] = 'DROP TABLE `'._DB_PREFIX_.'venipak_manifest`';
        $sql[] = 'DROP TABLE `'._DB_PREFIX_.'venipak_order_info`';
        $sql[] = 'DROP TABLE `'._DB_PREFIX_.'venipak_order_pack`';
        $sql[] = 'DROP TABLE `'._DB_PREFIX_.'venipak_cart_comments`';

        foreach ($sql as $query) {
            try {
                $res_query = Db::getInstance()->execute($query);
            } catch(Exception $e) {
            }
        }

        return true;
    }

    public static function checkTableIfExists($tableName)
    {
        if (array_key_exists($tableName, self::$tables))
        {
            return Db::getInstance()->execute(self::$tables[$tableName]) === false ? false : true;
        }
        else
            return false;
    }

    public static function addColumn($table, $name, $type)
    {
        try
        {
            $return = Db::getInstance()->execute('ALTER TABLE  `'._DB_PREFIX_.''.$table.'` ADD `'.$name.'` '.$type);
        } catch(Exception $e)
        {
            return true;
        }
        return true;
    }

    public static function modifyColumn($table, $name, $type)
    {
        try
        {
            $return = Db::getInstance()->execute('ALTER TABLE  `'._DB_PREFIX_.''.$table.'` MODIFY `'.$name.'` '.$type);
        } catch(Exception $e)
        {
            return true;
        }
        return true;
    }

    public static function getWarehouses($id_shop = 1){
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'venipak_carrier_addresses WHERE id_shop = '.$id_shop.' AND `type`=\'warehouse\' ORDER BY is_default DESC, id ASC';
        return $db->executeS($sql);
    }

    public static function getWarehouse($warehouseId){
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'venipak_carrier_addresses WHERE `type`=\'warehouse\' AND id = '.(int)$warehouseId.' ORDER BY is_default DESC, id ASC';
        return $db->getRow($sql);
    }

    public static function getDefaultWarehouse($id_shop = 1){
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'venipak_carrier_addresses WHERE `type`=\'warehouse\' AND is_default = 1 AND id_shop = '.$id_shop;
        return $db->getRow($sql);
    }

    public static function getSenders($id_shop = 1){
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'venipak_carrier_addresses WHERE id_shop = '.$id_shop.' AND `type`=\'sender\' ORDER BY is_default DESC, id ASC';
        return $db->executeS($sql);
    }

    public static function getAddress($id){
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'venipak_carrier_addresses WHERE id='.(int)$id;
        return $db->getRow($sql);
    }

    public static function hasDefaultAddress($type, $id_shop = 1){
        $db = Db::getInstance();
        $sql = 'SELECT 1 FROM ' . _DB_PREFIX_ . 'venipak_carrier_addresses WHERE `type`=\''.$type.'\' AND id_shop = '.$id_shop.' AND is_default = 1';
        return $db->getValue($sql);
    }

    public static function deleteAddress($id){
        $db = Db::getInstance();
        $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'venipak_carrier_addresses WHERE id='.(int)$id;
        return $db->execute($sql);
    }

    public static function getCarrierCallByDate($warehouseId, $date, $id_shop = 1){
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'venipak_carrier_call WHERE id_shop = '.$id_shop.' AND date(call_date)="'.pSQL(strval($date)).'" AND warehouse_id='.(int)$warehouseId;
        return Db::getInstance()->getRow($sql);
    }

    public static function getCarrierCallFromDate($warehouseId, $date, $id_shop = 1){
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'venipak_carrier_call WHERE id_shop = '.$id_shop.' AND date(call_date)>="'.pSQL(strval($date)).'" AND warehouse_id='.(int)$warehouseId;
        return Db::getInstance()->executeS($sql);
    }

    public static function getCartComments($cartId){
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'venipak_cart_comments WHERE cart_id="'.$cartId.'"';
        return $db->getRow($sql);
    }

    public static function getPickupPointData($id_pickup_point){
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'venipak_pickup_points WHERE id = '.$id_pickup_point;
        return $db->getRow($sql);
    }

    public static function getCarrierReference($id_carrier) {
        $sql = 'SELECT id_reference FROM '._DB_PREFIX_.'carrier WHERE id_carrier = '.(int)$id_carrier;
        return Db::getInstance()->getValue($sql);
    }

}

?>
