{*
* Home Delivery Slots
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

<div id="hd_creneau_selected" class="alert alert-success">
		<h5>{l s='Your slot' mod='prestatillhomedelivery'}</h5>
	<div id="hd_creneau_day" class="">
		{if isset($creneau)}
			{$creneau|escape:'htmlall':'UTF-8'}
		{else}
			{l s='no selected slot' mod='prestatillhomedelivery'}
		{/if}
	</div>
</div>
<div id="hd_store_selected" class="background-light col-sm-12"></div>
<div class="clearfix"></div>
