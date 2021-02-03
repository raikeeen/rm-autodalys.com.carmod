<?php

function upgrade_module_1_0_1($module)
{
    $installedSuccessfully = true;
    // installing pickup carrier
    if (Configuration::get("VENIPAK_PICKUP_POINT_CARRIER_ID") === false)
    {
        if (!$module->installExternalCarrierPickupPoint())
        {
            $installedSuccessfully = false;
        }
    }

    // inserting pickup points table
    if ($installedSuccessfully !== false)
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'venipak_pickup_points` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        if (!Db::getInstance()->execute($sql))
        {
            $installedSuccessfully = false;
        }
    }

    //syncing venipak pickup points
    if ($installedSuccessfully !== false)
    {
        if (empty(Configuration::get("VENIPAK_ALLOWED_PICKUP_COUNTRIES")))
            Configuration::updateValue("VENIPAK_ALLOWED_PICKUP_COUNTRIES", "LT");

        try
        {
            $module->updatePickupPoints();
        }
        catch (Exception $ex)
        {

        }
    }


    // altering order info table
    if ($installedSuccessfully !== false && empty(Db::getInstance()->executeS("SHOW COLUMNS FROM `"._DB_PREFIX_."venipak_order_info` LIKE 'id_pickup_point'")))
    {
        $sql = "ALTER TABLE "._DB_PREFIX_."venipak_order_info ADD id_pickup_point int(10) unsigned NOT NULL DEFAULT '0'";

        if (!Db::getInstance()->execute($sql))
        {
            $installedSuccessfully = false;
        }
    }

    // altering order info table
    if ($installedSuccessfully !== false && empty(Db::getInstance()->executeS("SHOW COLUMNS FROM `"._DB_PREFIX_."venipak_cart_comments` LIKE 'id_pickup_point'")))
    {
        $sql = "ALTER TABLE "._DB_PREFIX_."venipak_cart_comments ADD id_pickup_point int(10) unsigned NOT NULL DEFAULT '0'";

        if (!Db::getInstance()->execute($sql))
        {
            $installedSuccessfully = false;
        }
    }

    if ($installedSuccessfully)
        return true;
    else
        return false;
}

