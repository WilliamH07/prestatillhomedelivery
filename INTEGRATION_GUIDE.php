<?php
/**
 * GUIDE D'INTÉGRATION DU SYSTÈME DE ZONES
 *
 * Ce fichier contient le code à ajouter dans prestatillhomedelivery.php
 */

// =================================================================
// 1. AJOUTER EN HAUT DU FICHIER (après les autres require_once)
// =================================================================
require_once(dirname(__FILE__).'/classes/PrestatillHomeDeliveryZone.php');


// =================================================================
// 2. DANS LA MÉTHODE __construct(), après parent::__construct()
// =================================================================
// Initialiser les configurations pour les zones si elles n'existent pas
if (!Configuration::get('PRESTATILL_HD_GOOGLE_MAPS_API_KEY')) {
    Configuration::updateValue('PRESTATILL_HD_GOOGLE_MAPS_API_KEY', '');
}
if (!Configuration::get('PRESTATILL_HD_USE_ZONES')) {
    Configuration::updateValue('PRESTATILL_HD_USE_ZONES', '0');
}


// =================================================================
// 3. DANS LA MÉTHODE getContent(), AJOUTER CE CODE
//    (avant le $this->assignConfiguration();)
// =================================================================

// Gestion de l'onglet Zones (tab = 6)
if (Tools::isSubmit('submitZonesConfig')) {
    Configuration::updateValue('PRESTATILL_HD_GOOGLE_MAPS_API_KEY', Tools::getValue('PRESTATILL_HD_GOOGLE_MAPS_API_KEY'));
    Configuration::updateValue('PRESTATILL_HD_USE_ZONES', Tools::getValue('PRESTATILL_HD_USE_ZONES'));
    $tab = 6;
    $confirmation = true;
}


// =================================================================
// 4. DANS LA MÉTHODE assignConfiguration() OU JUSTE AVANT
//    $this->display(__FILE__, 'views/templates/admin/configure.tpl')
//    AJOUTER CE CODE POUR PASSER LES VARIABLES AU TEMPLATE
// =================================================================

// Récupère les zones de livraison
require_once(dirname(__FILE__).'/classes/PrestatillHomeDeliveryZone.php');
$delivery_zones = Db::getInstance()->executeS(
    'SELECT * FROM `'._DB_PREFIX_.'prestatill_homedelivery_delivery_zones`
    WHERE `id_shop` = '.(int)$this->context->shop->id.'
    ORDER BY `priority` DESC, `id_delivery_zone` ASC'
);

// Récupère les magasins pour le mapping
$stores_list = Store::getStores();
$stores_array = array();
foreach ($stores_list as $store) {
    $stores_array[$store['id_store']] = $store;
}

// Récupère les transporteurs pour le mapping
$carriers_list = Carrier::getCarriers(
    $this->context->language->id,
    false,
    false,
    false,
    null,
    Carrier::ALL_CARRIERS
);
$carriers_array = array();
foreach ($carriers_list as $carrier) {
    $carriers_array[$carrier['id_reference']] = $carrier;
}

// Assigne les variables au template
$this->context->smarty->assign(array(
    'google_maps_api_key' => Configuration::get('PRESTATILL_HD_GOOGLE_MAPS_API_KEY'),
    'use_zones' => Configuration::get('PRESTATILL_HD_USE_ZONES'),
    'delivery_zones' => $delivery_zones,
    'stores' => $stores_array,
    'carriers' => $carriers_array,
    'default_lat' => Configuration::get('PS_STORES_CENTER_LAT') ?: '48.8566',
    'default_lng' => Configuration::get('PS_STORES_CENTER_LONG') ?: '2.3522',
    'tab' => $tab,
));


// =================================================================
// 5. DANS LA MÉTHODE loadAsset() OU CRÉER UNE MÉTHODE loadAsset()
//    AJOUTER LE CHARGEMENT DES ASSETS JS/CSS
// =================================================================

protected function loadAsset()
{
    // Assets existants...

    // Ajoute Google Maps API si la clé est configurée
    $google_maps_key = Configuration::get('PRESTATILL_HD_GOOGLE_MAPS_API_KEY');
    if ($google_maps_key && $google_maps_key !== '') {
        $this->context->controller->addJS('https://maps.googleapis.com/maps/api/js?key='.$google_maps_key.'&libraries=drawing,geometry');
    }

    // Ajoute le JS et CSS pour les zones
    $this->context->controller->addJS($this->_path.'views/js/zones_tab.js?v='.time());
    $this->context->controller->addCSS($this->_path.'views/css/admin_zones.css?v='.time());
}


