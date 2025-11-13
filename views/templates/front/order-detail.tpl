{*
* Home Delivery Slots
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

<div id="hd_order_detail" class="box text-center">
	{if $creneau->id_day > 0}
		<h5>{l s='Your delivery slot' mod='prestatillhomedelivery'}</h5>
		<p class="alert alert-info" style="text-align: center;">
			<span><b>{$msg_creneau|escape:'htmlall':'UTF-8'}</b></span>
		</p>
	{/if}
	<div class="clearfix"></div>
</div>