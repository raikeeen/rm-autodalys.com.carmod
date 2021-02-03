<?php

class makecommerceparcellabelsModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        if(Tools::getIsset('order_ids')){
            $order_ids = Tools::getValue('order_ids');
            $parcel_labels = $this->getParcelLabel($order_ids);
            die(Tools::jsonEncode($parcel_labels));
        }
    }

    public function getParcelLabel($order_ids){

        foreach($order_ids as $order_id){
            $order = new Order($order_id);
            $makecommerceCarrier = FALSE;

            foreach ($order->getShipping() as $carrier){
                $carrire_obj = new Carrier($carrier['id_carrier']);
                if($carrire_obj->external_module_name == 'makecommerceomniva' || $carrire_obj->external_module_name == 'makecommercesmartpost'){
                    $makecommerceCarrier = TRUE;
                    if($carrire_obj->external_module_name == 'makecommerceomniva'){
                        $carrier_name = 'OMNIVA';
                    }elseif($carrire_obj->external_module_name == 'makecommercesmartpost'){
                        $carrier_name = 'SMARTPOST';
                    }
                    break;
                }
            }

            if($makecommerceCarrier){
                $address = new Address($order->id_address_delivery);
                $customer = new Customer($order->id_customer);
                $makecommerce = new MakeCommerce();

                // Prepare phone number - for Omniva mainly
                $phone_number = (isset($address->phone_mobile) && $address->phone_mobile) ? $address->phone_mobile : $address->phone;
                if( substr( $phone_number, 0, 1) != '+') {
                    switch(Country::getIsoById($address->id_country)) {
                         case 'EE':
                             if (substr($phone_number,0,1) == '5')
                                 $phone_number = '+372' . $phone_number;
                             elseif (substr($phone_number,0,4) == '3725')
                                 $phone_number = '+' . $phone_number;
                             elseif (substr($phone_number,0,5) == '+3725')
                                 ; / break;
                             else
                                 error_log("Recipients phone number $phone_number is (probably) invalid for country Estonia!");
                             break;
                         case 'LV':
                             if (substr($phone_number,0,1) == '2')
                                 $phone_number = '+371' . $phone_number;
                             elseif (substr($phone_number,0,4) == '3712')
                                 $phone_number = '+' . $phone_number;
                             elseif (substr($phone_number,0,5) == '+3712')
                                 ; // break;
                             else
                                 error_log("Recipients phone number $phone_number is (probably) invalid for country Latvia!");
                             break;
                         case 'LT':
                             if (substr($phone_number,0,1) == '6')
                                $phone_number = '+370' . $phone_number;
                             elseif (substr($phone_number,0,4) == '3706')
                                 $phone_number = '+' . $phone_number;
                             elseif (substr($phone_number,0,5) == '+3706')
                                 ; // break;
                             else
                                error_log("Recipients phone number $phone_number is (probably) invalid for country Lithuania!");
                    }
                }        

                $shipment_id = Db::getInstance()->getValue(
                    'SELECT `tracking_number` FROM `'._DB_PREFIX_.'order_carrier` WHERE `id_order`=' . $order->id
                );
                if(empty($shipment_id)){
                    $this->createShipments($order, $carrier_name);
                    $shipment_id = Db::getInstance()->getValue(
                        'SELECT `tracking_number` FROM `'._DB_PREFIX_.'order_carrier` WHERE `id_order`=' . $order->id
                    );
                }
                if(!empty($shipment_id)){
                    $credentials[] = array(
                        'carrier' => $carrier_name,
                        'username' => $makecommerce->getConfig($carrier_name.'_username'),
                        'password' => $makecommerce->getConfig($carrier_name.'_password')
                    );

                    $orders[] = array(
                        'orderId' => 'order '.$order->id,
                        'carrier' => $carrier_name,
                        'destination' => array(
                            'destinationId' => $address->other,
                        ),
                        'recipient' => array(
                            'name' => $address->firstname.' '.$address->lastname,
                            'phone' => $phone_number,
                            'email' => $customer->email
                        ),
                        'sender' => array(
                            'name' => $makecommerce->getConfig($carrier_name.'_sender_name'),
                            'phone' => $makecommerce->getConfig($carrier_name.'_phone'),
                            'email' => $makecommerce->getConfig($carrier_name.'_email'),
                            'street' => $makecommerce->getConfig($carrier_name.'_street'),
                            'city' => $makecommerce->getConfig($carrier_name.'_city'),
                            'country' => $makecommerce->getConfig($carrier_name.'_country'),
                            'postalCode' => $makecommerce->getConfig($carrier_name.'_zip')
                        ),
                        'shipmentId' => $shipment_id,
                    );
                }
            }
        }
        if(isset($credentials) and isset($orders)){
            $request_body = array(
                'credentials' => $credentials,
                'orders' => $orders,
                'printFormat' => 'A4'
            );

            $api = $makecommerce->getApi();
            $label = $api->createLabels($request_body);
            return $label->labelUrl;
        }
    }

    public function createShipments($order, $carrier_name)
    {
        $address = new Address($order->id_address_delivery);
        $customer = new Customer($order->id_customer);
        $makecommerce = new MakeCommerce();

        // Prepare phone number - for Omniva mainly
        $phone_number = (isset($address->phone_mobile) && $address->phone_mobile) ? $address->phone_mobile : $address->phone;
        if( substr( $phone_number, 0, 1) != '+') {
            switch(Country::getIsoById($address->id_country)) {
                case 'EE':
                    if (substr($phone_number,0,1) == '5')
                        $phone_number = '+372' . $phone_number;
                    elseif (substr($phone_number,0,4) == '3725')
                        $phone_number = '+' . $phone_number;
                    elseif (substr($phone_number,0,5) == '+3725')
                         ; // break;
                    else
                        error_log("Recipients phone number $phone_number is (probably) invalid for country Estonia!");
                    break;
                case 'LV':
                    if (substr($phone_number,0,1) == '2')
                        $phone_number = '+371' . $phone_number;
                    elseif (substr($phone_number,0,2) == '3712')
                        $phone_number = '+' . $phone_number;
                    elseif (substr($phone_number,0,5) == '+3712')
                        ; // break;
                    else
                        error_log("Recipients phone number $phone_number is (probably) invalid for country Latvia!");
                    break;
              case 'LT':
                   if (substr($phone_number,0,1) == '6')
                       $phone_number = '+370' . $phone_number;
                   elseif (substr($phone_number,0,4) == '3706')
                       $phone_number = '+' . $phone_number;
                   elseif (substr($phone_number,0,5) == '+3706')
                       ; // break;
                   else
                       error_log("Recipients phone number $phone_number is (probably) invalid for country Lithuania!");
            }
        }
        $credentials = array(
            'carrier' => $carrier_name,
            'username' => $makecommerce->getConfig($carrier_name.'_username'),
            'password' => $makecommerce->getConfig($carrier_name.'_password')
        );
        $orders = array(
            'orderId'=> 'order '.$order->id,
            'carrier'=> $carrier_name,
            'destination' => array(
                'destinationId' => $address->other,
            ),
            'recipient'=> array(
                'name' => $address->firstname.' '.$address->lastname,
                'phone' => $phone_number,
                'email' => $customer->email,
            ),
            'sender' => array(
                'name' => $makecommerce->getConfig($carrier_name.'_sender_name'),
                'phone' => $makecommerce->getConfig($carrier_name.'_phone'),
                'email' => $makecommerce->getConfig($carrier_name.'_email'),
                'postalCode' => $makecommerce->getConfig($carrier_name.'_zip'),
            ),

        );
        $request_body = array(
            'credentials' => array($credentials),
            'orders' => array($orders)
        );

        $api = $makecommerce->getApi();
        $shipments = $api->createShipments($request_body);

        $shipment = json_decode(json_encode($shipments), True);
        if (!empty($shipments) && !isset($shipment[0]['errorMessage']))
        {
            Db::getInstance()->update(
                'order_carrier',
                array('tracking_number' => $shipment[0]['shipmentId']),
                '`id_order`=' . $order->id
            );
        }
    }

    public function display()
    {
        return false;
    }
}
