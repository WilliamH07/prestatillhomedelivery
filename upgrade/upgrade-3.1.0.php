<?php
/**
* Prestatill Home Delivery Slots - Upgrade 3.1.0
*
* Ajoute le système de zones de livraison
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_1_0($module)
{
    // Exécute le script d'installation des tables de zones
    include(dirname(__FILE__).'/../sql/zones_install.php');

    foreach ($sql_requests as $request) {
        if (!Db::getInstance()->execute($request)) {
            return false;
        }
    }

    // Ajoute la configuration pour la clé API Google Maps si elle n'existe pas
    if (!Configuration::get('PRESTATILL_HD_GOOGLE_MAPS_API_KEY')) {
        Configuration::updateValue('PRESTATILL_HD_GOOGLE_MAPS_API_KEY', '');
    }

    // Active le mode zones par défaut
    if (!Configuration::get('PRESTATILL_HD_USE_ZONES')) {
        Configuration::updateValue('PRESTATILL_HD_USE_ZONES', '0');
    }

    // Installe le nouvel onglet admin pour les zones
    $tab = new Tab();
    $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentOrders');
    $tab->class_name = 'AdminPrestatillHomeDeliveryZones';
    $tab->module = 'prestatillhomedelivery';
    $tab->active = 1;

    foreach (Language::getLanguages(true) as $lang) {
        $tab->name[$lang['id_lang']] = 'Zones de livraison';
    }

    if (!$tab->add()) {
        return false;
    }

    return true;
}
