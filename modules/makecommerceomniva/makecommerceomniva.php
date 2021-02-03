<?php

require_once( _PS_MODULE_DIR_ .'makecommerce/makecommerce.php');
if(!class_exists('MakeCommerceCarrierModule'))
    include( _PS_MODULE_DIR_ .'makecommerce/libs/makecommerce_carrier_module.php');

class MakeCommerceOmniva extends MakeCommerceCarrierModule
{
    public function __construct()
    {
        $this->name = 'makecommerceomniva';
        $this->tab = 'shipping_logistics';
        $this->version = '3.1.0';
        $this->author = 'MakeCommerce.net (Maksekeskus AS)';
        $this->need_instance = 0;
        $this->carrier_name = 'OMNIVA';
        $this->carrier_front_name = 'Omniva parcel terminals';

        parent::__construct();

        $this->displayName = $this->l('Omniva parcel terminals');
        $this->description = $this->l('Omniva carrier');
        $this->description = $this->l('Creates carrier that can be further configured under MakeCommerce module settings');
    }

}