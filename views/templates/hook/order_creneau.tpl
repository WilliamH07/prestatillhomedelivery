{*
* Home Delivery Slots
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

{if $msg_creneau}
	<div class="hd_order_creneau">
		<span>{l s='Delivery slot :' mod='prestatillhomedelivery'}</span><br />
		<span> <b>{$msg_creneau|escape:'htmlall':'UTF-8'}</b></span><br />
        <button class="btn btn-default" data-type="edit"><i class="icon-pencil"></i><span>{l s='Edit slot' mod='prestatillhomedelivery'}</span></button>
        <button class="btn btn-default" style="background:red;color:#FFF;" data-type="delete"><i class="icon-times" style="color:#FFF;"></i><span>{l s='Delete slot' mod='prestatillhomedelivery'}</span></button>
		<input type="hidden" id="hd_confirm_msg" value="{l s='Be carefull, by clicking OK, the current Pick Up Slot will directly be released until you validate a new one.' mod='prestatillhomedelivery'}"/>
		<div class="clearfix"></div>
	</div>
{include file="./admin_order_edit_slot.tpl"}
{/if}
