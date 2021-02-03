<?php

/*
 */

/**
 * Description of AdminVenipakController
 *
 * @author Rokas
 */
class AdminVenipakController extends ModuleAdminController
{
	public function __construct()
	{
        
        $this->tpl_folder = 'venipakcarrier';
        $this->display = NULL;
        $this->bootstrap = true;
        
		parent::__construct();

	}

    public function initContent()
    {
        VenipakCarrier::checkForClass('VenipakOrder');
        VenipakCarrier::checkForClass('VenipakManifest');
        VenipakCarrier::checkForClass('VenipakDatabaseHelper');

        parent::initContent();
        $smarty = $this->context->smarty;
        $context = Context::getContext();
        $id_shop = $context->shop->id;

        if (
            empty(Configuration::get('VENIPAK_API_URL')) ||
            empty(Configuration::get('VENIPAK_API_PASSWORD')) ||
            empty(Configuration::get('VENIPAK_API_LOGIN')) ||
            empty(Configuration::get('VENIPAK_API_ID_CODE'))
        )
            Tools::redirectAdmin('index.php?tab=AdminModules&configure=venipakcarrier&showError=1002&token='.Tools::getAdminTokenLite('AdminModules'));

        if (
            !VenipakDatabaseHelper::hasDefaultAddress('warehouse') ||
            !VenipakDatabaseHelper::hasDefaultAddress('sender')
        )
            Tools::redirectAdmin('index.php?tab=AdminModules&configure=venipakcarrier&showError=1001&token='.Tools::getAdminTokenLite('AdminModules'));


        if (!isset($id_shop) || empty($id_shop))
        {
            parent::initContent();
            return "Prašome pasirinkti konkrečią parduotuvę.";
        }


        $manifests = array();
        $warehouses = VenipakDatabaseHelper::getWarehouses($id_shop);
        $defaultWarehouse = VenipakDatabaseHelper::getDefaultWarehouse($id_shop);

        $page = (isset($_GET['p']) ? (int)pSQL($_GET['p']) : 1);
        $limit = (isset($_GET['limit']) ? (int)pSQL($_GET['limit']) : 5);

        if(isset($_GET['warehouse_id'])){
            $warehouseId = (int)pSQL($_GET['warehouse_id']);
        }else{
            $warehouseId = $defaultWarehouse['id'];
        }

        $openOrdersObj = new VenipakOrder();
        $manifestsObj = new VenipakManifest();
        
        $open_manifests = $openOrdersObj->getOrders($warehouseId, (int)$context->language->id, 1, 1, 10);
        $closed_manifests = $openOrdersObj->getOrders($warehouseId, (int)$context->language->id, 0, $page, $limit);

        $totalCount = $manifestsObj->getTotalManifestsByWarehouse($warehouseId);
        $totalPages = $totalCount/$limit;


        $pagesToShow = intval(ceil($totalCount/$limit));
        $page = intval($page);

        if($page <= 0 || $page > $pagesToShow)
            $page = 1;

        if($pagesToShow <= 5) {
            $endGroup = $pagesToShow;
        } else {
            if($pagesToShow - $page > 2){
                $endGroup = $page + 2;
            } else {
                $endGroup = $pagesToShow;
            }
        }
        if($endGroup - 4 > 0) {
            $startGroup = $endGroup - 4;
        } else {
            $startGroup = 1;
        }


        $smarty->assign(array(
            'open_manifests' => $open_manifests,
            'closed_manifests' => $closed_manifests,
            'warehouses' => $warehouses,
            'warehouseId' => $warehouseId,
            'baseUrl' => $context->link->getAdminLink('AdminVenipak'),
            'printlabelsurl'=>$context->link->getModuleLink('venipakcarrier', 'ajax',array('action'=>'printlabels')),
            'printmanifesturl' => $context->link->getModuleLink('venipakcarrier', 'ajax',array('action'=>'printmanifest')),
            'closemanifesturl' => $context->link->getModuleLink('venipakcarrier', 'ajax',array('action'=>'closemanifest')),
            'orderLink' => $this->context->link->getAdminLink('AdminOrders', true).'&vieworder',
            'ajaxCall' => $context->link->getModuleLink('venipakcarrier', 'ajax',array('action'=>'search')).'&warehouse_id='.$warehouseId,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages,
            'nb_products' =>$totalCount,
            'products_per_page' =>$limit,
            'pages_nb' => $pagesToShow,
            'prev_p' => (int)$page != 1 ? $page-1 : 1,
            'next_p' => (int)$page + 1 > $pagesToShow? $pagesToShow : $page + 1,
            'requestPage' => $this->context->link->getAdminLink('AdminVenipak', true).'&warehouse_id='.$warehouseId.'&tab=closed',
            'current_url' => $this->context->link->getAdminLink('AdminVenipak', true).'&warehouse_id='.$warehouseId.'&tab=closed',
            'requestNb' => $this->context->link->getAdminLink('AdminVenipak', true).'&warehouse_id='.$warehouseId.'&tab=closed',
            'p' => $page,
            'n' => $limit,
            'start' => $startGroup,
            'stop' => $endGroup,
            ));

        $smarty->assign(array(
            'pagination_content' => $this->context->smarty->fetch(_PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/pagination.tpl'),
            'pagination' => array('items_shown_from'=>1,'items_shown_to'=>1,'total_items'=>$totalCount,'should_be_displayed'=>1,'pages'=>1),
        ));

        $content = $smarty->fetch(_PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/venipak_orders.tpl');
        $this->content = $content;
        parent::initContent();
    }


    public function initPageHeaderToolbar()
    {

        $this->page_header_toolbar_title = $this->l('Venipak');
        if ($this->display != 'view') {
            $this->page_header_toolbar_btn['venipakbtn'] = array(
                'short' => $this->l('Call Carrier'),
                'href' => $this->context->link->getModuleLink('venipakcarrier', 'ajax',array('action'=>'popup')),
                'desc' => $this->l('Call Venipak Carrier'),
                'class' => 'venipak-icon icon-truck',
            );

            $this->page_header_toolbar_btn['venipakmodulesettingsbtn'] = array(
                'short' => $this->l('Module settings'),
                'href' => $this->context->link->getAdminLink('AdminModules', true).'&configure=venipakcarrier',
                'desc' => $this->l('Module settings'),
                'class' => 'venipak-icon icon-gear',
            );

            $this->page_header_toolbar_btn['venipakhelpbtn'] = array(
                'short' => $this->l('Venipak Help'),
                'href' => 'http://venipak.elpresta.lt',
                'desc' => $this->l('Venipak Help'),
                'class' => 'venipak-icon icon-question',
                'target' => true,
            );
        }

        parent::initPageHeaderToolbar();
        $this->context->smarty->clearAssign('help_link');
    }

}
