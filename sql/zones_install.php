<?php
/**
* Prestatill Home Delivery Slots - Zones Installation
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

// Table pour stocker les zones de livraison
$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_homedelivery_delivery_zones
                    (
                        id_delivery_zone INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
                        zone_name VARCHAR(255) NOT NULL,
                        id_store INT(10) UNSIGNED NOT NULL,
                        id_carrier INT(10) UNSIGNED NOT NULL DEFAULT 0,
                        zone_type ENUM("polygon", "circle", "rectangle") NOT NULL DEFAULT "polygon",
                        zone_data TEXT NOT NULL,
                        zone_color VARCHAR(7) DEFAULT "#FF0000",
                        active TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                        priority INT(10) UNSIGNED NOT NULL DEFAULT 0,
                        date_add DATETIME NOT NULL,
                        date_upd DATETIME NOT NULL,
                        id_shop INT(10) UNSIGNED NULL DEFAULT 1,
                        id_shop_group INT(10) UNSIGNED NULL DEFAULT 1,
                        KEY idx_store (id_store),
                        KEY idx_carrier (id_carrier),
                        KEY idx_active (active)
                    )
                    ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';

// Table pour associer les zones aux jours de livraison
$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_homedelivery_zone_days
                    (
                        id_zone_day INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
                        id_delivery_zone INT(10) UNSIGNED NOT NULL,
                        id_day INT(10) UNSIGNED NOT NULL DEFAULT 0,
                        KEY idx_delivery_zone (id_delivery_zone),
                        KEY idx_day (id_day)
                    )
                    ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';

// Table pour le cache de géolocalisation des adresses
$sql_requests[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'prestatill_homedelivery_address_cache
                    (
                        id_address_cache INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
                        id_address INT(10) UNSIGNED NOT NULL,
                        latitude DECIMAL(10, 8) NOT NULL,
                        longitude DECIMAL(11, 8) NOT NULL,
                        id_delivery_zone INT(10) UNSIGNED NULL,
                        date_add DATETIME NOT NULL,
                        date_upd DATETIME NOT NULL,
                        UNIQUE KEY idx_address (id_address),
                        KEY idx_delivery_zone (id_delivery_zone)
                    )
                    ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8;';
