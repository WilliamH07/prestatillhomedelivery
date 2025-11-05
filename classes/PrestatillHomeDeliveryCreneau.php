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

class PrestatillHomeDeliveryCreneau extends ObjectModel
{
    public $id_creneau;
    public $id_week;
    public $id_day;
    public $id_order;
    public $id_cart;
    public $cause;
    public $hour;
    public $day;
	// 1.0.3
	public $reminded = 0;
	public $store_informed = 0;
	// 1.2.2
	public $hour_end = null;
	// 1.3.1 
	public $id_reference = 0;
	public $manual = 0;
	public $manual_comment = '';

    public static $definition = array(
        'table' => 'prestatill_homedelivery_creneau',
        'primary' => 'id_creneau',
        'multilang' => false,
        'fields' => array(
            'id_week' =>  array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_day' =>   array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_cart' =>  array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'cause' =>    array('type' => self::TYPE_STRING, 'validate' => 'isCatalogName', 'size' => 128),
            'hour' =>     array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'day' =>      array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            //1.0.3
            'reminded' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'store_informed' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            //1.2.2
            'hour_end' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            //1.3.1
            'id_reference' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'manual' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'manual_comment' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
        ),

    );

    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
    }

    public static function getAllCreneauByIdCart($id_cart)
    {
        $request = 'SELECT * FROM '._DB_PREFIX_.'prestatill_homedelivery_creneau WHERE id_cart = '.(int)$id_cart ;

        $result = Db::getInstance()->getRow($request);
        return $result;
    }

    /*
     *  Récupérer un id_creneau à partir de la commande (id_order)
     */
    public static function getCreneauByIdOrder($id_order = 0)
    {
        $request = 'SELECT id_creneau FROM '._DB_PREFIX_.'prestatill_homedelivery_creneau WHERE id_order = '.(int)$id_order ;

        $result = Db::getInstance()->getRow($request);
        if (!empty($result)) {
            return new PrestatillHomeDeliveryCreneau((int)$result['id_creneau']);
        }

        return false;
    }
	
	/*
     *  1.3.1 : Récupérer un id_creneau à partir d'un créneau manuel
     */
    public static function getManualCreneau($id_week, $id_day, $hour, $id_reference)
    {
        $request = 'SELECT id_creneau FROM '._DB_PREFIX_.'prestatill_homedelivery_creneau 
			        	WHERE id_week = '.(int)$id_week.'
			        	AND id_day ='.(int)$id_day.'
			        	AND hour = "'.pSQL($hour).'"
			        	AND manual = 1
			        	AND (id_reference = '.(int)$id_reference.' AND id_reference > 0)';
						
        $result = Db::getInstance()->getRow($request);
        if (!empty($result)) {
            return new PrestatillHomeDeliveryCreneau((int)$result['id_creneau']);
        }

        return false;
    }
	
	public static function getAllManualCreneau($id_reference = 0)
    {
        $delivery_day = date('Y-m-d');
		
		$request = 'SELECT *
					FROM '._DB_PREFIX_.'prestatill_homedelivery_creneau phc
					WHERE phc.day >= "'.pSQL($delivery_day).'" 
					AND phc.manual = 1
					AND id_reference = '.(int)$id_reference;
				
		$result = Db::getInstance()->executeS($request);	
		
		$table = array();
		if(!empty($result))
		{
			foreach($result as $res)
			{
				if(!isset($table[$res['day']." ".$res['hour']]))
					$table[$res['day']." ".$res['hour']] = 0;
				
				$table[$res['day']." ".$res['hour']] += 1;
			}
			return $table;
		}
		
        return $result;		
    }

    public static function updateCartCreneau($id_creneau, $id_cart)
    {
        $cart = new Cart((int)$id_cart);
        if (Validate::isLoadedObject((int)$id_cart)) {
            $cart->id_creneau = (int)$id_creneau;
            $cart->update();
        }
    }
	
	public static function ChekIfOpen($id_day, $id_shop, $id_shop_group)
	{
		$request = 'SELECT * FROM '._DB_PREFIX_.'prestatill_homedelivery
			WHERE id_day = '.(int)$id_day.'
			AND id_shop = '.(int)$id_shop.'
			AND id_shop_group = '.(int)$id_shop_group.'
			AND openning > 0';
			
			$result = Db::getInstance()->getRow($request);
        	return $result;
	}

    public static function updateOrdersCreneau($id_creneau, $id_order, $id_cart)
    {
        $creneau = new PrestatillHomeDeliveryCreneau((int)$id_creneau);
        if (Validate::isLoadedObject($creneau)) {
            if ($creneau->id_cart == (int)$id_cart) {
                $creneau->id_order = (int)$id_order;
                $creneau->update();
            }

            $order = new Order((int)$id_order);
            if (Validate::isLoadedObject($order)) {
                $order->delivery_date = pSQL($creneau->day).' '.pSQL($creneau->hour);
                $order->update();
            }
        }
    }

    public static function updateStoreByIdcreneau($id_creneau, $id_order = 0)
    {
        $iso_lang = Context::getContext()->language->iso_code.'_'.Tools::strtoupper(Context::getContext()->language->iso_code!=='en'?Context::getContext()->language->iso_code:'US');
        setlocale(LC_TIME, $iso_lang.'.utf8');

        if ((int)$id_creneau == 0) {
            $creneau = new PrestatillHomeDeliveryCreneau();
            $creneau->id_cart = (int)Context::getContext()->cart->id;
            $creneau->day = null;
			$creneau->hour = null;
			
			if($id_order > 0)
				$creneau->id_order = (int)$id_order;
			
            $creneau->save();
        } else {
            $creneau = new PrestatillHomeDeliveryCreneau((int)$id_creneau);
        }

        if (Validate::isLoadedObject($creneau)) {
			$creneau->day = null;
			$creneau->hour = null;
			
			if($id_order > 0)
				$creneau->id_order = (int)$id_order;
			
            $creneau->update();
            return true;
        }
    }

    public static function updateOrdersState($id_order, $id_state)
    {
        $order = new Order((int)$id_order);
        if (Validate::isLoadedObject($order)) {
            $order->id_state = (int)$id_state;
            $order->update();
        }
    }
	
    public static function countOrder($delivery_date)
    {
		$request = 'SELECT COUNT(id_order) FROM '._DB_PREFIX_.'prestatill_homedelivery_creneau 
			WHERE day = LEFT("'.pSQL($delivery_date).'",10)
			AND hour = RIGHT("'.pSQL($delivery_date).'",8)
			AND id_order > 0';
	   
        $result = Db::getInstance()->executeS($request);
        return $result;
    }
	
	public static function getReservedCreneau($id_reference = 0)
    {
		$delivery_day = date('Y-m-d');
		
		$request = 'SELECT 
						phc.id_week, phc.id_day, phc.day, phc.hour 
					FROM '._DB_PREFIX_.'prestatill_homedelivery_creneau phc
					LEFT JOIN '._DB_PREFIX_.'orders o ON (phc.id_order = o.id_order)
					WHERE phc.day >= "'.pSQL($delivery_day).'" 
					AND phc.id_order > 0
					AND o.id_shop = '.(int)Context::getContext()->shop->id.' 
					AND o.id_shop_group = '.(int)Context::getContext()->shop->id_shop_group.'
					AND manual = 0';
					
		// 1.3.1 : On intègre le filtre sur les transporteurs pour les créneaux réservés
		$request .= ' AND (id_reference = 0 OR id_reference = '.(int)$id_reference.')';			
					
					
        $result = Db::getInstance()->executeS($request);
		
        $table = array();
		if(!empty($result))
		{
			foreach($result as $res)
			{
				if(!isset($table[$res['day']." ".$res['hour']]))
					$table[$res['day']." ".$res['hour']] = 0;
				
				$table[$res['day']." ".$res['hour']] += 1;
			}
			return $table;
		}
		
        return $result;
    }
	
	/*
     *  Récupérer les créneaux pour lequels envoyer un rappel
     */
    public static function getOrdersForReminder($time = 120)
    {
    	$now = date("H:i"); 
    	$max_time = date("H:i", strtotime(date("Y-m-d H:i:s").' +'.$time.' Minutes')); 
		
        $request = 'SELECT id_creneau FROM '._DB_PREFIX_.'prestatill_homedelivery_creneau WHERE day = "'.date('Y-m-d').'" AND hour >= "'.pSQL($now).'" and hour <= "'.pSQL($max_time).'" AND reminded = 0';

        $result = Db::getInstance()->executeS($request);
        
		return $result;
    }
}
