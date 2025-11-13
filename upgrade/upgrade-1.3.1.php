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

function upgrade_module_1_3_1($module) {

		$sql_requests = [];
	
		$tables = array(
		            'prestatill_homedelivery_creneau' =>
		                array(
		                	'id_reference' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
		                	'manual' => 'INT(10) UNSIGNED NULL DEFAULT 0',
		                	'manual_comment' => 'varchar(255) NOT NULL',
		                ),
	        );
	
	foreach ($tables as $table => $fields) {
	    foreach ($fields as $field => $type) {
	        $temp_sql = 'SELECT COUNT(column_name)
                    FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = "' . _DB_PREFIX_ .pSQL($table). '"
                    AND table_schema = "'._DB_NAME_.'" AND column_name = "'.pSQL($field).'"';

			$col_exist = Db::getInstance()->getValue($temp_sql);

			if($col_exist == 0)
			{
				$sql_requests[] = 'ALTER TABLE `'._DB_PREFIX_.$table.'` ADD COLUMN '.pSQL($field).' '.pSQL($type).';';
			}
	    }
	}
					
	$result = true;
	foreach ($sql_requests as $request) {
        if (!empty($request)) {
            $result &= Db::getInstance()->execute(trim($request));
        }
    }

	return $result;
}
