<?php


if (!defined('_PS_VERSION_')) {
    return;
}

class VenipakCarrierFrontModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {

        if (Tools::getValue('venipak_token') != Tools::getToken(false))
        {
            return;
        }

        $carrierId = Tools::getValue('chosenCarrier');

        if ($carrierId != Configuration::get('VENIPAK_CARRIER_ID') && $carrierId != Configuration::get('VENIPAK_PICKUP_POINT_CARRIER_ID')) {
            return;
        }

        if ($carrierId == Configuration::get('VENIPAK_CARRIER_ID'))
        {
            $comments_data = array(
                'delivery_type' => pSQL(trim(Tools::getValue('venipak_delivery_type'))),
                'comment_door_code' => pSQL(trim(Tools::getValue('venipak_door_code'))),
                'comment_office_no' => pSQL(trim(Tools::getValue('venipak_office_no'))),
                'comment_warehous_no' => pSQL(trim(Tools::getValue('venipak_warehouse_no'))),
                'comment_call' => pSQL(trim(Tools::getValue('venipak_comment_call'))),
                'id_pickup_point' => '',
            );
        }
        else
        {
            $comments_data = array(
                'delivery_type' => '',
                'comment_door_code' => '',
                'comment_office_no' => '',
                'comment_warehous_no' => '',
                'comment_call' => '',
                'id_pickup_point' => pSQL(trim(Tools::getValue('venipak_id_pickup_point'))),
            );
        }




        if (Configuration::get('VENIPAK_SHOW_DELIVERY_TYPES') != '1') {
            $comments_data['delivery_type'] = '';
        }
        if (Configuration::get('VENIPAK_SHOW_COMMENT_DOOR_CODE') != '1') {
            $comments_data['comment_door_code'] = '';
        }
        if (Configuration::get('VENIPAK_SHOW_COMMENT_OFFICE_NO') != '1') {
            $comments_data['comment_office_no'] = '';
        }
        if (Configuration::get('VENIPAK_SHOW_COMMENT_WAREHOUSE_NO') != '1') {
            $comments_data['comment_warehous_no'] = '';
        }
        if (Configuration::get('VENIPAK_SHOW_COMMENT_CALL') != '1') {
            $comments_data['comment_call'] = '';
        }


        if (!empty($comments_data)) {

            if (
                (!isset($comments_data['delivery_type']) || empty($comments_data['delivery_type'])) &&
                (!isset($comments_data['comment_door_code']) || empty($comments_data['comment_door_code'])) &&
                (!isset($comments_data['comment_office_no']) || empty($comments_data['comment_office_no'])) &&
                (!isset($comments_data['comment_warehous_no']) || empty($comments_data['comment_warehous_no'])) &&
                (!isset($comments_data['comment_call']) || empty($comments_data['comment_call'])) &&
                (!isset($comments_data['id_pickup_point']) || empty($comments_data['id_pickup_point']))
            )
                return;


            if (!Db::getInstance()->getValue("select 1 from "._DB_PREFIX_."venipak_cart_comments where cart_id = ".pSQL($this->context->cart->id)))
            {
                $comments_data['cart_id'] = pSQL($this->context->cart->id);
                Db::getInstance()->insert('venipak_cart_comments', $comments_data);
            }
            else
            {
                Db::getInstance()->update('venipak_cart_comments', $comments_data, 'cart_id = '.pSQL($this->context->cart->id));
            }

        }

    }
}