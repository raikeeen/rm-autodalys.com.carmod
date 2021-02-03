<?php
/*
 */
class VenipakAPI {

    private $apiBaseUrl = 'https://go.venipak.lt';

    private $apiUrl = '/import/send.php';
    private $printPacksUrl = '/ws/print_label';
    private $printManifestUrl = '/ws/print_list';
    private $pickupPointsUrl = '/ws/get_pickup_points';

    private $apiUrlSuffix = '/import/send.php';
    private $printPacksUrlSuffix = '/ws/print_label.php';
    private $printManifestUrlSuffix = '/ws/print_list.php';
    private $pickupPointsUrlSuffix = '/ws/get_pickup_points.php';

    public function __construct() {
        //set base
        $apiBaseUrl = Configuration::get('VENIPAK_API_URL');

        if (!empty($apiBaseUrl))
            $this->apiBaseUrl = $apiBaseUrl;

        $this->apiUrl = $this->apiBaseUrl.$this->apiUrlSuffix;
        $this->printPacksUrl = $this->apiBaseUrl.$this->printPacksUrlSuffix;
        $this->printManifestUrl = $this->apiBaseUrl.$this->printManifestUrlSuffix;
        $this->pickupPointsUrl = $this->apiBaseUrl.$this->pickupPointsUrlSuffix;

    }

