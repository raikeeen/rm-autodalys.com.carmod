<?php

function upgrade_module_1_0_3($module)
{
	$module->registerHook('displayBackOfficeHeader');
	
    Configuration::updateValue("VENIPAK_TRACKING_ACTION", 1);
    Configuration::updateValue("VENIPAK_PARCEL_CALCULATE_METHOD", 1);

	Tools::clearCache();

	return true;
}

