<?php
/**
 *
 */
class VenipakOrder {
    public function __construct() {

    }

    //for popup
    public function getOrders($warehouseId, $languageId, $status, $limitManifestsPage=0, $limitManifestsCount=0, $getTotal=false){
        $db = Db::getInstance();
        $return = array();
        $openSql = ' AND status = '.$status;

        $limitSql = '';
        if($limitManifestsPage && $limitManifestsCount){
            $limitSql = 'LIMIT '.(int)(($limitManifestsPage - 1) * $limitManifestsCount).', '.(int)($limitManifestsCount);
        }


        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'venipak_manifest AS manifests WHERE warehouse_id = \''.(int)$warehouseId.'\' AND status = '.$status.' ORDER BY manifest_date DESC '.$limitSql;
        $results = $db->executeS($sql);
        if(!empty($results)){

            foreach($results as $r){
                $sqlOrders = 'SELECT venipakorders.*, realorders.date_upd, realorders.reference, realorders.total_paid_tax_incl, COUNT(pack_no) AS packscount, country.name AS countryname, realorders.payment, address.firstname, address.lastname, carrier.tracking_number '
                        . 'FROM ' . _DB_PREFIX_ . 'venipak_order_info AS venipakorders '
                        . 'INNER JOIN ' . _DB_PREFIX_ . 'venipak_order_pack AS packs ON venipakorders.order_id=packs.order_id '
                        . 'INNER JOIN ' . _DB_PREFIX_ . 'orders AS realorders ON venipakorders.order_id=realorders.id_order '
                        . 'INNER JOIN ' . _DB_PREFIX_ . 'order_carrier AS carrier ON venipakorders.order_id=carrier.id_order '
                        . 'INNER JOIN ' . _DB_PREFIX_ . 'address AS address ON realorders.id_address_delivery=address.id_address '
                        . 'INNER JOIN ' . _DB_PREFIX_ . 'country_lang AS country ON (address.id_country=country.id_country AND country.id_lang="'.(int)$languageId.'")'
                        . 'WHERE warehouse_id = "'.(int)$warehouseId.'" AND venipakorders.manifest_no = "'.$r['manifest_no'].'" GROUP BY venipakorders.order_id';

                $ordersResult = $db->executeS($sqlOrders);
                $return[$r['manifest_no']]['manifest'] = $r;
                //round prices
                $packets_count = 0;
                foreach($ordersResult as $key => $r){
                    $ordersResult[$key]['total_paid_tax_incl'] = number_format($r['total_paid_tax_incl'], _PS_PRICE_COMPUTE_PRECISION_, '.', '');
                    $packets_count += intval($ordersResult[$key]['packscount']);
                }
                $return[$r['manifest_no']]['orders'] = $ordersResult;
                $return[$r['manifest_no']]['manifest']['orders_count'] = count($ordersResult);
                $return[$r['manifest_no']]['manifest']['packets_count'] = $packets_count;

                if (count($ordersResult) == 0)
                    unset($return[$r['manifest_no']]);

            }
        }

        return $return;
    }


