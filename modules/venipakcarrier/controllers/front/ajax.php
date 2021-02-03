<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class VenipakcarrierajaxModuleFrontController extends ModuleFrontController {

    private $_module = NULL;
    public $module = 'venipakcarrier';

    public function __construct() {
        $context = Context::getContext();

        $cookie = new Cookie ('psAdmin');
        $employee = new Employee ($cookie->id_employee);

        $context->employee = $employee;
        $context->cookie = $cookie;

        if(!Context::getContext()->employee->isLoggedBack()){
            exit('Restricted.');
        }

        $this->_module = new VenipakCarrier();
        $this->module = 'venipakcarrier';

        $this->parseActions();

        parent::__construct();
    }

    private function parseActions(){
        $action = Tools::getValue('action');

        switch($action){
            case 'savevenipakorder': $this->saveVenipakOrder(); break;
            case 'popup': $this->renderPopup(); break;
            case 'printlabels': $this->printLabels(); break;
            case 'printmanifest': $this->printManifest(); break;
            case 'closemanifest': $this->closeManifest(); break;
            case 'callcarrier': $this->callCarrier(); break;
            case 'search': $this->ajaxSearch(); break;
        }
    }

    protected function renderPopup()
    {
        global $smarty;
        $context = Context::getContext();
        $id_shop = $context->shop->id;
        VenipakCarrier::checkForClass('VenipakOrder');
        VenipakCarrier::checkForClass('VenipakDatabaseHelper');

        $warehouses = VenipakDatabaseHelper::getWarehouses($id_shop);
        $manifests = array();
        $courrierCallToday = array();
        $courrierCalls = array();
        $weightsByWarehouseByDate = array();
        foreach($warehouses as $wh){

            $courrierCalls[$wh['id']] = VenipakDatabaseHelper::getCarrierCallFromDate($wh['id'], date('Y-m-d'), $id_shop);
            if($courrierCalls[$wh['id']]){
                //parse saved JSON data
                foreach($courrierCalls[$wh['id']] as $key=>$val){
                    $courrierCalls[$wh['id']][$key]['data_parsed'] = json_decode($courrierCalls[$wh['id']][$key]['call_data'], true);
                }

            }
        }

        //create dates for calling couries
        $couriesCallDates = array();
        $weightsByWarehouseByManifest = array();
        $datetime = new DateTime(date('Y-m-d'));

        if (date('G') >= 15)
            $datetime->modify('+1 day');

        for($i=0;$i<5;$i++){
            $date = $datetime->format('Y-m-d');
            $couriesCallDates[]=array('date'=>$date);
            $datetime->modify('+1 day');

            //add weights
            foreach($warehouses as $wh){
                $weight = $this->_module->getTotalWeightByDate($date, $wh['id']);
                $weightsByWarehouseByDate[$wh['id']][$date]=$weight;
            }
        }

        //check if courier was called today

        $smarty->assign(array(
            'venipak_warehouses' => $warehouses,
            'manifests' => $manifests,
            'printlabelsurl'=>$context->link->getModuleLink('venipakcarrier', 'ajax',array('action'=>'printlabels')),
            'callcarrierurl' => $context->link->getModuleLink('venipakcarrier', 'ajax',array('action'=>'callcarrier')),
            'printmanifesturl' => $context->link->getModuleLink('venipakcarrier', 'ajax',array('action'=>'printmanifest')),
            'couries_call_dates'=>$couriesCallDates,
            'weights_by_warehouse_by_date' => $weightsByWarehouseByDate,
            'courrierCalls' => $courrierCalls
        ));
        echo $smarty->fetch(_PS_MODULE_DIR_.$this->_module->name.'/views/templates/admin/'.'popup.tpl');
        exit();
    }

    protected function saveVenipakOrder(){
        if(!empty($this->_module->warning)){
            exit(json_encode(array('error'=>$this->_module->warning)));
        }
        $orderId = Tools::getValue('order_id', NULL);
        $order = new Order((int)$orderId);
        if(!$order){
            exit(json_encode(array('error'=>'No order ID provided.')));
        }
        VenipakCarrier::checkForClass('VenipakOrder');
        $venipakOrderObj = new VenipakOrder();
        $saveResult = $venipakOrderObj->saveVenipakOrder($order);

        if(isset($saveResult['success']))
        {
            //call venipak API to save order with packs
            VenipakCarrier::checkForClass('VenipakAPI');
            $venipakAPI = new VenipakAPI();
            $venipakResult = $venipakAPI->sendShipmentXML(array($saveResult['success']), $order->id_shop);

            if(is_array($venipakResult) && isset($venipakResult['success']))
            {
                $orderStatus = Configuration::get('VENIPAK_ORDER_STATUS');
                if($orderStatus!='')
                {
                    //change order status
                    $history = new OrderHistory();
                    $history->id_order = (int)$orderId;
                    $history->changeIdOrderState((int)($orderStatus), (int)$orderId);
                    $history->addWithemail();
                }

                //set tracking number
                $trackingNo = VenipakOrder::getFirstPack($orderId);

                if (!empty($trackingNo)) {
                    // Yes = Default PrestaShop behaviour (sending email in_transit). No = assign tracking_number to order;
                    if (Configuration::get('VENIPAK_TRACKING_ACTION')) {

                        $id_order_carrier = Db::getInstance()->getValue('SELECT `id_order_carrier` FROM `'._DB_PREFIX_.'order_carrier` WHERE `id_order` = '.(int)$orderId);
                        if ($id_order_carrier) {
                            $order_carrier = new OrderCarrier($id_order_carrier);
                            $order_carrier->tracking_number = $trackingNo;
                            $order_carrier->update();

                            if (version_compare(_PS_VERSION_, '1.7', '>='))
                            {
                                if ($order_carrier->sendInTransitEmail($order)) {
                                    $customer = new Customer((int) $order->id_customer);
                                    $carrier = new Carrier((int) $order->id_carrier, $order->id_lang);

                                    Hook::exec('actionAdminOrdersTrackingNumberUpdate', array(
                                        'order' => $order,
                                        'customer' => $customer,
                                        'carrier' => $carrier,
                                    ), null, false, true, false, $order->id_shop);
                                }
                            }

                            // In case
                            Db::getInstance()->update('order_carrier', array('tracking_number' => $trackingNo), 'id_order = '.(int)$orderId);
                        } else {
                            Db::getInstance()->update('order', array('shipping_number' => $trackingNo), 'id_order = '.(int)$orderId);
                        }
                    } else {
                        // Assign tracking number without mail;
                        Db::getInstance()->update('order', array('shipping_number' => $trackingNo), 'id_order = '.(int)$orderId);
                        Db::getInstance()->update('order_carrier', array('tracking_number' => $trackingNo), 'id_order = '.(int)$orderId);
                    }
                }

            }
            else
            {
                //DELETE order data
                $venipakOrderObj->deleteOrderData((int)$orderId);
            }

            echo json_encode($venipakResult); exit();
        }
        echo json_encode($saveResult); exit();
    }

    protected function printLabels(){

        if (!empty($_POST['order_ids']))
        {
            $orderIds = $_POST['order_ids'];
        }
        else if (!empty($_GET['order_ids']))
        {
            $orderIds = explode(';', $_GET['order_ids']);
        }


        if(empty($orderIds) || !is_array($orderIds)){
            echo json_encode(array('error'=>'No order ID provided.'));
        }


        VenipakCarrier::checkForClass('VenipakAPI');
        $apiClass = new VenipakAPI();

        $result = $apiClass->getLabelsForOrders($orderIds);
        if(is_array($result)){
            $type = $result['type'];
            $content = $result['result'];
        }else{
            echo "Error occured.";
            exit();
        }
        header('Content-type: application/'.$type);
        header('Content-Disposition: attachment; filename="venipak-labels.'.$type.'"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        if($type == 'zip'){
            echo readfile($content);
        }else{
            echo $content;
        }
        exit();

    }

    protected function printManifest()
    {
        $manifest_no = trim(Tools::getValue('manifest_no'));
        if(empty($manifest_no) || !is_numeric($manifest_no)){
            echo "Invalid manifest no.";
            exit();
        }
        VenipakCarrier::checkForClass('VenipakAPI');
        $apiClass = new VenipakAPI();
        $result = $apiClass->getManifestFile($manifest_no);
        if(strlen($result)>20){
            //close manifest
            //VenipakCarrier::checkForClass('VenipakManifest');
            //$manifestObj = new VenipakManifest();
            //$manifestObj->closeManifest($manifest_no);
        }
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="venipak-manifest-'.$manifest_no.'.pdf"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        echo $result;
        exit();
    }

    protected function closeManifest()
    {
        $manifest_no = trim(Tools::getValue('manifest_no'));
        if(empty($manifest_no) || !is_numeric($manifest_no)){
            echo "Invalid manifest no.";
            exit();
        }

        $context = Context::getContext();
        VenipakCarrier::checkForClass('VenipakManifest');
        $manifestObj = new VenipakManifest();
        $manifestObj->closeManifest($manifest_no);


        header('Location: '.$context->link->getAdminLink('AdminVenipak', true).'&conf=5');
    }

    protected function ajaxSearch()
    {
        $customer = Tools::getValue('customer');
        $tracking = Tools::getValue('tracking_nr');
        $manifest_date = Tools::getValue('manifest_date');
        $order_id = Tools::getValue('order_id');
        $manifest_no = Tools::getValue('manifest_no');
        $warehouse_id = Tools::getValue('warehouse_id');
        $where = '';

        if($tracking != '' && $tracking != null && $tracking != 'undefined')
            $where .= ' AND oc.tracking_number LIKE "%'. $tracking .'%" ';

        if($customer != '' && $customer != null && $customer != 'undefined')
            $where .= ' AND CONCAT(oh.firstname, " ",oh.lastname) LIKE "%' .$customer. '%" ';

        if($manifest_date != null && $manifest_date != 'undefined' && $manifest_date != '')
            $where .= ' AND vm.manifest_date = "'.$manifest_date.'" ';

        if($order_id != null && $order_id != 'undefined' && $order_id != '')
            $where .= ' AND a.id_order = '.$order_id;

        if($manifest_no != null && $manifest_no != 'undefined' && $manifest_no != '')
            $where .= ' AND voi.manifest_no = '.$manifest_no;

        if($warehouse_id != null && $warehouse_id != 'undefined' && $warehouse_id != '' && !empty($where))
            $where .= ' AND vm.warehouse_id = '.$warehouse_id;


        if($where == '')
            die(Tools::jsonEncode( array(array(
            ))));


        $orders = "SELECT
                  a.id_order,
                  oc.date_add,
                  a.date_upd,
                  a.total_paid_tax_incl,
                  CONCAT(oh.firstname, ' ', oh.lastname) as full_name,
                  oc.tracking_number,
                  voi.`manifest_no`,
                  voi.`warehouse_id`,
                  voi.`is_cod`,
                  vm.`manifest_date`
                FROM
                  "._DB_PREFIX_."orders a
                  INNER JOIN "._DB_PREFIX_."customer oh
                    ON a.id_customer = oh.id_customer
                  INNER JOIN "._DB_PREFIX_."venipak_order_info voi
                    on voi.`order_id` = a.`id_order`
                INNER JOIN "._DB_PREFIX_."venipak_manifest vm
                    on voi.`manifest_no` = vm.`manifest_no`
                  LEFT JOIN "._DB_PREFIX_."order_carrier oc
                    ON a.id_order = oc.id_order
                Where 1=1 ".$where."
                ORDER BY voi.`manifest_no` DESC,
                  a.id_order DESC
                LIMIT 20";

        $searchResponse = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($orders);
        die(Tools::jsonEncode($searchResponse));
    }

    protected function callCarrier()
    {

        if(!class_exists("VenipakDatabaseHelper")){
            require_once(_PS_MODULE_DIR_.'venipakcarrier/classes/VenipakDatabaseHelper.php');
        }

        $context = Context::getContext();
        $id_shop = $context->shop->id;

        $weight = trim(Tools::getValue("weight",""));
        $pallets = trim(Tools::getValue("pallets",""));
        $comment = trim(Tools::getValue("comment",""));
        $arrive_date = trim(Tools::getValue('arrive_date'));
        $hour_from = trim(Tools::getValue('hour_from'));
        $min_from = trim(Tools::getValue('min_from'));
        $hour_to = trim(Tools::getValue('hour_to'));
        $min_to = trim(Tools::getValue('min_to'));
        $warehouse_id = (int)trim(Tools::getValue("warehouse_id"));
        //validation

        if(!is_numeric($warehouse_id) || $warehouse_id<1){
            echo json_encode(array("error"=>$this->_module->l("Incorrect warehouse.")));
            exit();
        }
        $warehouse = VenipakDatabaseHelper::getWarehouse($warehouse_id);
        if($warehouse==false){
            echo json_encode(array("error"=>$this->_module->l("Incorrect warehouse (1).")));
            exit();
        }

        if(!is_numeric($weight)){
            echo json_encode(array("error"=>$this->_module->l("Incorrect weight.")));
            exit();
        }
        if(!is_numeric($pallets)){
            echo json_encode(array("error"=>$this->_module->l("Incorrect number of pallets.")));
            exit();
        }

        if($arrive_date==""){
            echo json_encode(array("error"=>$this->_module->l("Arrival time is required.")));
            exit();
        }

        if($hour_from=='' || $min_from=='' || $hour_to=='' || $min_to==''){
            echo json_encode(array("error"=>$this->_module->l("Arrival time is required.")));
            exit();
        }

        $format = "Y-m-d";
        $formatFull = "Y-m-d H:i:s";
        $testDate = DateTime::createFromFormat($format, $arrive_date);
        //if date is in not today - forbid to call another time for tomorrow
        if($testDate->format($format)!=date($format)){

            $checkFutureCall = VenipakDatabaseHelper::getCarrierCallByDate($warehouse_id, $testDate->format($format), $id_shop);
            if(!empty($checkFutureCall)){
                echo json_encode(array("error"=>$this->_module->l("Cannot call another time in the future.")));
                exit();
            }
        }

        //check for 2 hours interval
        $testDateFrom = DateTime::createFromFormat($formatFull, $arrive_date.' '.$hour_from.':'.$min_from.':00');
        $testDateTo = DateTime::createFromFormat($formatFull, $arrive_date.' '.$hour_to.':'.$min_to.':00');
        $diff = $testDateTo->diff($testDateFrom, true);
        if($diff->h<2){
            echo json_encode(array("error"=>$this->_module->l("Interval must be at least 2 hours.")));
            exit();
        }

        if($comment!=''){
            //htmlspecialchars
            $comment = htmlspecialchars(strip_tags($comment));
        }
        //if we got this far, everything is ok
        if(!class_exists("VenipakAPI")){
            require_once(_PS_MODULE_DIR_.'venipakcarrier/classes/VenipakAPI.php');
        }

        if(!empty($this->_module->warning)){
            exit(json_encode(array('error'=>$this->_module->warning)));
        }

        $apiClass = new VenipakAPI();
        $dataArray = array(
            'weight' => $weight,
            'pallets' => $pallets,
            'comment'=> $comment,
            'date_y' => $testDate->format('Y'),
            'date_m' => $testDate->format('m'),
            'date_d' => $testDate->format('d'),
            'hour_from' => $hour_from,
            'min_from' => $min_from,
            'hour_to' => $hour_to,
            'min_to' => $min_to
        );

        $result = $apiClass->callCourier($warehouse_id, $dataArray);
        //fix utf8 characters
        if(version_compare(PHP_VERSION, '5.4', '>=')){
            $dataArrayEncoded = json_encode($dataArray, JSON_UNESCAPED_UNICODE);
        }else{
            $dataArrayEncodedRaw = json_encode($dataArray);
            $dataArrayEncoded = preg_replace_callback('/\\\u(\w{4})/', function ($matches) {
                return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
            }, $dataArrayEncodedRaw);
        }


        if(!empty($result) && isset($result['success'])){
            //insert in database
            $this->_module->saveCarrierCall($id_shop, $warehouse_id, $testDate->format('Y-m-d'), $dataArrayEncoded);
        }

        if(isset($result['error'])){
            $result['status'] = 0;
        }else{
            $result['status'] = 1;
        }
        echo json_encode($result);
        exit();
    }

}
