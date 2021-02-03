<?php

/* SSL Management */
$useSSL = true;
require_once(dirname(__FILE__).'/classes/opay_8.1.gateway.inc.php');
require_once(dirname(__FILE__).'/classes/crossversionshelper.class.php');
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/opay.php');

if (version_compare(_PS_VERSION_, '1.5', '<'))
{
    require(_PS_MODULE_DIR_.'/opay/backward_compatibility/backward.php');
}


$module = new Opay();

CrossVersionsHelper::validation($module);
