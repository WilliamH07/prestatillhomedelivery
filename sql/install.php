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

$sql_requests = array();

//$result = Db::getInstance()->executeS($request);

$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_homedelivery
                    (
                    	id_prestatill_homedelivery INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
                        id_day INT(10) UNSIGNED NOT NULL,
                        day varchar(255) NOT NULL,
                        openning tinyint UNSIGNED ,
                        hour_open_am time NULL,
                        hour_close_am time NULL,
                        hour_open_pm time NULL,
                        hour_close_pm time NULL,
                        nonstop tinyint NOT NULL,
                        id_shop INT(10) UNSIGNED NULL DEFAULT 1,
	                	id_shop_group INT(10) UNSIGNED NULL DEFAULT 1,
                        PRIMARY KEY (id_prestatill_homedelivery,id_day)                       
                    )
                    ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';
					
$sql_requests[] = 'ALTER TABLE '._DB_PREFIX_.'prestatill_homedelivery AUTO_INCREMENT = 1';					

$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_homedelivery_vacation
                    (
                        id_vacation int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
                        vacation_start date NULL,
                        vacation_end date NULL
                    )
                    ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';

$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_homedelivery_creneau
                    (
                        id_creneau int(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
                        id_week int(10) UNSIGNED NOT NULL,
                        id_day int(10) UNSIGNED NOT NULL,
                        id_cart int(10) UNSIGNED NOT NULL,
                        id_order int(10) UNSIGNED NOT NULL,
                        id_reference int(10) UNSIGNED NOT NULL DEFAULT 0,
                        cause varchar(255) NOT NULL,
                        hour time NULL,
                        hour_end time NULL,
                        day varchar(10) NOT NULL,
                        reminded INT(10) UNSIGNED NULL DEFAULT 0,
                        store_informed INT(10) UNSIGNED NULL DEFAULT 0,
                        manual INT(10) UNSIGNED NULL DEFAULT 0,
                        manual_comment varchar(255) NOT NULL
                    )
                    ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';

$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_homedelivery_availability_by_product
                (
                    id_product INT(10) NOT NULL,
                    id_product_attribute INT(10) NOT NULL DEFAULT 0,
                    id_day INT(10) NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id_product`, `id_product_attribute`, `id_day`)
                )
                ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';

$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_homedelivery_carence_supp_by_product
                (
                    id_product INT(10) NOT NULL,
                    id_product_attribute INT(10) NOT NULL DEFAULT 0,
                    carence_supp INT(10) NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id_product`)
                )
                ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';

$tables = array(
                'orders' =>
	                array(
	                ),
                'prestatill_homedelivery' =>
	                array(
	                	'id_shop' => 'INT(10) UNSIGNED NULL DEFAULT 1',
	                	'id_shop_group' => 'INT(10) UNSIGNED NULL DEFAULT 1',
	                ),
	            'prestatill_homedelivery_creneau' =>
	                array(
	                	'reminded' => 'INT(10) UNSIGNED NULL DEFAULT 0',
	                	'store_informed' => 'INT(10) UNSIGNED NULL DEFAULT 0',
	                	'hour_end' => 'TIME NULL DEFAULT NULL',
	                	'id_reference' => 'INT(10) UNSIGNED NOT NULL DEFAULT 0',
	                	'manual' => 'INT(10) UNSIGNED NULL DEFAULT 1',
	                	'manual_comment' => 'varchar(255) NOT NULL',
	                ),
        );

if(!empty($tables) && Configuration::get('PRESTATILL_HD_VERSION') != '')
{
  foreach ($tables as $table => $fields)
  {
    // Check if table exists
    $exists_table_sql = 'SELECT COUNT(*)
                    FROM INFORMATION_SCHEMA.tables WHERE table_schema = "'._DB_NAME_.'"
                    AND table_name LIKE "'. _DB_PREFIX_ .pSQL($table).'"';

    $exists_table = Db::getInstance()->getValue($exists_table_sql);

    // Update only on reset case, if table exists
    if($exists_table > 0)
    {
        foreach ($fields as $field => $type) {
            $temp_sql = 'SELECT COUNT(column_name)
                        FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = "' . _DB_PREFIX_ .pSQL($table). '"
                        AND table_schema = "'._DB_NAME_.'" AND column_name = "'.pSQL($field).'"';
    
            $col_exist = Db::getInstance()->getValue($temp_sql);

            //PrestaShopLogger::addLog('ALTER TABLE - FIELD : '.$field.' - COL : '.$col_exist, 1, null, 'SQL_Update', '', true);
    
            if($col_exist == 0)
            {
                $sql_requests[] = 'ALTER TABLE `'._DB_PREFIX_.$table.'` ADD COLUMN '.pSQL($field).' '.pSQL($type).';';
            }
        }
    }
  }	 
}	
		