// =================================================================
// 6. CRÉER UNE NOUVELLE MÉTHODE POUR GÉRER LES ACTIONS AJAX
//    À AJOUTER DANS LA CLASSE PrestatillHomeDelivery
// =================================================================

/**
 * Gère les actions AJAX pour les zones de livraison
 */
public function hookActionFrontControllerSetMedia($params)
{
    // Cette méthode sera appelée pour gérer les requêtes AJAX
    // On vérifie si c'est une action sur les zones

    if (Tools::getValue('action') === 'saveDeliveryZone') {
        $this->ajaxProcessSaveDeliveryZone();
    } elseif (Tools::getValue('action') === 'getZone') {
        $this->ajaxProcessGetZone();
    } elseif (Tools::getValue('action') === 'deleteDeliveryZone') {
        $this->ajaxProcessDeleteDeliveryZone();
    }
}

protected function ajaxProcessSaveDeliveryZone()
{
    $id_delivery_zone = (int)Tools::getValue('id_delivery_zone');
    $zone_name = Tools::getValue('zone_name');
    $id_store = (int)Tools::getValue('id_store');
    $id_carrier = (int)Tools::getValue('id_carrier');
    $zone_type = Tools::getValue('zone_type');
    $zone_data = Tools::getValue('zone_data');
    $zone_color = Tools::getValue('zone_color');
    $active = (int)Tools::getValue('active');
    $priority = (int)Tools::getValue('priority');

    if ($id_delivery_zone > 0) {
        $zone = new PrestatillHomeDeliveryZone($id_delivery_zone);
    } else {
        $zone = new PrestatillHomeDeliveryZone();
        $zone->date_add = date('Y-m-d H:i:s');
        $zone->id_shop = (int)$this->context->shop->id;
        $zone->id_shop_group = (int)$this->context->shop->id_shop_group;
    }

    $zone->zone_name = $zone_name;
    $zone->id_store = $id_store;
    $zone->id_carrier = $id_carrier;
    $zone->zone_type = $zone_type;
    $zone->zone_data = $zone_data;
    $zone->zone_color = $zone_color;
    $zone->active = $active;
    $zone->priority = $priority;
    $zone->date_upd = date('Y-m-d H:i:s');

    if ($zone->save()) {
        die(json_encode(array(
            'status' => 'success',
            'message' => 'Zone saved successfully',
            'id_delivery_zone' => $zone->id,
        )));
    } else {
        die(json_encode(array(
            'status' => 'error',
            'message' => 'Error saving zone',
        )));
    }
}

protected function ajaxProcessGetZone()
{
    $id_delivery_zone = (int)Tools::getValue('id_delivery_zone');

    if ($id_delivery_zone > 0) {
        $zone = Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'prestatill_homedelivery_delivery_zones`
            WHERE `id_delivery_zone` = '.(int)$id_delivery_zone
        );

        if ($zone) {
            die(json_encode(array(
                'status' => 'success',
                'zone' => $zone,
            )));
        }
    }

    die(json_encode(array(
        'status' => 'error',
        'message' => 'Zone not found',
    )));
}

protected function ajaxProcessDeleteDeliveryZone()
{
    $id_delivery_zone = (int)Tools::getValue('id_delivery_zone');

    if ($id_delivery_zone > 0) {
        $zone = new PrestatillHomeDeliveryZone($id_delivery_zone);
        if ($zone->delete()) {
            die(json_encode(array(
                'status' => 'success',
                'message' => 'Zone deleted successfully',
            )));
        }
    }

    die(json_encode(array(
        'status' => 'error',
        'message' => 'Error deleting zone',
    )));
}


// =================================================================
// 7. MODIFIER LE CONTRÔLEUR FRONT validateordercarrier.php
//    POUR GÉRER LES REQUÊTES AJAX
//    Fichier: controllers/front/validateordercarrier.php
// =================================================================

// Au début de la classe, ajouter :
public function initContent()
{
    parent::initContent();

    // Gère les actions AJAX pour les zones
    if (Tools::getValue('action')) {
        $this->processAjaxActions();
    }
}

