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

require_once(dirname(__FILE__).'/classes/PrestatillHomeDeliveryConfiguration.php');
require_once(dirname(__FILE__).'/classes/PrestatillHomeDeliveryCreneau.php');
require_once(dirname(__FILE__).'/classes/PrestatillHomeDeliveryVacation.php');

class PrestatillHomeDelivery extends Module
{
    public function __construct()
    {
        $this->name = 'prestatillhomedelivery';
        $this->tab = 'administration';
        $this->version = '3.0.0';
        $this->author = 'Prestatill';
        parent::__construct();

        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->displayName = $this->l('Home Delivery with time slots');
        $this->description = $this->l('Home Delivery Slots for Prestashop');
        $this->confirmUninstall = $this->l('Confirm Uninstall ?');
        $this->module_key = '5c4529030b2273f8fdbb9efe467af208';
    }

    public function install()
    {
        if (!parent::install()
            || !$this->installSql()
            || !$this->registerHook('displayBeforeCarrier')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('displayPaymentTop')
            || !$this->registerHook('actionValidateOrder')
            || !$this->registerHook('actionGetExtraMailTemplateVars')
            || !$this->registerHook('actionOrderStatusPostUpdate')
            || !$this->registerHook('displayOrderDetail')
			|| !$this->registerHook('displayAdminOrderContentShip')
            || !$this->_installTab('AdminParentOrders', 'AdminPrestatillHomeDelivery', $this->l('HomeDelivery'))
            || !$this->_installTab('AdminParentOrders', 'AdminPrestatillHomeDeliveryPickingList', $this->l('HomeDelivery Picking List'))
			|| !$this->registerHook('displayBackOfficeHeader')
			|| !$this->registerHook('actionCarrierUpdate')
			// 1.0.3
			|| !$this->registerHook('displayInvoiceLegalFreeText')
			|| !$this->registerHook('addWebserviceResources')
			// NEW HOOK 1.7.7
			|| !$this->registerHook('displayAdminOrderTabContent')
			|| !$this->registerHook('actionGetIDZoneByAddressID')
			// 1.2.2 : compatibility for Proof Of Delivery module
			|| !$this->registerHook('displayAfterProofOfDeliveryCustomerDetails')
			// UPDATE 3.0.0
			|| !$this->registerHook('displayAdminProductsExtra')
	        || !$this->registerHook('actionProductSave')
			) {
            return false;
        }
		Configuration::updateValue('PRESTATILL_HD_VERSION',$this->version);
		if (Configuration::get('PRESTATILL_HD_CARENCE') == '')
		{
			Configuration::updateValue('PRESTATILL_HD_CARENCE', '240');
            Configuration::updateValue('PRESTATILL_HD_DUREE', '60');
            Configuration::updateValue('PRESTATILL_HD_NB_DISPO', '1');
            Configuration::updateValue('PRESTATILL_HD_OPEN', '08:00:00');
            Configuration::updateValue('PRESTATILL_HD_CLOSE', '18:30:00');
            Configuration::updateValue('PRESTATILL_HD_NB_DAY', '14');
            Configuration::updateValue('PRESTATILL_HD_CARRIER', '1');
		}
        return true;
    }

	public function hookDisplayAfterProofOfDeliveryCustomerDetails($params)
	{
		
	}

	public function hookDisplayAdminProductsExtra($params)
    {
    	// 3.0.0 : On active l'option uniquement si le module Drive n'est pas installé (sinon doublon car géré via Hook)
    	if(isset($params['id_product']) || Tools::getIsset('id_product') && !Module::isEnabled('prestatilldrive'))
		{
			if(isset($params['id_product']))
			{
				$id_product = (int)$params['id_product'];
			}
			else 
			{
				$id_product = (int)Tools::getValue('id_product');
			}

			// 2.2.0 : On récupère les jours de la semaine
			$weekdays = $this->getWeekDays();
			
			if($id_product > 0)
			{
				$actual_carence_supp = PrestatillHomeDeliveryConfiguration::getAdditionnalCarence($id_product);
				
				$product_availability = PrestatillHomeDeliveryConfiguration::getAdditionnalAvailability($id_product);

				$carence_supp = 0;
				if(!empty($actual_carence_supp))
					$carence_supp = (int)$actual_carence_supp['carence_supp'];
				
				$this->context->smarty->assign(
		            array(
			            'carence_supp' => $carence_supp, 
			            'ps16' => version_compare(_PS_VERSION_, '1.6.1.24', '<='),
			            'weekdays' => $weekdays,
			            'id_product' => $params['id_product'],
			            'product_availability' => $product_availability,
					)
		        );
				
			 	return $this->display(__FILE__, 'views/templates/hook/prestatillhomedelivery_admin_product_extra.tpl');
			}
		}
	}

	public function hookActionProductSave($params)
	{
		if(Tools::getValue('carence_supp') && !Module::isEnabled('prestatilldrive'))
		{
			$id_product = $params['id_product'];
			$carence_supp = 0;
			if($id_product > 0)
			{
				$carence_supp = (int)Tools::getValue('carence_supp');
				// On vérifie si le produit a des déclinaisons
				// On récupère la carence actuelle s'il y en a une
				
				$update_carence_supp = PrestatillHomeDeliveryConfiguration::setAdditionnalCarence($id_product, 0, $carence_supp);
			}
		}
	}

	public function hookActionCarrierUpdate($params)
	{
		// On met à jour l'id du transporteur dans la configuration du module HomeDelivery
		if((int)$params['id_carrier'] == (int)Configuration::get('PRESTATILL_HD_CARRIER')) {
			Configuration::updateValue('PRESTATILL_HD_CARRIER',(int)$params['carrier']->id);
		}
	}

	public function hookDisplayBackOfficeHeader($params)
	{
		// 3.0.0
		if($this->context->controller->controller_name == 'AdminProducts') {
			if (version_compare(_PS_VERSION_, '9.0.0', '<')) {
				$this->context->controller->addJquery();
			}
			$this->context->controller->addCSS(($this->_path).'views/css/adminprestatillhomedelivery.css', 'all');
			$this->context->controller->addJS(($this->_path).'views/js/adminprestatillhomedeliveryproduct.js?'.microtime(true));
		}
		
		if(version_compare(_PS_VERSION_, '1.7.6.8', '>') && $this->context->controller->controller_name == 'AdminOrders') {
		   
			$js = array(
				$this->_path.'views/js/jquery-dateFormat.js',
	            $this->_path.'views/js/admin-order-hook.js'
	        );
	        $css = array(
	           $this->_path.'views/css/admin-order-hook.css',
	           $this->_path.'views/css/config.css',
	           $this->_path.'views/css/config17.css'
	        );

	        $this->context->controller->addJS($js);
	        $this->context->controller->addCSS($css);
		}
	}
	
	private function _updateStoresOpenAndClose() 
	{
		$pdc = new PrestatillHomeDeliveryConfiguration();
		$update_stores = $pdc->updateStoresOpening();		
		
		return $update_stores;
		
	}

