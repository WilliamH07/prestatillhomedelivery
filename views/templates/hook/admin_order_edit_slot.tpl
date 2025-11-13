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
	<input type="hidden" name="hd_oc_id_order" id="hd_oc_id_order" value="{$id_order|escape:'htmlall':'UTF-8'}" />
	<div id="hd_dispo" data-url="{Context::getContext()->link->getModuleLink('prestatillhomedelivery', 'validateordercarrier')|escape:'htmlall':'UTF-8'}"></div>
	<div class="col-xs-12 col-md-4 col177">
		<select name="id_carrier" id="id_carrier" class="custom-select">
			<option value="0" selected>{l s='Select' mod='prestatillhomedelivery'}</option>
			{foreach from=$carriers item=carrier}
			<option value="{$carrier.id_reference|escape:'htmlall':'UTF-8'}">{$carrier.name|escape:'htmlall':'UTF-8'}</option>
			{/foreach}
		</select>
	</div>
	<div class="col-xs-12 col-md-3 col177">
		<input type="date" name="hd_slot_date" id="hd_slot_date" class="form-control" value="" readonly="readonly"/>
	</div>
	<div class="col-xs-12 col-md-2 col177">
		<input name="hd_slot_hour" id="hd_slot_hour" type="hour" class="form-control" value="" readonly="readonly" />
	</div>
	<div class="col-xs-12 col-md-3 col177">
		<button name="submitHomeDeliverySlotCreate" class="btn btn-primary">{l s='Validate Slot' mod='prestatillhomedelivery'}</button>
	</div>
	<div class="clearfix"></div>
	<div class="colx-xs-12">
		<div class="send_email">
			<label class="control-label col-lg-8 col177" for="order_send_mail_modif">
				<span>
					{l s='Send an Email to inform the customer for the modification ?' mod='prestatillhomedelivery'}
				</span>
			</label>
			<div class="col-lg-4 col177" style="text-align:right;">
				<span class="switch prestashop-switch fixed-width-lg" style="display:inline-block;margin-right:0;">
					<input class="drive_enabled" type="radio" name="hd_order_send_mail_modif" id="hd_order_send_mail_modif_on" value="1" checked="checked">
						<label for="hd_order_send_mail_modif_on" class="radioCheck">
							{l s='Yes' mod='prestatillhomedelivery'}
						</label>
					<input class="drive_enabled" type="radio" name="hd_order_send_mail_modif" id="hd_order_send_mail_modif_off" value="0">
						<label for="hd_order_send_mail_modif_off" class="radioCheck">
							{l s='No' mod='prestatillhomedelivery'}
						</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	<div class="clearfix"></div>
</div>
<div class="hd_message_validation alert alert-success" style="display:none;">{l s='Slot updated with success !' mod='prestatillhomedelivery'}</div>