protected function processAjaxActions()
{
    $action = Tools::getValue('action');

    switch ($action) {
        case 'saveDeliveryZone':
            $this->ajaxProcessSaveDeliveryZone();
            break;
        case 'getZone':
            $this->ajaxProcessGetZone();
            break;
        case 'deleteDeliveryZone':
            $this->ajaxProcessDeleteDeliveryZone();
            break;
        // Vos autres actions existantes...
    }
}

protected function ajaxProcessSaveDeliveryZone()
{
    require_once(dirname(__FILE__).'/../../classes/PrestatillHomeDeliveryZone.php');

    $id_delivery_zone = (int)Tools::getValue('id_delivery_zone');
    $zone_name = Tools::getValue('zone_name');
    $id_store = (int)Tools::getValue('id_store');
    $id_carrier = (int)Tools::getValue('id_carrier');
    $zone_type = Tools::getValue('zone_type');
    $zone_data = Tools::getValue('zone_data');
    $zone_color = Tools::getValue('zone_color');
    $active = (int)Tools::getValue('active');
    $priority = (int)Tools::getValue('priority');

    if ($id_delivery_zone > 0) {
        $zone = new PrestatillHomeDeliveryZone($id_delivery_zone);
    } else {
        $zone = new PrestatillHomeDeliveryZone();
        $zone->date_add = date('Y-m-d H:i:s');
        $zone->id_shop = (int)Context::getContext()->shop->id;
        $zone->id_shop_group = (int)Context::getContext()->shop->id_shop_group;
    }

    $zone->zone_name = $zone_name;
    $zone->id_store = $id_store;
    $zone->id_carrier = $id_carrier;
    $zone->zone_type = $zone_type;
    $zone->zone_data = $zone_data;
    $zone->zone_color = $zone_color;
    $zone->active = $active;
    $zone->priority = $priority;
    $zone->date_upd = date('Y-m-d H:i:s');

    if ($zone->save()) {
        die(json_encode(array(
            'status' => 'success',
            'message' => 'Zone saved successfully',
            'id_delivery_zone' => $zone->id,
        )));
    } else {
        die(json_encode(array(
            'status' => 'error',
            'message' => 'Error saving zone',
        )));
    }
}

protected function ajaxProcessGetZone()
{
    $id_delivery_zone = (int)Tools::getValue('id_delivery_zone');

    if ($id_delivery_zone > 0) {
        $zone = Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'prestatill_homedelivery_delivery_zones`
            WHERE `id_delivery_zone` = '.(int)$id_delivery_zone
        );

        if ($zone) {
            die(json_encode(array(
                'status' => 'success',
                'zone' => $zone,
            )));
        }
    }

    die(json_encode(array(
        'status' => 'error',
        'message' => 'Zone not found',
    )));
}

protected function ajaxProcessDeleteDeliveryZone()
{
    require_once(dirname(__FILE__).'/../../classes/PrestatillHomeDeliveryZone.php');

    $id_delivery_zone = (int)Tools::getValue('id_delivery_zone');

    if ($id_delivery_zone > 0) {
        $zone = new PrestatillHomeDeliveryZone($id_delivery_zone);
        if ($zone->delete()) {
            die(json_encode(array(
                'status' => 'success',
                'message' => 'Zone deleted successfully',
            )));
        }
    }

    die(json_encode(array(
        'status' => 'error',
        'message' => 'Error deleting zone',
    )));
}


// =================================================================
// 8. INSTALLER LES TABLES EN BASE DE DONNÉES
//    Exécuter le script SQL ou via l'upgrade
// =================================================================

// Option 1: Manuellement dans phpMyAdmin ou via SQL
// Copier le contenu de sql/zones_install.php et l'exécuter

// Option 2: Via l'upgrade du module
// Le fichier upgrade/upgrade-3.1.0.php sera exécuté automatiquement

// Option 3: Ajouter dans la méthode install() du module
public function install()
{
    // Code existant...

    // Ajoute l'installation des tables de zones
    include(dirname(__FILE__).'/sql/zones_install.php');
    foreach ($sql_requests as $request) {
        if (!Db::getInstance()->execute($request)) {
            return false;
        }
    }

    return true;
}
