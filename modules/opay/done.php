<?php

/* SSL Management */
$useSSL = true;

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/opay.php');

$module = new Opay();

$historyLink = version_compare(_PS_VERSION_, '1.5', '<') ? '/history.php' : '/index.php?controller=history';
if (Tools::getValue('status') == 1 || Tools::getValue('status') == 2)
{
    $smarty->assign(array(
        'status'       => Tools::getValue('status'),
        'history_link' => $historyLink
    ));
    echo $module->display(dirname(__FILE__)."/opay.php", '/views/templates/front/done.tpl');
    include_once(dirname(__FILE__).'/../../footer.php');
}
else
{
    Tools::redirect($historyLink);
}