    public function sendShipmentXML($orderIds, $id_shop = 1){
        $db = Db::getInstance();

        $allOrders = array();
        foreach($orderIds as $id){

            $sql = 'SELECT *, venipakorders.manifest_no, packs.weight as packweight, venipakorders.packs as packs_count, venipakorders.cod_amount, address.firstname, address.lastname, address1, address2, postcode, city, phone, phone_mobile, dni, country.iso_code AS countrycode '
                . 'FROM ' . _DB_PREFIX_ . 'venipak_order_info AS venipakorders '
                . 'INNER JOIN ' . _DB_PREFIX_ . 'venipak_order_pack AS packs ON venipakorders.order_id=packs.order_id '
                . 'INNER JOIN ' . _DB_PREFIX_ . 'orders AS realorders ON venipakorders.order_id=realorders.id_order '
                . 'INNER JOIN ' . _DB_PREFIX_ . 'address AS address ON realorders.id_address_delivery=address.id_address '
                . 'INNER JOIN ' . _DB_PREFIX_ . 'country AS country ON (address.id_country=country.id_country)'
                . 'WHERE venipakorders.order_id = "'.$id.'"';

            $orderData = $db->executeS($sql);
            if(!empty($orderData)){
                foreach($orderData as $d){

                    $pickup_point = false;
                    if (!empty($d['id_pickup_point']) && $d['id_pickup_point'] != 0)
                    {
                        $pickup_point = Db::getInstance()->getRow("SELECT * FROM "._DB_PREFIX_."venipak_pickup_points WHERE id = ".intval($d['id_pickup_point']));
                    }

                    $manifestNo = $d['manifest_no'];
                    $order = array();
                    $order['packs_count'] = $d['packs_count'];
                    $order['cod'] = $d['cod_amount'];
                    $order['sender_id'] = $d['warehouse_id'];
                    $order['delivery_time'] = $d['delivery_time'];
                    $order['return_doc'] = $d['return_docs'];
                    $order['check_doc'] = $d['check_docs'];
                    $order['dlr_code'] = $d['dlr_code'];
                    $order['consignor_id'] = $d['show_sender'];
                    $order['comment_door_code'] = $d['comment_door_code'];
                    $order['comment_office_no'] = $d['comment_office_no'];
                    $order['comment_warehous_no'] = $d['comment_warehous_no'];
                    $order['comment_call'] = $d['comment_call'];
                    $order['id_pickup_point'] = $d['id_pickup_point'];

                    // Postcode only numbers to venipak;
                    $d['postcode'] = preg_replace('/[^0-9]/', '', $d['postcode']);

                    if (!empty($pickup_point))
                    {
                        $order['consignee'] = array(
                            'name' => $pickup_point['name'],
                            'company_code' => $pickup_point['code'],
                            'country' => $pickup_point['country'],
                            'city' => $pickup_point['city'],
                            'address' => $pickup_point['address'],
                            'post_code' => $pickup_point['zip']
                        );
                    }
                    else
                    {
                        $order['consignee'] = array(
                            'name' => $d['firstname'] . " " . $d['lastname'],
                            'company_code' => $d['dni'],
                            'country' => $d['countrycode'],
                            'city' => $d['city'],
                            'address' => $d['address1'] ." ". $d['address2'],
                            'post_code' => preg_replace('/[^0-9.]+/', '', $d['postcode']),
                        );
                    }

                    $order['consignee'] = array_merge($order['consignee'],
                        array(
                            'contact_person' => $d['firstname'] . " " . $d['lastname'],
                            'contact_tel' => ($d['phone'] !='' ? $d['phone'] : $d['phone_mobile'])
                        )
                    );


                    $addPack = array(
                        'pack_no' => 'V'.str_pad($d['api_id'], 5, "0", STR_PAD_LEFT).'E'.str_pad($d['pack_no'], 7, "0", STR_PAD_LEFT),
                        'weight' => $d['packweight'],

                    );

                    if(!isset($allOrders[$manifestNo][$d['order_id']]))
                    {
                        $allOrders[$manifestNo][$d['order_id']] = $order;
                    }

                    $allOrders[$manifestNo][$d['order_id']]['packs'][] = $addPack;


                }
            }
        }
        $warehousesOriginal = VenipakDatabaseHelper::getWarehouses($id_shop);
        $warehouses = array();
        //reformat
        if(!empty($warehousesOriginal)){
            foreach($warehousesOriginal as $wh){
                $warehouses[$wh['id']] = $wh;
            }
        }else{
            return false;
        }

        $consignorsOriginal = VenipakDatabaseHelper::getSenders($id_shop);
        $consignors = array();
        //reformat
        if(!empty($consignorsOriginal)){
            foreach($consignorsOriginal as $co){
                $consignors[$co['id']] = $co;
            }
        }

        $xml = new SimpleXMLElement('<description/>');
        $xml->addAttribute('type','1');

        foreach($allOrders as $manifestNo => $orders){
            $manifest = $xml->addChild('manifest');
            $manifest->addAttribute('title',$manifestNo);

            foreach($orders as $order_id => $order){

                $shipment = $manifest->addChild('shipment');


                if(!empty($order['consignor_id']) && $order['consignor_id']!='0' && $order['consignor_id']!=''){
                    $consignor = $shipment->addChild('consignor');
                    $consignor->addChild('name', $consignors[$order['consignor_id']]['name']);
                    $consignor->addChild('company_code', $consignors[$order['consignor_id']]['company']);
                    $consignor->addChild('country', $consignors[$order['consignor_id']]['country']);
                    $consignor->addChild('city', $consignors[$order['consignor_id']]['city']);
                    $consignor->addChild('address', $consignors[$order['consignor_id']]['address']);
                    $consignor->addChild('post_code', preg_replace('/[^0-9.]+/', '', $consignors[$order['consignor_id']]['postcode']));
                    $consignor->addChild('contact_person', $consignors[$order['consignor_id']]['person']);
                    $consignor->addChild('contact_tel', $consignors[$order['consignor_id']]['phone_number']);
                }


                $sender = $shipment->addChild('sender');
                $sender->addChild('name', $warehouses[$order['sender_id']]['name']);
                $sender->addChild('company_code', $warehouses[$order['sender_id']]['company']);
                $sender->addChild('country', $warehouses[$order['sender_id']]['country']);
                $sender->addChild('city', $warehouses[$order['sender_id']]['city']);
                $sender->addChild('address', $warehouses[$order['sender_id']]['address']);
                $sender->addChild('post_code', preg_replace('/[^0-9.]+/', '', $warehouses[$order['sender_id']]['postcode']));
                $sender->addChild('contact_person', $warehouses[$order['sender_id']]['person']);
                $sender->addChild('contact_tel', $warehouses[$order['sender_id']]['phone_number']);

                //add consignee
                $consignee = $shipment->addChild('consignee');
                $consigneeData = $order['consignee'];
                $consignee->addChild('name', $consigneeData['name']);
                $consignee->addChild('company_code', $consigneeData['company_code']); //assume that DNI is for company code
                $consignee->addChild('country', $consigneeData['country']);
                $consignee->addChild('city', $consigneeData['city']);
                $consignee->addChild('address', $consigneeData['address']);
                $consignee->addChild('post_code', $consigneeData['post_code']);
                $consignee->addChild('contact_person', $consigneeData['contact_person']);
                $consignee->addChild('contact_tel', $consigneeData['contact_tel']);

                //$shipment->addChild('receiver');



                $attribute = $shipment->addChild('attribute');
                $attribute->addChild('delivery_type',$order['delivery_time']);
                $attribute->addChild('return_doc', $order['return_doc']);
                $attribute->addChild('cod',$order['cod']);
                $attribute->addChild('cod_type','EUR');
                $attribute->addChild('dlr_code', $order['dlr_code']);

                if($order['comment_door_code']){
                    $attribute->addChild('comment_door_code',$order['comment_door_code']);
                }
                if($order['comment_office_no']){
                    $attribute->addChild('comment_office_no',$order['comment_office_no']);
                }
                if($order['comment_warehous_no']){
                    $attribute->addChild('comment_warehous_no',$order['comment_warehous_no']);
                }
                if($order['comment_call']){
                    $attribute->addChild('comment_call',$order['comment_call']);
                }



                foreach($order['packs'] as $p){
                    $pack = $shipment->addChild('pack');
                    $pack->addChild('pack_no', $p['pack_no']);
                    $pack->addChild('weight', $p['weight']);
                }

            }
        }

        $xmlText = $xml->asXML();
        //file_put_contents(_PS_CACHE_DIR_.'test_log.txt', $xmlText);

        //header('Content-Type: application/xml; charset=utf-8');


        if (Shop::isFeatureActive()) {
            // Load order object to identify id_shop;
            $orderObj = new Order($order_id);
            $venipak_api_login = Configuration::get('VENIPAK_API_LOGIN', null, null, $orderObj->id_shop);
            $venipak_api_password = Configuration::get('VENIPAK_API_PASSWORD', null, null, $orderObj->id_shop);
        } else {
            $venipak_api_login = Configuration::get('VENIPAK_API_LOGIN');
            $venipak_api_password = Configuration::get('VENIPAK_API_PASSWORD');
        }

        $data = array('user'=> $venipak_api_login, 'pass' => $venipak_api_password, 'xml_text' => $xmlText);


        $result = $this->makeCurlCall($data);

        return $this->parseReturnXml($result);


    }

