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

class PrestatillHomeDeliveryVacation extends ObjectModel
{
    public $id_vacation;
	
    public $vacation_start;
	
    public $vacation_end;
	
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'prestatill_homedelivery_vacation',
        'primary' => 'id_vacation',
        'multilang' => false,
        'fields' => array(
            'vacation_start' =>                 array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true),
            'vacation_end' =>               array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true),
        ),
    );

    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
    }

    public static function getAllVacation()
    {
        $request = 'SELECT * FROM '._DB_PREFIX_.'prestatill_homedelivery_vacation v';
		
        $result = Db::getInstance()->executeS($request);

        // 1.4.1 : Addition of a hook to interact with base carence according to the Prestatill Drive Module > V2.3.0
		Hook::exec('actionGetUnavailbilitiesAsVacation', array('result' => &$result), null, true);
        
        return $result;
    }

    public static function deleteVacation($id)
    {
        $vac = new PrestatillHomeDeliveryVacation((int)$id);
        if (Validate::isLoadedObject($vac)) {
            $vac->delete();
        }
    }
}
