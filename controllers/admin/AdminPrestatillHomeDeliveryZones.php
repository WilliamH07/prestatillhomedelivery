<?php
/**
* Prestatill Home Delivery Zones Controller
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

require_once(dirname(__FILE__).'/../../classes/PrestatillHomeDeliveryZone.php');

class AdminPrestatillHomeDeliveryZonesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'prestatill_homedelivery_delivery_zones';
        $this->className = 'PrestatillHomeDeliveryZone';
        $this->identifier = 'id_delivery_zone';
        $this->lang = false;

        parent::__construct();

        $this->fields_list = array(
            'id_delivery_zone' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'zone_name' => array(
                'title' => $this->l('Zone Name'),
                'filter_key' => 'a!zone_name',
            ),
            'store_name' => array(
                'title' => $this->l('Restaurant'),
                'filter_key' => 's!name',
            ),
            'zone_type' => array(
                'title' => $this->l('Type'),
                'align' => 'center',
                'class' => 'fixed-width-sm',
            ),
            'zone_color' => array(
                'title' => $this->l('Color'),
                'align' => 'center',
                'callback' => 'displayColorBadge',
            ),
            'priority' => array(
                'title' => $this->l('Priority'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'class' => 'fixed-width-sm',
            ),
        );

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
            ),
        );
    }

    public function displayColorBadge($value, $row)
    {
        return '<span style="display:inline-block;width:30px;height:20px;background-color:'.$value.';border:1px solid #ccc;border-radius:3px;"></span>';
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        // Join avec la table des stores
        $this->_select = 's.name as store_name';
        $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'store` s ON (a.`id_store` = s.`id_store`)';

        return parent::renderList();
    }

    public function renderForm()
    {
        // Récupère les magasins disponibles
        $stores = Store::getStores();
        $stores_options = array();
        foreach ($stores as $store) {
            $stores_options[] = array(
                'id_store' => $store['id_store'],
                'name' => $store['name'].' ('.$store['city'].')',
            );
        }

        // Récupère les transporteurs
        $carriers = Carrier::getCarriers(
            $this->context->language->id,
            false,
            false,
            false,
            null,
            Carrier::ALL_CARRIERS
        );
        $carriers_options = array(
            array('id_reference' => 0, 'name' => $this->l('All carriers'))
        );
        foreach ($carriers as $carrier) {
            $carriers_options[] = array(
                'id_reference' => $carrier['id_reference'],
                'name' => $carrier['name'],
            );
        }

        // Récupère les jours de la semaine
        $days = array(
            array('id_day' => 1, 'name' => $this->l('Monday')),
            array('id_day' => 2, 'name' => $this->l('Tuesday')),
            array('id_day' => 3, 'name' => $this->l('Wednesday')),
            array('id_day' => 4, 'name' => $this->l('Thursday')),
            array('id_day' => 5, 'name' => $this->l('Friday')),
            array('id_day' => 6, 'name' => $this->l('Saturday')),
            array('id_day' => 7, 'name' => $this->l('Sunday')),
        );

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Delivery Zone'),
                'icon' => 'icon-map-marker',
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Zone Name'),
                    'name' => 'zone_name',
                    'required' => true,
                    'col' => 4,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Restaurant'),
                    'name' => 'id_store',
                    'required' => true,
                    'options' => array(
                        'query' => $stores_options,
                        'id' => 'id_store',
                        'name' => 'name',
                    ),
                    'col' => 4,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Carrier'),
                    'name' => 'id_carrier',
                    'desc' => $this->l('Select a specific carrier or "All carriers"'),
                    'options' => array(
                        'query' => $carriers_options,
                        'id' => 'id_reference',
                        'name' => 'name',
                    ),
                    'col' => 4,
                ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Zone Color'),
                    'name' => 'zone_color',
                    'col' => 2,
                    'desc' => $this->l('Color displayed on the map'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Priority'),
                    'name' => 'priority',
                    'desc' => $this->l('Higher priority zones will be checked first (higher number = higher priority)'),
                    'col' => 2,
                    'class' => 'fixed-width-sm',
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Active'),
                    'name' => 'active',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                ),
                array(
                    'type' => 'html',
                    'name' => 'zone_map',
                    'label' => $this->l('Draw Zone on Map'),
                    'html_content' => $this->renderZoneMap(),
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'zone_type',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'zone_data',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        return parent::renderForm();
    }

    protected function renderZoneMap()
    {
        $zone_data = '';
        $zone_type = 'polygon';

        if (Tools::isSubmit('id_delivery_zone')) {
            $id_delivery_zone = (int)Tools::getValue('id_delivery_zone');
            $zone = new PrestatillHomeDeliveryZone($id_delivery_zone);
            if (Validate::isLoadedObject($zone)) {
                $zone_data = $zone->zone_data;
                $zone_type = $zone->zone_type;
            }
        }

        $this->context->smarty->assign(array(
            'google_maps_api_key' => Configuration::get('PRESTATILL_HD_GOOGLE_MAPS_API_KEY'),
            'zone_data' => $zone_data,
            'zone_type' => $zone_type,
            'default_lat' => Configuration::get('PS_STORES_CENTER_LAT') ?: '48.8566',
            'default_lng' => Configuration::get('PS_STORES_CENTER_LONG') ?: '2.3522',
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'prestatillhomedelivery/views/templates/admin/zone_map.tpl'
        );
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        // Ajoute Google Maps API
        $api_key = Configuration::get('PRESTATILL_HD_GOOGLE_MAPS_API_KEY');
        if ($api_key) {
            $this->addJS('https://maps.googleapis.com/maps/api/js?key='.$api_key.'&libraries=drawing,geometry');
        }

        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/admin_zones.js');
        $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/admin_zones.css');
    }

    public function ajaxProcessSaveZone()
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
                'message' => $this->l('Zone saved successfully'),
                'id_delivery_zone' => $zone->id,
            )));
        } else {
            die(json_encode(array(
                'status' => 'error',
                'message' => $this->l('Error saving zone'),
            )));
        }
    }

    public function ajaxProcessGetZones()
    {
        $id_shop = (int)$this->context->shop->id;
        $zones = PrestatillHomeDeliveryZone::getActiveZones($id_shop);

        die(json_encode(array(
            'status' => 'success',
            'zones' => $zones,
        )));
    }

    public function ajaxProcessGetStoresAndCarriers()
    {
        // Get stores
        $stores = Store::getStores();
        $stores_data = array();
        foreach ($stores as $store) {
            $stores_data[] = array(
                'id_store' => $store['id_store'],
                'name' => $store['name'],
                'city' => isset($store['city']) ? $store['city'] : '',
            );
        }

        // Get carriers
        $carriers = Carrier::getCarriers(
            $this->context->language->id,
            false,
            false,
            false,
            null,
            Carrier::ALL_CARRIERS
        );
        $carriers_data = array();
        foreach ($carriers as $carrier) {
            $carriers_data[] = array(
                'id_carrier' => $carrier['id_carrier'],
                'id_reference' => $carrier['id_reference'],
                'name' => $carrier['name'],
            );
        }

        die(json_encode(array(
            'status' => 'success',
            'stores' => $stores_data,
            'carriers' => $carriers_data,
        )));
    }

    public function ajaxProcessGetZone()
    {
        $id_delivery_zone = (int)Tools::getValue('id_delivery_zone');

        if ($id_delivery_zone) {
            $zone = new PrestatillHomeDeliveryZone($id_delivery_zone);

            if (Validate::isLoadedObject($zone)) {
                die(json_encode(array(
                    'status' => 'success',
                    'zone' => array(
                        'id_delivery_zone' => $zone->id,
                        'zone_name' => $zone->zone_name,
                        'id_store' => $zone->id_store,
                        'id_carrier' => $zone->id_carrier,
                        'zone_type' => $zone->zone_type,
                        'zone_data' => $zone->zone_data,
                        'zone_color' => $zone->zone_color,
                        'priority' => $zone->priority,
                        'active' => $zone->active,
                    ),
                )));
            }
        }

        die(json_encode(array(
            'status' => 'error',
            'message' => $this->l('Zone not found'),
        )));
    }

    public function ajaxProcessSaveDeliveryZone()
    {
        return $this->ajaxProcessSaveZone();
    }

    public function ajaxProcessDeleteDeliveryZone()
    {
        $id_delivery_zone = (int)Tools::getValue('id_delivery_zone');

        if ($id_delivery_zone) {
            $zone = new PrestatillHomeDeliveryZone($id_delivery_zone);

            if (Validate::isLoadedObject($zone)) {
                if ($zone->delete()) {
                    die(json_encode(array(
                        'status' => 'success',
                        'message' => $this->l('Zone deleted successfully'),
                    )));
                }
            }
        }

        die(json_encode(array(
            'status' => 'error',
            'message' => $this->l('Error deleting zone'),
        )));
    }
}
