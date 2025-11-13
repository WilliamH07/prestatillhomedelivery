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

class PrestatillHomeDeliveryConfiguration extends ObjectModel
{
    public $id_day;
    public $day;
    public $openning;
    public $hour_open_am;
    public $hour_close_am;
    public $hour_open_pm;
    public $hour_close_pm;
    public $nonstop;
	public $id_prestatill_homedelivery;
	
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'prestatill_homedelivery',
        'primary' => 'id_prestatill_homedelivery',
        'multilang' => false,
        'fields' => array(
            'day' =>                array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true),
            'openning' =>               array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),
            'hour_open_am' =>                   array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true),
            'hour_close_am' =>              array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true),
            'hour_open_pm' =>               array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true),
            'hour_close_pm' =>                  array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true),
            'nonstop' =>                array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),
            'id_day' =>                array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
        ),
    );

    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct((int)$id, (int)$id_lang);
    }
	
	public function updateStoresOpening()
    {
        $pHomeDelivery = new PrestatillHomeDelivery();
		$days = $pHomeDelivery->getWeekDays();
		
		$days_in_bdd = self::getAllDays();
		
		$db = Db::getInstance();
		
        foreach ($days as $day => $key) 
        {
			if(empty($days_in_bdd))
            {
			    $query = 'INSERT INTO '._DB_PREFIX_.'prestatill_homedelivery
			                     (
			                            id_day,
			                            day,
			                            openning,
			                            hour_open_am,
			                            hour_close_am,
			                            hour_open_pm,
			                            hour_close_pm,
			                            nonstop,
			                            id_shop,
			                            id_shop_group
			                        )
			                        VALUES ('.pSQL($day).', "'.pSQL($key).'", "1","8:30:00", "12:00:00", "13:30:00", "18:30:00", "0", '.(int)Context::getContext()->shop->id.', '.(int)Context::getContext()->shop->id_shop_group.')';				
				
				$db->execute($query);
			}
		}
    }
	
	public function http_get_contents($url, Array $opts = array())
  	{
	    $ch = curl_init();
	    if(!isset($opts[CURLOPT_TIMEOUT])) {
	    	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	    }
	    curl_setopt($ch, CURLOPT_URL, $url);
	    if(is_array($opts) && $opts) {
	    	foreach($opts as $key => $val) {
	    		curl_setopt($ch, $key, $val);
	    	}
	    }
	    if(!isset($opts[CURLOPT_USERAGENT])) {
	    	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['SERVER_NAME']);
	    }
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    if(FALSE === ($retval = curl_exec($ch))) {
	    	error_log(curl_error($ch));
	    }
	    return $retval;
  	}

    public static function getAllDays()
    {
        $request = 'SELECT * FROM '._DB_PREFIX_.'prestatill_homedelivery
                	WHERE id_shop = '.(int)Context::getContext()->shop->id.' AND id_shop_group = '.(int)Context::getContext()->shop->id_shop_group;

        $result = Db::getInstance()->executeS($request);
		
        return $result;
    }

    public static function getNbrStatePaid($params)
    {
        $request = 'SELECT COUNT(*) as nbr
                    FROM '._DB_PREFIX_.'order_history oh
                    LEFT JOIN '._DB_PREFIX_.'order_state os ON(os.id_order_state = oh.id_order_state)
                    WHERE oh.id_order = '.(int)$params['object']->id.' AND os.paid = 1';
        $result = Db::getInstance()->getRow($request);
        return $result;
    }

    public static function getOrderHistory($params, $id_state)
    {
        $request = 'SELECT *
                    FROM '._DB_PREFIX_.'order_history
                    WHERE id_order = '.(int)$params['object']->id.' AND id_order_state = '.(int)$id_state;
        $result = Db::getInstance()->executeS($request);
        return $result;
    }
	
    public function getValidStores($all_stores = false, $id_carrier = 0)
    {
    	//$id_stores = Configuration::get('PRESTATILL_HD_ID_MAIN_STORES');
		
		// On récupère l'adresse de livraison du customer
		if(isset(Context::getContext()->cart))
		{
			$id_address = (int)Context::getContext()->cart->id_address_delivery;
			$address_delivery = new Address($id_address);
		}	
		
    	if(Configuration::get('PRESTATILL_SEARCH_HD_STORE') == 1 && $all_stores == true)
		{
			// On initialise les variables
			$distance = 0;
			$distance_unit = 'km';
			$address = null;
			$lat = null;
			$long = null;
			$error = null;
			$request = null;
			
			if(Validate::isLoadedObject($address_delivery))
			{
				// On créé l'adresse pour l'api
				//$address = $address_delivery->address1.', '.$address_delivery->postcode.' '.$address_delivery->city;
				$address = $address_delivery->postcode.' '.$address_delivery->city;
				
				// On prépare la requette
				if(Configuration::get('PS_API_KEY') != '') {
						
					$geocoder = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false&key='.Configuration::get('PS_API_KEY').'&sensor=false';
					// On exécute
					$query = sprintf($geocoder, urlencode(mb_convert_encoding($address, 'UTF-8', 'ISO-8859-1')));
					$result = json_decode(Tools::file_get_contents($query));
					
					// On récupère la lat et long si on a un résultat
					if(!empty($result->results))
					{
						$json = $result->results[0];	
						$lat = $json->geometry->location->lat;
						$long = $json->geometry->location->lng;
					}
					else 
					{
						$error = $result->error_message;	
						return $error;
					}
				}
				else 
				{	
					$addr = urlencode(mb_convert_encoding($this->skip_accents($address), 'UTF-8', 'ISO-8859-1'));
					
					$return = $this->http_get_contents('https://nominatim.openstreetmap.org/search?addressdetails=1&q='.$addr.'&format=json&addressdetails=1&limit=1');
					
					$json = json_decode($return);
					
					if(!empty($json))
					{
						$lat = $json[0]->lat;
						$long = $json[0]->lon;
					}
					else 
					{
						// On tente juste avec CP + Ville
						$address = $address_delivery->postcode.' '.$address_delivery->city;
						$addr = urlencode(mb_convert_encoding($address, 'UTF-8', 'ISO-8859-1'));
						
						$return = $this->http_get_contents('https://nominatim.openstreetmap.org/search?addressdetails=1&q='.$addr.'&format=json&addressdetails=1&limit=1');
					
						$json = json_decode($return);
						
						if(!empty($json))
						{
							$lat = $json[0]->lat;
							$long = $json[0]->lon;
						}
					} 
				}
			
			/*if(Configuration::get('PRESTATILL_SEARCH_HD_RADIUS'))
				$distance = (int)Configuration::get('PRESTATILL_SEARCH_HD_RADIUS');*/
			
			// On récupère ici la MAX distance par rapport aux distances additionnel poru voir s'il y a au moins un créneau possible
			// basé sur l'adresse de livraison sélectionnée.
			if(Configuration::get('PRESTATILL_HD_DBD_SUPP',null,(int)Context::getContext()->shop->id_shop_group,(int)Context::getContext()->shop->id))
				$dbd_supp = json_decode(Configuration::get('PRESTATILL_HD_DBD_SUPP',null,(int)Context::getContext()->shop->id_shop_group,(int)Context::getContext()->shop->id), true);
			
			// 1.2.2 : On vérifie soit à partir d'un transporteur au moment du checkout, soit dans la zone (hook)
			$dist_max = 0;
			if((int)$id_carrier > 0)
			{
				$carrier = new Carrier((int)$id_carrier);
				$id_reference = 0;
				if(Validate::isLoadedObject($carrier))
				{
					$id_reference = $carrier->id_reference;
				}
				
				if(isset($dbd_supp[$id_reference]))
				{
					foreach($dbd_supp[$id_reference] as $supp)
					{
						if($supp['dbd_supp'] > $dist_max)
							$dist_max = $supp['dbd_supp'];
						
						$distance += $dist_max;
						
						if($supp['id_store'])
							$id_stores[] = (int)$supp['id_store'];
					}
				}
			}
			else 
			{
				if(!empty($dbd_supp))
				{
					foreach($dbd_supp as $dbd)
					{
						foreach($dbd as $supp)
						{
							if($supp['dbd_supp'] > $dist_max)
								$dist_max = $supp['dbd_supp'];
							
							$distance += $dist_max;

							if($supp['id_store'])
								$id_stores[] = (int)$supp['id_store'];
						}
					}
				}
			}

	        $multiplicator = ($distance_unit == 'km' ? 6371 : 3959);
	
	        $request = 'SELECT s.*, cl.name country, st.iso_code state,
				('.(int)$multiplicator.'
					* acos(
						cos(radians('.(float)$lat.'))
						* cos(radians(latitude))
						* cos(radians(longitude) - radians('.(float)$long.'))
						+ sin(radians('.(float)$lat.'))
						* sin(radians(latitude))
					)
				) distance,
				cl.id_country id_country
				FROM '._DB_PREFIX_.'store s
				'.Shop::addSqlAssociation('store', 's').'
				LEFT JOIN '._DB_PREFIX_.'country_lang cl ON (cl.id_country = s.id_country)
				LEFT JOIN '._DB_PREFIX_.'state st ON (st.id_state = s.id_state)
				WHERE s.active = 1 AND cl.id_lang = '.(int)Context::getContext()->language->id;
				
				if(!empty($id_stores)) 
				{
					//$request .= 'AND s.id_store IN '.implode(',',$id_stores);
					//$request .= ' AND s.id_store = '.(int)$id_stores;
				}
				
				//1.2.2
				if(!empty($id_stores)) 
				{
					$request .= ' AND s.id_store IN ('.implode(',',$id_stores).')';
				}
				
				$request .= ' HAVING distance <= '.(float)$dist_max.'
				ORDER BY distance ASC';
				
			}
	    }
		else 
		{
			if(version_compare(_PS_VERSION_, '1.7.3', '>'))
            {
                $request = 'SELECT s.id_country, s.id_state, s.id_store, sl.name, sl.address1, sl.address2, s.postcode, s.city, sl.hours, s.phone, s.email
                        FROM '._DB_PREFIX_.'store s
                        LEFT JOIN '._DB_PREFIX_.'store_lang sl ON (s.id_store = sl.id_store)
                        LEFT JOIN '._DB_PREFIX_.'store_shop ss ON (s.id_store = ss.id_store) AND (ss.id_shop = '.(int)Context::getContext()->shop->id.')						
                        WHERE s.active = 1
                        AND sl.id_lang = '.(int)Context::getContext()->language->id;
            }
            else 
            {
                $request = 'SELECT s.id_country, s.id_state, s.id_store, s.name, s.address1, s.address2, s.postcode, s.city, s.hours, s.phone, s.email
                        FROM '._DB_PREFIX_.'store s
                        LEFT JOIN '._DB_PREFIX_.'store_shop ss ON (s.id_store = ss.id_store) AND (ss.id_shop = '.(int)Context::getContext()->shop->id.')
                        WHERE s.active = 1';
            }
			
			// 1.0.4 : Si on est dans une recherche par CP
			if(Configuration::get('PRESTATILL_SEARCH_HD_ZIP') == 1 && $all_stores == true)
			{
				$zips = $this->recupZipCodes($id_carrier, substr($address_delivery->country, 0, 2));
				
				$formatted_zips = explode(',', $zips);
				
				if(Validate::isLoadedObject($address_delivery))
				{
					$request .= ' AND ("'.$address_delivery->postcode.'" IN ("'.trim(implode('","',$formatted_zips)).'")';
					
					// 1.3.1 : on inclue les CP RANGES
					$range_zip = [];
					foreach($formatted_zips as $zip)
					{
						if(strpos($zip, '*'))
						{
							$range_zip[] = str_replace('*', '', $zip.'%');
						}
					}

					$request .= ' OR ("'.$address_delivery->postcode.'" LIKE "'.trim(implode('" OR "'.$address_delivery->postcode.'" LIKE "',$range_zip)).'"))';
				}
			}
			else if(!empty($id_stores) && $all_stores == true) 
			{
				//$request .= 'AND s.id_store IN '.implode(',',$id_stores);
				$request .= ' AND s.id_store = '.(int)$id_stores;
			}
			
			if(version_compare(_PS_VERSION_, '1.7.3', '>'))
            {
				$request .= ' ORDER BY sl.name ASC';
			}
			else 
			{
				$request .= ' ORDER BY s.name ASC';
			}
		}

		if($request != null)
		{
			$result = Db::getInstance()->executeS($request);
		}
		else
		{
			$result = false;
		}
		
		//TODO : if empty result. Dans Smarty ?
		
	    return $result;	
	}

	public function skip_accents( $str, $charset='utf-8' ) {
 
	    $str = htmlentities( $str, ENT_NOQUOTES, $charset );
	    
	    $str = preg_replace( '#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str );
	    $str = preg_replace( '#&([A-za-z]{2})(?:lig);#', '\1', $str );
	    $str = preg_replace( '#&[^;]+;#', '', $str );
	    
	    return $str;
	}

	public function recupZipCodes($id_carrier = 0, $iso_code = null)
	{
		// On retrouve l'id_reference à partir de l'id_carrier
		$carrier = new Carrier((int)$id_carrier);
		$id_reference = 0;
		if(Validate::isLoadedObject($carrier))
		{
			$id_reference = $carrier->id_reference;
		}
		
		$zips = null;
		if($id_reference > 0)
		{
			if(!empty(Configuration::get('PRESTATILL_SEARCH_HD_POSTCODES')))
				$zips = str_replace(' ', '', Configuration::get('PRESTATILL_SEARCH_HD_POSTCODES'));
			
			// On récupère les codes postaux en fonction des jours
			if(Configuration::get('PRESTATILL_HD_ZIP_SUPP',null,(int)Context::getContext()->shop->id_shop_group,(int)Context::getContext()->shop->id))
				$zip_supp = json_decode(Configuration::get('PRESTATILL_HD_ZIP_SUPP',null,(int)Context::getContext()->shop->id_shop_group,(int)Context::getContext()->shop->id), true);
			
			$zip_max = null;
			
			//@TODO: VERIFIER SI ID_CARRIER = 0;
			
			if(!empty($zip_supp[$id_reference]))
			{
				foreach($zip_supp[$id_reference] as $id_zone)
				{
					foreach($id_zone as $supp)
					{
						$zip_max = $supp['zip_supp'];
						$zips .= ','.$zip_max;
					}
				}
			}
			
			// On nettoie les éventuelles , en doublon
			$zips = str_replace(',,', ',', $zips);
			//$zips = str_replace(' ', '', $zips);
			
			if(Tools::substr($zips, -1) == ',')
				$zips = Tools::substr($zips, 0, -1);
			
			if(Tools::substr($zips, 0, 1) == ',')
				$zips = Tools::substr($zips, 1);

			// 1.6.0 : Intégration du préfixe au niveau de la vérification du CP
			$zips = explode(',', $zips);
			if(!empty($zips) && $iso_code != null)
			{
				foreach($zips as $key => $zip)
				{
					$sub_zip = strtolower(substr(trim($zip), 0, 2));
					
					if($sub_zip == strtolower($iso_code))
					{
						$zips[$key] = substr(trim($zip), 2);
					}
					else if(!is_int((int)$sub_zip))
					{
						unset($zips[$key]);
					}
				}
			}
	
			asort($zips);
			$zips = implode(',', $zips);
		}
				
		return $zips;	
	}

	public static function getStore($id_store)
    {
        if(version_compare(_PS_VERSION_, '1.7.3', '>'))
        {
            $request = 'SELECT s.id_store, sl.name, sl.address1, sl.address2, s.postcode, s.city, sl.hours, s.phone, s.email
                    FROM '._DB_PREFIX_.'store s
                    LEFT JOIN '._DB_PREFIX_.'store_lang sl ON (s.id_store = sl.id_store)
					LEFT JOIN '._DB_PREFIX_.'store_shop ss ON (s.id_store = ss.id_store) AND (ss.id_shop = '.(int)Context::getContext()->shop->id.')
                    WHERE s.active = 1 
                    AND sl.id_lang = '.(int)Context::getContext()->language->id.'
                    AND s.id_store = '.(int)$id_store;
        }
        else 
        {
            $request = 'SELECT s.id_store, s.name, s.address1, s.address2, s.postcode, s.city, s.hours, s.phone, s.email
                    FROM '._DB_PREFIX_.'store s
                    LEFT JOIN '._DB_PREFIX_.'store_shop ss ON (s.id_store = ss.id_store) AND (ss.id_shop = '.(int)Context::getContext()->shop->id.')
                    WHERE s.active = 1 
                    AND s.id_store = '.(int)$id_store;
        }
        
        $result = Db::getInstance()->getRow($request);
        return $result;
    }

	// 3.0.0 : Get Additinonal Carence
	public static function getAdditionnalCarence($id_product, $id_product_attribute = 0)
	{
		$request = 'SELECT *
					FROM '._DB_PREFIX_.'prestatill_homedelivery_carence_supp_by_product
                    WHERE id_product = '.(int)$id_product.'
					AND id_product_attribute = '.(int)$id_product_attribute;
					
        $result = Db::getInstance()->getRow($request);
		
        return $result;
	}
	
	// 3.0.0 : Set Additinonal Carence
	public static function setAdditionnalCarence($id_product, $id_product_attribute = 0, $carence_supp = null)
	{
		$query = 'INSERT INTO '._DB_PREFIX_.'prestatill_homedelivery_carence_supp_by_product
                         (
                                id_product,
                                id_product_attribute,
                                carence_supp
                            )
                            VALUES ('.(int)($id_product).', '.(int)($id_product_attribute).', '.(int)$carence_supp.')
							ON DUPLICATE KEY UPDATE
                                id_product = '.(int)$id_product.',
								carence_supp = '.(int)$carence_supp;
                                
        $result = Db::getInstance()->execute($query);
		
        return $result;
	}

	// 3.0.0 : Get Weedays Availabilities 
	// Si aucun résultat = aucune indisponibilité, on enregistre uniquement les jours d'indisponibilité
	public static function getAdditionnalAvailability($id_product, $id_product_attribute = 0)
	{
		$request = 'SELECT *
					FROM '._DB_PREFIX_.'prestatill_drive_availability_by_product
                    WHERE id_product = '.(int)$id_product.'
					AND id_product_attribute = '.(int)$id_product_attribute;
					
        $result = Db::getInstance()->executeS($request);
		
		$days = array();
		if(!empty($result))
		{
			foreach($result as $res)
			{
				$days[$res['id_day']] = $res;
			}
		}
		
        return $days;
	}
	
	// 3.0.0 : Get Weedays Availabilities 
	// Si aucun résultat = aucune indisponibilité, on enregistre uniquement les jours d'indisponibilité
	public static function setAdditionnalAvailability($id_product, $id_product_attribute = 0, $id_day = null)
	{
		$query = 'INSERT INTO '._DB_PREFIX_.'prestatill_drive_availability_by_product
                         (
                                id_product,
                                id_product_attribute,
                                id_day
                            )
                            VALUES ('.(int)($id_product).', '.(int)($id_product_attribute).', '.(int)$id_day.')
							ON DUPLICATE KEY UPDATE
                                id_product = '.(int)$id_product.',
                                id_product_attribute = '.(int)$id_product_attribute.',
								id_day = '.(int)$id_day;
                                
        $result = Db::getInstance()->execute($query);
		
        return $result;
	}
	
	// 3.0.0 : Get Weedays Availabilities 
	// On supprime l'indisponibilité si elle existe déjà
	public static function unsetAdditionnalAvailability($id_product, $id_product_attribute = 0, $id_day = null)
	{
		$query = 'DELETE FROM '._DB_PREFIX_.'prestatill_drive_availability_by_product
                         WHERE id_product='.(int)($id_product).' AND id_product_attribute='.(int)($id_product_attribute).' AND id_day='.(int)$id_day;
						 
        $result = Db::getInstance()->execute($query);
		
        return $result;
	}
}