    /**
     *
     * @param array $orderIds
     */
    public function getLabelsForOrders($orderIds)
    {

        if(empty($orderIds) || !is_array($orderIds)){ return false; }
        //fetch packs for each order
        $packNos = array();
        $db = Db::getInstance();
        $packNos = $db->executeS('SELECT api_id, pack_no, manifest_no FROM ' . _DB_PREFIX_ . 'venipak_order_pack AS packs INNER JOIN ' . _DB_PREFIX_ . 'venipak_order_info AS ord ON packs.order_id=ord.order_id  WHERE packs.order_id IN ('.implode(',',$orderIds).') ORDER BY packs.order_id ASC');

        $packsSorted = array();
        if(!empty($packNos))
        {
            //sort all packs by manifest no
            foreach($packNos as $d)
            {
                $generatedPackNo = 'V'.str_pad($d['api_id'], 5, "0", STR_PAD_LEFT).'E'.str_pad($d['pack_no'], 7, "0", STR_PAD_LEFT);
                $packsSorted[$d['manifest_no']][] = $generatedPackNo;
            }

            $returnType = 'pdf';
            $returnContent = NULL;
            if(count($packsSorted) > 1)
            {
                //will put into zip
                $zip = new ZipArchive;
                $res = $zip->open(_PS_CACHE_DIR_.'tcpdf/venipak-labels.zip', ZipArchive::OVERWRITE);
                $returnType = 'zip';
                if ($res !== TRUE)
                {
                    return false;
                }
            }

            $result = '';

            foreach($packsSorted as $manifest => $pack_no)
            {
                $data = array(
                    'user' => Configuration::get('VENIPAK_API_LOGIN'),
                    'pass' => Configuration::get('VENIPAK_API_PASSWORD'),
                    'pack_no' => $pack_no,
                    'code' => $manifest,
                    'type' => Configuration::get('VENIPAK_LABELS_FORMAT')
                );

                $result = $this->makeCurlCall($data, $this->printPacksUrl);
                if(count($packsSorted) > 1)
                {
                    //will put into zip
                    $zip->addFromString($manifest.'.pdf', $result);
                }
            }
        }
        if(count($packsSorted)>1){

            $returnContent = $zip->filename;
            $zip->close();
        }else{
            $returnContent = $result;
        }

        return array('type'=>$returnType, 'result' => $returnContent);
    }

