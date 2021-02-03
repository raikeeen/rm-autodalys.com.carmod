<?php
/**
 * 2015-2017 elPresta
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
 * @copyright 2015-2017 elPresta
 * @license   Commercial License
 * Property of elPresta
 */

class VenipakHelper
{
    public static function getAddressFields()
    {
        return array(
            'VENIPAK_ADDRESS_TITLE',
            'VENIPAK_ADDRESS_NAME',
            'VENIPAK_ADDRESS_COMPANY',
            'VENIPAK_ADDRESS_ADDRESS',
            'VENIPAK_ADDRESS_CITY',
            'VENIPAK_ADDRESS_COUNTRY',
            'VENIPAK_ADDRESS_POSTCODE',
            'VENIPAK_ADDRESS_PHONE_NUMBER',
            'VENIPAK_ADDRESS_PERSON',
            'VENIPAK_ADDRESS_IS_DEFAULT'
        );
    }

    public static function getConfigFormKeys()
    {
        return array(
            'VENIPAK_API_PASSWORD',
            'VENIPAK_API_LOGIN',
            'VENIPAK_API_ID_CODE',
            'VENIPAK_API_URL',
            'VENIPAK_SHOW_RETURN_DOCS',
            'VENIPAK_SHOW_CHECK_ID',
            'VENIPAK_SHOW_PICKUP',
            'VENIPAK_ORDER_STATUS',
            'VENIPAK_LABELS_FORMAT',
            'VENIPAK_SHOW_DELIVERY_TYPES',
            'VENIPAK_DELIVERY_TYPES',
            'VENIPAK_COD_MODULES',
            'VENIPAK_SHOW_COMMENT_DOOR_CODE',
            'VENIPAK_SHOW_COMMENT_OFFICE_NO',
            'VENIPAK_SHOW_COMMENT_WAREHOUSE_NO',
            'VENIPAK_SHOW_COMMENT_CALL',
            'VENIPAK_LAST_PACK_NO_CHANGE',
            'VENIPAK_LAST_PACK_NO_NUMBER',
            'VENIPAK_ALLOWED_PICKUP_COUNTRIES',
            'VENIPAK_PICKUP_FREESHIP',
            'VENIPAK_ORDER_WEIGHT',
            'VENIPAK_TRACKING_ACTION',
            'VENIPAK_PARCEL_LT',
            'VENIPAK_PARCEL_LV',
            'VENIPAK_PARCEL_EE',
            'VENIPAK_PARCEL_CALCULATE_METHOD',
        );
    }

    public static function getConfigFormValues()
    {
        $values = array();

        foreach (self::getConfigFormKeys() as $key)
        {
            if (in_array($key, array('VENIPAK_DELIVERY_TYPES', 'VENIPAK_COD_MODULES', 'VENIPAK_ALLOWED_PICKUP_COUNTRIES')))
            {
                foreach (explode(',', Configuration::get($key)) as $value)
                    $values[$key . '_' . $value] = true;
            }
            else
                $values[$key] = Configuration::get($key);
        }

        return $values;
    }

    public static function deleteConfigValues()
    {
        foreach (self::getConfigFormKeys() as $key)
            Configuration::deleteByName($key);
        return true;
    }

    public static function checkIfOrderModuleIsCOD($orderModule)
    {
        $cod_modules_from_config = Configuration::get('VENIPAK_COD_MODULES');

        if (!empty($cod_modules_from_config))
        {
            $cod_modules_from_config_array = explode(',', $cod_modules_from_config);
            return in_array($orderModule, $cod_modules_from_config_array);
        }
        else
        {
            //return in_array($orderModule, array('cod', 'cashondelivery'));
            return false;
        }
    }

    public static function getSupportedCountries(){
        $supported_countries = array(
            array('id' => 'LT', 'name'=>'LT'),
            array('id' => 'EE', 'name'=>'EE'),
            array('id' => 'LV', 'name'=>'LV'),
            //array('id' => 'PL', 'name'=>'PL'),
        );
        return $supported_countries;
    }



    public static function getMessage($messageCode, $module)
    {
        $messages =
            array(
                '1001' => $module->l('Venipak carrier must have default sender and warehouse addresses!'),
                '1002' => $module->l('Venipak API settings are not filled!'),
                '2001' => $module->l('Warehouse saved.'),
                '2002' => $module->l('Sender saved.'),
                '3001' => $module->l('Pickup Points were successfully updated!'),
                '3002' => $module->l('Pickup Points update failed! Contact module authors.'),
                '3003' => $module->l('Module was updated. You must click the link to finalize the update: '),
        );

        return $messages[$messageCode];
    }


    public static function getCountryNameByISO($iso)
    {
        switch ($iso){
            case 'LT':
                return "Lithuania";
            case 'LV':
                return "Latvia";
            case 'EE':
                return "Estonia";
            /*case 'PL':
                return "Poland";*/
        }
    }

}

?>