    public function saveVenipakOrder($order = null){
        //check if entry with such order ID already exists
        $data = $_POST;

        if(empty($_POST)){
            return false;
        }
        $venipakCarrier = new VenipakCarrier;

        $orderId = Tools::getValue('order_id', NULL);
        $packs = Tools::getValue('packs', NULL);
        $weight = Tools::getValue('weight', NULL);
        $isCod = Tools::getValue('is_cod', NULL);
        $codAmount = Tools::getValue('cod_amount', NULL);
        $warehouseId = Tools::getValue('warehouse_id', NULL);
        $deliveryTime = Tools::getValue('delivery_time', NULL);
        $returnDocs = Tools::getValue('return_docs', NULL);
        $checkDocs = Tools::getValue('check_docs', NULL);
        $showSender = Tools::getValue('show_sender', NULL);
        $manifestDate = Tools::getValue('manifest_date', NULL);

        $commentDoorCode = Tools::getValue('comment_door_code', NULL);
        $commentOfficeNo = Tools::getValue('comment_office_no', NULL);
        $commentWarehousNo = Tools::getValue('comment_warehous_no', NULL);
        $commentCall = Tools::getValue('comment_call', NULL);
        $pickupPointCode = Tools::getValue('id_pickup_point', NULL);

        //defaults for optional fields
        if ($isCod == NULL)
            $isCod = '0';

        if ($returnDocs == NULL)
            $returnDocs = '0';

        if ($checkDocs == NULL)
            $checkDocs = '0';

        if ($weight != NULL)
            $weight = str_replace(',', '.', $weight);

        //try to load order and check for real COD values
        if ($order == null || (is_object($order) && strtolower(get_class($order)) != 'order'))
            $order = new Order((int)$orderId);

        if(!$order){
            return array('error'=>'Invalid order.');
        }

        VenipakCarrier::checkForClass('VenipakHelper');
        if(VenipakHelper::checkIfOrderModuleIsCOD($order->module)){
            $isCod = '1';
            $codAmount = $order->total_paid_tax_incl;
        }

        if ($isCod == "1" && !empty($pickupPointCode) && intval($pickupPointCode) > 0)
        {
            $isCod = "0";
            $codAmount = 0;
        }

        //validate fields
        if(empty($orderId) || !is_numeric($data['order_id'])){
            return array('error'=>'Bad order ID.');
        }
        //packs
        if($packs==NULL || !is_numeric($packs) || (int)$packs<1){
            return array('error'=>'Bad packs number.');
        }
        if($weight==NULL || !Validate::isFloat($weight) || $weight<=0){
            return array('error'=>'Bad weight.');
        }
        if($isCod!='0' && $isCod!='1'){
            return array('error'=>'Bad COD value.');
        }
        if($isCod=='1' && ($codAmount=='' || !Validate::isFloat($codAmount))){
            return array('error'=>'Bad COD amount.');
        }
        //make sure cod amount is clear is COD is actually disabled
        if($isCod=='0'){ $codAmount = ''; }
        if(!is_numeric($warehouseId)){
            return array('error'=>'Bad Warehouse.');
        }
        //check if such warehouse actually exists
        VenipakCarrier::checkForClass('VenipakDatabaseHelper');
        $warehouseObj = VenipakDatabaseHelper::getAddress($warehouseId);
        if(empty($warehouseObj) || $warehouseObj['type'] != 'warehouse'){
            return array('error'=>'Bad Warehouse.');
        }
        $allDeliveryTimes = $venipakCarrier->getDeliveryTimes();
        if(!isset($allDeliveryTimes[$deliveryTime])){
            return array('error'=>'Bad delivery time.');
        }
        if($returnDocs!='1' && $returnDocs!='0'){
            return array('error'=>'Bad value for returning docs.');
        }
        if($checkDocs!='1' && $checkDocs!='0'){
            return array('error'=>'Bad value for checking docs.');
        }

        if($showSender!='' && !is_numeric($showSender)){
            return array('error'=>'Bad value for showing sender.');
        }
        //check if such sender actually exists
        if($showSender!=''){
            $senderObj = VenipakDatabaseHelper::getAddress($showSender);
            if(empty($senderObj) || $senderObj['type'] != 'sender'){
                return array('error'=>'Bad value for showing sender.');
            }
        }

        $format = "Y-m-d";
        $manifestDateTest = DateTime::createFromFormat($format, $manifestDate);
        if($manifestDate=='' || !$manifestDateTest){
            return array('error'=>'Bad value for manifest date.');
        }
        //check if entry for order_id is not already inserted
        $orderDataCheck = $this->getOrderVeniData($orderId);
        if(!empty($orderDataCheck)){
            return array('error'=>'Entry for this order already exists.');
        }

        $db = Db::getInstance();

        //everything seems ok, insert
        //check and if needed create new manifest no
        VenipakCarrier::checkForClass('VenipakManifest');
        $manifestObj = new VenipakManifest();
        $manifestNo = $manifestObj->getManifestNo($manifestDate, $warehouseId);
        if(!$manifestNo){
            return array('error'=>'Error with manifest number.');
        }

        //check for paysera dlr code
        $dlrCode = NULL;
        if(file_exists(_PS_MODULE_DIR_.'paysera/paysera.php')){
            include_once(_PS_MODULE_DIR_.'paysera/paysera.php');
            try {
                if(class_exists('Paysera')){
                    $paysera = new Paysera();
                    if(method_exists('Paysera','getShippingNumber')){
                        $dlrCode = $paysera->getShippingNumber($orderId);
                    }
                }
            }catch(Exception $e){
                $dlrCode = NULL;
            }
        }

        $result = Db::getInstance()->insert('venipak_order_info', array(
                'order_id' => $orderId,
                'packs' => $packs,
                'weight' => $weight,
                'is_cod' => $isCod,
                'cod_amount' => $codAmount,
                'warehouse_id' => $warehouseId,
                'delivery_time' => $deliveryTime,
                'return_docs' => $returnDocs,
                'check_docs' => $checkDocs,
                'show_sender' => $showSender,
                'manifest_no' => $manifestNo,
                'dlr_code' => $dlrCode,

                'comment_door_code'=>$commentDoorCode,
                'comment_office_no'=>$commentOfficeNo,
                'comment_warehous_no' => $commentWarehousNo,
                'comment_call' => $commentCall,
                'id_pickup_point' => $pickupPointCode
            ));
            //$lastId = Db::getInstance()->Insert_ID();

            // GET API data by id_shop if multishop enabled;
            if (Shop::isFeatureActive()) {
                $apiId = Configuration::get('VENIPAK_API_ID_CODE', null, null, $order->id_shop);
            } else {
                $apiId = Configuration::get('VENIPAK_API_ID_CODE');
            }


            //fetch max pack_no
            $maxPackNo = $db->getRow("SELECT MAX(pack_no) AS maxno FROM "._DB_PREFIX_."venipak_order_pack WHERE api_id = '".(int)$apiId."'");

            if ($maxPackNo === false)
                $generatedPackNo = 0;
            else
                $generatedPackNo = $maxPackNo['maxno'];

            //save packs
            for($i=0;$i<$packs;$i++){
                $generatedPackNo = $generatedPackNo+1;
                $result = Db::getInstance()->insert('venipak_order_pack', array(
                    'pack_no' => $generatedPackNo,
                    'api_id' => $apiId,
                    'order_id' => $orderId,
                    'weight' => $weight/$packs,
                ));
            }

        if ($result)
        {
            Db::getInstance()->delete("venipak_cart_comments", "cart_id = " . $order->id_cart);
            return array('success' => $orderId);
        }
        else
        {
            return array('error' => $result);
        }
    }

    /**
     * Deletes data from venipak_order_info and venipak_order_pack tables for specified order ID.
     * @param int $order_id
     */
    public function deleteOrderData($order_id){
        Db::getInstance()->delete('venipak_order_info', 'order_id = '.(int)$order_id);
        Db::getInstance()->delete('venipak_order_pack', 'order_id = '.(int)$order_id);
    }

    public function getOrderVeniData($orderId){
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'venipak_order_info WHERE order_id = '.(int)$orderId;
        return $db->getRow($sql);
    }

    public static function getFirstPack($orderId){
        $db = Db::getInstance();
        $sql = 'SELECT pack_no, api_id FROM ' . _DB_PREFIX_ . 'venipak_order_pack WHERE order_id = '.(int)$orderId.' ORDER BY pack_no DESC';


        $d = $db->getRow($sql);
        if(!empty($d)){
            return 'V'.str_pad($d['api_id'], 5, "0", STR_PAD_LEFT).'E'.str_pad($d['pack_no'], 7, "0", STR_PAD_LEFT);
        }
    }
}
