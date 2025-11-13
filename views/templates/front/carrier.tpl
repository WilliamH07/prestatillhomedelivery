{*
* Home Delivery Slots
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

{if !empty($elligible_carriers)}
    {foreach from=$elligible_carriers item=ec}
        <input id="hd_id_carrier_{$ec|escape:'htmlall':'UTF-8'}" type="hidden" value="{$ec|escape:'htmlall':'UTF-8'}"/>
    {/foreach}
{/if}
<input id="hd_id_carrier" type="hidden" value="{$carrier|escape:'htmlall':'UTF-8'}"/>

<div id="hd_box" class="col-xs-12" data-creneau="{if isset($creneau_day) && isset($creneau_hour)}1{else}0{/if}">
	<input type="hidden" value="{$base_url|escape:'htmlall':'UTF-8'}" id="psd_base_url" name="psd_base_url" />
	<input type="hidden" value="{$lpf_slot_min_duration|escape:'htmlall':'UTF-8'}" id="lpf_slot_min_duration" name="lpf_slot_min_duration" />
	<div class="row">
	    {include file="./shop.tpl"}
	</div>
	<div id="hd_dispo_overlay"></div>
	<h3>{l s='Choose a slot for your delivery' mod='prestatillhomedelivery'}</h3>	
	<div class="clear"></div>
	<div class="alert alert-warning" id="no_delivery_on_date">{l s='Please choose a delivery date in the next' mod='prestatillhomedelivery'} <b>{$max_days|escape:'htmlall':'UTF-8'} {l s='days' mod='prestatillhomedelivery'}</b></div>
	<div id="hd_nav_buttons" >
	   <div class="btn btn-default buttondays" id="hd_prev_days">< {l s='Previous Days' mod='prestatillhomedelivery'}</div>
	   <div class="btn btn-default buttondays" id="hd_next_days">{l s='Following Days' mod='prestatillhomedelivery'} ></div>
	   <input type="date" id="delivery_date_calendar" name="delivery_date_calendar" />
	</div>
	<div class="clearfix"></div>
	<div id="hd_dispo_head" class="scroll scroll4"></div>
	<div id="hd_dispo" data-url="{Context::getContext()->link->getModuleLink('prestatillhomedelivery', 'validateordercarrier')|escape:'htmlall':'UTF-8'}"></div>
	
	<div id="hd_legend">
		<ul>
			<li class="dispo">{l s='Available' mod='prestatillhomedelivery'}</li>
			<li>{l s='Unavailable' mod='prestatillhomedelivery'}</li>
			<li class="busy">{l s='Busy' mod='prestatillhomedelivery'}</li>
			<li class="vacation	">{l s='Full' mod='prestatillhomedelivery'}</li>
		</ul>
	</div>
	
	<div class="row">
		{include file="./order.tpl"}
		<input type="hidden" id="address_max_dist" name="address_max_dist" value="0" />		
	</div>
	<div class="clear"></div>
</div>

<div class="modal fade" id="modal_creneau">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <button type="button" class="close" data-dismiss="modal"  aria-label="{l s='Close' mod='prestatillhomedelivery'}">
                  <span aria-hidden="true">&times;</span>
            </button>
            <div class="js-modal-content"></div>
        </div>
    </div>
</div>