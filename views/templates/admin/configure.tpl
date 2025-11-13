{*
* Home Delivery Slots
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

<div class="clearfix"></div>

<div class="bootstrap">
	<!-- Module content -->
	<div id="modulecontent" class="clearfix {if $ps_version_bo >= '1.7.8'}new{/if}">	
		{if isset($confirmation)}		
			{if $confirmation == true}
			<div class="alert alert-success">{l s='Settings updated' mod='prestatillhomedelivery'}</div>
			{else}
			<div class="alert alert-warning">
				{l s='Error on settings updated' mod='prestatillhomedelivery'}		
			</div>
			{/if}
		{/if}
		<!-- Nav tabs -->
		<div class="col-lg-2">
			<div class="list-group">
				<a href="#parameter" class="menu_tab list-group-item {if $tab == 1}active{/if}" data-toggle="tab"><i class="icon-cogs"></i> {l s='Parameters' mod='prestatillhomedelivery'}</a>
				<a href="#carrier_parameter" class="menu_tab list-group-item {if $tab == 2}active{/if}" data-toggle="tab"><i class="icon-truck"></i> {l s='Parameters by carriers' mod='prestatillhomedelivery'}</a>
				<a href="#openingdays" class="menu_tab list-group-item {if $tab == 3}active{/if} " data-toggle="tab"><i class="icon-calendar"></i> {l s='Delivery Days & hours' mod='prestatillhomedelivery'}</a>
				<a href="#deliveryarea" class="menu_tab list-group-item {if $tab == 4}active{/if} " data-toggle="tab"><i class="icon-truck"></i> {l s='Delivery area' mod='prestatillhomedelivery'}</a>
				<a href="#zones_management" class="menu_tab list-group-item {if $tab == 6}active{/if}" data-toggle="tab"><i class="icon-map-marker"></i> {l s='Delivery Zones Management' mod='prestatillhomedelivery'}</a>
				<a href="#vacation" class="menu_tab list-group-item {if $tab == 5}active{/if}" data-toggle="tab"><i class="icon-sun-o"></i> {l s='Vacation' mod='prestatillhomedelivery'}</a>
			</div>
			<div class="list-group">
				<a class="list-group-item"><i class="icon-info"></i> {l s='Version' mod='prestatillhomedelivery'} {$module_version|escape:'htmlall':'UTF-8'}</a>
			</div>
			<div class="list-group other_modules">
				<h3>{l s='Our other modules' mod='prestatillhomedelivery'}</h3>
				<a class="list-group-item" href="{if Context::getContext()->language->iso_code == 'fr'}https://addons.prestashop.com/fr/preparation-expedition/88373-smart-stock-gestion-de-stock-simplifiee-par-code-barre.html{else}https://addons.prestashop.com/en/preparation-shipping/88373-smart-stock-simplified-stock-management-using-barcode.html{/if}" target="_blank">
					<span class="new">{l s='NEW' mod='prestatillhomedelivery'}</span>
					<img src="{$base_dir|escape:'htmlall':'UTF-8'}modules/prestatillhomedelivery/views/img/ss-logo.png" width="57" height="57" /> 
					{l s='Module Smart Stock: simplified stock management using barcode' mod='prestatillhomedelivery'} <br />
					<b><i class="icon-external-link"></i> {l s='View on Addons' mod='prestatillhomedelivery'}</b>
					<div class="clearfix"></div>
				</a>
				<a class="list-group-item" href="{if Context::getContext()->language->iso_code == 'fr'}https://addons.prestashop.com/fr/gestion-stocks-fournisseurs/87737-stock-par-magasin-transformez-vos-magasins-en-entrepot.html{else}https://addons.prestashop.com/en/stock-supplier-management/87737-stock-per-store-turn-your-stores-into-warehouses.html{/if}" target="_blank">
					<img src="{$base_dir|escape:'htmlall':'UTF-8'}modules/prestatillhomedelivery/views/img/sbs-logo.png" width="57" height="57" /> 
					{l s='Module Stock per Store: turn your stores into warehouses' mod='prestatillhomedelivery'} <br />
					<b><i class="icon-external-link"></i> {l s='View on Addons' mod='prestatillhomedelivery'}</b>
					<div class="clearfix"></div>
				</a>
				<a class="list-group-item" href="{if Context::getContext()->language->iso_code == 'fr'}https://addons.prestashop.com/fr/preparation-expedition/85720-live-picking-preparation-de-commandes-avancee.html{else}https://addons.prestashop.com/en/preparation-shipping/85720-live-picking-advanced-orders-preparation.html{/if}" target="_blank">
					<img src="{$base_dir|escape:'htmlall':'UTF-8'}modules/prestatillhomedelivery/views/img/lp-logo.png" width="57" height="57" /> 
					{l s='Module Live Picking : Advanced Orders Preparation' mod='prestatillhomedelivery'} <br />
					<b><i class="icon-external-link"></i> {l s='View on Addons' mod='prestatillhomedelivery'}</b>
					<div class="clearfix"></div>
				</a>
				<a class="list-group-item" style="background: aliceblue;text-align:center;" href="{if Context::getContext()->language->iso_code == 'fr'}https://addons.prestashop.com/fr/218_adam-dev{else}https://addons.prestashop.com/en/218_adam-dev{/if}" target="_blank">
					<b><i class="icon-list"></i>&nbsp; {l s='View all our modules' mod='prestatillhomedelivery'}</b>
					<div class="clearfix"></div>
				</a>
			</div>
		</div>
		
		<!-- Tab panes -->
        <div class="tab-content col-lg-10">
			<div class="tab-pane panel {if $tab == 1}active{/if}" id="parameter">
				{include file="./tabs/parameter.tpl"}
			</div>
		</div>
		<div class="tab-content col-lg-10">
			<div class="tab-pane panel {if $tab == 2}active{/if}" id="carrier_parameter">
				{include file="./tabs/carrier_parameter.tpl"}
			</div>
		</div>
		<div class="tab-content col-lg-10">
			<div class="tab-pane panel col-md-12 tab_padd_12 {if $tab == 3}active{/if}" id="openingdays">
				<div class="col-md-12">
					{include file="./tabs/openingdays.tpl"}
				</div>
			</div>
		</div>
		
		<div class="tab-content col-lg-10">
			<div class="tab-pane panel col-md-12 tab_padd_12 {if $tab == 4}active{/if}" id="deliveryarea">
				<div class="col-md-12">
					{include file="./tabs/deliveryarea.tpl"}
				</div>
			</div>
		</div>
		
		<div class="tab-content col-lg-10">
			<div class="tab-pane panel col-md-12 tab_padd_12 {if $tab == 6}active{/if}" id="zones_management">
				<div class="col-md-12">
					{include file="./tabs/zones_management.tpl"}
				</div>
			</div>
		</div>

		<div class="tab-content col-lg-10">
			<div class="tab-pane panel {if $tab == 5}active{/if}" id="vacation">
				{include file="./tabs/vacation.tpl"}
			</div>
		</div>
	</div>
</div>