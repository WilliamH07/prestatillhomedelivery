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

require_once(dirname(_PS_MODULE_DIR_).'/modules/prestatillhomedelivery/prestatillhomedelivery.php');

class AdminPrestatillHomeDeliveryController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'order';
        $this->className = 'Order';
        $this->bootstrap = true;
        $this->lang  = false;
        $this->addRowAction('view');
		$this->allow_export = true;
		
		parent::__construct();
		
        $this->_select = 'a.id_order, c.firstname AS firstname, c.lastname AS lastname, pdc.day AS day, pdc.hour AS hour, a.current_state, os.name AS state,
                        CONCAT(LEFT(c.firstname,1), \'. \', c.lastname) customer';
						
        $this->_join = 'LEFT JOIN '._DB_PREFIX_.'customer c ON (c.id_customer = a.id_customer)
                        LEFT JOIN '._DB_PREFIX_.'order_state_lang os ON (os.id_order_state = a.current_state) AND os.id_lang ='.(int)Context::getContext()->language->id.'
                        LEFT JOIN '._DB_PREFIX_.'prestatill_homedelivery_creneau pdc ON (pdc.id_order = a.id_order)';

        $this->fields_list = array(
        	'id_order' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
            ),
            'day' => array(
                'title' => $this->l('Delivery day'),
                'align' => 'center',
                'type' => 'date',
                'filter_key' => 'pdc!day',
            ),
            'hour' => array(
                'title' => $this->l('Delivery hour'),
                'align' => 'center',
                'type' => 'hour',
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'align' => 'center',
                'filter_key' => 'c!lastname',
            ),
            'state' => array(
                'title' => $this->l('Status'),
                'align' => 'center',
                'filter_key' => 'os!name',
            ),

        );

        $objetPD = new PrestatillHomeDelivery;
		$arrayConfigPD = $objetPD->getConfigFieldsValues();
		
		$this->_where .= ' AND a.id_shop = '.(int)Context::getContext()->shop->id.' AND a.id_shop_group = '.(int)Context::getContext()->shop->id_shop_group;
		
        if(!empty($arrayConfigPD[0])) 
        {
        	$this->_where .= ' AND os.id_order_state IN ('.implode(",", $arrayConfigPD).') AND pdc.id_creneau > 0';    
        }
		else 
		{
        	$this->_where .= ' AND pdc.id_creneau > 0';    
		}  
		$this->_orderBy = 'pdc.day';
		$this->_orderWay = 'ASC';        
    }

	public function initContent()
    {
        parent::initContent();
		
		// Blocage de créneau / ajout manuel
		if(Tools::getIsset('addorder'))
		{
			// Add JS
			$this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/jquery-dateFormat.js');
			$this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/admin-order-hook.js');
			
			// Add CSS
			$this->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/admin-order-hook.css');
			$this->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/config.css');
			
			// SET LOCALES
			$iso_exp = Context::getContext()->language->iso_code;
			$iso_exp_BG = Context::getContext()->language->iso_code;
			
			switch ($iso_exp) {
				case 'ca':
					$iso_exp_BG = 'es';
					break;
				
				case 'en':
					$iso_exp_BG = 'us';
					break;
			}
				
	        $iso_lang = $iso_exp.'_'.Tools::strtoupper($iso_exp_BG);
	
	        setlocale(LC_TIME, $iso_lang.'.utf8');
			// END SET LOCALES
			
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
			
			//$this->setTemplate('add_creneau.tpl');
			$pConfig = new PrestatillHomeDelivery();
    		$carriers = $pConfig->reorderCarriers();
			
			// ON récupère les créneaux manuels
			$manual_slots = PrestatillHomeDeliveryCreneau::getAllManualCreneau();
			
			$this->context->smarty->assign(
		        array(
		            'carriers' => $carriers, 
		            'base_dir' => $base_dir,
		            'creneau' => null,
		            'manual_slots' => $manual_slots,
				)
	        );
			
			$this->setTemplate('add_creneau.tpl');
		}
		
    }

	public function initPageHeaderToolbar() 
	{
		if (empty($this->display))
			$this->page_header_toolbar_btn['new_slot'] = array(
				'href' => self::$currentIndex.'&addorder&token='.$this->token,
				'desc' => $this->l('Add a manual slot', null, null, false),
				'icon' => 'process-icon-new'
			);

		parent::initPageHeaderToolbar();
	}

    public function initToolbar()
    {

        if ($this->display == 'view') {
            $id_order = Tools::getValue('id_order');
            $order = new Order((int)$id_order);
            if (Validate::isLoadedObject($order)) {
                    Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders').'&vieworder&id_order='.(int)$id_order);
            }
        }
        return parent::initToolbar();
    }

    public function createTemplate($tpl_name)
    {
        if (file_exists(_PS_THEME_DIR_.'modules/'.$this->module->name.'/views/templates/admin/'.$tpl_name) && $this->viewAccess()) {
            return $this->context->smarty->createTemplate(_PS_THEME_DIR_.'modules/'.$this->module->name.'/views/templates/admin/'.$tpl_name, $this->context->smarty);
        } elseif (file_exists($this->getTemplatePath().$this->override_folder.$tpl_name) && $this->viewAccess()) {
            return $this->context->smarty->createTemplate($this->getTemplatePath().$this->override_folder.$tpl_name, $this->context->smarty);
        }

        return parent::createTemplate($tpl_name);
    }

    /**
     * Get path to back office templates for the module
     *
     * @return string
     */
    public function getTemplatePath()
    {
        return _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/';
    }
}
