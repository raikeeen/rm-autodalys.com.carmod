<?php

if (!defined('_PS_VERSION_'))
    exit;

include_once(_PS_MODULE_DIR_.'/venipakcarrier/classes/VenipakDatabaseHelper.php');

function upgrade_module_1_0_4($module)
{
    $id_default_shop = Configuration::get('PS_SHOP_DEFAULT');

    // ====================================================================================================
    // adding id_shop field in venipak_carrier_addresses

    VenipakDatabaseHelper::addColumn('venipak_carrier_addresses', 'id_shop', 'int(11) AFTER `id`');
    Db::getInstance()->update('venipak_carrier_addresses', ['id_shop' => $id_default_shop]);

    $id_shops = Shop::getCompleteListOfShopsID();
    foreach ($id_shops as $id_shop)
        if ($id_shop != $id_default_shop)
            Db::getInstance()->query("INSERT INTO "._DB_PREFIX_."venipak_carrier_addresses SELECT NULL AS id, ".$id_shop." AS id_shop, address_title, `name`, company, address, city, country, postcode, phone_number, person, is_default, `type` FROM "._DB_PREFIX_."venipak_carrier_addresses WHERE id_shop = ".$id_default_shop);

    VenipakDatabaseHelper::modifyColumn('venipak_carrier_addresses', 'id_shop', 'int(11) NOT NULL AFTER `id`');

    // ====================================================================================================
    // adding id_shop field in venipak_carrier_call

    VenipakDatabaseHelper::addColumn('venipak_carrier_call', 'id_shop', 'int(11) AFTER `call_id`');
    Db::getInstance()->update('venipak_carrier_call', ['id_shop' => $id_default_shop]);
    VenipakDatabaseHelper::modifyColumn('venipak_carrier_call', 'id_shop', 'int(11) NOT NULL AFTER `call_id`');

    // ====================================================================================================
    // adding app_id field in venipak_manifest

    VenipakDatabaseHelper::addColumn('venipak_manifest', 'app_id', 'int(11) AFTER `manifest_no`');

    $app_id = Configuration::get('VENIPAK_API_ID_CODE');
    if ((int)$app_id > 0)
        Db::getInstance()->update('venipak_manifest', ['app_id' => $app_id]);

    VenipakDatabaseHelper::modifyColumn('venipak_manifest', 'app_id', 'int(11) NOT NULL AFTER `manifest_no`');
    // ====================================================================================================
    /*$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'venipak_credentials` (
                  `api_id` int(11) NOT NULL,
                  `username` varchar(100) NOT NULL,
                  `password` varchar(100) NOT NULL,
                  PRIMARY KEY (`api_id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
    Db::getInstance()->query($sql);

    $api_username = Configuration::get('VENIPAK_API_LOGIN');
    $api_password = Configuration::get('VENIPAK_API_PASSWORD');
    $api_app_id = $app_id;

    Db::getInstance()->insert('venipak_credentials', ['app_id' => $api_app_id, 'username' => $api_username, 'password' => $api_password]);*/
    // ====================================================================================================



    Tools::clearCache();

	return true;
}