    private function _installTab($parent, $class_name, $name)
    {
        $tab = new Tab();
        $tab->id_parent = pSQL(Tab::getIdFromClassName($parent));
        $tab->class_name = pSQL($class_name);
        $tab->module = pSQL($this->name);

        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = pSQL($name);
        }
        return $tab->save();
    }

    private function _uninstallTab($class_name)
    {
        $id_tab = (int)Tab::getIdFromClassName($class_name);
        $tab = new Tab((int)$id_tab);
        return $tab->delete();
    }

    public function uninstall()
    {
        if (!parent::uninstall()
            || !$this->_uninstallTab('AdminPrestatillHomeDelivery')
            || !$this->_uninstallTab('AdminPrestatillHomeDeliveryPickingList')
			) {
            return false;
        }
            return true;
    }

	public function uninstallSql()
	{
		$sql_requests = array();
        include(dirname(__FILE__).'/sql/uninstall.php');
        $result = true;
        foreach ($sql_requests as $request) {
            if (!empty($request)) {
                $result &= Db::getInstance()->execute(trim($request));
            }
        }
        return $result;
	}

    public function installSql()
    {
        $sql_requests = array();
        include(dirname(__FILE__).'/sql/install.php');
        $result = true;
        foreach ($sql_requests as $request) {
            if (!empty($request)) {
                $result &= Db::getInstance()->execute(trim($request));
            }
        }
        return $result;
    }

    public function loadAsset()
    {
        // Load JS
        $js = array(
            $this->_path.'views/js/jquery-dateFormat.js',
            $this->_path.'views/js/config.js',
        );
        $css = array(
           $this->_path.'views/css/config.css',
        );

        $this->context->controller->addJS($js);
        $this->context->controller->addCSS($css);
    }

    public function processConfiguration()
    {
        if (Tools::isSubmit('submitParameters')) {
            Configuration::updateValue('PRESTATILL_HD_CARENCE', Tools::getValue('PRESTATILL_HD_CARENCE'));
            Configuration::updateValue('PRESTATILL_HD_DUREE', Tools::getValue('PRESTATILL_HD_DUREE'));
            Configuration::updateValue('PRESTATILL_HD_NB_DISPO', Tools::getValue('PRESTATILL_HD_NB_DISPO'));
            Configuration::updateValue('PRESTATILL_HD_CARRIER', Tools::getValue('PRESTATILL_HD_CARRIER'));
            Configuration::updateValue('PRESTATILL_HD_OPEN', Tools::getValue('PRESTATILL_HD_OPEN'));
            Configuration::updateValue('PRESTATILL_HD_CLOSE', Tools::getValue('PRESTATILL_HD_CLOSE'));
            Configuration::updateValue('PRESTATILL_HD_NB_DAY', Tools::getValue('PRESTATILL_HD_NB_DAY'));
            Configuration::updateValue('PRESTATILL_HD_SLOT_MIN_DURATION', Tools::getValue('PRESTATILL_HD_SLOT_MIN_DURATION'));

            $id_lang = (int)$this->context->language->id;
            $states = OrderState::getOrderStates((int)$id_lang);
            $tmp_search=array();
            $tmp_state = array();
            foreach ($states as $state) {
                if (Tools::getIsset('PRESTATILL_HD_STATE_PREPARE'.(int)$state['id_order_state'])) {
                    $tmp_search[] = (int)$state['id_order_state'];
                    $tmp_state[] = $state;
                }
            }
            Configuration::updateValue('PRESTATILL_HD_STATE_PREPARE', implode(';', $tmp_search));
			Configuration::updateValue('PS_API_KEY',Tools::getValue('PS_API_KEY'));
			Configuration::updateValue('PRESTATILL_HD_SEND_EMAIL',Tools::getValue('PRESTATILL_HD_SEND_EMAIL'));
			Configuration::updateValue('PRESTATILL_HD_DISPLAY_TABLE',Tools::getValue('PRESTATILL_HD_DISPLAY_TABLE'));
			// 1.0.3
			Configuration::updateValue('PRESTATILL_HD_MODIFY_PDF',Tools::getValue('PRESTATILL_HD_MODIFY_PDF'));
			Configuration::updateValue('PRESTATILL_HD_SEND_REMINDER',Tools::getValue('PRESTATILL_HD_SEND_REMINDER'));
			Configuration::updateValue('PRESTATILL_HD_SEND_REMINDER_TIME',Tools::getValue('PRESTATILL_HD_SEND_REMINDER_TIME'));

            return true;
        }
    }

    public function getConfigFieldsValues()
    {
        $id_lang = (int)$this->context->language->id;
        $states = OrderState::getOrderStates((int)$id_lang);
        $arrayConfig = array();

        foreach ($states as $state) {
            $arrayConfig['STATES_'.$state['id_order_state']] = (int)Configuration::get('PRESTATILL_HD_STATE_PREPARE'.$state['id_order_state']);
        }
        $arrayState = explode(';', ''.Configuration::get('PRESTATILL_HD_STATE_PREPARE'));
        return $arrayState;
    }

    public function assignConfiguration()
    {
            $arrayState = array();
            $arrayState = explode(';', ''.Configuration::get('PRESTATILL_HD_STATE_PREPARE'));
			
			//1.2.0 : Check if id_carrier is elligible
			$hd_supp = [];
			if(Configuration::get('PRESTATILL_SEARCH_HD_ZIP'))
			{
				$hd_supp = json_decode(Configuration::get('PRESTATILL_HD_ZIP_SUPP'), true);
			}
			else
			{
				$hd_supp = json_decode(Configuration::get('PRESTATILL_HD_DBD_SUPP'), true);
			}	
			
			$carriers = $this->reorderCarriers();
			$elligible_carriers = [];
			
			if(!empty($carriers))
			{
				if(!empty($hd_supp))
				{
					foreach($carriers as $id_reference => $carrier)
					{
						if(isset($hd_supp[$id_reference]))
						{
							$elligible_carriers[] = $carrier['id_carrier'];
						}
					}
				}
			}
			
            $this->context->smarty->assign('carrier', Configuration::get('PRESTATILL_HD_CARRIER'));
            $this->context->smarty->assign('nbdayview', Configuration::get('PRESTATILL_HD_NB_DAY'));
            $this->context->smarty->assign('closehomedelivery', Configuration::get('PRESTATILL_HD_CLOSE'));
            $this->context->smarty->assign('openhomedelivery', Configuration::get('PRESTATILL_HD_OPEN'));
			// 1.3.1 : devient la dispo par créneau globale
            $this->context->smarty->assign('nb_dispo', Configuration::get('PRESTATILL_HD_NB_DISPO'));
            $this->context->smarty->assign('carence', Configuration::get('PRESTATILL_HD_CARENCE'));
            $this->context->smarty->assign('duree', Configuration::get('PRESTATILL_HD_DUREE'));
            $this->context->smarty->assign('arrayState', $arrayState);
            $this->context->smarty->assign('elligible_carriers', $elligible_carriers);
    }

    public function getContent()
    {
		// On vérifie que toutes les tables et champs soient bien installé
		$this->installSql();

		// 1.4.1 : Tab records
		$tab = 1;

    	// On met à jour la liste des horaires d'ouverture pour les éventuels nouveaux magasins
    	$this->_updateStoresOpenAndClose();

        $confirmation = null;
        $this->loadAsset();
        if ($this->processConfiguration()) {
            $confirmation = true;
        }

		// LPF DEV SUPPORT
		$lpf_slot_min_duration = Configuration::get('PRESTATILL_HD_SLOT_MIN_DURATION');
		$lpf_active = Configuration::get('PRESTATILL_HD_LPF_ACTIVE');

		if(Tools::isSubmit('sumbitCarrierParameters'))
		{
			// SINCE 1.4.1 : Update des paramètres par transporteur
			$carriers = $this->reorderCarriers();
			if(!empty($carriers))
			{
				foreach($carriers as $id_reference => $carrier)
				{
					Configuration::updateValue('PRESTATILL_HD_CARENCE_'.(int)$id_reference, Tools::getValue('PRESTATILL_HD_CARENCE_'.(int)$id_reference));
					Configuration::updateValue('PRESTATILL_HD_DUREE_'.(int)$id_reference, Tools::getValue('PRESTATILL_HD_DUREE_'.(int)$id_reference));
					Configuration::updateValue('PRESTATILL_HD_NB_DISPO_'.(int)$id_reference, Tools::getValue('PRESTATILL_HD_NB_DISPO_'.(int)$id_reference));
				}
			}
			$tab = 2;
		}

		if (Tools::isSubmit('submitDeliveryParameters')) {
			Configuration::updateValue('PRESTATILL_SEARCH_HD_STORE',Tools::getValue('PRESTATILL_SEARCH_HD_STORE'));
			//Configuration::updateValue('PRESTATILL_SEARCH_HD_RADIUS',Tools::getValue('PRESTATILL_SEARCH_HD_RADIUS'));
			Configuration::updateValue('PRESTATILL_SEARCH_HD_ZIP',Tools::getValue('PRESTATILL_SEARCH_HD_ZIP'));
			//Configuration::updateValue('PRESTATILL_SEARCH_HD_POSTCODES',Tools::getValue('PRESTATILL_SEARCH_HD_POSTCODES'));
			//Configuration::updateValue('PRESTATILL_HD_ID_MAIN_STORES',Tools::getValue('PRESTATILL_HD_ID_MAIN_STORES'));
			Configuration::updateValue('PRESTATILL_HD_AVAILABLE_ZONE_ONLY',Tools::getValue('PRESTATILL_HD_AVAILABLE_ZONE_ONLY'));
			$tab = 4;
		}
		
        $this->assignConfiguration();

        $id_lang = (int)$this->context->language->id;
        $carriers = $this->reorderCarriers();
        $states = OrderState::getOrderStates((int)$id_lang);
        $this->context->smarty->assign(array(
            'carriers' => $carriers,
            'states' => $states
        ));
		
		if(Configuration::get('PRESTATILL_HD_SEND_EMAIL') == null)
			Configuration::updateValue('PRESTATILL_HD_SEND_EMAIL',1); 
			
		if(Configuration::get('PRESTATILL_HD_DISPLAY_TABLE') == null)
			Configuration::updateValue('PRESTATILL_HD_DISPLAY_TABLE',1);
		
		// 1.0.3	
		if(Configuration::get('PRESTATILL_HD_MODIFY_PDF') == null)
			Configuration::updateValue('PRESTATILL_HD_MODIFY_PDF',0);
		
		if(Configuration::get('PRESTATILL_HD_SEND_REMINDER') == null)
			Configuration::updateValue('PRESTATILL_HD_SEND_REMINDER',0);
		
		if(Configuration::get('PRESTATILL_HD_SEND_REMINDER_TIME') == null)
			Configuration::updateValue('PRESTATILL_HD_SEND_REMINDER_TIME',120);
		
		// 1.0.4
		if(Configuration::get('PRESTATILL_SEARCH_HD_STORE') == null)
			Configuration::updateValue('PRESTATILL_SEARCH_HD_STORE',1);
		
		if(Configuration::get('PRESTATILL_SEARCH_HD_STORE') == 1)
			Configuration::updateValue('PRESTATILL_SEARCH_HD_ZIP',0); 
		
		// 1.2.0
		if(Configuration::get('PRESTATILL_HD_AVAILABLE_ZONE_ONLY') == null)
			Configuration::updateValue('PRESTATILL_HD_AVAILABLE_ZONE_ONLY',0);
			
        if (Tools::isSubmit('submitConfigHomeDelivery')) {
        	
            $days_by_store = PrestatillHomeDeliveryConfiguration::getAllDays();
			
			foreach($days_by_store as $d)
			{
				$update_day = new PrestatillHomeDeliveryConfiguration((int)$d['id_prestatill_homedelivery']);
			
                if (array_key_exists('PRESTATILL_HD_'.(int)$d['id_prestatill_homedelivery'], $_POST) && array_key_exists('PRESTATILL_HD_OPENING_NONSTOP_'.(int)$d['id_prestatill_homedelivery'], $_POST)) {
                    $update_day->openning = 1;
                    $update_day->nonstop = 1;
                } else if (array_key_exists('PRESTATILL_HD_'.(int)$d['id_prestatill_homedelivery'], $_POST) && array_key_exists('PRESTATILL_HD_OPENING_NONSTOP_'.(int)$d['id_prestatill_homedelivery'], $_POST) == false) {
                    $update_day->openning = 1;
                    $update_day->nonstop = 0;
                } else {
                    $update_day->openning = 0;
                    $update_day->nonstop = 0;
                }

                $update_day->hour_open_am = !empty(Tools::getValue('PRESTATILL_HD_OPENING_AM_'.(int)$d['id_prestatill_homedelivery']))?Tools::getValue('PRESTATILL_HD_OPENING_AM_'.(int)$d['id_prestatill_homedelivery']):'00:00';
                $update_day->hour_close_am = !empty(Tools::getValue('PRESTATILL_HD_CLOSING_AM_'.(int)$d['id_prestatill_homedelivery']))?Tools::getValue('PRESTATILL_HD_CLOSING_AM_'.(int)$d['id_prestatill_homedelivery']):'00:00';
                $update_day->hour_open_pm = !empty(Tools::getValue('PRESTATILL_HD_OPENING_PM_'.(int)$d['id_prestatill_homedelivery']))?Tools::getValue('PRESTATILL_HD_OPENING_PM_'.(int)$d['id_prestatill_homedelivery']):'00:00';
                $update_day->hour_close_pm = !empty(Tools::getValue('PRESTATILL_HD_CLOSING_PM_'.(int)$d['id_prestatill_homedelivery']))?Tools::getValue('PRESTATILL_HD_CLOSING_PM_'.(int)$d['id_prestatill_homedelivery']):'00:00';

                // save
                if ($update_day->save()) {
                    $confirmation = true;
                } else {
                    $confirmation = false;
                }
    		}
			$tab = 3;
    	}

		// LPF param activation
		//Configuration::updateValue('PRESTATILL_HD_LPF_ACTIVE', 1);

        $days_by_store = PrestatillHomeDeliveryConfiguration::getAllDays();
	
        if (Tools::isSubmit('submitVacation')) {
            $vacation = new PrestatillHomeDeliveryVacation();
            if (!empty(Tools::getValue('PRESTATILL_HD_VACATION_START')) && !empty(Tools::getValue('PRESTATILL_HD_VACATION_END'))) {
                $vacation->vacation_start = Tools::getValue('PRESTATILL_HD_VACATION_START');
                $vacation->vacation_end = Tools::getValue('PRESTATILL_HD_VACATION_END');

                if ($vacation->save()) {
                    $confirmation = true;
                } else {
                    $confirmation = false;
                }
            }
        }

        $vacations = PrestatillHomeDeliveryVacation::getAllVacation();
        foreach ($vacations as $vacation) {
            if (Tools::isSubmit('deleteVacation_'.(int)$vacation['id_vacation'])) {
                PrestatillHomeDeliveryVacation::deleteVacation((int)$vacation['id_vacation']);
            }
        }

        $vacations = PrestatillHomeDeliveryVacation::getAllVacation();
		
		$phd_config = new PrestatillHomeDeliveryConfiguration;
		$stores = $phd_config->getValidStores();
		$stores = $this->reorderStores($stores);
		
		// On récupère la configuration des stores actifs / inactifs
		$homedelivery_enabled = array();
		
		$cron_url = Tools::getHttpHost(true, true).__PS_BASE_URI__.basename(_PS_MODULE_DIR_).
            '/'.$this->name.'/generatecron.php?token='.Tools::substr(_COOKIE_KEY_, 34, 8);
			
		$link = new Link();
		$stores_link = $link->getAdminLink('AdminStores');
		$carriers_link = $link->getAdminLink('AdminCarriers');
		
		$zip_supp = Configuration::get('PRESTATILL_HD_ZIP_SUPP')?json_decode(Configuration::get('PRESTATILL_HD_ZIP_SUPP'), true):'';
		$dbd_supp = Configuration::get('PRESTATILL_HD_DBD_SUPP')?json_decode(Configuration::get('PRESTATILL_HD_DBD_SUPP'), true):'';
		
		// UPDATE TO 1.2.0 <> RECUP INFOS < VERSIONS
		if(version_compare(Configuration::get('PRESTATILL_HD_VERSION'), '1.2.0', '<'))
		{
			if((Configuration::get('PRESTATILL_SEARCH_HD_POSTCODES') != null
				&& Configuration::get('PRESTATILL_HD_CARRIER') != null)
				|| 
				(Configuration::get('PRESTATILL_HD_ID_MAIN_STORES') != null
				&& Configuration::get('PRESTATILL_SEARCH_HD_RADIUS') != null)) {
					
				$id_carrier_temp = (int)Configuration::get('PRESTATILL_HD_CARRIER');
				$carrier = new Carrier((int)$id_carrier_temp);
				$zips = Configuration::get('PRESTATILL_SEARCH_HD_POSTCODES');
				if(Validate::isLoadedObject($carrier))
				{
					$zone = new Zone();
					$zone->name = 'DELIVERY_ZONE_1';
					if($zone->save())
					{
						$id_zone = (int)$zone->id;
						$carrier->addZone($id_zone);
					}
					
					if(!empty($zip_supp))
					{
						foreach($zip_supp as $key => $supp)
						{
							if($supp['id_day'] > 0)
							{
								$zip_supp[$carrier->id_reference][$key] = $supp;
								$zip_supp[$carrier->id_reference][$key]['id_reference'] = (int)$carrier->id_reference;
								$zip_supp[$carrier->id_reference][$key]['id_zone'] = (int)$id_zone;
								
								unset($zip_supp[$key]);
							}
						}
					}
	
					if(empty($zip_supp))
						$zip_supp = [];
					
					// On récupère les zips principaux
					if(Configuration::get('PRESTATILL_SEARCH_HD_POSTCODES'))
					{
						$zip_supp[$carrier->id_reference][0]['id_reference'] = (int)$carrier->id_reference;
						$zip_supp[$carrier->id_reference][0]['id_zone'] = (int)$id_zone;
				        $zip_supp[$carrier->id_reference][0]['zip_supp'] = $zips;
						$zip_supp[$carrier->id_reference][0]['id_day'] = 0;
						$zip_supp[$carrier->id_reference][0]['op'] = 0;
						$zip_supp[$carrier->id_reference][0]['cl'] = 0;
					}
					
					// UPDATE TO 1.2.0 <> RECUP INFOS DBD
					if(!empty($dbd_supp))
					{
						foreach($dbd_supp as $key => $supp)
						{
							if($supp['id_day'] > 0)
							{
								$dbd_supp[$carrier->id_reference][$key]['id_reference'] = (int)$carrier->id_reference;
								$dbd_supp[$carrier->id_reference][$key] = $supp;
							}	
						}
					}
					
					if(empty($dbd_supp))
						$dbd_supp = [];
					
					// On récupère les distances principales
					if(Configuration::get('PRESTATILL_SEARCH_HD_RADIUS'))
					{
						$dbd_supp[$carrier->id_reference][$key]['id_reference'] = (int)$carrier->id_reference;
						$dbd_supp[$carrier->id_reference][$key]['id_store'] = (int)Configuration::get('PRESTATILL_HD_ID_MAIN_STORES');
						$dbd_supp[$carrier->id_reference][$key]['dbd_supp'] = (int)Configuration::get('PRESTATILL_SEARCH_HD_RADIUS');
						$dbd_supp[$carrier->id_reference][$key]['id_day'] = 0;
					}
				}
	
				// On vide les anciennes valeurs plus utilisées
				Configuration::updateValue('PRESTATILL_SEARCH_HD_POSTCODES',null);
				Configuration::updateValue('PRESTATILL_HD_CARRIER',null);
				Configuration::updateValue('PRESTATILL_SEARCH_HD_RADIUS',0);
				Configuration::updateValue('PRESTATILL_HD_ZIP_SUPP',json_encode($zip_supp));
				Configuration::updateValue('PRESTATILL_HD_DBD_SUPP',json_encode($dbd_supp));
			}
		}
		// UPDATE TO 1.3.0 : Addition of id_zone on radius areas
		else if (version_compare(Configuration::get('PRESTATILL_HD_VERSION'), '1.3.0', '<')) {
			//dump($dbd_supp);
			if(!empty($dbd_supp))
			{
				foreach($dbd_supp as $key => $supp)
				{
					if(!isset($supp['id_zone']))
					{
						unset($dbd_supp[$key]);
					}	
				}
			}

			// On met à jour la configuration
			Configuration::updateValue('PRESTATILL_HD_DBD_SUPP',json_encode($dbd_supp));
			Configuration::updateValue('PRESTATILL_HD_VERSION', $this->version);
		}
		// UPDATE TO 1.4.0 : Addition of id_zone on radius areas
		else if (version_compare(Configuration::get('PRESTATILL_HD_VERSION'), '1.4.0', '<')) {

			if(Configuration::get('PRESTATILL_HD_ZIP_SUPP'))
				$zip_supp = json_decode(Configuration::get('PRESTATILL_HD_ZIP_SUPP'), true);

			$new_zip_supp = [];
			if(!empty($zip_supp))
			{
				foreach($zip_supp as $id_carrier => $day)
				{
					foreach($day as $id_day => $supp)
					{
						$new_zip_supp[$id_carrier][$id_day][$supp['id_zone']] = $supp;
					}
				}

				$zip_supp = $new_zip_supp;

				Configuration::updateValue('PRESTATILL_HD_ZIP_SUPP', json_encode($zip_supp));
			}

			Configuration::updateValue('PRESTATILL_HD_VERSION', $this->version);
		}
		// UPDATE TO 2.0.0 : Carence supp can now be defined by carriers
		else if (version_compare(Configuration::get('PRESTATILL_HD_VERSION'), '2.0.0', '<')) {

			if(Configuration::get('PRESTATILL_HD_CARENCE_SUPP', null, (int)$this->context->shop->id_shop_group, (int)$this->context->shop->id))
				$carence_supp = json_decode(Configuration::get('PRESTATILL_HD_CARENCE_SUPP', null, (int)$this->context->shop->id_shop_group, (int)$this->context->shop->id), true);
		
			if(!empty($carence_supp))
			{
				$new_carence_supp = [];
				foreach($carence_supp as $carence)
				{
					$new_carence_supp[0][$carence['id_day']] = $carence;
				}
			}
			
			Configuration::updateValue('PRESTATILL_HD_CARENCE_SUPP', json_encode($new_carence_supp), false, (int)$this->context->shop->id_shop_group, (int)$this->context->shop->id);
			Configuration::updateValue('PRESTATILL_HD_VERSION', $this->version);
		}
		
		if(is_array($zip_supp))
			ksort($zip_supp);
		
		if(is_array($dbd_supp))
			ksort($dbd_supp);

		$base_dir = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";
			
		// On regarde si le shop est dans un sous dossier ou non
		$shop_url = new ShopUrl(Context::getContext()->shop->id);
		if(Validate::isLoadedObject($shop_url)) 
		{
			if($shop_url != '/')
			{
				$base_dir .= $shop_url->physical_uri;
			}
		}

        $this->context->smarty->assign(array(
            'days' => $days_by_store,
            'homedelivery_enabled' => $homedelivery_enabled,
            'vacations' => $vacations,
            'module_version' => $this->version,
            'config_id_carrier' => Configuration::get('PRESTATILL_HD_CARRIER'),
            'gg_api_key' => Configuration::get('PS_API_KEY'),
            'confirmation' =>$confirmation,
            'stores' => $stores,
            'presta_version_update' => Configuration::get('PRESTATILL_HD_VERSION'),
            'store_search' => Configuration::get('PRESTATILL_SEARCH_HD_STORE'),
            'search_radius' => Configuration::get('PRESTATILL_SEARCH_HD_RADIUS'),
            'id_main_stores' => Configuration::get('PRESTATILL_HD_ID_MAIN_STORES'),
            'send_email' => Configuration::get('PRESTATILL_HD_SEND_EMAIL'),
            'display_table' => Configuration::get('PRESTATILL_HD_DISPLAY_TABLE'),
            // 1.0.3
            'modify_pdf' => Configuration::get('PRESTATILL_HD_MODIFY_PDF'),
            'cron_url' => $cron_url,
            'send_reminder' => Configuration::get('PRESTATILL_HD_SEND_REMINDER'),
            'send_reminder_time' => Configuration::get('PRESTATILL_HD_SEND_REMINDER_TIME'),
            'stores_link' => $stores_link,
			'carriers_link' => $carriers_link,
			'formatted_days' => $this->getWeekDays(),
			'carence_supp' => Configuration::get('PRESTATILL_HD_CARENCE_SUPP')?json_decode(Configuration::get('PRESTATILL_HD_CARENCE_SUPP'), true):'',
        	'id_lang' => (int)$this->context->language->id,
        	'id_shop_group' => (int)$this->context->shop->id_shop_group,
        	'id_shop' => (int)$this->context->shop->id,
        	// 1.0.4
			'dbd_supp' => $dbd_supp,
			'zip_supp' => $zip_supp,
            'store_zip' => Configuration::get('PRESTATILL_SEARCH_HD_ZIP'),
            'search_postcodes' => Configuration::get('PRESTATILL_SEARCH_HD_POSTCODES'),
            // 1.0.5
            'deliv_open' => Configuration::get('PRESTATILL_HD_OPEN'),
            'deliv_close' => Configuration::get('PRESTATILL_HD_CLOSE'),   
            'p_version_update' => Configuration::get('PRESTATILL_HD_VERSION'),
            'zones' => $this->reorderZones(),  
            // 1.2.0
            'only_av_zone' => Configuration::get('PRESTATILL_HD_AVAILABLE_ZONE_ONLY'),
			// 1.3.2
            'ps_version_bo' => _PS_VERSION_,
			// 1.4.1
			'tab' => $tab,
			'lpf_slot_min_duration' => $lpf_slot_min_duration,
			'lpf_active' => $lpf_active,
			// 3.0.0
			'base_dir' => $base_dir,
        ));
		
        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

	public function getCarrenceSupp($params)
    {
		if(Configuration::get('PRESTATILL_HD_CARENCE_SUPP',null,(int)$params['id_shop_group'],(int)$params['id_shop']))
			$carence_supp = json_decode(Configuration::get('PRESTATILL_HD_CARENCE_SUPP',null,(int)$params['id_shop_group'],(int)$params['id_shop']), true);
		
		//dump($carence_supp);
		// 2.0.0 : Carence supplémentaire par transporteur
		$carence_supp[$params['id_reference']][$params['id_day']]['hour_limit'] = $params['hour_limit'];
		$carence_supp[$params['id_reference']][$params['id_day']]['id_day'] = $params['id_day'];
		$carence_supp[$params['id_reference']][$params['id_day']]['hour_limit_end'] = $params['hour_limit_end'];
		$carence_supp[$params['id_reference']][$params['id_day']]['waiting_time'] = $params['waiting_time'];
		$carence_supp[$params['id_reference']][$params['id_day']]['id_day_end'] = $params['id_day_end'];

		//dump($carence_supp);
		//die();
		
		if(Configuration::updateValue('PRESTATILL_HD_CARENCE_SUPP', json_encode($carence_supp),false,(int)$params['id_shop_group'],(int)$params['id_shop']))
		{
			$this->context->smarty->assign(
	            array(
					'carriers' => $this->reorderCarriers(),
		            'carence_supp' => $carence_supp,
		            'formatted_days' => $this->getWeekDays(),
				)
	        );
			
	        return $this->display(__FILE__, 'views/templates/admin/tabs/carence_supp.tpl');
		}
    }

	public function reorderStores($stores)
	{
		$order_stores = [];
		if(!empty($stores))
		{
			foreach($stores as $store)
			{
				$order_stores[$store['id_store']] = $store;
			}
		}
		return $order_stores;
	}

	public function getDbdSupp($params)
    {
		if(Configuration::get('PRESTATILL_HD_DBD_SUPP',null,(int)$params['id_shop_group'],(int)$params['id_shop']))
			$dbd_supp = json_decode(Configuration::get('PRESTATILL_HD_DBD_SUPP',null,(int)$params['id_shop_group'],(int)$params['id_shop']), true);
		
		// 1.2.2 : Check de la zone
		$id_zone = (int)$params['id_zone'];
		
		// 1.3.0 : Récupération du id_carrier à partir de id_reference
		$carrier = Carrier::getCarrierByReference((int)$params['id_carrier']);
		
		if($id_zone == 0)
		{
			$zone_name = $params['zone_name']?trim($params['zone_name']):'DELIVERY_ZONE_'.(int)$carrier->id;
			// Check if zone exist
			$exist_id_zone = Zone::getIdByName($zone_name);
			if($exist_id_zone)
			{
				$zone = new Zone((int)$exist_id_zone);
				if(Validate::isLoadedObject($zone))
				{
					$exist_carrier_zone = Carrier::checkCarrierZone($carrier->id, $exist_id_zone);
					if(!$exist_carrier_zone)
					{
						$carrier = new Carrier((int)$carrier->id);
						$carrier->addZone($exist_id_zone);
					}
				}
			}
			else 
			{
				$zone = new Zone();
				$zone->name = $zone_name;
				if($zone->save())
				{
					$id_zone = (int)$zone->id;
					$carrier = new Carrier((int)$carrier->id);
					$carrier->addZone($id_zone);
				}
			}
			
			$id_zone = $zone->id;
		}
		else 
		{
			$zone = new Zone((int)$id_zone);
			if(Validate::isLoadedObject($zone))
			{
				$exist_carrier_zone = Carrier::checkCarrierZone($carrier->id, $id_zone);
				if(!$exist_carrier_zone)
				{
					$carrier = new Carrier((int)$carrier->id);
					$carrier->addZone($id_zone);
				}
			}
		}
		
		$dbd_supp[$params['id_carrier']][$params['id_day']]['id_reference'] = $params['id_carrier'];
		$dbd_supp[$params['id_carrier']][$params['id_day']]['id_store'] = $params['id_store'];
		$dbd_supp[$params['id_carrier']][$params['id_day']]['id_zone'] = (int)$id_zone;
		$dbd_supp[$params['id_carrier']][$params['id_day']]['dbd_supp'] = $params['dbd_supp'];
		$dbd_supp[$params['id_carrier']][$params['id_day']]['id_day'] = $params['id_day'];
		
		if(Configuration::updateValue('PRESTATILL_HD_DBD_SUPP', json_encode($dbd_supp),false,(int)$params['id_shop_group'],(int)$params['id_shop']))
		{
			if(!empty($dbd_supp))
			{
				ksort($dbd_supp[$params['id_carrier']]);
				ksort($dbd_supp);
			}
			
			$phd_config = new PrestatillHomeDeliveryConfiguration;
			$stores = $phd_config->getValidStores();
			$stores = $this->reorderStores($stores);
			
			$this->context->smarty->assign(
	            array(
		            'dbd_supp' => $dbd_supp,
		            'formatted_days' => $this->getWeekDays(),
			        'carriers' => $this->reorderCarriers(),
			        'stores' => $stores,
			        'zones' => $this->reorderZones(),
				)
	        );
			
	        return $this->display(__FILE__, 'views/templates/admin/tabs/delivery_supp.tpl');
		}
    }

	public function cleanZips($zips, $iso_code = null, $zone_check = false)
	{
		$zips = str_replace(',,', ',', $zips);
		//$zips = str_replace(' ', '', $zips);
		$zips = str_replace(', ', ',', $zips);
		
		if(Tools::substr($zips, -1) == ',')
			$zips = Tools::substr($zips, 0, -1);
		
		if(Tools::substr($zips, 0, 1) == ',')
			$zips = Tools::substr($zips, 1);

		$zips = explode(',', $zips);

		if(!$zone_check)
		{
			// 1.6.0 : Intégration du préfixe au niveau de la vérification du CP
			if(!empty($zips) && $iso_code != null)
			{
				foreach($zips as $key => $zip)
				{
					$sub_zip = strtolower(substr(trim($zip), 0, 2));
					if($sub_zip == strtolower($iso_code) || !is_int((int)$sub_zip))
					{
						$zips[$key] = substr(trim($zip), 2);
					}
				}
			}
		}

		asort($zips);
		$zips = implode(',', $zips);
			
		return $zips;
	}
	
	public function getZipSupp($params)
    {
		if(Configuration::get('PRESTATILL_HD_ZIP_SUPP',null,(int)$params['id_shop_group'],(int)$params['id_shop']))
			$zip_supp = json_decode(Configuration::get('PRESTATILL_HD_ZIP_SUPP',null,(int)$params['id_shop_group'],(int)$params['id_shop']), true);
		
		if($params['id_day'] > 0)
		{
			$id_day_temp = explode('_', $params['id_day']);
		}
		else 
		{
			$id_day_temp = 0;
		}
		
		$id_lang = (int)Context::getContext()->language->id;
		
		// On tri les CP
		$zips = $this->cleanZips($params['zip_supp'], null, true);
		
		// Récupération du id_carrier à partir de id_reference
		$carrier = Carrier::getCarrierByReference((int)$params['id_carrier']);
		
		// Check de la zone
		$id_zone = (int)$params['id_zone'];
		if($id_zone == 0)
		{
			$zone_name = $params['zone_name']?trim($params['zone_name']):'DELIVERY_ZONE_'.(int)$carrier->id;
			// Check if zone exist
			$exist_id_zone = Zone::getIdByName($zone_name);
			if($exist_id_zone)
			{
				$zone = new Zone((int)$exist_id_zone);
				if(Validate::isLoadedObject($zone))
				{
					$exist_carrier_zone = Carrier::checkCarrierZone($carrier->id, $exist_id_zone);
					if(!$exist_carrier_zone)
					{
						$carrier = new Carrier((int)$carrier->id);
						$carrier->addZone($exist_id_zone);
					}
				}
			}
			else 
			{
				$zone = new Zone();
				$zone->name = $zone_name;
				if($zone->save())
				{
					$id_zone = (int)$zone->id;
					$carrier = new Carrier((int)$carrier->id);
					$carrier->addZone($id_zone);
				}
			}
			
			$id_zone = $zone->id;
		}
		else 
		{
			$zone = new Zone((int)$id_zone);
			if(Validate::isLoadedObject($zone))
			{
				$exist_carrier_zone = Carrier::checkCarrierZone($carrier->id, $id_zone);
				if(!$exist_carrier_zone)
				{
					$carrier = new Carrier((int)$carrier->id);
					$carrier->addZone($id_zone);
				}
			}
		}
		
		$zip_supp[$params['id_carrier']][$params['id_day']][$id_zone]['id_reference'] = (int)$params['id_carrier'];
		$zip_supp[$params['id_carrier']][$params['id_day']][$id_zone]['id_zone'] = (int)$id_zone;
        $zip_supp[$params['id_carrier']][$params['id_day']][$id_zone]['zip_supp'] = $zips;
		if($params['id_day'] > 0)
		{
			$zip_supp[$params['id_carrier']][$params['id_day']][$id_zone]['id_day'] = $id_day_temp[0];
			$zip_supp[$params['id_carrier']][$params['id_day']][$id_zone]['op'] = Tools::substr($id_day_temp[1], 0, 5);
			$zip_supp[$params['id_carrier']][$params['id_day']][$id_zone]['cl'] = Tools::substr($id_day_temp[2], 0, 5);
		}
		else 
		{
			$zip_supp[$params['id_carrier']][$params['id_day']][$id_zone]['id_day'] = 0;
			$zip_supp[$params['id_carrier']][$params['id_day']][$id_zone]['op'] = 0;
			$zip_supp[$params['id_carrier']][$params['id_day']][$id_zone]['cl'] = 0;
		}
		
		if(Configuration::updateValue('PRESTATILL_HD_ZIP_SUPP', json_encode($zip_supp),false,(int)$params['id_shop_group'],(int)$params['id_shop']))
		{
			if(!empty($zip_supp))
			{
				ksort($zip_supp[$params['id_carrier']]);
				ksort($zip_supp);
			}
			
            $this->context->smarty->assign(
	            array(
		            'zip_supp' => $zip_supp,
		            'formatted_days' => $this->getWeekDays(),
			        'carriers' => $this->reorderCarriers(),
			        'zones' => $this->reorderZones(),
				)
	        );
			
	        return $this->display(__FILE__, 'views/templates/admin/tabs/zip_supp.tpl');
		}
    }

	public function reorderZones()
	{
		$zones = Zone::getZones(true);
		$reorder_zone = [];
		if(!empty($zones))
		{
			foreach($zones as $zone)
			{
				$reorder_zone[$zone['id_zone']] = $zone;
			}
		}
		
		return $reorder_zone;
	}

	//1.2.0 : Reorder carriers with id_carrier
	public function reorderCarriers()
	{
		$id_lang = (int)Context::getContext()->language->id;
		
		if (version_compare(_PS_VERSION_, '9.0.0', '<')) {
			// PS < 9 : on conserve l’ancien appel avec ALL_CARRIERS
			$carriers_temp = Carrier::getCarriers(
				(int)$id_lang,
				false,
				false,
				false,
				null,
				ALL_CARRIERS
			);
		} else {
			// PS ≥ 9 : on utilise la nouvelle signature (sans ALL_CARRIERS)
			$carriers_temp = Carrier::getCarriers(
				(int)$id_lang,
				false,
				false,
				false,
				null
			);
		}
		$carriers = [];
		if(!empty($carriers_temp))
		{
			foreach($carriers_temp as $carrier)
			{
				$carriers[$carrier['id_reference']] = $carrier;
				
				// 1.4.1 : on ajoute les informations par transporteur
				$carriers[$carrier['id_reference']]['carence'] = (int)Configuration::get('PRESTATILL_HD_CARENCE_'.(int)$carrier['id_reference']);
				$carriers[$carrier['id_reference']]['duration'] = (int)Configuration::get('PRESTATILL_HD_DUREE_'.(int)$carrier['id_reference']);
				$carriers[$carrier['id_reference']]['nb_dispo'] = (int)Configuration::get('PRESTATILL_HD_NB_DISPO_'.(int)$carrier['id_reference']);
			}
		}
		
		return $carriers;
	}

	public function deleteCarrenceSupp($params)
    {
    	$carence_supp = array();

		if(Configuration::get('PRESTATILL_HD_CARENCE_SUPP',null,(int)$params['id_shop_group'],(int)$params['id_shop']))
			$carence_supp = json_decode(Configuration::get('PRESTATILL_HD_CARENCE_SUPP',null,(int)$params['id_shop_group'],(int)$params['id_shop']), true);
		
		$carriers = $this->reorderCarriers();

		if(!empty($carence_supp))
		{
			foreach($carence_supp as $id_reference => $supp )
			{
				foreach($supp as $id_day => $carrence)
				{
					if($id_reference == $params['id_reference'] && $id_day == $params['id_day'])
					{
						unset($carence_supp[$params['id_reference']][$params['id_day']]);
						if(empty($carence_supp[$params['id_reference']]))
							unset($carence_supp[$params['id_reference']]);
					}
				}
				
			}

			if(Configuration::updateValue('PRESTATILL_HD_CARENCE_SUPP', json_encode($carence_supp),false,(int)$params['id_shop_group'],(int)$params['id_shop']))
			{
				$this->context->smarty->assign(
		            array(
			            'carence_supp' => $carence_supp,
			            'formatted_days' => $this->getWeekDays(),
						'carriers' => $this->reorderCarriers(),
					)
		        );
				
		        return $this->display(__FILE__, 'views/templates/admin/tabs/carence_supp.tpl');
			}
		}
    }
	
	// 1.0.4
	public function deleteDBDSupp($params)
    {
    	$dbd_supp = array();

		if(Configuration::get('PRESTATILL_HD_DBD_SUPP',null,(int)$params['id_shop_group'],(int)$params['id_shop']))
			$dbd_supp = json_decode(Configuration::get('PRESTATILL_HD_DBD_SUPP',null,(int)$params['id_shop_group'],(int)$params['id_shop']), true);
		
		$id_carrier = (int)$params['id_carrier'];
		$id_zone = (int)$params['id_zone'];
		
		if(!empty($dbd_supp[$id_carrier]))
		{
			foreach($dbd_supp[$id_carrier] as $key => $dbd)
			{
				if($key == $params['id_day'])
				{
					unset($dbd_supp[$id_carrier][$key]);
				}
			}
			
			// On vide s'il n'y a plus de valeur
			if(empty($dbd_supp[$id_carrier]))
				unset($dbd_supp[$id_carrier]);

			if(Configuration::updateValue('PRESTATILL_HD_DBD_SUPP', json_encode($dbd_supp),false,(int)$params['id_shop_group'],(int)$params['id_shop']))
			{
				$phd_config = new PrestatillHomeDeliveryConfiguration;
				$stores = $phd_config->getValidStores();
				$stores = $this->reorderStores($stores);
				
				if(is_array(($dbd_supp)))
				{
					if(!empty($dbd_supp[$params['id_carrier']]))
						ksort($dbd_supp[$params['id_carrier']]);
					
					ksort($dbd_supp);
				}
				
				$this->context->smarty->assign(
		            array(
			            'dbd_supp' => $dbd_supp,
			            'formatted_days' => $this->getWeekDays(),
			            'carriers' => $this->reorderCarriers(),
			            'stores' => $stores,
			            'zones' => $this->reorderZones(),
					)
		        );
				
		        return $this->display(__FILE__, 'views/templates/admin/tabs/delivery_supp.tpl');
			}
		}
    }

	// 1.0.4
	public function deleteZipSupp($params)
    {
    	$zip_supp = array();
		$id_lang = (int)Context::getContext()->language->id;

		if(Configuration::get('PRESTATILL_HD_ZIP_SUPP',null,(int)$params['id_shop_group'],(int)$params['id_shop']))
			$zip_supp = json_decode(Configuration::get('PRESTATILL_HD_ZIP_SUPP',null,(int)$params['id_shop_group'],(int)$params['id_shop']), true);
		
		$id_carrier = (int)$params['id_carrier'];
		$id_zone = (int)$params['id_zone'];

		if(!empty($zip_supp[$id_carrier]))
		{
			foreach($zip_supp[$id_carrier] as $id_day => $day)
			{
				foreach($day as $s_id_zone => $zip)
				{	
					if($zip['id_day'] == $params['id_day'] && $id_zone == $zip['id_zone'])
					{
						unset($zip_supp[$id_carrier][$id_day][$s_id_zone]);
					}

					// On vide s'il n'y a plus de valeur
					if(empty($zip_supp[$id_carrier][$id_day]))
						unset($zip_supp[$id_carrier][$id_day]);
				}
			}
			
			// On vide s'il n'y a plus de valeur
			if(empty($zip_supp[$id_carrier]))
				unset($zip_supp[$id_carrier]);

			if(Configuration::updateValue('PRESTATILL_HD_ZIP_SUPP', json_encode($zip_supp),false,(int)$params['id_shop_group'],(int)$params['id_shop']))
			{
				if(!empty($zip_supp))
				{
					if(!empty($zip_supp[$params['id_carrier']]))
						ksort($zip_supp[$params['id_carrier']]);
					
					ksort($zip_supp);
				}
				
				$this->context->smarty->assign(
		            array(
			            'zip_supp' => $zip_supp,
			            'formatted_days' => $this->getWeekDays(),
			            'carriers' => $this->reorderCarriers(),
			            'zones' => $this->reorderZones(),
					)
		        );
				
		        return $this->display(__FILE__, 'views/templates/admin/tabs/zip_supp.tpl');
			}
		}
    }
	
    public function setMedia()
    {
        parent::setMedia();
        $this->context->controller->addJS($this->_path . '/js/configurator.js');
    }
	
	public function hookActionGetIDZoneByAddressID($params)
	{
		if(Configuration::get('PRESTATILL_HD_AVAILABLE_ZONE_ONLY') && Configuration::get('PRESTATILL_SEARCH_HD_ZIP'))
		{
			if(isset($params['id_address']))
			{
				//@TODO : vérifier si on active l'option ou non ?
				$address = new Address((int)$params['id_address']);
				if(Validate::isLoadedObject($address))
				{
					//1.2.0 : Check if id_carrier is elligible
					$zip_supp = null;
					if(Configuration::get('PRESTATILL_HD_ZIP_SUPP'))
						$zip_supp = json_decode(Configuration::get('PRESTATILL_HD_ZIP_SUPP'), true);
					
					$id_zone = 0;
					if(!empty($zip_supp))
					{
						$carriers = $this->reorderCarriers();
						if(!empty($carriers))
						{
							foreach($carriers as $carrier)
							{
								if(isset($zip_supp[$carrier['id_reference']]))
								{
									foreach($zip_supp[$carrier['id_reference']] as $id_day => $day)
									{
										foreach($day as $s_id_zone => $supp)
										{
											$zips = $this->cleanZips($supp['zip_supp'], substr($address->country, 0, 2));
											if(!empty($zips))
											{
												// On check les ranges en priorité
												$zips = explode(',', $zips);
												foreach($zips as $zip)
												{
													$pos = strpos($zip, '*');
													if($pos)
													{
														if(Tools::substr($address->postcode, 0, $pos) == Tools::substr($zip, 0, $pos))
															return (int)$supp['id_zone'];
													}
												}
												// Sinon on check les CP ?
												if(in_array($address->postcode, $zips))
													return (int)$supp['id_zone'];
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		//1.2.2
		else if(Configuration::get('PRESTATILL_HD_AVAILABLE_ZONE_ONLY') && Configuration::get('PRESTATILL_SEARCH_HD_STORE'))
		{
			if(isset($params['id_address']))
			{
				//@TODO : vérifier si on active l'option ou non ?
				$address = new Address((int)$params['id_address']);
				if(Validate::isLoadedObject($address))
				{
					// 1. On géocode l'adresse du client et on récupère le magasin le plus proche 
					// parmi tous les magasins possibles paramétrés (distance la + petite en km)
					$dbd_supp = null;
					if(Configuration::get('PRESTATILL_HD_DBD_SUPP'))
						$dbd_supp = json_decode(Configuration::get('PRESTATILL_HD_DBD_SUPP'), true);
					
					$id_zone = 0;
					$dist_max = 0;
					if(!empty($dbd_supp))
					{
						// On récupère les id_stores éligibles
						$phd_config = new PrestatillHomeDeliveryConfiguration();
						$address_valid = $phd_config->getValidStores(true);
						//dump($address_valid);
						if(!empty($address_valid))
						{
							foreach($dbd_supp as $dbd)
							{
								foreach($dbd as $supp)
								{
									// On récupère la première limite de zone
									//dump([$supp['dbd_supp'],$address_valid[0]['distance']]);
									if((float)$supp['dbd_supp'] >= (float)$address_valid[0]['distance'])
									{
										// On vérifie si la prochaine distance est également élligible ou non
										if(((float)$supp['dbd_supp'] < $dist_max || $dist_max == 0) && $address_valid[0]['id_store'] == $supp['id_store'])
										{
											$dist_max = (int)$supp['dbd_supp'];
											$id_zone = (int)$supp['id_zone'];
										}
										//dump([$dist_max,$id_zone,(float)$address_valid[0]['distance']]);
									}
								}
							}
							//dump([$dist_max,$id_zone]);
							//@TODO : Laurent continuer ici
							//die();
							if($id_zone > 0)
								return (int)$id_zone;
						}
					} 
				}
			}
		}
	}
	
    public function hookDisplayBeforeCarrier($params)
    {
    	$iso_lang = Context::getContext()->language->iso_code.'_'.Tools::strtoupper(Context::getContext()->language->iso_code!=='en'?Context::getContext()->language->iso_code:'US');
        setlocale(LC_TIME, $iso_lang.'.utf8');
		
        $this->assignConfiguration();

        $base_dir = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";
		
		// On regarde si le shop est dans un sous dossier ou non
		$shop_url = new ShopUrl(Context::getContext()->shop->id);
		
		// On récupère l'adresse de livraison du client
		$id_cart = (int)Context::getContext()->cart->id;
		$address = null;
		if((int)$id_cart > 0)
		{
			$id_address = (int)Context::getContext()->cart->id_address_delivery;
			if((int)$id_address > 0) {
				$address = new Address((int)$id_address);
			}
		}
		
		if(Validate::isLoadedObject($shop_url)) 
		{
			if($shop_url != '/')
			{
				$base_dir .= $shop_url->physical_uri;
			}
		}

        $this->context->smarty->assign(array(
            'base_dir' => $base_dir,
            'base_url' => $base_dir,
            'address' => $address,
            'max_days' => (int)Configuration::get('PRESTATILL_HD_NB_DAY'),
			'lpf_slot_min_duration' => Configuration::get('PRESTATILL_HD_SLOT_MIN_DURATION'),
        ));

        return $this->display(__FILE__, 'views/templates/front/carrier.tpl');
    }

    public function assignCreneau()
    {
        $context = Context::getContext();
        $id_cart = (int)$context->cart->id;

        $result = PrestatillHomeDeliveryCreneau::getAllCreneauByIdCart((int)$id_cart);
        $creneau = new PrestatillHomeDeliveryCreneau((int)$result['id_creneau']);
        $id_creneau = (int)$creneau->id;
        PrestatillHomeDeliveryCreneau::updateOrdersCreneau((int)$id_creneau, 0, (int)$id_cart);
        if (Validate::isLoadedObject($creneau)) {
            $this->context->smarty->assign(array(
                'creneau_day' => $creneau->day,
                'creneau_hour' => $creneau->hour,
                'id' => (int)$id_creneau,

            ));
        }
    }
    
    public function hookDisplayHeader($params)
    {
        if (isset($this->context->controller->php_self) && $this->context->controller->php_self == 'index') {
            $this->context->controller->addCSS(_THEME_CSS_DIR_.'product_list.css');
        }

		// 3.0.0
        if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
			// Pour PS 9 et supérieures on utilise registerJavascript()
			$this->context->controller->registerJavascript(
				'module-prestatilldrive-jquery',
				'https://code.jquery.com/jquery-3.6.0.min.js',
				[
					'server'   => 'remote',
					'position' => 'bottom',
					'priority' => 50,
					'fallback' => [
						'test' => 'window.jQuery',
						'path' => 'module:'.$this->name.'/views/js/jquery-3.6.0.min.js'
					],
				]
			);
		} else {
			// Pour PS < 9 : on conserve l'ancien addJquery()
			$this->context->controller->addJquery();
		}

        $this->context->controller->addJS(($this->_path).'views/js/jquery-dateFormat.js', 'all');
		
		if(version_compare(_PS_VERSION_, '1.7.3', '>'))
		{
			// 1.4.2 : Use this JS file for hummingrid theme
			//$this->context->controller->addJS(($this->_path).'views/js/carrier18.js', 'all');
			// LPF
			if(Configuration::get('PRESTATILL_HD_LPF_ACTIVE'))
			{
				$this->context->controller->addJS(($this->_path).'views/js/carrierlpf.js', 'all');
				$this->context->controller->addCSS(($this->_path).'views/css/configlpf.css', 'all');
			}
			else
			{
				$this->context->controller->addJS(($this->_path).'views/js/carrier.js', 'all');
				$this->context->controller->addCSS(($this->_path).'views/css/config.css', 'all');
			}
			
		}
		else
		{
			$this->context->controller->addJS(($this->_path).'views/js/carrier16.js', 'all');
			$this->context->controller->addCSS(($this->_path).'views/css/config16.css', 'all');
		}
    }

    public function hookActionValidateOrder($params)
    {
        $iso_lang = Context::getContext()->language->iso_code.'_'.Tools::strtoupper(Context::getContext()->language->iso_code!=='en'?Context::getContext()->language->iso_code:'US');
        setlocale(LC_TIME, $iso_lang.'.utf8');
		
        $id_order = (int)$params['order']->id;
        $id_cart = (int)$params['order']->id_cart;

        $result = PrestatillHomeDeliveryCreneau::getAllCreneauByIdCart((int)$id_cart);
		
		if(!empty($result)) {
			
			$creneau = new PrestatillHomeDeliveryCreneau((int)$result['id_creneau']);

	        if (Validate::isLoadedObject($creneau)) {
	        	
				if($creneau->day != '0000-00-00' && $creneau->hour != '00:00:00') 
				{
					$id_creneau = (int)$creneau->id_creneau;
		            PrestatillHomeDeliveryCreneau::updateOrdersCreneau((int)$id_creneau, (int)$id_order, (int)$id_cart);
		
		            // Un fois le créneau assigné on le supprime du cookie pour libérer les prochaines commandes.
		            $context = Context::getContext();
		            //@TODO : donner la possibilité de conserver ou supprimer le magasin sélectionné
		            $context->cookie->__set('hd_msg', '');
		            $context->cookie->__set('hd_id_creneau', '');
		            $context->cookie->write();
					
					// On propose d'envoyer un mail distinct avec les informations de retrait
					if(Configuration::get('PRESTATILL_HD_SEND_EMAIL') == 1)
					{
						// On envoi un mail si PS < à 1.6.1.5
						$id_lang = (int)Context::getContext()->language->id;
						
						$order = new Order((int)$id_order);
						$customer = new Customer($order->id_customer);
						$email = null;
						if(Validate::isLoadedObject($customer))
						{
							$email = $customer->email;
						}

						// 1.4.1 Replace STRFTIME
						$pHD = new PrestatillHomeDelivery();
						$msg_creneau = $pHD->getMsgCreneau($creneau);

						$cname = $customer->firstname. ' '.$customer->lastname;

						$vars = array(
								'{firstname}' => $customer->firstname,
								'{lastname}' => $customer->lastname,
								'{shop_name}' => 'Nom boutique',
								'{msg_creneau}' => $msg_creneau,
								'{order_ref}' => $order->reference,
								'{order_date}' => date('d-m-Y', strtotime($order->date_add))
								);
								
						$template_path = _PS_MODULE_DIR_.'prestatillhomedelivery/mails/';   
			
						$template = 'creneau';
						
						@Mail::Send(
							$id_lang, 
							$template, 
							$this->l('Some informations about your order'), 
							$vars, 
							$email,
							$cname,
							null,
							null,
							null,
							null,
							$template_path
						);
					}
				}
			}
		}
    }

	public function getMsgCreneau($creneau)
	{
		$day_creneau = $creneau->day;
		$day = date('d', strtotime($day_creneau));
		$month = $this->l(date('F', strtotime($day_creneau)));
		$hour_creneau = date("H:i", strtotime($creneau->hour));
		$creneau_duration = Configuration::get('PRESTATILL_HD_DUREE');

		// 1.4.1 : On vérifie s'il existe un paramètre spéciale pour le transporteur
		if(isset($creneau->id_reference) && Configuration::get('PRESTATILL_HD_DUREE_'.$creneau->id_reference) >= 0)
		{
			$creneau_duration = (int)Configuration::get('PRESTATILL_HD_DUREE_'.$creneau->id_reference);
		}

		$end_creneau = date("H:i", strtotime($hour_creneau.' +'.$creneau_duration.' Minutes')); 

		// 1.4.1 : Addition of END CRENEAU
		if($creneau->hour_end != null && $creneau_duration == 0)
		{
			$end_creneau = date("H:i", strtotime($creneau->hour_end));
		}

		// Fix pour les anciens créneaux si on est à 0 en durée et que hour_end = null
		if(($creneau->hour_end == null || $creneau->hour_end == '00:00:00') && $creneau_duration == 0)
		{
			$end_creneau = date("H:i", strtotime($hour_creneau.' +60 Minutes'));
		}

		// LPF 
		if(Configuration::get('PRESTATILL_HD_LPF_ACTIVE'))
		{
			$end_creneau = date("H:i", strtotime($creneau->hour_end));
		}
		
		return $this->getDateFormat($day_creneau).' '.$this->l('between').' '.$hour_creneau.' '.$this->l('and').' '.$end_creneau;
	}

	// 3.0.0 Gestion des différents formats de date
	public function getDateFormat($day_creneau)
	{
		if(Configuration::get('PRESTATILL_HD_DATE_FORMAT') != '')
		{
			switch (Configuration::get('PRESTATILL_HD_DATE_FORMAT')) {
				case 'l d F':
					$month = $this->l(date('F', strtotime($day_creneau)));
					$day = date('d', strtotime($day_creneau));
					$day_name = $this->l(date('l', strtotime($day_creneau)));
					$date = $day_name.' '.$day.' '.$month;

					break;

				case 'l, d F':
					$month = $this->l(date('F', strtotime($day_creneau)));
					$day = date('d', strtotime($day_creneau));
					$day_name = $this->l(date('l', strtotime($day_creneau)));
					$date = $day_name.', '.$day.' '.$month;

					break;

				case 'l d F Y':
					$month = $this->l(date('F', strtotime($day_creneau)));
					$day = date('d', strtotime($day_creneau));
					$day_name = $this->l(date('l', strtotime($day_creneau)));
					$year = date('Y', strtotime($day_creneau));
					$date = $day_name.' '.$day.' '.$month.' '.$year;

					break;

				case 'D d F':
					$month = $this->l(date('F', strtotime($day_creneau)));
					$day = date('d', strtotime($day_creneau));
					$day_name = $this->l(date('D', strtotime($day_creneau)));
					$year = date('Y', strtotime($day_creneau));
					$date = $day_name.' '.$day.' '.$month;

					break;

				case 'D, d F':
					$month = $this->l(date('F', strtotime($day_creneau)));
					$day = date('d', strtotime($day_creneau));
					$day_name = $this->l(date('D', strtotime($day_creneau)));
					$year = date('Y', strtotime($day_creneau));
					$date = $day_name.', '.$day.' '.$month;

					break;

				case 'D d F Y':
					$month = $this->l(date('F', strtotime($day_creneau)));
					$day = date('d', strtotime($day_creneau));
					$day_name = $this->l(date('D', strtotime($day_creneau)));
					$year = date('Y', strtotime($day_creneau));
					$date = $day_name.' '.$day.' '.$month.' '.$year;

					break;

				case 'd F Y':
					$month = $this->l(date('F', strtotime($day_creneau)));
					$day = date('d', strtotime($day_creneau));
					$year = date('Y', strtotime($day_creneau));
					$date = $day.' '.$month.' '.$year;

					break;

				case 'd/m/Y':
					$month = date('m', strtotime($day_creneau));
					$day = date('d', strtotime($day_creneau));
					$year = date('Y', strtotime($day_creneau));
					$date = $day.'/'.$month.'/'.$year;

					break;

				case 'Y-m-d':
					$month = date('m', strtotime($day_creneau));
					$day = date('d', strtotime($day_creneau));
					$year = date('Y', strtotime($day_creneau));
					$date = $year.'-'.$month.'-'.$day;

					break;
				
				default:
					$month = $this->l(date('F', strtotime($day_creneau)));
					$day = date('d', strtotime($day_creneau));
					$day_name = $this->l(date('l', strtotime($day_creneau)));
					$date = $day_name.' '.$day.' '.$month;

					break;
			}
		}
		else
		{
			$month = $this->l(date('F', strtotime($day_creneau)));
			$day = date('d', strtotime($day_creneau));
			$day_name = $this->l(date('l', strtotime($day_creneau)));
			$date = $day_name.' '.$day.' '.$month;
		}
		
		return $date;
	}

    public static function changeOrderState($id_order_state, $order)
    {
        $order_state = new OrderState((int)$id_order_state);
        $errors = array();
        //d(Validate::isLoadedObject($order_state));
        if (Validate::isLoadedObject($order_state)) {
            $current_order_state = $order->getCurrentOrderState();
            //d($current_order_state);
            if ((int)$current_order_state->id != (int)$order_state->id) {
                // Create new OrderHistory
                $history = new OrderHistory();
                $history->id_order = (int)$order->id;
                $history->id_employee = 0;

                $use_existings_payment = false;
                if (!$order->hasInvoice()) {
                    $use_existings_payment = true;
                }

                $history->changeIdOrderState((int)$order_state->id, $order, $use_existings_payment);

                $carrier = new Carrier((int)$order->id_carrier, (int)$order->id_lang);
                $templateVars = array();
                if ($history->id_order_state == Configuration::get('PS_OS_SHIPPING') && $order->shipping_number) {
                    $templateVars = array('{followup}' => str_replace('@', $order->shipping_number, $carrier->url));
                }

                // Save all changes
                if ($history->addWithemail(true, $templateVars)) {
                    // synchronizes quantities if needed..
                    if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                        foreach ($order->getProducts() as $product) {
                            if (StockAvailable::dependsOnStock((int)$product['product_id'])) {
                                StockAvailable::synchronize((int)$product['product_id'], (int)$product['id_shop']);
                            }
                        }
                    }
                }
            }
        } else {
            $errors[] = Tools::displayError('The new order status is invalid.');
        }
    }

    public function hookActionOrderStatusPostUpdate($params)
	{
		setlocale(LC_TIME, 'fr_FR.utf8');
		$id_order = (int)$params['id_order'];
		$order = new Order($id_order);

		if (Validate::isLoadedObject($order)) {
			$id_cart = (int)$order->id_cart;

			// Vérifie que le résultat est bien un tableau et contient la clé 'id_creneau'
			$result = PrestatillHomeDeliveryCreneau::getAllCreneauByIdCart($id_cart);

			if (is_array($result) && isset($result['id_creneau'])) {
				$creneau = new PrestatillHomeDeliveryCreneau((int)$result['id_creneau']);

				if (Validate::isLoadedObject($creneau)) {
					// On met à jour la date de livraison de la commande avec le créneau horaire
					$order->delivery_date = pSQL($creneau->day) . ' ' . pSQL($creneau->hour);
					$order->save();
				}
			}
		}
	}

    public function hookDisplayPaymentTop()
    {
        $context = Context::getContext();
        $msg = $context->cookie->hd_msg;
        $id_creneau = (int)$context->cookie->hd_id_creneau;
		
		if((int)$id_creneau > 0)
		{
            $creneau = new PrestatillHomeDeliveryCreneau((int)$id_creneau);
		
			if(Validate::isLoadedObject($creneau)) 
			{
				if($creneau->day != '0000-00-00' && $creneau->hour != '00:00:00') 
				{
					$this->context->smarty->assign(array(
			            'creneau' => $msg,
			            'id_creneau' => (int)$id_creneau,
			
			        ));
			
			    	return $this->display(__FILE__, 'views/templates/front/creneau.tpl');
				}
			}
		}
    }

    public function hookDisplayOrderDetail($params)
    {
        $creneau = PrestatillHomeDeliveryCreneau::getCreneauByIdOrder((int)$params['order']->id);
		
		if(Validate::isLoadedObject($creneau)) 
		{
			if((int)$creneau->id_order > 0) {
				
				$iso_lang = Context::getContext()->language->iso_code.'_'.Tools::strtoupper(Context::getContext()->language->iso_code!=='en'?Context::getContext()->language->iso_code:'US');
        		setlocale(LC_TIME, $iso_lang.'.utf8');
				
				if($creneau->day != '0000-00-00' && $creneau->hour != '00:00:00') 
				{
					// 1.4.1 Replace STRFTIME
					$pHD = new PrestatillHomeDelivery();
					$msg_creneau = $pHD->getMsgCreneau($creneau);
				}
				else 
				{
					$msg_creneau = null;
				}
					
				$this->context->smarty->assign(
		            array(
			            'creneau' => $creneau, 
			            'msg_creneau' => $msg_creneau,
			            'id_lang' => version_compare(_PS_VERSION_, '1.7.3', '>')?(int)Context::getContext()->language->id:0,
					)
		        );
		
		        return $this->display(__FILE__, 'views/templates/front/order-detail.tpl');
			}
		}
    }

	public function hookDisplayAdminOrderTabContent($hookParams)
	{
		$order = new Order((int)$hookParams['id_order']);
		
		$iso_lang = Context::getContext()->language->iso_code.'_'.Tools::strtoupper(Context::getContext()->language->iso_code!=='en'?Context::getContext()->language->iso_code:'US');
        setlocale(LC_TIME, $iso_lang.'.utf8');

        if (Validate::isLoadedObject($order)) {
            //$id_cart = (int)$order->id_cart;
			//$store = null;
			$msg_creneau = null;
			$day_creneau = null;
			$hour_creneau = null;
			$end_creneau = null;
			
			$js = array(
				$this->_path.'views/js/jquery-dateFormat.js',
	            $this->_path.'views/js/admin-order-hook.js'
	        );
	        $css = array(
	           $this->_path.'views/css/admin-order-hook.css',
	            $this->_path.'views/css/config.css'
	        );
	
	        $this->context->controller->addJS($js);
	        $this->context->controller->addCSS($css);

            $result = PrestatillHomeDeliveryCreneau::getCreneauByIdOrder((int)$order->id);
			
			// 1.2.2 : On récupère la liste des transporteurs éligilibles
			$carriers = $this->reorderCarriers();
			
			
			$base_dir = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";
				
			// On regarde si le shop est dans un sous dossier ou non
			$shop_url = new ShopUrl(Context::getContext()->shop->id);
			if(Validate::isLoadedObject($shop_url)) 
			{
				if($shop_url != '/')
				{
					$base_dir .= $shop_url->physical_uri;
				}
			}
			
			if($result)
			{
				$creneau = new PrestatillHomeDeliveryCreneau((int)$result->id_creneau);
			
	            if (Validate::isLoadedObject($creneau)) 
	            {
	            	if($creneau->id_order > 0)
					{
						if($creneau->day != '0000-00-00' && $creneau->hour != '00:00:00') 
						{
							// 1.4.1 Replace STRFTIME
							$pHD = new PrestatillHomeDelivery();
							$msg_creneau = $pHD->getMsgCreneau($creneau);
						}
						else 
						{
							$msg_creneau = null;
						}
						
						$this->context->smarty->assign(
				            array(
					            'creneau' => $creneau, 
					            'msg_creneau' => $msg_creneau,			            
					            'day_creneau' => $day_creneau,
					            'hour_creneau' => $hour_creneau,
					            'base_dir' => $base_dir,
					            'id_creneau' => $creneau->id,
					            'id_store' => (int)Configuration::get('PRESTATILL_HD_ID_MAIN_STORES'),	
			            		'carriers' => $carriers,	
					            'id_lang' => (int)Context::getContext()->language->id,
					            'id_order'=> (int)$hookParams['id_order']					            
							)
				        );
						
					 	return $this->display(__FILE__, 'views/templates/hook/order_creneau.tpl');
					}
	            }
			}
			// On propose la création d'un créneau
			else 
			{
				$this->context->smarty->assign(
			        array(
			            //'stores' => $stores, 
			            'base_dir' => $base_dir,
			            'id_store' => (int)Configuration::get('PRESTATILL_HD_ID_MAIN_STORES'),
			            'carriers' => $carriers,
			            'creneau' => null,
			            'id_order'=> (int)$hookParams['id_order']
					)
		        );
				
				return $this->display(__FILE__, 'views/templates/hook/order_creneau_new.tpl');
			}
        }
	}
	
	public function hookDisplayAdminOrderContentShip($params) 
	{
        $order = new Order((int)$params['order']->id);
		
		$iso_lang = Context::getContext()->language->iso_code.'_'.Tools::strtoupper(Context::getContext()->language->iso_code!=='en'?Context::getContext()->language->iso_code:'US');
        setlocale(LC_TIME, $iso_lang.'.utf8');

        if (Validate::isLoadedObject($order)) {
            //$id_cart = (int)$order->id_cart;
			//$store = null;
			$msg_creneau = null;
			$day_creneau = null;
			$hour_creneau = null;
			$end_creneau = null;
			
			$js = array(
				$this->_path.'views/js/jquery-dateFormat.js',
	            $this->_path.'views/js/admin-order-hook.js'
	        );
	        $css = array(
	           $this->_path.'views/css/admin-order-hook.css',
	            $this->_path.'views/css/config.css'
	        );
	
	        $this->context->controller->addJS($js);
	        $this->context->controller->addCSS($css);

            $result = PrestatillHomeDeliveryCreneau::getCreneauByIdOrder((int)$order->id);
			
			// 1.2.2 : On récupère la liste des transporteurs éligilibles
			$carriers = $this->reorderCarriers();
			
			$base_dir = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";
				
			// On regarde si le shop est dans un sous dossier ou non
			$shop_url = new ShopUrl(Context::getContext()->shop->id);
			if(Validate::isLoadedObject($shop_url)) 
			{
				if($shop_url != '/')
				{
					$base_dir .= $shop_url->physical_uri;
				}
			}
			
			if($result)
			{
				$creneau = new PrestatillHomeDeliveryCreneau((int)$result->id_creneau);
			
	            if (Validate::isLoadedObject($creneau)) 
	            {
	            	if($creneau->id_order > 0)
					{
						if($creneau->day != '0000-00-00' && $creneau->hour != '00:00:00') 
						{
							// 1.4.1 Replace STRFTIME
							$pHD = new PrestatillHomeDelivery();
							$msg_creneau = $pHD->getMsgCreneau($creneau);
						}
						else 
						{
							$msg_creneau = null;
						}
						
						$this->context->smarty->assign(
				            array(
					            'creneau' => $creneau, 
					            'msg_creneau' => $msg_creneau,			            
					            'day_creneau' => $day_creneau,
					            'hour_creneau' => $hour_creneau,
					            'base_dir' => $base_dir,
					            'id_creneau' => $creneau->id,
					            'id_store' => (int)Configuration::get('PRESTATILL_HD_ID_MAIN_STORES'),	
			            		'carriers' => $carriers,	
					            'id_lang' => (int)Context::getContext()->language->id,
					            'id_order'=> (int)$params['order']->id
							)
				        );
						
					 	return $this->display(__FILE__, 'views/templates/hook/order_creneau.tpl');
					}
	            }
			}
			// On propose la création d'un créneau
			else 
			{
				$this->context->smarty->assign(
			        array(
			            //'stores' => $stores, 
			            'base_dir' => $base_dir,
			            'id_store' => (int)Configuration::get('PRESTATILL_HD_ID_MAIN_STORES'),
			            'carriers' => $carriers,	
			            'creneau' => null,
			            'id_order'=> (int)$params['order']->id
					)
		        );
				
				return $this->display(__FILE__, 'views/templates/hook/order_creneau_new.tpl');
			}
        }
	}
	
	public function hookDisplayInvoiceLegalFreeText($params) 
	{
		$iso_lang = Context::getContext()->language->iso_code.'_'.Tools::strtoupper(Context::getContext()->language->iso_code!=='en'?Context::getContext()->language->iso_code:'US');
        setlocale(LC_TIME, $iso_lang.'.utf8');
		
		$creneau = PrestatillHomeDeliveryCreneau::getCreneauByIdOrder((int)$params['order']->id);
		
		if(Validate::isLoadedObject($creneau)) {
				
			// 1.4.1 Replace STRFTIME
			$pHD = new PrestatillHomeDelivery();
			$msg_creneau = $pHD->getMsgCreneau($creneau);
			
			$this->context->smarty->assign(
	            array(
		            'creneau' => $creneau, 
		            'msg_creneau' => $msg_creneau,
		            'id_lang' => (int)Context::getContext()->language->id,
				)
	        );
	
	        return $this->display(__FILE__, 'views/templates/hook/order-invoice.tpl');
		}
	}

	public function hookAddWebserviceResources($params) 
	{
		if(!empty($params['resources'])) 
		{
			$params['resources']['prestatill_homedelivery_creneau'] = array('description' => 'Home Delivery Slots', 'class' => 'PrestatillHomeDeliveryCreneau');
		}
		return $params['resources'];
		
	}
	
	/**
	 * On envoie une mail de rappel si activé
	 */
	public function sendMailReminderForHomeDeliveryOrders($time = 120)
	{
		$iso_lang = Context::getContext()->language->iso_code.'_'.Tools::strtoupper(Context::getContext()->language->iso_code!=='en'?Context::getContext()->language->iso_code:'US');
        setlocale(LC_TIME, $iso_lang.'.utf8');
		
		// On récupère la liste des créneaux pour lesquels envoyer un rappel
		$slots = PrestatillHomeDeliveryCreneau::getOrdersForReminder($time);
		
		if(!empty($slots))
		{
			$id_lang = (int)Context::getContext()->language->id;

			foreach($slots as $slot)
			{
				$creneau = new PrestatillHomeDeliveryCreneau($slot['id_creneau']);
				if(Validate::isLoadedObject($creneau))
				{
					if($creneau->reminded == 0)
					{
						$order = new Order($creneau->id_order);
						if(Validate::isLoadedObject($order))
						{
							if((int)$creneau->id_order > 0) 
							{
								$customer = new Customer($order->id_customer);
								$email = null;
								if(Validate::isLoadedObject($customer))
								{
									$email = $customer->email;
								}
								
								// 1.4.1 Replace STRFTIME
								$pHD = new PrestatillHomeDelivery();
								$msg_creneau = $pHD->getMsgCreneau($creneau);

								$cname = $customer->firstname. ' '.$customer->lastname;
								
								$vars = array(
										'{firstname}' => $customer->firstname,
										'{lastname}' => $customer->lastname,
										'{shop_name}' => 'Nom boutique',
										'{msg_creneau}' => $msg_creneau,
										'{order_ref}' => $order->reference,
										'{order_date}' => date('d-m-Y', strtotime($order->date_add))
									);
										
								$template_path = _PS_MODULE_DIR_.'prestatillhomedelivery/mails/';   
						
								$template = 'creneau';
								
								if(Mail::Send(
									$id_lang, 
									$template, 
									$this->l('Reminder : Your Delivery informations'), 
									$vars, 
									$email,
									$cname,
									null,
									null,
									null,
									null,
									$template_path
								)){
									$creneau->reminded = true;
									$creneau->update();
								}
							}
						}
					}
				}
			}
		}		
	}

	public function sendEmailModification($creneau, $creation = false)
	{
		$id_lang = (int)$this->context->language->id;
		$iso_lang = Context::getContext()->language->iso_code.'_'.Tools::strtoupper(Context::getContext()->language->iso_code!=='en'?Context::getContext()->language->iso_code:'US');
        setlocale(LC_TIME, $iso_lang.'.utf8');
		
		if($creneau->id_creneau > 0)
		{
			$msg_creneau = '';
						
			// 1.4.1 Replace STRFTIME
			$pHD = new PrestatillHomeDelivery();
			$msg_creneau = $pHD->getMsgCreneau($creneau);

			$order = new Order((int)$creneau->id_order);
			if(Validate::isLoadedObject($order))
			{
				$customer = new Customer((int)$order->id_customer);
				if(Validate::isLoadedObject($customer))
				{
					$email = $customer->email;
					$cname = $customer->firstname. ' '.$customer->lastname;
					
					$vars = array(
							'{firstname}' => $customer->firstname,
							'{lastname}' => $customer->lastname,
							'{shop_name}' => 'Nom boutique',
							'{msg_creneau}' => $msg_creneau,
							'{order_ref}' => $order->reference,
							'{order_date}' => date('d-m-Y', strtotime($order->date_add))
						);
							
				}
			}

			$template_path = _PS_MODULE_DIR_.'prestatillhomedelivery/mails/';   
			$template = 'creneau';
			
			$mail_object = $this->l('Modification of your Delivery Slot');
			
			if($creation == true)
				$mail_object = $this->l('Informations about your Delivery Slot');
			
			if(
				Mail::Send(
						$id_lang, 
						$template, 
						$mail_object, 
						$vars, 
						$email,
						$cname,
						null,
						null,
						null,
						null,
						$template_path
					)
				)
			{
				return true;
			}
		}
	}
	
	public function getDays($force = false)
	{
		if($force == false)
		{
			$days = array('Mon' => 1,'Tue' => 2,'Wed' => 3,'Thu' => 4,'Fri' => 5,'Sat' => 6,'Sun' => 7);
		}
		else 
		{
			// On identifie la locale courante si celle souhaitée par l'utilisateur n'est pas installée
			$currentLocale = setlocale(LC_CTYPE, 0);

			switch ($currentLocale) {
				/*case 'fr_FR.UTF-8':
					$days = array('lun.' => 1,'mar.' => 2,'mer.' => 3,'jeu.' => 4,'ven.' => 5,'sam.' => 6,'dim.' => 7);
					break;*/
				
				default:
					$days = array('Mon' => 1,'Tue' => 2,'Wed' => 3,'Thu' => 4,'Fri' => 5,'Sat' => 6,'Sun' => 7);
					break;
			}
		}
		
		return $days;
	}
	
	public function getWeekDays($all_days = false) 
	{
		if($all_days == true)
			return $this->l('All days'); 
			
		$days = array(
	        '1' => $this->l('Monday'),
	        '2' => $this->l('Tuesday'),
	        '3' => $this->l('Wednesday'),
	        '4' => $this->l('Thursday'),
	        '5' => $this->l('Friday'),
	        '6' => $this->l('Saturday'),
	        '7' => $this->l('Sunday'),
	    );
		
		return $days;
	}
	
	/*
	 * FOR PS 1.6.X
	 */
	public function hookActionGetExtraMailTemplateVars($params)
	{
		if (isset($params['cart']->id) && (Tools::getValue('controller') != 'validateordercarrier' || $params['template'] == 'new_order' || $params['template'] == 'order_conf')) 
		{
			$result = PrestatillHomeDeliveryCreneau::getAllCreneauByIdCart((int)$params['cart']->id);
			
			// Vérifier si $result est un tableau et contient 'id_creneau'
			if ($result && isset($result['id_creneau'])) {
				$creneau = new PrestatillHomeDeliveryCreneau((int)$result['id_creneau']);
				
				if (Validate::isLoadedObject($creneau)) {
					if ($creneau->day != '0000-00-00' && $creneau->hour != '00:00:00') {
						// 1.4.1 Replace STRFTIME
						$pHD = new PrestatillHomeDelivery();
						$msg_creneau = $pHD->getMsgCreneau($creneau);
						
						if (isset($params['template_vars']['{payment}'])) {
							$extra_vars_payment = $params['template_vars']['{payment}'];
							$this->context->smarty->assign('msg_creneau', $msg_creneau);
							$extra_vars_payment .= $this->display(__FILE__, 'views/templates/front/mail-slot.tpl');
							$params['extra_template_vars']['{payment}'] = $extra_vars_payment;
						}
					}
				}
			}
		}
	}

}
