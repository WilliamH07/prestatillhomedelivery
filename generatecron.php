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

if (!defined('_PS_ADMIN_DIR_')) {
    define('_PS_ADMIN_DIR_', getcwd());
}

include(_PS_ADMIN_DIR_.'/../../config/config.inc.php');

if (Tools::substr(_COOKIE_KEY_, 34, 8) != Tools::getValue('token') || !Module::isInstalled('prestatillhomedelivery')) {
    die;
}

require_once(dirname(__FILE__).'/prestatillhomedelivery.php');

//ini_set('max_execution_time', 7200);

if(Configuration::get('PRESTATILL_HD_SEND_REMINDER'))
{
	$time = Configuration::get('PRESTATILL_HD_SEND_REMINDER_TIME')?Configuration::get('PRESTATILL_HD_SEND_REMINDER_TIME'):60;
	$hd = new PrestatillHomeDelivery();
	$hd->sendMailReminderForHomeDeliveryOrders($time);
}


if (Tools::getValue('redirect') && isset($_SERVER['HTTP_REFERER'])) {
    Tools::redirectAdmin($_SERVER['HTTP_REFERER'].'&conf=4');
}
