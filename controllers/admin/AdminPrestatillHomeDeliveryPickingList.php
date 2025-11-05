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

class AdminPrestatillHomeDeliveryPickingListController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'order';
        $this->className = 'Order';
        $this->bootstrap = true;
        $this->lang  = false;
		$this->allow_export = true;
		$this->list_no_link = true;
		
		parent::__construct();
		
		$id_shop = (int)Context::getContext()->shop->id;
		
        $this->_select = 'a.id_order, 
        					a.reference, od.product_reference, 
        					od.product_supplier_reference, 
        					SUM(od.product_quantity) as product_quantity, 
        					SUM(od.product_quantity_in_stock) as product_quantity_in_stock, 
        					od.total_price_tax_excl, 
        					od.total_price_tax_incl, 
        					od.product_id, 
        					od.product_attribute_id, 
        					od.product_name, 
        					c.firstname AS firstname, 
        					c.lastname AS lastname, 
        					pdc.day AS day, 
        					pdc.hour AS hour, 
        					a.current_state, 
        					os.name AS state,
                            cl.name AS cat_name,
                        	CONCAT(LEFT(c.firstname,1), \'. \', c.lastname) customer';

        $this->_join = 'LEFT JOIN '._DB_PREFIX_.'customer c ON (c.id_customer = a.id_customer)
						LEFT JOIN '._DB_PREFIX_.'order_detail od ON (a.id_order = od.id_order)
                        LEFT JOIN '._DB_PREFIX_.'order_state_lang os ON (os.id_order_state = a.current_state) AND os.id_lang ='.(int)Context::getContext()->language->id.'
                        LEFT JOIN '._DB_PREFIX_.'prestatill_homedelivery_creneau pdc ON (pdc.id_order = a.id_order)
                        LEFT JOIN '._DB_PREFIX_.'product p ON (p.id_product = od.product_id)
						LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = p.id_category_default) AND cl.id_lang ='.(int)Context::getContext()->language->id.'
                        LEFT JOIN '._DB_PREFIX_.'image_shop image_shop ON (image_shop.id_product = p.id_product AND image_shop.cover = 1 AND image_shop.id_shop = '.(int)$id_shop.')
						LEFT JOIN '._DB_PREFIX_.'image i ON (i.id_image = image_shop.id_image)';

		$objetPD = new PrestatillHomeDelivery;
        $arrayConfigPD = $objetPD->getConfigFieldsValues();
		
		$this->_where .= ' AND a.id_shop = '.(int)Context::getContext()->shop->id.' AND a.id_shop_group = '.(int)Context::getContext()->shop->id_shop_group;

        //afficher seulement les statuts présents dans la conf ($ids)
        if(!empty($arrayConfigPD[0])) 
        {
        	$this->_where .= ' AND os.id_order_state IN ('.implode(",", $arrayConfigPD).') AND pdc.id_creneau > 0';    
        }
		else 
		{
        	$this->_where .= ' AND pdc.id_creneau > 0';    
		}

		$this->_group = ' GROUP BY od.product_id,od.product_attribute_id,pdc.day';
		
		$this->_orderBy = 'pdc.day';
		$this->_orderWay = 'ASC';
        
        $this->fields_list = array(
            'product_id' => array(
                'title' => $this->l('ID Product'),
                'align' => 'center',
            ),
            'id_image' => array(
                'title' => $this->l('Image'),
                'align' => 'center',
                'orderby' => false,
                'filter' => false,
                'search' => false,
                //'callback' => 'addProductImg',
                //'callback_object' => $this,
      		),
            'product_reference' => array(
                'title' => $this->l('Product Ref.'),
                'align' => 'center',
            ),
            'product_supplier_reference' => array(
                'title' => $this->l('Supplier Ref.'),
                'align' => 'center',
            ),
        	'product_name' => array(
                'title' => $this->l('Product Name'),
                'align' => 'left',
			    'filter_key' => 'od!product_name',
            ),
            'product_quantity' => array(
                'title' => $this->l('Qty'),
                'align' => 'center',
			    'filter_key' => 'product_quantity',
			    'search' => false
            ),
            'cat_name' => array(
                'title' => $this->l('Category'),
                'align' => 'center',
			    'filter_key' => 'cl!name'
            ),
            /*'product_quantity_in_stock' => array(
                'title' => $this->l('Qty in stock'),
                'align' => 'center',
			    'filter_key' => 'od!product_quantity_in_stock',
            ),
            'total_price_tax_excl' => array(
                'title' => $this->l('Total HT'),
                'align' => 'center',
                'type' => 'price',
            ),
            'total_price_tax_incl' => array(
                'title' => $this->l('Total TTC'),
                'align' => 'center',
                'type' => 'price',  
            ),*/
            'unit_price_tax_incl' => array(
                'title' => $this->l('PU TTC'),
                'align' => 'center',
                'type' => 'price',  
            ),
            'day' => array(
                'title' => $this->l('Delivery day'),
                'align' => 'center',
                'type' => 'date',
                'filter_key' => 'pdc!day',
            ),
            /*
            'hour' => array(
                'title' => $this->l('Delivery hour'),
                'align' => 'center',
                'type' => 'hour',
            ),*/
        );   
    }

	public function initPageHeaderToolbar()
    {
    	/*
        $this->page_header_toolbar_btn['generate_pdf'] = array(
            'href' => self::$currentIndex.'&token='.$this->token,
            'desc' => $this->l('Generate PDF', null, null, false),
            'icon' => 'process-icon-save-date'
        );*/
		
		$this->page_header_toolbar_btn['print'] = array(
            'href' => 'javascript:window.print()',
            'desc' => $this->l('Print'),
            'icon' => 'process-icon-hey icon-print'
        );
		
		$this->page_header_toolbar_btn['exportorder'] = array(
            'href' => self::$currentIndex.'&token='.$this->token.'&exportorder',
            'desc' => $this->l('Export CSV'),
            'icon' => 'process-icon-export'
        );

        parent::initPageHeaderToolbar();
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

	public function addProductImg($value)
    {
	  /*
    	if($value > 0)
		{
			$img = new Image((int)$value);
			$prod = new Product($img->id_product);
			$link = new Link();
			$img_link = $link->getImageLink($prod->link_rewrite[Context::getContext()->language->id],$img->id_product.'-'.$img->id_image, ImageType::getFormatedName('small'));
	    	return '<img src="//'.$img_link.'" width="50">';
		}
		else 
		{
			$base_dir = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";
	    	return '<img src="'.$base_dir.'/img/p/'.Context::getContext()->language->iso_code.'-default-'.ImageType::getFormatedName('small').'.jpg" width="50">';
		}
    	*/
    }
}
