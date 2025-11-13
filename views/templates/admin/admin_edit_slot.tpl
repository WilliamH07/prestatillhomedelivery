{*
* Home Delivery Slots
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

<div class="hd_order_creneau_edit">
	<div id="hd_dispo_head" class="scroll scroll4"></div>
	<div id="hd_dispo" data-url="{Context::getContext()->link->getModuleLink('prestatillhomedelivery', 'validateordercarrier')|escape:'htmlall':'UTF-8'}"></div>
	<div class="col-xs-12 col-md-3 col177">
		<select name="id_carrier" id="id_carrier" class="custom-select">
			<option value="0" selected>{l s='Select' mod='prestatillhomedelivery'}</option>
			{foreach from=$carriers item=carrier}
			<option value="{$carrier.id_reference|escape:'htmlall':'UTF-8'}">{$carrier.name|escape:'htmlall':'UTF-8'}</option>
			{/foreach}
		</select>
	</div>
	<div class="col-xs-12 col-md-2 col177">
		<input type="date" name="hd_slot_date" id="hd_slot_date" class="form-control" value="" readonly="readonly"/>
	</div>
	<div class="col-xs-12 col-md-2 col177">
		<input name="hd_slot_hour" id="hd_slot_hour" type="hour" class="form-control" value="" readonly="readonly" />
	</div>
    <div class="col-xs-12 col-md-3 col177">
        <textarea class="form-control" id="manual_comment" name="manual_commment" placeholder="Write a comment here..."></textarea>
    </div>
	<div class="col-xs-12 col-md-2 col177">
		<button name="submitHomeDeliverySlotCreate" class="btn btn-primary">{l s='Validate Slot' mod='prestatillhomedelivery'}</button>
	</div>
	<div class="clearfix"></div>
</div>
<div class="hd_message_validation alert alert-success" style="display:none;">{l s='Slot updated with success !' mod='prestatillhomedelivery'}</div>