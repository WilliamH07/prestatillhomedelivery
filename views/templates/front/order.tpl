{*
* Home Delivery Slots
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}
	
<div class="col-xs-12">
	<div id="hd_creneau_selected" class="alert alert-warning">
		<h5>{l s='Your slot' mod='prestatillhomedelivery'}</h5>
		<div id="hd_creneau_day" class="">
			{if isset($creneau_day) && isset($creneau_hour)}
				{$creneau_day|escape:'htmlall':'UTF-8'|date_format:"%A, %e %B, %Y"} à {$creneau_hour|escape:'htmlall':'UTF-8'|date_format:"%R"}	
			{/if}
		</div>
		<button type="button" class="btn btn-secondary changeSlot">
            {l s='Change slot' mod='prestatillhomedelivery'}
        </button>
	</div>
</div>

<button class="continue btn btn-primary float-xs-right" id="HDverifyCreneau" style="display: none;">{l s='Continue' mod='prestatillhomedelivery'}</button>