    public function getManifestFile($manifestNo)
    {
        $data = array('user' => Configuration::get('VENIPAK_API_LOGIN'), 'pass' => Configuration::get('VENIPAK_API_PASSWORD'), 'code' => $manifestNo);
        $result = $this->makeCurlCall($data, $this->printManifestUrl);
        return $result;
    }

    private function makeCurlCall($data, $url=''){

        if($url==''){
            $url = $this->apiUrl;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        if (!empty($data))
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        // execute the request
        $output = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($output, 0, $header_size);
        $body = substr($output, $header_size);

        // close curl resource to free up system resources
        curl_close($ch);
        return $body;
    }

    private function parseReturnXml($result){
        $parsed = simplexml_load_string($result);
        if(is_bool($parsed)){ return array('error'=>'Error occured while reading response from webservice.'); }
        if(strval($parsed->attributes()->type[0])=='ok'){
            return array('success'=>'1');
        }else{
            //we have error
            $errors = [];
            foreach($parsed->error as $err){
                $errorText = '';
                if($err->attributes()->code!=""){
                    $errorText .= $err->attributes()->code.': ';
                }
                $errorText .= $err->text.' ';
                $errors[] = $errorText;
            }
            return array('error'=>$errors);
        }
    }

    public function callCourier($warehouseId, $params){
        //fetch warehouse
        $warehouse = VenipakDatabaseHelper::getWarehouse($warehouseId);
        if($warehouse == false)
            return false;

        $xml = new SimpleXMLElement('<description/>');
        $xml->addAttribute('type','3');
        $sender = $xml->addChild("sender");
        $sender->addChild("name", $warehouse['name']);
        $sender->addChild("company_code",$warehouse['company']);
        $sender->addChild("country", $warehouse['country']);
        $sender->addChild('city', $warehouse['city']);
        $sender->addChild('address', $warehouse['address']);
        $sender->addChild('post_code',  preg_replace('/[^0-9.]+/', '', $warehouse['postcode']));
        $sender->addChild('contact_person', $warehouse['person']);
        $sender->addChild('contact_tel', $warehouse['phone_number']);
        $weight = $xml->addChild('weight',$params['weight']);
        $dateY = $xml->addChild('date_y', $params['date_y']);
        $dateM = $xml->addChild('date_m', $params['date_m']);
        $dateD = $xml->addChild('date_d', $params['date_d']);
        $hourFrom = $xml->addChild('hour_from', $params['hour_from']);
        $minFrom = $xml->addChild('min_from', $params['min_from']);
        $hourTo = $xml->addChild('hour_to', $params['hour_to']);
        $minTo = $xml->addChild('min_to', $params['min_to']);
        $comment = $xml->addChild('comment', $params['comment']);

        $xmlText = $xml->asXML();


        // Get ID shop by warehouse ID;
        $sql = 'SELECT id_shop FROM '._DB_PREFIX_.'venipak_carrier_addresses WHERE id = '.(int)$warehouseId;
        $warehouse_id_shop = (int)Db::getInstance()->getValue($sql);

        // Get API details by SHOP id;
        if (Shop::isFeatureActive()) {
            $venipak_api_login = Configuration::get('VENIPAK_API_LOGIN', null, null, $warehouse_id_shop);
            $venipak_api_password = Configuration::get('VENIPAK_API_PASSWORD', null, null, $warehouse_id_shop);
        } else {
            $venipak_api_login = Configuration::get('VENIPAK_API_LOGIN');
            $venipak_api_password = Configuration::get('VENIPAK_API_PASSWORD');
        }

        $data = array('user'=>$venipak_api_login, 'pass' => $venipak_api_password, 'xml_text' => $xmlText);

        $result = $this->makeCurlCall($data);

        if($result!=''){
            return $this->parseReturnXml($result);
        }
    }

    public function getPickupPoints()
    {
        $result = $this->makeCurlCall('', $this->pickupPointsUrl);
        return $result;
    }


}
