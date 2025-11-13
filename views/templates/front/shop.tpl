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
	<div id="hd_shop_selected" class="alert alert-success">
			<h5>{l s='Your selected delivery address' mod='prestatillhomedelivery'}</h5>
		<div id="hd_delivery_address" class="">
			{if $address != null}
				{$address->firstname|escape:'htmlall':'UTF-8'} {$address->lastname|escape:'htmlall':'UTF-8'}<br />
				{$address->address1|escape:'htmlall':'UTF-8'}<br />
				{$address->postcode|escape:'htmlall':'UTF-8'} {$address->city|escape:'htmlall':'UTF-8'}
			{/if}
		</div>
	</div>
</div>
