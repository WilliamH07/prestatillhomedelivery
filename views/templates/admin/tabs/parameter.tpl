{*
* Home Delivery Slots
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

<h3><i class="icon-cogs"></i> {l s='Parameters' mod='prestatillhomedelivery'}</h3>
<form role="form" class="form-horizontal"  action="#" method="POST" id="parameter_form" name="parameter_form">
    <div class="new_features_box" class="col-xs-12">
       <h4>{l s='Welcome to the Home Delivery with time slots Module for Prestashop' mod='prestatillhomedelivery'}<span class="label">{$module_version|escape:'htmlall':'UTF-8'}</span></h4>
       <p>{l s='To work, you just have to configure some parameters on "parameters" and "opening days" tabulations on the left side.' mod='prestatillhomedelivery'}</p>
       <p>{l s='Since the version 1.2.0, the module can be used for several carriers and now uses Prestashop zones for the "postal codes" part, for which you can define your own price rules, depending on the postal codes you assign to each "zone".' mod='prestatillhomedelivery'}</p>
       <p>{l s='You can configure this options below.' mod='prestatillhomedelivery'}</p>
       <p><b>{l s='If you encounter any problem regarding the correct functioning of the module with your store, do not hesitate to contact our support.' mod='prestatillhomedelivery'}</b></p>
    </div>	
    {if $p_version_update < '1.2.0'}
    <div class="alert alert-danger">{l s='Please reset the Home Delivery Module to enable all new features.' mod='prestatillhomedelivery'}</div>
    {/if}
	<h4>{l s='General parameters' mod='prestatillhomedelivery'}</h4>

	{if $lpf_active}
		<div class="form-group">
			<label class="control-label col-lg-3" for="PRESTATILL_HD_SLOT_MIN_DURATION">
				<span>
					{l s='Min duration for a slot' mod='prestatillhomedelivery'}
				</span>
			</label>
			<div class="col-lg-9">				
				<div class="col-lg-3">
					<div class="input-group">
						<input type="number" name="PRESTATILL_HD_SLOT_MIN_DURATION" style="text-align: center" id="PRESTATILL_HD_SLOT_MIN_DURATION" value="{if $lpf_slot_min_duration}{$lpf_slot_min_duration|escape:'htmlall':'UTF-8'}{else}0{/if}" />	
						<span class="input-group-addon">min</span>				
					</div>
				</div>
			</div>
		</div>
	{/if}
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_HD_DISPLAY_TABLE">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Define if you want to see slots with or without a table with opening days & hours' mod='prestatillhomedelivery'}">
				{l s='Display available slots in a list instead of a table' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-lg-4">
				<span class="switch prestashop-switch fixed-width-lg">
					<input class="slot_enabled" type="radio" name="PRESTATILL_HD_DISPLAY_TABLE" id="PRESTATILL_HD_DISPLAY_TABLE_on" value="1" {if $display_table == 1} checked="checked"{/if}>
						<label for="PRESTATILL_HD_DISPLAY_TABLE_on" class="radioCheck">
							{l s='Yes' mod='prestatillhomedelivery'}
						</label>
					<input class="slot_enabled" type="radio" name="PRESTATILL_HD_DISPLAY_TABLE" id="PRESTATILL_HD_DISPLAY_TABLE_off" value="0" {if $display_table == 0} checked="checked"{/if}>
						<label for="PRESTATILL_HD_DISPLAY_TABLE_off" class="radioCheck">
							{l s='No' mod='prestatillhomedelivery'}
						</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
		</div>
	</div>
	<div class="illustration">
        <img src="{$base_dir|escape:'htmlall':'UTF-8'}modules/prestatillhomedelivery/views/img/table-list.jpg" />
    </div>
    <br />
    <hr>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_HD_SEND_EMAIL">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Send an email with all details of delivery slot' mod='prestatillhomedelivery'}">
				{l s='Send a separate email with delivery informations' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-lg-4">
				<span class="switch prestashop-switch fixed-width-lg">
					<input class="slot_enabled" type="radio" name="PRESTATILL_HD_SEND_EMAIL" id="PRESTATILL_HD_SEND_EMAIL_on" value="1" {if $send_email == 1} checked="checked"{/if}>
						<label for="PRESTATILL_HD_SEND_EMAIL_on" class="radioCheck">
							{l s='Enabled' mod='prestatillhomedelivery'}
						</label>
					<input class="slot_enabled" type="radio" name="PRESTATILL_HD_SEND_EMAIL" id="PRESTATILL_HD_SEND_EMAIL_off" value="0" {if $send_email == 0} checked="checked"{/if}>
						<label for="PRESTATILL_HD_SEND_EMAIL_off" class="radioCheck">
							{l s='Disabled' mod='prestatillhomedelivery'}
						</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
		</div>
	</div>
	<div class="clearfix"></div>
	<br />
	<h4>{l s='Delivery parameters' mod='prestatillhomedelivery'}</h4>	
	<div class="form-group">
		
		<label class="control-label col-lg-3" for="PS_API_KEY">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Enter a Google Maps API KEY if you want to use GOOGLE GEOCODING API instead.' mod='prestatillhomedelivery'}">
				{l s='Google Maps API KEY (Geocoding)' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-xs-12">
			<div class="alert alert-info">
				{l s='The module uses the free GEO.API.GOUV.FR for all french addresses geocoding and the OpenStreetMap Geocoding API for other countries. But if you want, you can use GOOGLE GEOCODING API instead. Just enter your API KEY below.' mod='prestatillhomedelivery'}
			</div>
				<div class="input-group col-xs-6">
					<input type="text" class="form-control" name="PS_API_KEY" style="text-align: center" id="PS_API_KEY" {if $gg_api_key}value="{$gg_api_key|escape:'htmlall':'UTF-8'}"{/if}/>
				</div>
			</div>
		</div>
	</div>
	<div class="clearfix"></div>

	{* 3.1.0 - Zones Management API Key *}
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_HD_ZONES_API_KEY">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Google Maps API KEY for Zones Management (with Maps JavaScript API and Drawing enabled)' mod='prestatillhomedelivery'}">
				{l s='Google Maps API KEY (Zones)' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-xs-12">
			<div class="alert alert-info">
				<i class="icon-info-circle"></i>
				{l s='This API key is used specifically for the Delivery Zones Management feature. It requires "Maps JavaScript API" and "Maps JavaScript API - Drawing" to be enabled.' mod='prestatillhomedelivery'}
				<br />
				<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank" class="btn btn-sm btn-primary" style="margin-top: 10px;">
					<i class="icon-external-link"></i> {l s='Get an API Key' mod='prestatillhomedelivery'}
				</a>
			</div>
				<div class="input-group col-xs-6">
					<input type="text" class="form-control" name="PRESTATILL_HD_ZONES_API_KEY" style="text-align: center" id="PRESTATILL_HD_ZONES_API_KEY" {if isset($zones_api_key) && $zones_api_key}value="{$zones_api_key|escape:'htmlall':'UTF-8'}"{/if} placeholder="AIzaSy..." />
				</div>
			</div>
		</div>
	</div>
	<div class="clearfix"></div>

	{* 3.1.1 - Geocoding API Keys *}
	<h4>{l s='Geocoding API Configuration' mod='prestatillhomedelivery'}</h4>
	<div class="alert alert-warning">
		<i class="icon-exclamation-triangle"></i>
		<strong>{l s='Address Geocoding System' mod='prestatillhomedelivery'}</strong><br />
		{l s='The module uses a fallback system with 3 geocoding APIs in order:' mod='prestatillhomedelivery'}
		<ol style="margin-top: 10px; margin-bottom: 10px;">
			<li><strong>OpenStreetMap Nominatim</strong> - {l s='Free (default), max 1 req/sec' mod='prestatillhomedelivery'}</li>
			<li><strong>Geocode.Maps.Co</strong> - {l s='Freemium, 1000 requests/day free' mod='prestatillhomedelivery'}</li>
			<li><strong>Google Maps Geocoding</strong> - {l s='Paid with $200/month free credit' mod='prestatillhomedelivery'}</li>
		</ol>
		{l s='Configure at least one API key below for better reliability.' mod='prestatillhomedelivery'}
	</div>

	<div class="form-group">
		<label class="control-label col-lg-3" for="GEOCODE_MAPS_API_KEY">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Geocode.Maps.Co API Key (Fallback #2)' mod='prestatillhomedelivery'}">
				{l s='Geocode.Maps.Co API Key' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-xs-12">
				<div class="alert alert-info">
					<i class="icon-info-circle"></i>
					{l s='This is the recommended fallback API. Free tier includes 1,000 requests per day.' mod='prestatillhomedelivery'}
					<br />
					<strong>{l s='How to get an API key:' mod='prestatillhomedelivery'}</strong>
					<ol style="margin-top: 10px; margin-bottom: 10px;">
						<li>{l s='Go to' mod='prestatillhomedelivery'} <a href="https://geocode.maps.co/" target="_blank">https://geocode.maps.co/</a></li>
						<li>{l s='Sign up for a free account' mod='prestatillhomedelivery'}</li>
						<li>{l s='Get your API key from the dashboard' mod='prestatillhomedelivery'}</li>
						<li>{l s='Paste it below' mod='prestatillhomedelivery'}</li>
					</ol>
					<a href="https://geocode.maps.co/" target="_blank" class="btn btn-sm btn-primary">
						<i class="icon-external-link"></i> {l s='Get a Free API Key' mod='prestatillhomedelivery'}
					</a>
				</div>
				<div class="input-group col-xs-6">
					<span class="input-group-addon"><i class="icon-key"></i></span>
					<input type="text" class="form-control" name="GEOCODE_MAPS_API_KEY" style="font-family: monospace;" id="GEOCODE_MAPS_API_KEY" {if isset($geocode_maps_api_key) && $geocode_maps_api_key}value="{$geocode_maps_api_key|escape:'htmlall':'UTF-8'}"{/if} placeholder="65abc..." />
				</div>
			</div>
		</div>
	</div>
	<div class="clearfix"></div>

	<div class="form-group">
		<label class="control-label col-lg-3" for="GOOGLE_MAPS_API_KEY">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Google Maps Geocoding API Key (Fallback #3)' mod='prestatillhomedelivery'}">
				{l s='Google Maps Geocoding API Key' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-xs-12">
				<div class="alert alert-info">
					<i class="icon-info-circle"></i>
					{l s='Ultimate fallback with $200/month free credit. Requires Google Cloud account.' mod='prestatillhomedelivery'}
					<br />
					<strong>{l s='How to get an API key:' mod='prestatillhomedelivery'}</strong>
					<ol style="margin-top: 10px; margin-bottom: 10px;">
						<li>{l s='Go to' mod='prestatillhomedelivery'} <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
						<li>{l s='Create a new project or select existing one' mod='prestatillhomedelivery'}</li>
						<li>{l s='Enable "Geocoding API"' mod='prestatillhomedelivery'}</li>
						<li>{l s='Create credentials (API key)' mod='prestatillhomedelivery'}</li>
						<li>{l s='Paste it below' mod='prestatillhomedelivery'}</li>
					</ol>
					<a href="https://console.cloud.google.com/" target="_blank" class="btn btn-sm btn-primary">
						<i class="icon-external-link"></i> {l s='Get Google API Key' mod='prestatillhomedelivery'}
					</a>
				</div>
				<div class="input-group col-xs-6">
					<span class="input-group-addon"><i class="icon-key"></i></span>
					<input type="text" class="form-control" name="GOOGLE_MAPS_API_KEY" style="font-family: monospace;" id="GOOGLE_MAPS_API_KEY" {if isset($google_maps_api_key) && $google_maps_api_key}value="{$google_maps_api_key|escape:'htmlall':'UTF-8'}"{/if} placeholder="AIzaSy..." />
				</div>
			</div>
		</div>
	</div>
	<div class="clearfix"></div>
	<hr>
	<h4>{l s='Slots parameters' mod='prestatillhomedelivery'}</h4>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_HD_CARENCE">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Time between receiving the order and the first available delivery slot' mod='prestatillhomedelivery'}">
				{l s='Waiting time :' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">				
			<div class="col-lg-3">
				<div class="input-group">
					<input type="number" name="PRESTATILL_HD_CARENCE" style="text-align: center" id="PRESTATILL_HD_CARENCE" value="{$carence|escape:'htmlall':'UTF-8'}" />	
					<span class="input-group-addon">min</span>				
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_HD_OPEN">
			<span class="label-tooltip" data-toggle="tooltip" title="Ex: 08:00:00">
				{l s='Begining time of the delivery service' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="alert alert-info">
				{l s='If you are on table mode display, choose your deliveries MIN and MAX hours to delimit the table.' mod='prestatillhomedelivery'}<br />
				<i>{l s='If you want to display all day, enter 00:00:00 to 00:00:00.' mod='prestatillhomedelivery'}</i>
			</div>		
			<div class="col-lg-3">
				<div class="input-group">
					<input type="time" name="PRESTATILL_HD_OPEN" style="text-align: center" id="PRESTATILL_HD_OPEN" {if $openhomedelivery}value="{$openhomedelivery|escape:'htmlall':'UTF-8'}"{/if}/>			
					<span class="input-group-addon">h</span>				
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_HD_CLOSE">
			<span class="label-tooltip" data-toggle="tooltip" title="Ex: 20:00:00">
				{l s='Closing Time of the delivery service :' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">				
			<div class="col-lg-3">
				<div class="input-group">
					<input type="time" name="PRESTATILL_HD_CLOSE" style="text-align: center" id="PRESTATILL_HD_CLOSE" {if $closehomedelivery}value="{$closehomedelivery|escape:'htmlall':'UTF-8'}"{/if}/>			
					<span class="input-group-addon">h</span>				
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_HD_NB_DAY">
			<span class="label-tooltip" data-toggle="tooltip" title="">
				{l s='Number of days to display' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">				
			<div class="col-lg-3">
				<div class="input-group">
					<input type="number" name="PRESTATILL_HD_NB_DAY" style="text-align: center" id="PRESTATILL_HD_NB_DAY" {if $nbdayview}value="{$nbdayview|escape:'htmlall':'UTF-8'}"{/if}/>			
					<span class="input-group-addon">{l s='days' mod='prestatillhomedelivery'}</span>				
				</div>
			</div>
		</div>
	</div>
	<hr>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_HD_DUREE">
			<span class="label-tooltip" data-toggle="tooltip" title="Ex: 60 min">
				{l s='Duration of slot (in min) : ' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="alert alert-info">
				{l s='If you enter 0, the module will automaticly creates slots duration according to the openning and closing time you entered on "Delivery Days & hours tabulation" for each delivery area.' mod='prestatillhomedelivery'}
				<i>{l s='NB : Works only on LIST mode (not on TABLE mode).' mod='prestatillhomedelivery'}</i>
			</div>
			<div class="col-lg-3">
				<div class="input-group">
					<input type="number" name="PRESTATILL_HD_DUREE" style="text-align: center" id="PRESTATILL_HD_DUREE" value="{if $duree}{$duree|escape:'htmlall':'UTF-8'}{else}0{/if}"/>			
					<span class="input-group-addon">min</span>				
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_HD_NB_DISPO">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Define maximum orders for a same delivery slot : ' mod='prestatillhomedelivery'}">
				{l s='Number of orders for the same slot : ' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">				
			<div class="col-lg-3">
				<div class="input-group">
					<input type="number" min="1" name="PRESTATILL_HD_NB_DISPO" style="text-align: center" id="PRESTATILL_HD_NB_DISPO" {if $nb_dispo}value="{$nb_dispo|escape:'htmlall':'UTF-8'}"{/if}/>	
					<span class="input-group-addon">{l s='Orders' mod='prestatillhomedelivery'}</span>	
				</div>
			</div>
		</div>
	</div>
	
	<br />
	<h4>{l s='Carrier & States parameters' mod='prestatillhomedelivery'}</h4>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_HD_STATE_PREPARE">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='All orders in the selected states below will be displayed in the Delivery List' mod='prestatillhomedelivery'}">
					{l s='Choose one or more states for Order to prepare' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">
			{foreach from=$states item=state name=foo}					
				<div class="col-lg-4 homedelivery_state">
					<div class="input-group {foreach from=$arrayState item=idstate}{if $idstate == $state.id_order_state}active{/if}{/foreach}">
						<label for="PRESTATILL_HD_STATE_PREPARE{$state.id_order_state|escape:'htmlall':'UTF-8'}" >				
							<input {foreach from=$arrayState item=idstate}{if $idstate == $state.id_order_state}checked="checked"{/if}{/foreach}type="checkbox" id="PRESTATILL_HD_STATE_PREPARE{$state.id_order_state|escape:'htmlall':'UTF-8'}" name="PRESTATILL_HD_STATE_PREPARE{$state.id_order_state|escape:'htmlall':'UTF-8'}" value="{$state.id_order_state|escape:'htmlall':'UTF-8'}" />
							{$state.name|escape:'htmlall':'UTF-8'}
						</label>
					</div>
				</div>
				{if $smarty.foreach.foo.index % 3 == 2}
					<div class="clearfix"></div>
				{/if}
			{/foreach}
		</div>
	</div>
	<br />
	<h4>{l s='Notifications parameters' mod='prestatillhomedelivery'}</h4>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_HD_SEND_REMINDER">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Send an email with delivery day and hour' mod='prestatillhomedelivery'}">
				{l s='Send an email reminder to the customers with their slots informations' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-lg-4">
				<span class="switch prestashop-switch fixed-width-lg">
					<input class="slot_enabled" type="radio" name="PRESTATILL_HD_SEND_REMINDER" id="PRESTATILL_HD_SEND_REMINDER_on" value="1" {if $send_reminder == 1} checked="checked"{/if}>
						<label for="PRESTATILL_HD_SEND_REMINDER_on" class="radioCheck">
							{l s='Yes' mod='prestatillhomedelivery'}
						</label>
					<input class="slot_enabled" type="radio" name="PRESTATILL_HD_SEND_REMINDER" id="PRESTATILL_HD_SEND_REMINDER_off" value="0" {if $send_reminder == 0} checked="checked"{/if}>
						<label for="PRESTATILL_HD_SEND_REMINDER_off" class="radioCheck">
							{l s='No' mod='prestatillhomedelivery'}
						</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_HD_SEND_REMINDER_TIME">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Define when send the email reminder' mod='prestatillhomedelivery'}">
				{l s='Send reminder email X minutes before time slot' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">				
			<div class="col-lg-3">
				<div class="input-group">
					<input type="number" name="PRESTATILL_HD_SEND_REMINDER_TIME" style="text-align: center" id="PRESTATILL_HD_SEND_REMINDER_TIME" {if $send_reminder_time}value="{$send_reminder_time|escape:'htmlall':'UTF-8'}"{/if} />	
					<span class="input-group-addon">min</span>				
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_HD_SEND_REMINDER">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Define if you want to modify the delivery address on the orders invoice & delivery documents.' mod='prestatillhomedelivery'}">
				{l s='Use this link to create a cron task' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-lg-12">
				<div class="alert alert-info">{l s='You can for example turn on the cron task below every 5 minutes, but not necessary at exact time (example : 11:03, 11:08...)' mod='prestatillhomedelivery'}</div>
				<a href="{$cron_url|escape:'htmlall':'UTF-8'}">
					<i class="icon-external-link-sign"></i>
					<!-- {l s='beetween' mod='prestatillhomedelivery'}{l s='and' mod='prestatillhomedelivery'} -->
					{$cron_url|escape:'htmlall':'UTF-8'}
				</a>
			</div>
		</div>
	</div>
			
			<div class="panel-footer">
				 <div class="btn-group pull-right">
	                <button name="submitParameters" id="submitParameters" type="submit" class="btn btn-default"><i class="process-icon-save"></i> {l s='Save' mod='prestatillhomedelivery'}</button>
	            </div>
	       </div>
</form>
