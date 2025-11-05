{*
* Home Delivery Slots
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

<div id="hd_dispo_overlay" ></div>
<div class="hd_order_creneau_new_button">
	<i class="fa fa-clock"></i><span>{l s='Home Delivery Slot' mod='prestatillhomedelivery'}</span>
	<button class="btn btn-default">{l s='Add a Delivery Slot' mod='prestatillhomedelivery'}</button>	
	<div class="clearfix"></div>	
</div>
{include file="./admin_order_edit_slot.tpl"}
