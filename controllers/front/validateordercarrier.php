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

class PrestatillHomeDeliveryValidateOrderCarrierModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        
        parent::initContent();
        $context = Context::getContext();
		
        $action = Tools::getValue('action');
		
		$message = array();
        $response = array();
		$status = 'success';
		
		switch ($action) {

			// Since 3.0.0
			case 'adminSetProductAvailability':
			
			$id_product = (int)Tools::getValue('id_product');
			$id_product_attribute = (int)Tools::getValue('id_product_attribute');
			$id_day = (int)Tools::getValue('id_day');
			$availability = (int)Tools::getValue('availability');
			
			// On met à jour la valeur dans la bdd
			if($id_product > 0 && $id_day > 0)
			{
				if($availability == 0)
				{
					$setAvailability = PrestatillHomeDeliveryConfiguration::setAdditionnalAvailability($id_product, $id_product_attribute, $id_day);
				}
				else
				{
					$setAvailability = PrestatillHomeDeliveryConfiguration::unsetAdditionnalAvailability($id_product, $id_product_attribute, $id_day);
				}
			}
			
			$message = array();
		    $message['update_ok'] = $setAvailability;
		    $message['availability'] = $availability;
			
			break;
			
			case 'initTable':
		        $message = array();
				// A voir si on fait ici le test sur PRESTATILL_HD_DISPLAY_TABLE
				
				//1.2.0 : Addition of id_carrier
				$id_carrier = (int)str_replace(',','', Tools::getValue('id_carrier'));
				
				$init_bo = (int)Tools::getValue('init_bo');
				$id_order = (int)Tools::getValue('id_order');
				
				$message['display_table'] = Configuration::get('PRESTATILL_HD_DISPLAY_TABLE',null,(int)$context->shop->id_shop_group,(int)$context->shop->id);
		        $message['table_days'] = $this->initTable($id_carrier, $init_bo, $id_order);
		        $message['creneau'] = null;
		        $message['id_carrier'] = (int)$id_carrier;
				
				
				// On réinitialise le créneau au chargement
				$context = Context::getContext();
				$context->cookie->__set('hd_msg', '');
				$context->cookie->__set('hd_id_creneau', 0);
		        $context->cookie->write();
		
		        $infos = $context = Context::getContext()->cookie;
		        if (!empty($infos->hd_msg)) {
		            $message['creneau'] = $infos->hd_msg;
		        }
	        break;
			
			case 'assignSlot':
				
				$id_cart = (int)$context->cart->id;
				$slot = Tools::getValue('slot');
		        $id_day = (int)$slot['idDay'];
		        $hour = $slot['hour'];
		        //$date_msg = Tools::getValue('date');
		        $dist_max = Tools::getValue('dist_max');
		        $date = $slot['datetime'];
				$hour_end = $slot['hour_end'];
				$dbd_supp = array();
				$distance = Configuration::get('PRESTATILL_SEARCH_HD_STORE')?(int)Configuration::get('PRESTATILL_SEARCH_HD_RADIUS'):0;
				$possible_delivery = 1;
				$range_zip = false;
				
				//1.2.0 : Addition of id_carrier
				$id_carrier = (int)str_replace(',','', Tools::getValue('id_carrier'));

				$carrier = new Carrier((int)$id_carrier);
				$id_reference = 0;
				if(Validate::isLoadedObject($carrier))
				{
					$id_reference = (int)$carrier->id_reference;
				}
				
				if(Configuration::get('PRESTATILL_SEARCH_HD_STORE'))
				{
					// On vérifie si le créneau est toujours disponible et si le jour de livraison est dans la bonne distance
					if(Configuration::get('PRESTATILL_HD_DBD_SUPP',null,(int)$context->shop->id_shop_group,(int)$context->shop->id))
			        	$dbd_supp = json_decode(Configuration::get('PRESTATILL_HD_DBD_SUPP',null,(int)$context->shop->id_shop_group,(int)$context->shop->id), true);
				
					if(!empty($dbd_supp) && $distance > 0 && $dist_max > $distance)
					{
						if(isset($dbd_supp[$id_day]))
						{
							if($dist_max > $distance+$dbd_supp[$id_day]['dbd_supp'])
							{
								// Le jour de livraison sélectionné est trop loin par rapport à distance prévue
								$possible_delivery = $this->module->l('Your delivery address is outside the delivery aera. Please select another delivery address if it\'s possible or contact us.', 'validateordercarrier');
							}
						}
						else
						{
				            $possible_delivery = $this->module->l('Your delivery address is outside the delivery aera. Please select another delivery address if it\'s possible or contact us.', 'validateordercarrier');
						}
					}
				}
				else 
				{
					// On vérifie si le client est dans la liste des CP autorisés
					$zip_supp = array();
					$customer_zip = null;
					
					// On récupère l'adresse de livraison du customer
					$id_address = (int)Context::getContext()->cart->id_address_delivery;
			
					if($id_address > 0)
					{
						$address = new Address((int)$id_address);
						if(Validate::isLoadedObject($address))
						{
							$customer_zip = $address->postcode;
						}
					}
					
					$zips = Configuration::get('PRESTATILL_SEARCH_HD_ZIP')?Configuration::get('PRESTATILL_SEARCH_HD_POSTCODES'):null;
					
					if(Configuration::get('PRESTATILL_HD_ZIP_SUPP',null,(int)$context->shop->id_shop_group,(int)$context->shop->id))
						$zip_supp = json_decode(Configuration::get('PRESTATILL_HD_ZIP_SUPP',null,(int)$context->shop->id_shop_group,(int)$context->shop->id), true);
					
					// Petit nettoyage
					if($zips != null)
						$zips = $this->cleanZips($zips, substr($address->country, 0, 2));
					
					$z_supp = array();
					if($zips != null)
					{
						$z_supp = $zips;
						$z_supp = explode(',',$z_supp);					
						
						foreach($z_supp as $zip)
						{
							$pos = strpos($zip, '*');
							if($pos)
							{
								if(substr($customer_zip, 0, $pos) == substr($zip, 0, $pos))
									$range_zip = true;
							}
						}	

						// ON vérifie si le CP est dans l'addresse du client
						if(!in_array($customer_zip, $z_supp) && $range_zip == false)
						{
				            $possible_delivery = $this->module->l('Your delivery address is outside the delivery aera. Please select another delivery address if it\'s possible or contact us.', 'validateordercarrier');
						}
					}
					else if(!isset($zip_supp[$id_reference]))
					{
				        $possible_delivery = $this->module->l('Your delivery address is outside the delivery aera. Please select another delivery address if it\'s possible or contact us.', 'validateordercarrier');
					}

					if($id_reference > 0)
					{
						foreach($zip_supp[$id_reference] as $id_zone)
						{
							foreach($id_zone as $supp)
							{
								// 1.0.5 : postcode per day & slots
								if($id_day == $supp['id_day'] || $supp['id_day'] == 0)
								{
									if(!isset($supp['op']))
									{
										$supp['op'] = Configuration::get('PRESTATILL_HD_OPEN');
									}	
									
									if(!isset($supp['cl']))
									{
										$supp['cl'] = Configuration::get('PRESTATILL_HD_CLOSE');
									}	
									
									// On check l'heure sélectionnée par rapport au CP du customer
									if(($hour >= $supp['op'] && $hour < $supp['cl']) || $supp['id_day'] == 0)
									{
										$z_temp = $this->cleanZips($supp['zip_supp'], substr($address->country, 0, 2));	
										$z_supp = array_merge($z_supp,explode(',',$z_temp));
									}
								}
							}
						}
					}
					
					//1.3.1 : On intègre les CP RANGES
					foreach($z_supp as $zip)
					{
						$pos = strpos($zip, '*');
						if($pos)
						{
							if(substr($customer_zip, 0, $pos) == substr($zip, 0, $pos))
								$range_zip = true;
						}
					}

					if((!in_array($customer_zip, $z_supp) && $range_zip == false) || $id_reference == 0)
					{
			            $possible_delivery = $this->module->l('Your delivery address is outside the delivery aera. Please select another delivery address if it\'s possible or contact us.', 'validateordercarrier');
					}
					else 
					{
						$possible_delivery = 1;
					}		
				}

				// On vérifie si le créneau disponible est bien dans la bonne zone POSTCODES
				if($possible_delivery == 1)
				{
					$iso_lang = Context::getContext()->language->iso_code.'_'.Tools::strtoupper(Context::getContext()->language->iso_code!=='en'?Context::getContext()->language->iso_code:'US');
			        setlocale(LC_TIME, $iso_lang.'.utf8');
					
			        $id_week = date(date("W"), strtotime($date));
			        if ($id_week < 10) {
			            $id_week = substr($id_week, -1);
			        }
			
			        $result = PrestatillHomeDeliveryCreneau::getAllCreneauByIdCart((int)$id_cart);
					
					if(!$result)
					{
						$creneau = new PrestatillHomeDeliveryCreneau();
					}
					else
					{
						$creneau = new PrestatillHomeDeliveryCreneau((int)$result['id_creneau']);
					}
			        
					
			        $response = array();
					$error = false;
					// On vérifie si on le jour est ouvert et si le nombre de commandes passées est < à la limite
					$creneau_reserved = PrestatillHomeDeliveryCreneau::getReservedCreneau();
					if(!empty($creneau_reserved))
					{
						if(isset($creneau_reserved[$date]))
						{
							$max_orders = (int)Configuration::get('PRESTATILL_HD_NB_DISPO');
							
							// 1.4.1 : On vérifie s'il existe un paramètre spéciale pour le transporteur
							if(isset($id_reference) && Configuration::get('PRESTATILL_HD_NB_DISPO_'.$id_reference) > 0)
							{
								$max_orders = (int)Configuration::get('PRESTATILL_HD_NB_DISPO_'.$id_reference);
							}
							
							if($creneau_reserved[$date] >= $max_orders)
							{
								$error = $this->module->l('The maximum number of orders for this slot has already been reached. Please choose another one.', 'validateordercarrier');
							}
						}
					}
					
					// On vérifie que le jour et l'heure d'ouveture sont bien OK
					$checkIfOpen = PrestatillHomeDeliveryCreneau::ChekIfOpen($id_day, (int)Context::getContext()->shop->id,  (int)Context::getContext()->shop->id_shop_group);
					if($checkIfOpen == false)
					{
						$error = $this->module->l('This slot is no longer available. Please choose another one.', 'validateordercarrier');
					}
					
			        $creneau->id_day = (int)$id_day;
			        $creneau->hour = pSQL($hour);
			        $creneau->day = pSQL($date);
			        $creneau->id_week = (int)$id_week;
			        $creneau->id_cart = (int)$id_cart;
					$creneau->hour_end = pSQL($hour_end);
					$creneau->id_reference = (int)$id_reference;

			        $id_creneau = (int)$creneau->id_creneau;
					$creneau->save();
					
					// 1.4.1 Replace STRFTIME
					$pHD = new PrestatillHomeDelivery();
					$msg_creneau = $pHD->getMsgCreneau($creneau);

					if($error == false)
					{
						$this->context->cookie->__set('hd_msg', $msg_creneau);
				        $this->context->cookie->__set('hd_id_creneau', (int)$id_creneau);
				        $this->context->cookie->write();
						
						$response = array(
				            'success' => true,
				            'msg' => $msg_creneau,
				            'id' => (int)$id_creneau,
				            'possible_delivery' => $possible_delivery,
				            'dist_max' => $distance,
				            'id_carrier' => $id_carrier,
			                'nbr_to_display' => (int)Configuration::get('PRESTATILL_HD_NB_DAY'), 
				        );
						
						$creneau->update();
					}
					else 
					{
						$this->context->cookie->__set('hd_msg', '');
				        $this->context->cookie->__set('hd_id_creneau', 0);
				        $this->context->cookie->write();
						
						$response = array(
				            'success' => false,
				            'error' => $error,
				        );
					}
				}
				else
				{
					$this->context->cookie->__set('hd_msg', '');
			        $this->context->cookie->__set('hd_id_creneau', 0);
			        $this->context->cookie->write();
					
					$response = array(
			            'success' => true,
			            'msg' => null,
			            'id' => 0,
			            'possible_delivery' => $possible_delivery,
			            'dist_max' => $distance,
				        'id_carrier' => $id_carrier,
			            'nbr_to_display' => (int)Configuration::get('PRESTATILL_HD_NB_DAY'), 
			        );
				}
				
			break;

			case 'assignSlotLPF':
				
				$id_cart = (int)$context->cart->id;
				$slot = Tools::getValue('slot');
		        $id_day = (int)$slot['idDay'];
		        $hour = Tools::getValue('creneau_from');
		        //$date_msg = Tools::getValue('date');
		        $dist_max = Tools::getValue('dist_max');
		        $date = $slot['datetime'];
				$hour_end = Tools::getValue('creneau_to');
				$dbd_supp = array();
				$distance = Configuration::get('PRESTATILL_SEARCH_HD_STORE')?(int)Configuration::get('PRESTATILL_SEARCH_HD_RADIUS'):0;
				$possible_delivery = 1;
				$range_zip = false;
				
				//1.2.0 : Addition of id_carrier
				$id_carrier = (int)str_replace(',','', Tools::getValue('id_carrier'));

				$carrier = new Carrier((int)$id_carrier);
				$id_reference = 0;
				if(Validate::isLoadedObject($carrier))
				{
					$id_reference = (int)$carrier->id_reference;
				}
				
				if(Configuration::get('PRESTATILL_SEARCH_HD_STORE'))
				{
					// On vérifie si le créneau est toujours disponible et si le jour de livraison est dans la bonne distance
					if(Configuration::get('PRESTATILL_HD_DBD_SUPP',null,(int)$context->shop->id_shop_group,(int)$context->shop->id))
			        	$dbd_supp = json_decode(Configuration::get('PRESTATILL_HD_DBD_SUPP',null,(int)$context->shop->id_shop_group,(int)$context->shop->id), true);
				
					if(!empty($dbd_supp) && $distance > 0 && $dist_max > $distance)
					{
						if(isset($dbd_supp[$id_day]))
						{
							if($dist_max > $distance+$dbd_supp[$id_day]['dbd_supp'])
							{
								// Le jour de livraison sélectionné est trop loin par rapport à distance prévue
								$possible_delivery = $this->module->l('Your delivery address is outside the delivery aera. Please select another delivery address if it\'s possible or contact us.', 'validateordercarrier');
							}
						}
						else
						{
				            $possible_delivery = $this->module->l('Your delivery address is outside the delivery aera. Please select another delivery address if it\'s possible or contact us.', 'validateordercarrier');
						}
					}
				}
				else 
				{
					// On vérifie si le client est dans la liste des CP autorisés
					$zip_supp = array();
					$customer_zip = null;
					
					// On récupère l'adresse de livraison du customer
					$id_address = (int)Context::getContext()->cart->id_address_delivery;
			
					if($id_address > 0)
					{
						$address = new Address((int)$id_address);
						if(Validate::isLoadedObject($address))
						{
							$customer_zip = $address->postcode;
						}
					}
					
					$zips = Configuration::get('PRESTATILL_SEARCH_HD_ZIP')?Configuration::get('PRESTATILL_SEARCH_HD_POSTCODES'):null;
					
					if(Configuration::get('PRESTATILL_HD_ZIP_SUPP',null,(int)$context->shop->id_shop_group,(int)$context->shop->id))
						$zip_supp = json_decode(Configuration::get('PRESTATILL_HD_ZIP_SUPP',null,(int)$context->shop->id_shop_group,(int)$context->shop->id), true);
					
					// Petit nettoyage
					if($zips != null)
						$zips = $this->cleanZips($zips, substr($address->country, 0, 2));
					
					$z_supp = array();
					if($zips != null)
					{
						$z_supp = $zips;
						$z_supp = explode(',',$z_supp);					
						
						foreach($z_supp as $zip)
						{
							$pos = strpos($zip, '*');
							if($pos)
							{
								if(substr($customer_zip, 0, $pos) == substr($zip, 0, $pos))
									$range_zip = true;
							}
						}	

						// ON vérifie si le CP est dans l'addresse du client
						if(!in_array($customer_zip, $z_supp) && $range_zip == false)
						{
				            $possible_delivery = $this->module->l('Your delivery address is outside the delivery aera. Please select another delivery address if it\'s possible or contact us.', 'validateordercarrier');
						}
					}
					else if(!isset($zip_supp[$id_reference]))
					{
				        $possible_delivery = $this->module->l('Your delivery address is outside the delivery aera. Please select another delivery address if it\'s possible or contact us.', 'validateordercarrier');
					}

					if($id_reference > 0)
					{
						foreach($zip_supp[$id_reference] as $id_zone)
						{
							foreach($id_zone as $supp)
							{
								// 1.0.5 : postcode per day & slots
								if($id_day == $supp['id_day'] || $supp['id_day'] == 0)
								{
									if(!isset($supp['op']))
									{
										$supp['op'] = Configuration::get('PRESTATILL_HD_OPEN');
									}	
									
									if(!isset($supp['cl']))
									{
										$supp['cl'] = Configuration::get('PRESTATILL_HD_CLOSE');
									}	
									
									// On check l'heure sélectionnée par rapport au CP du customer
									if(($hour >= $supp['op'] && $hour < $supp['cl']) || $supp['id_day'] == 0)
									{
										$z_temp = $this->cleanZips($supp['zip_supp'], substr($address->country, 0, 2));	
										$z_supp = array_merge($z_supp,explode(',',$z_temp));
									}
								}
							}
						}
					}
					
					//1.3.1 : On intègre les CP RANGES
					foreach($z_supp as $zip)
					{
						$pos = strpos($zip, '*');
						if($pos)
						{
							if(substr($customer_zip, 0, $pos) == substr($zip, 0, $pos))
								$range_zip = true;
						}
					}

					if((!in_array($customer_zip, $z_supp) && $range_zip == false) || $id_reference == 0)
					{
			            $possible_delivery = $this->module->l('Your delivery address is outside the delivery aera. Please select another delivery address if it\'s possible or contact us.', 'validateordercarrier');
					}
					else 
					{
						$possible_delivery = 1;
					}		
				}

				// On vérifie si le créneau disponible est bien dans la bonne zone POSTCODES
				if($possible_delivery == 1)
				{
					$iso_lang = Context::getContext()->language->iso_code.'_'.Tools::strtoupper(Context::getContext()->language->iso_code!=='en'?Context::getContext()->language->iso_code:'US');
			        setlocale(LC_TIME, $iso_lang.'.utf8');
					
			        $id_week = date(date("W"), strtotime($date));
			        if ($id_week < 10) {
			            $id_week = substr($id_week, -1);
			        }
			
			        $result = PrestatillHomeDeliveryCreneau::getAllCreneauByIdCart((int)$id_cart);
					
					if(!$result)
					{
						$creneau = new PrestatillHomeDeliveryCreneau();
					}
					else
					{
						$creneau = new PrestatillHomeDeliveryCreneau((int)$result['id_creneau']);
					}
			        
					
			        $response = array();
					$error = false;
					// On vérifie si on le jour est ouvert et si le nombre de commandes passées est < à la limite
					$creneau_reserved = PrestatillHomeDeliveryCreneau::getReservedCreneau();
					if(!empty($creneau_reserved))
					{
						if(isset($creneau_reserved[$date]))
						{
							$max_orders = (int)Configuration::get('PRESTATILL_HD_NB_DISPO');
							
							// 1.4.1 : On vérifie s'il existe un paramètre spéciale pour le transporteur
							if(isset($id_reference) && Configuration::get('PRESTATILL_HD_NB_DISPO_'.$id_reference) > 0)
							{
								$max_orders = (int)Configuration::get('PRESTATILL_HD_NB_DISPO_'.$id_reference);
							}
							
							if($creneau_reserved[$date] >= $max_orders)
							{
								$error = $this->module->l('The maximum number of orders for this slot has already been reached. Please choose another one.', 'validateordercarrier');
							}
						}
					}
					
					// On vérifie que le jour et l'heure d'ouveture sont bien OK
					$checkIfOpen = PrestatillHomeDeliveryCreneau::ChekIfOpen($id_day, (int)Context::getContext()->shop->id,  (int)Context::getContext()->shop->id_shop_group);
					if($checkIfOpen == false)
					{
						$error = $this->module->l('This slot is no longer available. Please choose another one.', 'validateordercarrier');
					}
					
			        $creneau->id_day = (int)$id_day;
			        $creneau->hour = pSQL($hour);
			        $creneau->day = pSQL($date);
			        $creneau->id_week = (int)$id_week;
			        $creneau->id_cart = (int)$id_cart;
					$creneau->hour_end = pSQL($hour_end);
					$creneau->id_reference = (int)$id_reference;

			        $id_creneau = (int)$creneau->id_creneau;
					$creneau->save();
					
					// 1.4.1 Replace STRFTIME
					$pHD = new PrestatillHomeDelivery();
					$msg_creneau = $pHD->getMsgCreneau($creneau);

					if($error == false)
					{
						$this->context->cookie->__set('hd_msg', $msg_creneau);
				        $this->context->cookie->__set('hd_id_creneau', (int)$id_creneau);
				        $this->context->cookie->write();
						
						$response = array(
				            'success' => true,
				            'msg' => $msg_creneau,
				            'id' => (int)$id_creneau,
				            'possible_delivery' => $possible_delivery,
				            'dist_max' => $distance,
				            'id_carrier' => $id_carrier,
			                'nbr_to_display' => (int)Configuration::get('PRESTATILL_HD_NB_DAY'), 
				        );
						
						$creneau->update();
					}
					else 
					{
						$this->context->cookie->__set('hd_msg', '');
				        $this->context->cookie->__set('hd_id_creneau', 0);
				        $this->context->cookie->write();
						
						$response = array(
				            'success' => false,
				            'error' => $error,
				        );
					}
				}
				else
				{
					$this->context->cookie->__set('hd_msg', '');
			        $this->context->cookie->__set('hd_id_creneau', 0);
			        $this->context->cookie->write();
					
					$response = array(
			            'success' => true,
			            'msg' => null,
			            'id' => 0,
			            'possible_delivery' => $possible_delivery,
			            'dist_max' => $distance,
				        'id_carrier' => $id_carrier,
			            'nbr_to_display' => (int)Configuration::get('PRESTATILL_HD_NB_DAY'), 
			        );
				}
				
			break;
			
			// SINCE 1.3.0
			case 'adminDeleteSlot':
				$id_order = Tools::getValue('id_order');
				
		        $creneau = PrestatillHomeDeliveryCreneau::getCreneauByIdOrder((int)$id_order);

				if(Validate::isLoadedObject($creneau))
				{
					$creneau->delete();
				}
		
		        $response = array(
		            'success' => true,
		        );
		
			break;
			
			case 'assignSlotFromBO':
				$id_order = Tools::getValue('id_order');
				$slot = Tools::getValue('slot');
		        $id_day = (int)$slot['idDay'];
		        $hour = $slot['hour'];
				$hour_end = $slot['hour_end'];
		        //$date_msg = Tools::getValue('date');
		        $date = $slot['datetime'];
				
				$iso_lang = Context::getContext()->language->iso_code.'_'.Tools::strtoupper(Context::getContext()->language->iso_code!=='en'?Context::getContext()->language->iso_code:'US');
		        setlocale(LC_TIME, $iso_lang.'.utf8');
				
		        $id_week = date(date("W"), strtotime($date));
		        if ($id_week < 10) {
		            $id_week = substr($id_week, -1);
		        }
				
				$response = array();
		        $creneau = PrestatillHomeDeliveryCreneau::getCreneauByIdOrder((int)$id_order);

				if(!Validate::isLoadedObject($creneau))
				{
					$creneau = new PrestatillHomeDeliveryCreneau();
				}
				$creneau->id_day = (int)$id_day;
				$creneau->id_order = (int)$id_order;
		        $creneau->hour = pSQL($hour);
		        $creneau->day = pSQL($date);
		        $creneau->id_week = (int)$id_week;
		        $creneau->id_cart = 0;
		        $id_creneau = (int)$creneau->id_creneau;

				// 1.3.1 : addition of manual slots
				$id_reference = (int)Tools::getValue('id_carrier');
				$creneau->id_reference = (int)$id_reference;
				
				// 1.4.1 Replace STRFTIME
				$pHD = new PrestatillHomeDelivery();
				$msg_creneau = $pHD->getMsgCreneau($creneau);

		        $this->context->cookie->__set('hd_msg', $msg_creneau);
		        $this->context->cookie->__set('hd_id_creneau', (int)$id_creneau);
		        $this->context->cookie->write();
		
		        $response = array(
		            'success' => true,
		            'msg' => $msg_creneau,
		            'id' => (int)$id_creneau,
		        );
				$creneau->save();
		
			break;
			
			case 'assignManualSlotFromBO':
				$slot = Tools::getValue('slot');
		        $id_day = (int)$slot['idDay'];
		        $hour = $slot['hour'];
				$hour_end = $slot['hour_end'];
		        //$date_msg = Tools::getValue('date');
		        $date = $slot['datetime'];
				
				// 1.3.1 : addition of manual slots
				$id_reference = (int)Tools::getValue('id_reference');
				
				$manual = (int)Tools::getValue('manual');
				$manual_comment = Tools::getValue('manual_comment');
				
				$iso_lang = Context::getContext()->language->iso_code.'_'.Tools::strtoupper(Context::getContext()->language->iso_code!=='en'?Context::getContext()->language->iso_code:'US');
		        setlocale(LC_TIME, $iso_lang.'.utf8');
				
		        $id_week = date(date("W"), strtotime($date));
		        if ($id_week < 10) {
		            $id_week = substr($id_week, -1);
		        }
				
				$response = array();
				
				if($manual == 1)
				{
					// On récupère un créneau manuel sur le créneau
					$creneau = PrestatillHomeDeliveryCreneau::getManualCreneau($id_week, $id_day, $hour, $id_reference);
				}
				
				if(!validate::isLoadedObject($creneau))
				{
					$creneau = new PrestatillHomeDeliveryCreneau();
				}
				
				$creneau->id_day = (int)$id_day;
				$creneau->id_order = 0;
		        $creneau->hour = pSQL($hour);
		        $creneau->day = pSQL($date);
		        $creneau->id_week = (int)$id_week;
		        $creneau->id_cart = 0;
		        $id_creneau = (int)$creneau->id_creneau;
				
				// 1.3.1
				if($manual == 1)
				{
					$creneau->manual = 1;
					$creneau->id_reference = (int)$id_reference;
			    }
				if(!empty($manual_comment))
				{
					$creneau->manual_comment = pSQL($manual_comment);
			    }

				// 1.4.1 Replace STRFTIME
				$pHD = new PrestatillHomeDelivery();
				$msg_creneau = $pHD->getMsgCreneau($creneau);

		        $this->context->cookie->__set('hd_msg', $msg_creneau);
		        $this->context->cookie->__set('hd_id_creneau', (int)$id_creneau);
		        $this->context->cookie->write();
		
		        $response = array(
		            'success' => true,
		            'msg' => $msg_creneau,
		            'id' => (int)$creneau->id,
		        );
				$creneau->save();
				
			break;
			
			case 'processCarrier':

				$hd_msg = $context->cookie->hd_msg;

				//1.2.0 : Addition of id_carrier
				$id_carrier = (int)str_replace(',','', Tools::getValue('id_carrier'));

				// 3.1.0 : Check si l'adresse est dans une zone de livraison
				$address_valid = true;

				// Debug logs array
				$debug_logs = array();

				// Vérifier si le système de zones est activé
				$zones_enabled = Configuration::get('PRESTATILL_HD_USE_ZONES');
				$debug_logs['zones_enabled'] = $zones_enabled;

				// Ne faire la vérification que si les zones sont activées
				if ($zones_enabled) {
					// Récupérer l'adresse de livraison
					$id_address = (int)Context::getContext()->cart->id_address_delivery;
					$debug_logs['id_address'] = $id_address;

					if ($id_address > 0) {
						$address = new Address((int)$id_address);
						$debug_logs['address_loaded'] = Validate::isLoadedObject($address);

					if (Validate::isLoadedObject($address)) {
						// Géocoder l'adresse pour obtenir lat/lng
						$latitude = null;
						$longitude = null;

						// Construire l'adresse complète
						$full_address = $address->address1 . ', ' . $address->postcode . ' ' . $address->city;
						$debug_logs['full_address'] = $full_address;
						$debug_logs['address_details'] = array(
							'address1' => $address->address1,
							'address2' => $address->address2,
							'postcode' => $address->postcode,
							'city' => $address->city,
							'country' => $address->country
						);

						// Système de fallback avec 3 APIs de géocodage
						$geocoding_success = false;
						$geocoding_attempts = array();

						// TENTATIVE 1 : OpenStreetMap Nominatim
						if (!$geocoding_success) {
							try {
								// Load the rate limiter
								require_once(dirname(__FILE__) . '/../../classes/NominatimRateLimiter.php');

								// Respect the 1 request per second limit
								NominatimRateLimiter::waitIfNeeded();

								$addr = urlencode($full_address);
								$nominatim_url = 'https://nominatim.openstreetmap.org/search?addressdetails=1&q=' . $addr . '&format=json&limit=1';
								$debug_logs['nominatim_url'] = $nominatim_url;
								$debug_logs['nominatim_user_agent'] = NominatimRateLimiter::getUserAgent();
								$debug_logs['nominatim_referer'] = NominatimRateLimiter::getReferer();

								// Use proper headers as required by Nominatim
								$context = NominatimRateLimiter::createStreamContext(5);
								$return = @file_get_contents($nominatim_url, false, $context);
								$debug_logs['nominatim_raw_response'] = substr($return, 0, 500); // Limiter la taille du log

								$json = json_decode($return);
								$debug_logs['nominatim_json_response'] = $json;

								if (!empty($json) && isset($json[0]->lat) && isset($json[0]->lon)) {
									$latitude = $json[0]->lat;
									$longitude = $json[0]->lon;
									$debug_logs['nominatim_coordinates'] = array('lat' => $latitude, 'lon' => $longitude);
									$geocoding_success = true;
									$geocoding_attempts[] = 'Nominatim: SUCCESS';
								} else {
									$debug_logs['nominatim_error'] = 'No results found or empty response';
									$geocoding_attempts[] = 'Nominatim: FAILED';
								}
							} catch (Exception $e) {
								$debug_logs['nominatim_exception'] = $e->getMessage();
								$geocoding_attempts[] = 'Nominatim: EXCEPTION';
							}
						}

						// TENTATIVE 2 : Geocode Maps Co (si Nominatim échoue)
						if (!$geocoding_success) {
							try {
								$geocode_maps_api_key = Configuration::get('GEOCODE_MAPS_API_KEY'); // Nouvelle config
								if (!empty($geocode_maps_api_key)) {
									$addr = urlencode($full_address);
									$geocode_maps_url = 'https://geocode.maps.co/search?q=' . $addr . '&api_key=' . $geocode_maps_api_key;
									$debug_logs['geocode_maps_url'] = str_replace($geocode_maps_api_key, 'HIDDEN_KEY', $geocode_maps_url);

									$context_options = array(
										'http' => array(
											'method' => 'GET',
											'timeout' => 5
										)
									);
									$context = stream_context_create($context_options);
									$return = @file_get_contents($geocode_maps_url, false, $context);
									$debug_logs['geocode_maps_raw_response'] = substr($return, 0, 500);

									$json = json_decode($return);
									$debug_logs['geocode_maps_json_response'] = $json;

									if (!empty($json) && isset($json[0]->lat) && isset($json[0]->lon)) {
										$latitude = $json[0]->lat;
										$longitude = $json[0]->lon;
										$debug_logs['geocode_maps_coordinates'] = array('lat' => $latitude, 'lon' => $longitude);
										$geocoding_success = true;
										$geocoding_attempts[] = 'Geocode.Maps.Co: SUCCESS';
									} else {
										$debug_logs['geocode_maps_error'] = 'No results found';
										$geocoding_attempts[] = 'Geocode.Maps.Co: FAILED';
									}
								} else {
									$debug_logs['geocode_maps_error'] = 'API Key not configured';
									$geocoding_attempts[] = 'Geocode.Maps.Co: NO API KEY';
								}
							} catch (Exception $e) {
								$debug_logs['geocode_maps_exception'] = $e->getMessage();
								$geocoding_attempts[] = 'Geocode.Maps.Co: EXCEPTION';
							}
						}

						// TENTATIVE 3 : Google Maps Geocoding API (si les 2 précédents échouent)
						if (!$geocoding_success) {
							try {
								// Try new configuration key first, fallback to old PS_API_KEY
								$google_api_key = Configuration::get('GOOGLE_MAPS_API_KEY');
								if (empty($google_api_key)) {
									$google_api_key = Configuration::get('PS_API_KEY');
								}

								if (!empty($google_api_key)) {
									$geocoder = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=' . $google_api_key;
									$query = sprintf($geocoder, urlencode($full_address));
									$debug_logs['google_api_url'] = str_replace($google_api_key, 'HIDDEN_KEY', $query);

									$context_options = array(
										'http' => array(
											'method' => 'GET',
											'timeout' => 5
										)
									);
									$context = stream_context_create($context_options);
									$return = @file_get_contents($query, false, $context);
									$result = json_decode($return);
									$debug_logs['google_api_response'] = $result;

									if (!empty($result->results)) {
										$latitude = $result->results[0]->geometry->location->lat;
										$longitude = $result->results[0]->geometry->location->lng;
										$debug_logs['google_coordinates'] = array('lat' => $latitude, 'lng' => $longitude);
										$geocoding_success = true;
										$geocoding_attempts[] = 'Google Maps: SUCCESS';
									} else {
										$debug_logs['google_error'] = 'No results found';
										if (isset($result->status)) {
											$debug_logs['google_status'] = $result->status;
										}
										$geocoding_attempts[] = 'Google Maps: FAILED';
									}
								} else {
									$debug_logs['google_error'] = 'API Key not configured';
									$geocoding_attempts[] = 'Google Maps: NO API KEY';
								}
							} catch (Exception $e) {
								$debug_logs['google_exception'] = $e->getMessage();
								$geocoding_attempts[] = 'Google Maps: EXCEPTION';
							}
						}

						$debug_logs['geocoding_attempts'] = $geocoding_attempts;
						$debug_logs['final_coordinates'] = array('latitude' => $latitude, 'longitude' => $longitude);

						// Vérifier si le point est dans une zone
						if ($latitude && $longitude) {
							require_once(dirname(__FILE__) . '/../../classes/PrestatillHomeDeliveryZone.php');

							// Get all active zones for debugging - load ALL zones like the back-office
							try {
								// Simple SQL query to get ALL active zones
								$sql = 'SELECT * FROM `'._DB_PREFIX_.'prestatill_homedelivery_delivery_zones` WHERE `active` = 1 ORDER BY `priority` DESC';
								$active_zones = Db::getInstance()->executeS($sql);
								$debug_zones = array();

								$debug_logs['sql_query'] = $sql;
								$debug_logs['id_carrier_requested'] = $id_carrier;

								if ($active_zones && is_array($active_zones)) {
									foreach ($active_zones as $zone) {
										$zone_data = json_decode($zone['zone_data'], true);
										$debug_zones[] = array(
											'id_delivery_zone' => $zone['id_delivery_zone'],
											'zone_name' => $zone['zone_name'],
											'zone_type' => $zone['zone_type'],
											'id_store' => $zone['id_store'],
											'id_carrier' => $zone['id_carrier'],
											'priority' => $zone['priority'],
											'active' => $zone['active'],
											'zone_data' => $zone_data
										);
									}
								}
								$debug_logs['active_zones'] = $debug_zones;
								$debug_logs['zones_count'] = count($debug_zones);
							} catch (Exception $e) {
								$debug_logs['zones_error'] = $e->getMessage();
								$debug_logs['active_zones'] = array();
								$debug_logs['zones_count'] = 0;
							}

							$id_store = PrestatillHomeDeliveryZone::getStoreByCoordinates($latitude, $longitude, $id_carrier);
							$debug_logs['id_store_found'] = $id_store;

							if ($id_store === false) {
								// Adresse pas dans une zone
								$address_valid = $this->module->l('Votre adresse de livraison n\'est pas dans une zone de livraison disponible.', 'validateordercarrier');
								$debug_logs['error_type'] = 'address_not_in_zone';
							} else {
								// Récupérer le nom du restaurant
								$id_lang = (int)$context->language->id;
								if (version_compare(_PS_VERSION_, '1.7.3', '>')) {
									$store_query = 'SELECT sl.name, sl.address1, sl.address2, s.postcode, s.city, sl.hours, s.phone
													FROM '._DB_PREFIX_.'store s
													LEFT JOIN '._DB_PREFIX_.'store_lang sl ON (s.id_store = sl.id_store)
													WHERE s.id_store = '.(int)$id_store.' AND sl.id_lang = '.(int)$id_lang;
								} else {
									$store_query = 'SELECT s.name, s.address1, s.address2, s.postcode, s.city, s.hours, s.phone
													FROM '._DB_PREFIX_.'store s
													WHERE s.id_store = '.(int)$id_store;
								}
								$store_info = Db::getInstance()->getRow($store_query);

								$debug_logs['validation'] = 'Address valid and in zone';
								$debug_logs['store_info'] = $store_info;

								// Stocker le nom du restaurant dans une variable de session/cookie pour l'affichage
								$context->cookie->hd_store_name = $store_info['name'];
								$context->cookie->hd_store_id = $id_store;
							}
						} else {
							// Impossible de géocoder l'adresse
							$address_valid = $this->module->l('Impossible de vérifier votre adresse. Veuillez contacter le service client.', 'validateordercarrier');
							$debug_logs['error_type'] = 'geocoding_failed';
						}
					} else {
						$debug_logs['error'] = 'Address object not loaded';
					}
					} else {
						$debug_logs['error'] = 'No address ID found';
					}
				} else {
					$debug_logs['zones_disabled_message'] = 'Zone verification is disabled';
				}

		        $message = array(
		                    'success' => true,
		                    'address_valid' => $address_valid,
		                    'id_carrier' => (int)$id_carrier,
		                    'hd_msg' => $hd_msg,
			                'nbr_to_display' => (int)Configuration::get('PRESTATILL_HD_NB_DAY'),
			                'debug_logs' => $debug_logs,
		                );

			break;
			
			case 'addCarenceSupp':
		
				$params = array(
					'id_day' => Tools::getValue('id_day')?(int)Tools::getValue('id_day'):0,
					// 2.0.0 : id_carrier
					'id_reference' => Tools::getValue('id_reference')?(int)Tools::getValue('id_reference'):0,
					'id_day_end' => Tools::getValue('id_day_end')?(int)Tools::getValue('id_day_end'):0,
					'hour_limit' => Tools::getValue('hour_limit')?Tools::getValue('hour_limit'):'00:00:00',
					'hour_limit_end' => Tools::getValue('hour_limit_end')?Tools::getValue('hour_limit_end'):'00:00:00',
					'waiting_time' => Tools::getValue('waiting_time')?Tools::getValue('waiting_time'):0,
					'id_lang' => Tools::getValue('id_lang'),
					'id_shop_group' => $context->shop->id_shop_group,
					'id_shop' => $context->shop->id, 
				);
				
				$context->cookie->__set('id_lang', Tools::getValue('id_lang'));
		        $context->cookie->write();
				
				$phd = new PrestatillHomeDelivery();
				$message['tpl'] = $phd->getCarrenceSupp($params);
				
				//@TODO: Si all_days : on supprime les autres entrées du store
		
				break;
				
			case 'deleteCarenceSupp':
				
				$params = array(
					'id_day' => Tools::getValue('id_day')?Tools::getValue('id_day'):0,
					'id_reference' => Tools::getValue('id_reference')? Tools::getValue('id_reference') : 0,
					'id_shop_group' => $context->shop->id_shop_group,
					'id_shop' => $context->shop->id,
					);
				
				$phd = new PrestatillHomeDelivery();
				$message['tpl'] = $phd->deleteCarrenceSupp($params);
				
			break;
		
			// New since 1.0.3
			case 'adminCreateSlot':
				
				$id_order = (int)Tools::getValue('id_order');
				
				$creneau = PrestatillHomeDeliveryCreneau::getCreneauByIdOrder((int)$id_order);
				
				$send_email = (int)Tools::getValue('send_email');
				if(Validate::isLoadedObject($creneau)) {
			
					$msg_creneau = '';

					// 1.4.1 Replace STRFTIME
					$pHD = new PrestatillHomeDelivery();
					$msg_creneau = $pHD->getMsgCreneau($creneau);

					$message = array(
		                'success' => true,
		                'message_creneau' => $msg_creneau,                   
		            );
					
					if($send_email == 1)
					{
						$pdHomeDelivery = new PrestatillHomeDelivery();
						$send_success = $pdHomeDelivery->sendEmailModification($creneau, (int)Tools::getValue('type'));
						
						if($send_success)
						{
							$message['email_sended'] = true;
						}
					}
				}
				
			break;
				
			case 'reInitStore':
				
		        $id_cart = (int)Context::getContext()->cart->id;
		        if ($id_cart > 0) {
		            $creneau = PrestatillHomeDeliveryCreneau::getAllCreneauByIdCart((int)$id_cart);
		            if (!empty($creneau)) {
		                PrestatillHomeDeliveryCreneau::updateStoreByIdcreneau((int)$creneau['id_creneau']);
		            }
		        }
				
		        $context = Context::getContext();
				$context->cookie->__set('hd_msg', '');
		        $context->cookie->__set('hd_id_creneau', 0);
				$context->cookie->__set('distance', 0);
		        $context->cookie->write();
		
		        $message = array();
				
			break;
			
			// 1.0.4 : Add Additional delivery area by day
			case 'addDBDSupp':
				
		        $params = array(
					'id_carrier' => Tools::getValue('id_carrier')?Tools::getValue('id_carrier'):0,
					'id_zone' => Tools::getValue('id_zone')?Tools::getValue('id_zone'):0,
					'zone_name' => Tools::getValue('zone_name'),
					'id_store' => Tools::getValue('id_store')?Tools::getValue('id_store'):0,
					'id_day' => Tools::getValue('id_day')?(int)Tools::getValue('id_day'):0,
					'dbd_supp' => Tools::getValue('dbd_supp')?Tools::getValue('dbd_supp'):0,
					'id_shop_group' => $context->shop->id_shop_group,
					'id_shop' => $context->shop->id, 
				);
				
				$phd = new PrestatillHomeDelivery();
				$message['tpl'] = $phd->getDbdSupp($params);
				
			break;
			
			case 'deleteDBDSupp':
				
				$params = array(
					'id_carrier' => Tools::getValue('id_carrier')?Tools::getValue('id_carrier'):0,
					'id_store' => Tools::getValue('id_store')?Tools::getValue('id_store'):0,
					'id_zone' => Tools::getValue('id_zone')?Tools::getValue('id_zone'):0,
					'id_day' => Tools::getValue('id_day')?Tools::getValue('id_day'):0,
					'id_shop_group' => $context->shop->id_shop_group,
					'id_shop' => $context->shop->id,
					);
				
				$phd = new PrestatillHomeDelivery();
				$message['tpl'] = $phd->deleteDBDSupp($params);
				
			break;
			
			// 1.0.4 : Add Additional delivery area by day
			case 'addZipSupp':
				
		        $params = array(
					'id_carrier' => Tools::getValue('id_carrier')?Tools::getValue('id_carrier'):0,
					'id_zone' => Tools::getValue('id_zone')?Tools::getValue('id_zone'):0,
					'zone_name' => Tools::getValue('zone_name'),
					'id_day' => Tools::getValue('id_day')?Tools::getValue('id_day'):0,
					'zip_supp' => Tools::getValue('zip_supp')?Tools::getValue('zip_supp'):0,
					'id_shop_group' => $context->shop->id_shop_group,
					'id_shop' => $context->shop->id, 
				);
				
				$phd = new PrestatillHomeDelivery();
				$message['tpl'] = $phd->getZipSupp($params);
				
			break;
			
			case 'deleteZipSupp':
				
				$params = array(
					'id_day' => Tools::getValue('id_day')?Tools::getValue('id_day'):0,
					'id_carrier' => Tools::getValue('id_carrier')?Tools::getValue('id_carrier'):0,
					'id_zone' => Tools::getValue('id_zone')?Tools::getValue('id_zone'):0,
					'id_shop_group' => $context->shop->id_shop_group,
					'id_shop' => $context->shop->id,
					);
				
				$phd = new PrestatillHomeDelivery();
				$message['tpl'] = $phd->deleteZipSupp($params);
				
			break;

			// Zones Management - Get all zones
			case 'getZones':
				require_once(_PS_MODULE_DIR_.'prestatillhomedelivery/classes/PrestatillHomeDeliveryZone.php');

				$id_shop = (int)Tools::getValue('id_shop', Context::getContext()->shop->id);
				$zones = PrestatillHomeDeliveryZone::getActiveZones($id_shop);

				// Récupérer les jours de livraison pour chaque zone
				foreach ($zones as &$zone) {
					$zoneObj = new PrestatillHomeDeliveryZone((int)$zone['id_delivery_zone']);
					$zone['delivery_days'] = implode(',', $zoneObj->getDeliveryDays());
				}

				$message = array(
					'success' => true,
					'zones' => $zones
				);
			break;

			// Zones Management - Save a new zone
			case 'saveZone':
				require_once(_PS_MODULE_DIR_.'prestatillhomedelivery/classes/PrestatillHomeDeliveryZone.php');

				// Debug: Log all received data
				PrestaShopLogger::addLog('SaveZone - Received data: ' . print_r($_POST, true), 1, null, 'PrestatillHomeDelivery');

				try {
					$zone = new PrestatillHomeDeliveryZone();
					$zone->zone_name = pSQL(Tools::getValue('zone_name'));
					$zone->id_store = (int)Tools::getValue('id_store');
					$zone->id_carrier = (int)Tools::getValue('id_carrier', 0);
					$zone->zone_type = pSQL(Tools::getValue('zone_type'));

					// Ne pas utiliser pSQL sur les données JSON - utiliser mysqli_real_escape_string
					$zone_data_raw = Tools::getValue('zone_data');
					// Valider que c'est du JSON valide
					if (json_decode($zone_data_raw) === null && json_last_error() !== JSON_ERROR_NONE) {
						throw new Exception('Invalid JSON data for zone');
					}
					$zone->zone_data = $zone_data_raw;

					$zone->zone_color = pSQL(Tools::getValue('zone_color', '#FF0000'));
					$zone->active = (int)Tools::getValue('active', 1);
					$zone->priority = (int)Tools::getValue('priority', 0);
					$zone->id_shop = (int)Tools::getValue('id_shop', Context::getContext()->shop->id);
					$zone->id_shop_group = (int)Context::getContext()->shop->id_shop_group;
					$zone->date_add = date('Y-m-d H:i:s');
					$zone->date_upd = date('Y-m-d H:i:s');

					PrestaShopLogger::addLog('SaveZone - Zone object: ' . print_r($zone, true), 1, null, 'PrestatillHomeDelivery');

					$save_result = $zone->save();

					PrestaShopLogger::addLog('SaveZone - save() result: ' . ($save_result ? 'TRUE' : 'FALSE'), 1, null, 'PrestatillHomeDelivery');

					if ($save_result) {
						// Sauvegarder les jours de livraison
						$delivery_days = explode(',', Tools::getValue('delivery_days', ''));
						if (!empty($delivery_days[0])) {
							$zone->setDeliveryDays($delivery_days);
						}

						PrestaShopLogger::addLog('SaveZone - SUCCESS - Zone ID: ' . $zone->id, 1, null, 'PrestatillHomeDelivery');

						$message = array(
							'success' => true,
							'id_zone' => $zone->id,
							'message' => 'Zone saved successfully'
						);
					} else {
						PrestaShopLogger::addLog('SaveZone - FAILED - zone->save() returned false', 3, null, 'PrestatillHomeDelivery');

						$message = array(
							'success' => false,
							'error' => 'Error saving zone - save() returned false. Check PrestaShop logs for details.'
						);
					}
				} catch (Exception $e) {
					PrestaShopLogger::addLog('SaveZone - EXCEPTION: ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 3, null, 'PrestatillHomeDelivery');

					$message = array(
						'success' => false,
						'error' => 'Exception: ' . $e->getMessage()
					);
				}
			break;

			// Zones Management - Update existing zone
			case 'updateZone':
				require_once(_PS_MODULE_DIR_.'prestatillhomedelivery/classes/PrestatillHomeDeliveryZone.php');

				try {
					$id_zone = (int)Tools::getValue('id_zone');
					$zone = new PrestatillHomeDeliveryZone($id_zone);

					if (Validate::isLoadedObject($zone)) {
						$zone->zone_name = pSQL(Tools::getValue('zone_name'));
						$zone->id_store = (int)Tools::getValue('id_store');
						$zone->id_carrier = (int)Tools::getValue('id_carrier', 0);
						$zone->zone_type = pSQL(Tools::getValue('zone_type'));

						// Ne pas utiliser pSQL sur les données JSON
						$zone_data_raw = Tools::getValue('zone_data');
						// Valider que c'est du JSON valide
						if (json_decode($zone_data_raw) === null && json_last_error() !== JSON_ERROR_NONE) {
							throw new Exception('Invalid JSON data for zone');
						}
						$zone->zone_data = $zone_data_raw;

						$zone->zone_color = pSQL(Tools::getValue('zone_color', '#FF0000'));
						$zone->active = (int)Tools::getValue('active', 1);
						$zone->priority = (int)Tools::getValue('priority', 0);
						$zone->date_upd = date('Y-m-d H:i:s');

						if ($zone->save()) {
							// Sauvegarder les jours de livraison
							$delivery_days = explode(',', Tools::getValue('delivery_days', ''));
							$zone->setDeliveryDays($delivery_days);

							$message = array(
								'success' => true,
								'message' => 'Zone updated successfully'
							);
						} else {
							$message = array(
								'success' => false,
								'error' => 'Error updating zone'
							);
						}
					} else {
						$message = array(
							'success' => false,
							'error' => 'Zone not found'
						);
					}
				} catch (Exception $e) {
					$message = array(
						'success' => false,
						'error' => $e->getMessage()
					);
				}
			break;

			// Zones Management - Delete zone
			case 'deleteZone':
				require_once(_PS_MODULE_DIR_.'prestatillhomedelivery/classes/PrestatillHomeDeliveryZone.php');

				try {
					$id_zone = (int)Tools::getValue('id_zone');
					$zone = new PrestatillHomeDeliveryZone($id_zone);

					if (Validate::isLoadedObject($zone)) {
						if ($zone->delete()) {
							$message = array(
								'success' => true,
								'message' => 'Zone deleted successfully'
							);
						} else {
							$message = array(
								'success' => false,
								'error' => 'Error deleting zone'
							);
						}
					} else {
						$message = array(
							'success' => false,
							'error' => 'Zone not found'
						);
					}
				} catch (Exception $e) {
					$message = array(
						'success' => false,
						'error' => $e->getMessage()
					);
				}
			break;

			// Zones Management - Save zones configuration
			case 'saveZonesConfiguration':
				$use_zones = (int)Tools::getValue('use_zones', 0);
				$id_shop = (int)Tools::getValue('id_shop', Context::getContext()->shop->id);

				Configuration::updateValue('PRESTATILL_HD_USE_ZONES', $use_zones, false, null, $id_shop);

				$message = array(
					'success' => true,
					'message' => 'Configuration saved successfully'
				);
			break;

			default:
		        $status = 'error';
		        $message = 'Unknown parameters!';

		    exit;

		}
		
		$response['status'] = $status;
		$response['message'] = $message;

        header('Content-Type: application/json');
        echo(json_encode($response));
        die();
    }

	public static function getAllDaysOpen()
    {
        $request = 'SELECT * 
        	FROM '._DB_PREFIX_.'prestatill_homedelivery 
        	WHERE openning = 1
        	AND id_shop_group = '.(int)Context::getContext()->shop->id_shop_group.'
			AND id_shop = '.(int)Context::getContext()->shop->id;
			
        $result = Db::getInstance()->executeS($request);
        return $result;
    }
	
	public function initTable($id_carrier = 0, $init_bo = 0, $id_order = 0)
    {
		
        $table_days = array();
        $result = self::getAllDaysOpen();
		
        foreach ($result as $key => $res) {
           
 			if($res['hour_close_pm'] == '00:00:00' && $res['hour_open_pm'] != '00:00:00')
				$res['hour_close_pm'] = '23:59:00';
			
			 $table_days['open'][$res['id_day']] = $res;
			
        }
        
        $iso_lang = Context::getContext()->language->iso_code.'_'.Tools::strtoupper(Context::getContext()->language->iso_code!=='en'?Context::getContext()->language->iso_code:'US');
        setlocale(LC_TIME, $iso_lang.'.utf8');

        $dateen = date("Y-m-d");

		// 1.4.1 : Used to display on list_creneau table / list
		$day = date('d'); // d= 01, 02, 03
        $month = date("F"); // F = January
        $dateday = date("D"); // D = Mon / l = Monday
		$year = '';
		$date_display = $this->module->l($dateday, 'validateordercarrier').' '.$this->module->l($day, 'validateordercarrier').' '.$this->module->l($month, 'validateordercarrier').' '.$year;
		$pHD = new PrestatillHomeDelivery();

		// 1.4.1
		$date_display = $pHD->getDateFormat(date('Y-m-d'));
		
		$distance = Configuration::get('PRESTATILL_SEARCH_HD_STORE')?(int)Configuration::get('PRESTATILL_SEARCH_HD_RADIUS'):0;
		$zips = Configuration::get('PRESTATILL_SEARCH_HD_ZIP')?Configuration::get('PRESTATILL_SEARCH_HD_POSTCODES'):null;
		
		$id_address = (int)Context::getContext()->cart->id_address_delivery;
		
		if($id_address > 0)
		{
			$address = new Address((int)$id_address);
			if(Validate::isLoadedObject($address))
			{
				$customer_zip = $address->postcode;
			}
		}
		else if($init_bo > 0 && $id_order > 0)
		{
			$order = new Order($id_order);
			if(Validate::isLoadedObject($order))
			{
				$address = new Address((int)$order->id_address_delivery);
				if(Validate::isLoadedObject($address))
				{
					$customer_zip = $address->postcode;
				}
			}
		}
		
		if(Configuration::get('PRESTATILL_HD_DBD_SUPP',null,(int)Context::getContext()->shop->id_shop_group,(int)Context::getContext()->shop->id))
			$dbd_supp = json_decode(Configuration::get('PRESTATILL_HD_DBD_SUPP',null,(int)Context::getContext()->shop->id_shop_group,(int)Context::getContext()->shop->id), true);
		
		if(Configuration::get('PRESTATILL_HD_ZIP_SUPP',null,(int)Context::getContext()->shop->id_shop_group,(int)Context::getContext()->shop->id))
			$zip_supp = json_decode(Configuration::get('PRESTATILL_HD_ZIP_SUPP',null,(int)Context::getContext()->shop->id_shop_group,(int)Context::getContext()->shop->id), true);
		
		// 1.2.0
		$carrier = new Carrier((int)$id_carrier);
		$id_reference = 0;
		if(Validate::isLoadedObject($carrier))
		{
			$id_reference = $carrier->id_reference;
		}
		
        //loop on days
        
        $nb_days_view = Configuration::get('PRESTATILL_HD_NB_DAY');

		// 3.0.0 : Intégration des indisponibilités directement dans le module
		// 1. Initialisation
		$days_unavailable = [];
		$id_cart         = (int) Context::getContext()->cart->id;
		$cart            = new Cart($id_cart);

		// 2. Récupération des jours indisponibles
		if (Module::isEnabled('prestatilldrive')) {
			// --- Drive installé et version >= 2.2.0 ---
			$drive_module = Module::getInstanceByName('prestatilldrive');
			if (Validate::isLoadedObject($drive_module)
				&& version_compare($drive_module->version, '2.2.0', '>')
				&& Validate::isLoadedObject($cart)
			) {
				$products = $cart->getProducts();
				foreach ($products as $product) {
					$avail = PrestatillDriveConfiguration::getAdditionnalAvailability($product['id_product']);
					if (!empty($avail)) {
						foreach ($avail as $pa) {
							if (!in_array($pa['id_day'], $days_unavailable, true)) {
								$days_unavailable[] = $pa['id_day'];
							}
						}
					}
				}
			}
		} else {
			if (Validate::isLoadedObject($cart)) {
				$products = $cart->getProducts();
				foreach ($products as $product) {
					$avail = PrestatillHomeDeliveryConfiguration::getAdditionnalAvailability($product['id_product']);
					if (!empty($avail)) {
						foreach ($avail as $pa) {
							if (!in_array($pa['id_day'], $days_unavailable, true)) {
								$days_unavailable[] = $pa['id_day'];
							}
						}
					}
				}
			}
		}

		// 3. Reconstruction de table_days['open']
		foreach ($result as $res) {
			$table_days['open'][$res['id_day']] = $res;
		}

		// 4. On désactive les jours indisponibles
		if (!empty($days_unavailable)) {
			foreach ($days_unavailable as $day) {
				if (isset($table_days['open'][$day])) {
					unset($table_days['open'][$day]);
				}
			}
		}

		$i = 1;
		$d = 0;
        while ($i <= $nb_days_view) {
        	
            $table_days['days'][$i]['date'] = $this->module->l($day, 'validateordercarrier').' '.$this->module->l($month, 'validateordercarrier');;
            $table_days['days'][$i]['dateen'] = $dateen;
            // 1.4.1 USED TO DISPLAY DATE ON LIST_CRENEAU TABLE / LIST
            $table_days['days'][$i]['day'] = $date_display;
			//////////////////////////////////////////////////////////
            
			$table_days['days'][$i]['zips'] = 0;
			
			// 1.2.2 : Si on définit une durée de créneau à 0
			$slot_duration = (int)Configuration::get('PRESTATILL_HD_DUREE');
			//dump('jour > '.$i.' - '.$dateday);

			// 1.4.2
			$phm = new PrestatillHomeDelivery();
			$temp_carriers = $phm->reorderCarriers();
			if(isset($temp_carriers[$id_reference]['duration']))
			{
				$slot_duration = (int)$temp_carriers[$id_reference]['duration']*60;
			}

			// 1.0.4 : distance supp per day
			if(isset($dbd_supp[$id_reference][date("N", strtotime('+'.$d.' days'))]))
			{
				//1.2.2 : Ajout des heures d'ouverture et fermeture
				if($slot_duration <= 0)
				{
					$table_days['days'][$i]['op'] = array();
					$table_days['days'][$i]['cl'] = array();
					
					$dd = date("N", strtotime('+'.$d.' days'));
					//dump($dd);
					// Uniquement si incrément = 0
					$opcl = new PrestatillHomeDeliveryConfiguration((int)$dd);
					if(Validate::isLoadedObject($opcl))
					{
						if($opcl->hour_open_am != '00:00:00')
							$table_days['days'][$i]['op'][] = substr($opcl->hour_open_am, 0, 5);
						
						if($opcl->hour_open_pm != '00:00:00')
							$table_days['days'][$i]['op'][] = substr($opcl->hour_open_pm, 0, 5);
						
						if($opcl->hour_close_am != '00:00:00')
							$table_days['days'][$i]['cl'][] = substr($opcl->hour_close_am, 0, 5);
						
						if($opcl->hour_close_pm != '00:00:00')
							$table_days['days'][$i]['cl'][] = substr($opcl->hour_close_pm, 0, 5);
					}
				}
				
				$table_days['days'][$i]['dist_max'] = (int)$dbd_supp[$id_reference][date("N", strtotime('+'.$d.' days'))]['dbd_supp'];
			}
			else if(isset($dbd_supp[$id_reference][0]))
			{
				$table_days['days'][$i]['dist_max'] = (int)$dbd_supp[$id_reference][0]['dbd_supp'];

				//1.2.2 : Ajout des heures d'ouverture et fermeture
				if($slot_duration <= 0)
				{
					$table_days['days'][$i]['op'] = array();
					$table_days['days'][$i]['cl'] = array();
					
					$dd = date("N", strtotime('+'.$d.' days'));
					//dump($dd);
					// Uniquement si incrément = 0
					$opcl = new PrestatillHomeDeliveryConfiguration((int)$dd);
					if(Validate::isLoadedObject($opcl))
					{
						if($opcl->hour_open_am != '00:00:00')
							$table_days['days'][$i]['op'][] = substr($opcl->hour_open_am, 0, 5);
						
						if($opcl->hour_open_pm != '00:00:00')
							$table_days['days'][$i]['op'][] = substr($opcl->hour_open_pm, 0, 5);
						
						if($opcl->hour_close_am != '00:00:00')
							$table_days['days'][$i]['cl'][] = substr($opcl->hour_close_am, 0, 5);
						
						if($opcl->hour_close_pm != '00:00:00')
							$table_days['days'][$i]['cl'][] = substr($opcl->hour_close_pm, 0, 5);
					}
				}
			}
			else 
			{
				$table_days['days'][$i]['dist_max'] = 0;
			}
			
			// Petit nettoyage
			if($zips != null)
			{
				$zips = $this->cleanZips($zips);
				
				$z_supp = explode(',',$zips);
				if(in_array($customer_zip, $z_supp))
				{
					$table_days['days'][$i]['zips'] = 1;
				}
			}
			
			// On vérifie les CP supplémentaires
			if(!empty($zip_supp[$id_reference]))
			{
				$table_days['days'][$i]['op'] = array();
				$table_days['days'][$i]['cl'] = array();
				$table_days['days'][$i]['cl_carence'] = array();
				
				foreach($zip_supp[$id_reference] as $id_zone)
				{
					foreach($id_zone as $supp)
					{
						//dump('ici');
						// 1.0.5 : postcode per day & slots
						if(date("N", strtotime('+'.$d.' days')) == $supp['id_day'] || $supp['id_day'] == 0)
						{
							$z_supp = $this->cleanZips($supp['zip_supp'], substr($address->country, 0, 2));
							
							// ON vérifie si le CP est dans l'addresse du client
							$z_supp = explode(',',$z_supp);
							//dump([$z_supp,$customer_zip,in_array($customer_zip, $z_supp),$supp['id_day']]);
							//dump($z_supp);
							
							//1.3.1 : On intègre les CP RANGES
							$range_zip = false;
							foreach($z_supp as $zip)
							{
								$pos = strpos($zip, '*');
								if($pos)
								{
									if(substr($customer_zip, 0, $pos) == substr($zip, 0, $pos))
										$range_zip = true;
								}
							}
							
							// On vérifie si supp[id_day] == 0
							// On récupère les heures d'ouvertures et fermetures du jour
							if(in_array($customer_zip, $z_supp) || $range_zip == true || $init_bo == 1)
							{
								$table_days['days'][$i]['zips'] = 1;
								
								$dd = (int)date("N", strtotime('+'.$d.' days'));
								
								$opcl = new PrestatillHomeDeliveryConfiguration((int)$dd);
								
								//dump($opcl);
								if(Validate::isLoadedObject($opcl))
								{
									if(!isset($supp['op']))
									{
										$table_days['days'][$i]['op'][] = Configuration::get('PRESTATILL_HD_OPEN');
									}	
									else 
									{
										if($supp['id_day'] == 0)
										{
											if($opcl->hour_open_am != '00:00:00' || ($opcl->hour_open_am == '00:00:00' && $opcl->hour_close_am != '00:00:00'))
												$table_days['days'][$i]['op'][] = substr($opcl->hour_open_am, 0, 5);
											
											if($opcl->hour_open_pm != '00:00:00')
												$table_days['days'][$i]['op'][] = substr($opcl->hour_open_pm, 0, 5);
										}
										else 
										{
											$table_days['days'][$i]['op'][] = $supp['op'];
										}
									}
									
									if(!isset($supp['cl']))
									{
										$table_days['days'][$i]['cl'][] = Configuration::get('PRESTATILL_HD_CLOSE');
									}	
									else 
									{
										if($supp['id_day'] == 0)
										{
											if($opcl->hour_close_am != '00:00:00')
												$table_days['days'][$i]['cl'][] = substr($opcl->hour_close_am, 0, 5);
											
											if($opcl->hour_close_pm != '00:00:00'  || ($opcl->hour_close_pm == '00:00:00' && $opcl->hour_open_pm != '00:00:00'))
											{
												if($opcl->hour_close_pm == '00:00:00' && $opcl->hour_open_pm != '00:00:00')
												{
													$table_days['days'][$i]['cl'][] = '23:59';
												}
												else
												{
													/*if(Configuration::get('PRESTATILL_HD_SLOT_MIN_DURATION') > 0)
													{
														$temp_closed_hour = date('H:i', strtotime($opcl->hour_close_pm) - 60*Configuration::get('PRESTATILL_HD_SLOT_MIN_DURATION'));
													}
													$table_days['days'][$i]['cl'][] = substr($temp_closed_hour, 0, 5);*/
													$table_days['days'][$i]['cl'][] = substr($opcl->hour_close_pm, 0, 5);
												}
											}
										}
										else 
										{
											$table_days['days'][$i]['cl'][] = $supp['cl'];
										}
									}
									// LPF
									if(Configuration::get('PRESTATILL_HD_SLOT_MIN_DURATION') > 0)
									{
										$temp_closed_hour = date('H:i', strtotime($opcl->hour_close_pm) - 60*Configuration::get('PRESTATILL_HD_SLOT_MIN_DURATION'));
									}
									else
									{
										$temp_closed_hour = '00';
									}
									$table_days['days'][$i]['cl_carence'][] = substr($temp_closed_hour, 0, 5).':00';
								}
							}
						}
					}
				}
				//dump($table_days['days'][$i]);
				//die();
			}
			
            $today = date("Y-m-d", strtotime('+'.$i.' days'));
            $today_day = date("D", strtotime('+'.$i.' days'));
			$table_days['days'][$i]['id_day']= date("N", strtotime('+'.$d.' days'));
            $day = date("d", strtotime($today)); 
            $month = date('F', strtotime($today)); 
            $dateen = date("Y-m-d", strtotime('+'.$i.' days'));
            $dateday = date('l', strtotime($today_day));
			// 3.0.0
			$date_display = $pHD->getDateFormat($today);
            $i++;
			$d++;
        } 

        $days_dispo = array();
		// 1.2.2
		if(Configuration::get('PRESTATILL_HD_DUREE') > 0)
		{
			$increment = Configuration::get('PRESTATILL_HD_DUREE')*60;
		}
		else 
		{
			$increment = 1000;
		}

		// 1.4.1 : Paramètres généraux pouvant être disponibles par transporteur
        $nb_orders_conf = Configuration::get('PRESTATILL_HD_NB_DISPO');
		$table_days['increment'] = Configuration::get('PRESTATILL_HD_DUREE');
		$delai_carence = Configuration::get('PRESTATILL_HD_CARENCE');

		// LPF
		$table_days['hd_slot_min_duration'] = Configuration::get('PRESTATILL_HD_SLOT_MIN_DURATION');
		$close_carence = $table_days['hd_slot_min_duration'];
		
		// 1.4.1 : On vérifie s'il existe un paramètre spéciale pour le transporteur
		if(isset($temp_carriers[$id_reference]['carence']))
		{
			if($temp_carriers[$id_reference]['carence'] > 0)
				$delai_carence = (int)$temp_carriers[$id_reference]['carence'];
		}

		if(isset($temp_carriers[$id_reference]['duration']))
		{
			if($temp_carriers[$id_reference]['duration'] > 0)
			{
				$table_days['increment'] = (int)$temp_carriers[$id_reference]['duration']*60;
			}
			else if ($temp_carriers[$id_reference]['duration'] == 0)
			{
				$table_days['increment'] = 0;
			}
		}

		if(isset($temp_carriers[$id_reference]['nb_dispo']))
		{
			if($temp_carriers[$id_reference]['nb_dispo'] > 0)
				$nb_orders_conf = (int)$temp_carriers[$id_reference]['nb_dispo'];
		}

        $table_days['nb_days_view'] = $nb_days_view;
        foreach ($table_days['days'] as $id) {
            $date = date(date("Y-m-d"), strtotime($id['date']));

            $h = strtotime($date.' '.Configuration::get('PRESTATILL_HD_OPEN'));

            $i = 1;
			$closed_hour = Configuration::get('PRESTATILL_HD_CLOSE');
			
			// 1.3.1 : On vérifie si on est sur une journée "pleine"
			if($closed_hour == '00:00')
				$closed_hour = '23:59';
			
            while ($i <= $nb_days_view) {
                if (!isset($table_days['open'][$i]['id_day'])) {
                    $table_days['open'][$i]['id_day'] = array();
                }
                if ($id['id_day'] == $table_days['open'][$i]['id_day']) {
                	
					// 1.3.1 : On permet les commandes de nuits
					 if($table_days['open'][$i]['hour_close_pm'] == '00:00:00' && $table_days['open'][$i]['hour_close_pm'] != '00:00:00')
					 {
					 	$closed_hour = '23:59';
					 }
					
                    while ($h <= strtotime($date.' '.$closed_hour)) {
                        if ($table_days['open'][$i]['nonstop'] == true) {
                            if ($h >= $table_days['open'][$i]['hour_open_am'] && $h < $table_days['open'][$i]['hour_close_pm']) {
                                $days_dispo[$id['date']][] = $h;
                            }
                        } else {
                            if ((($h >= $table_days['open'][$i]['hour_open_am']) && ($h < $table_days['open'][$i]['hour_close_am']))
                            || (($h >= $table_days['open'][$i]['hour_open_pm']) && ($h < $table_days['open'][$i]['hour_close_pm']))) {
                                $days_dispo[$id['date']][] = $h;
                            }
                        }
                        $h = $h+$increment;
                    }
                }
                $i++;
            }
        }
		
        //loop for hours
        $h = strtotime($date.' '.Configuration::get('PRESTATILL_HD_OPEN'));
        //$increment = Configuration::get('PRESTATILL_HD_DUREE')*60;

        $h2 = strtotime($date.' '.Configuration::get('PRESTATILL_HD_OPEN'));
        while ($h <= strtotime($date.' '.$closed_hour)) {
            $table_days['hours'][] = date('H:i:s', $h);
            while ($h2 <= strtotime($date.' '.$closed_hour)) {
                $table_days['creneau'][] = date('H:i:s', $h2);
                $h2 = $h2+$increment;
            }
            $h = $h+3600;
			
			if($h > strtotime($date.' '.$closed_hour))
			{
				$table_days['hours'][] = date('H:i:s', $h2);
				$table_days['creneau'][] = date('H:i:s', $h2);
			}
        }

		if(Configuration::get('PRESTATILL_HD_CARENCE_SUPP'))
			$carence_supp = json_decode(Configuration::get('PRESTATILL_HD_CARENCE_SUPP'), true);
		
		//@TODO
		if(!empty($carence_supp))
		{
			$date_now = date("D");
			$id_day_now = (int)$this->getFullDateToLocales($date_now);
			if($id_day_now == 1)
			{
				$id_day_prev = 7;
			}
			else
			{
				$id_day_prev = $id_day_now-1;
			}
			$hour_now = date('H:i:s');

			foreach($carence_supp as $id_ref => $supp)
			{
				foreach($supp as $id_day => $carence)
				{
					if($id_ref == 0 || $id_ref == $id_reference)
					{
						// Carence AllDays
						if ($id_day == 0)
						{
							if($hour_now > $carence['hour_limit'] || ($hour_now > $carence['hour_limit'] && $hour_now < $carence['hour_limit_end'])) 
							{
								$delai_carence += $carence['waiting_time'];
							}
						}

						// Carence Day by day
						if (($id_day == $id_day_now && $id_day != 0) || isset($supp[$id_day_prev]))
						{
							if($carence['id_day'] == $carence['id_day_end'])
							{
								if($hour_now > $carence['hour_limit'] && $hour_now < $carence['hour_limit_end'])
								{
									$delai_carence += $carence['waiting_time'];
								}
							}
							else 
							{
								if($hour_now > $carence['hour_limit'])
								{
									$delai_carence += $carence['waiting_time'];
								}
							}
							
							if (isset($supp[$id_day_prev]))
							{
								if($hour_now > 0 && $hour_now < $carence['hour_limit_end'])
								{
									$delai_carence += $carence['waiting_time'];
								}
							}
						}
					}
				}
			}
		}

		// 1.5.2 : Ajout d'un hook pour définir un délai de carence via un module tiers
		Hook::exec('actionSetAdditionalDelaiCarence', array('delai_carence' => &$delai_carence), null, true);

		// 3.0.0 : On vérifie la carence supplémentaire par produit uniquement si le module drive est désinstallé
		if(!Module::isEnabled('prestatilldrive'))
		{
			if(Validate::isLoadedObject($cart))
			{
				// On récupère la liste des produits
				$products = $cart->getProducts();
				
				// On initialise le délai de carence avec le délai actuel
				$carrence_add = (int)$delai_carence;
				$temp_delai = 0;
				$carence_supp_list = [];

				foreach($products as $product)
				{
					$temp_carence_supp = 0;
					$actual_carence_supp = PrestatillHomeDeliveryConfiguration::getAdditionnalCarence($product['id_product']);

					if(!empty($actual_carence_supp))
					{
						$temp_carence_supp = (int)$actual_carence_supp['carence_supp']*60; // Conversion en min
						if($temp_carence_supp > 0)
						{
							if((int)$temp_carence_supp > (int)$carrence_add)
							{
								$carence_supp_list[] = (int)$temp_carence_supp;
							}
						}
					}
				}

				if(count($carence_supp_list) > 0) 
				{
					$temp_delai = max($carence_supp_list);
				}

				if($temp_delai > 0)
				{
					$delai_carence = $temp_delai;
				}
			}
		}
		
        // Calcul number of waiting time slots
        $creneau_carence = $delai_carence;
		
		// LPF
		if(Configuration::get('PRESTATILL_HD_LPF_ACTIVE') && Configuration::get('PRESTATILL_HD_SLOT_MIN_DURATION') > 0)
		{
			$creneau_carence += Configuration::get('PRESTATILL_HD_SLOT_MIN_DURATION');
		}

        //Add waiting time slots to $table_days
        $table_days['creneau_carence'] = $creneau_carence ;
        $table_days['vacations'] = PrestatillHomeDeliveryVacation::getAllVacation();
		
		// 1.0.4
		$table_days['store_zip'] = Configuration::get('PRESTATILL_SEARCH_HD_ZIP')?1:0;
        
        // loop for reserved
        $creneau_reserved = PrestatillHomeDeliveryCreneau::getReservedCreneau($id_reference);
        $table_days['reserved'] = null;
		$manual_slots = [];
        if(!empty($creneau_reserved))
        {
            $table_days['reserved'] = $creneau_reserved;
        }
		
		// 1.3.1 : On ajoute la notion de créneau manuel
		$manual_slots = PrestatillHomeDeliveryCreneau::getAllManualCreneau($id_reference);
		
		if(!empty($creneau_reserved))
		{
			$table_days['reserved'] = array_merge($manual_slots,$creneau_reserved);
		}
		else
		{
			$table_days['reserved'] = $manual_slots;
		}
		
        $table_days['creneau_limit'] = (int)$nb_orders_conf;
        
        return $table_days;
    }

	public function getFullDateToLocales($date)
    {
        $pHomeDelivery = new PrestatillHomeDelivery();
        $days = $pHomeDelivery->getDays();
        
        foreach ($days as $from => $to) {
            $date = str_replace($from, $to, $date);
        }
        if(is_numeric($date))
        {
            return $date;        
        }
        else 
        {
            $days = $pHomeDelivery->getDays(true);
            foreach ($days as $from => $to) {
                $date = str_replace($from, $to, $date);
            }
            
            return $date;
        }
	}
	
	public function cleanZips($zips, $iso_code = null)
	{
		$zips = str_replace(',,', ',', $zips);
		$zips = str_replace(', ', ',', $zips);
		$zips = trim($zips);
	
		if(substr($zips, -1) == ',')
			$zips = substr($zips, 0, -1);
		
		if(substr($zips, 0, 1) == ',')
			$zips = substr($zips, 1);

		$zips = explode(',', $zips);

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

		asort($zips);
		$zips = implode(',', $zips);
			
		return $zips;
	}
}