<?php
/**
* Prestatill Home Delivery Slots
*
* Home Delivery Module with slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_0_0($module) {

	$sql_requests = [];

	// SINCE 3.0.0					
	$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_homedelivery_carence_supp_by_product
                    (
                        id_product INT(10) NOT NULL,
                        id_product_attribute INT(10) NOT NULL DEFAULT 0,
                        carence_supp INT(10) NOT NULL DEFAULT 0,
                        PRIMARY KEY (`id_product`)
                    )
                    ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';
					
	$result = true;
	foreach ($sql_requests as $request) {
        if (!empty($request)) {
            $result &= Db::getInstance()->execute(trim($request));
        }
    }

	return 
		$module->registerHook('displayAdminProductsExtra')
        && $module->registerHook('actionProductSave')
		&& $result;
}
