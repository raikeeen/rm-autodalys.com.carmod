<?php


class VenipakConfig
{
    public static function get($config_name, $id_shop = null, $default = null)
    {
        if (Shop::isFeatureActive())
        {
            if ($id_shop == null || (int)$id_shop == 0)
            {
                $context = Context::getContext();
                if (isset($context->shop->id) && !empty($context->shop->id))
                    $id_shop = $context->shop->id;
                else
                    $id_shop = 1;
            }

            return Configuration::get($config_name, null, null, $id_shop, $default);
        }
        else
        {
            return Configuration::get($config_name, null, null, null, $default);
        }
    }

    public static function updateValue($config_name, $config_value, $id_shop = null)
    {
        if (Shop::isFeatureActive())
        {
            if ($id_shop == null || (int)$id_shop == 0)
            {
                $context = Context::getContext();
                if (isset($context->shop->id) && !empty($context->shop->id))
                    $id_shop = $context->shop->id;
                else
                    $id_shop = 1;
            }

            return Configuration::updateValue($config_name, $config_value, false, null, $id_shop);
        }
        else
        {
            return Configuration::updateValue($config_name, $config_value, false, null, $id_shop);
        }
    }
}