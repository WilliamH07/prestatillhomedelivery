{*
* Home Delivery Slots - Zones Tab
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

<div class="clearfix"></div>
<h3><i class="icon-map-marker"></i> {l s='Delivery Zones Management' mod='prestatillhomedelivery'}</h3>

<!-- Configuration Google Maps API Key -->
<form role="form" class="form-horizontal" action="#" method="POST" id="zones_config_form" name="zones_config_form">
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-cog"></i> {l s='Google Maps Configuration' mod='prestatillhomedelivery'}
		</div>
		<div class="form-group">
			<label class="control-label col-lg-3" for="PRESTATILL_HD_GOOGLE_MAPS_API_KEY">
				<span class="label-tooltip" data-toggle="tooltip" title="{l s='Enter your Google Maps API Key. Get one from Google Cloud Console.' mod='prestatillhomedelivery'}">
					{l s='Google Maps API Key' mod='prestatillhomedelivery'}
				</span>
			</label>
			<div class="col-lg-9">
				<input type="text" name="PRESTATILL_HD_GOOGLE_MAPS_API_KEY" id="PRESTATILL_HD_GOOGLE_MAPS_API_KEY" value="{$google_maps_api_key|escape:'htmlall':'UTF-8'}" class="form-control" />
				<p class="help-block">
					{l s='Required APIs: Maps JavaScript API, Geocoding API, Drawing API' mod='prestatillhomedelivery'}
					<br />
					<a href="https://console.cloud.google.com/google/maps-apis" target="_blank">
						<i class="icon-external-link"></i> {l s='Get your API Key' mod='prestatillhomedelivery'}
					</a>
				</p>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-lg-3" for="PRESTATILL_HD_USE_ZONES">
				<span class="label-tooltip" data-toggle="tooltip" title="{l s='Enable the zone-based delivery system instead of proximity-based' mod='prestatillhomedelivery'}">
					{l s='Use Zone-Based Delivery' mod='prestatillhomedelivery'}
				</span>
			</label>
			<div class="col-lg-9">
				<div class="col-lg-4">
					<span class="switch prestashop-switch fixed-width-lg">
						<input class="slot_enabled" type="radio" name="PRESTATILL_HD_USE_ZONES" id="PRESTATILL_HD_USE_ZONES_on" value="1" {if $use_zones == 1} checked="checked"{/if}>
							<label for="PRESTATILL_HD_USE_ZONES_on" class="radioCheck">
								{l s='Enabled' mod='prestatillhomedelivery'}
							</label>
						<input class="slot_enabled" type="radio" name="PRESTATILL_HD_USE_ZONES" id="PRESTATILL_HD_USE_ZONES_off" value="0" {if $use_zones == 0} checked="checked"{/if}>
							<label for="PRESTATILL_HD_USE_ZONES_off" class="radioCheck">
								{l s='Disabled' mod='prestatillhomedelivery'}
							</label>
						<a class="slide-button btn"></a>
					</span>
				</div>
			</div>
		</div>

		<div class="panel-footer">
			<button name="submitZonesConfig" id="submitZonesConfig" type="submit" class="btn btn-default">
				<i class="process-icon-save"></i> {l s='Save Configuration' mod='prestatillhomedelivery'}
			</button>
		</div>
	</div>
</form>

<!-- Zones Management -->
<div class="panel" id="zones_management">
	<div class="panel-heading">
		<i class="icon-map-marker"></i> {l s='Manage Delivery Zones' mod='prestatillhomedelivery'}
	</div>

	<div class="alert alert-info">
		<i class="icon-info-circle"></i>
		<strong>{l s='How to use:' mod='prestatillhomedelivery'}</strong>
		<ul>
			<li>{l s='Click "Add New Zone" to create a delivery zone' mod='prestatillhomedelivery'}</li>
			<li>{l s='Draw the zone on the map using polygon, circle or rectangle tools' mod='prestatillhomedelivery'}</li>
			<li>{l s='Assign a restaurant to each zone' mod='prestatillhomedelivery'}</li>
			<li>{l s='Higher priority zones are checked first when matching customer addresses' mod='prestatillhomedelivery'}</li>
		</ul>
	</div>

	{if !$google_maps_api_key || $google_maps_api_key == ''}
		<div class="alert alert-warning">
			<i class="icon-exclamation-triangle"></i>
			{l s='Please configure your Google Maps API Key above to use the delivery zones feature.' mod='prestatillhomedelivery'}
		</div>
	{else}
		<!-- Zones List -->
		<div class="row" style="margin-bottom: 20px;">
			<div class="col-md-12">
				<button type="button" class="btn btn-primary" id="add_new_zone_btn">
					<i class="icon-plus"></i> {l s='Add New Zone' mod='prestatillhomedelivery'}
				</button>
			</div>
		</div>

		<!-- Zones Table -->
		<table class="table table-striped" id="zones_list_table">
			<thead>
				<tr>
					<th style="width:5%;">{l s='ID' mod='prestatillhomedelivery'}</th>
					<th style="width:20%;">{l s='Zone Name' mod='prestatillhomedelivery'}</th>
					<th style="width:15%;">{l s='Restaurant' mod='prestatillhomedelivery'}</th>
					<th style="width:10%;">{l s='Carrier' mod='prestatillhomedelivery'}</th>
					<th style="width:10%;">{l s='Type' mod='prestatillhomedelivery'}</th>
					<th style="width:8%;" class="text-center">{l s='Color' mod='prestatillhomedelivery'}</th>
					<th style="width:8%;" class="text-center">{l s='Priority' mod='prestatillhomedelivery'}</th>
					<th style="width:8%;" class="text-center">{l s='Active' mod='prestatillhomedelivery'}</th>
					<th style="width:16%;" class="text-center">{l s='Actions' mod='prestatillhomedelivery'}</th>
				</tr>
			</thead>
			<tbody id="zones_list_body">
				{if !empty($delivery_zones)}
					{foreach from=$delivery_zones item=zone}
						<tr data-zone-id="{$zone.id_delivery_zone|escape:'htmlall':'UTF-8'}">
							<td>{$zone.id_delivery_zone|escape:'htmlall':'UTF-8'}</td>
							<td><strong>{$zone.zone_name|escape:'htmlall':'UTF-8'}</strong></td>
							<td>
								{if isset($stores[$zone.id_store])}
									{$stores[$zone.id_store].name|escape:'htmlall':'UTF-8'} ({$stores[$zone.id_store].city|escape:'htmlall':'UTF-8'})
								{else}
									--
								{/if}
							</td>
							<td>
								{if $zone.id_carrier == 0}
									{l s='All carriers' mod='prestatillhomedelivery'}
								{else if isset($carriers[$zone.id_carrier])}
									{$carriers[$zone.id_carrier].name|escape:'htmlall':'UTF-8'}
								{else}
									--
								{/if}
							</td>
							<td>{$zone.zone_type|escape:'htmlall':'UTF-8'}</td>
							<td class="text-center">
								<span style="display:inline-block;width:30px;height:20px;background-color:{$zone.zone_color|escape:'htmlall':'UTF-8'};border:1px solid #ccc;border-radius:3px;"></span>
							</td>
							<td class="text-center">{$zone.priority|escape:'htmlall':'UTF-8'}</td>
							<td class="text-center">
								{if $zone.active == 1}
									<span class="label label-success"><i class="icon-check"></i> {l s='Yes' mod='prestatillhomedelivery'}</span>
								{else}
									<span class="label label-danger"><i class="icon-times"></i> {l s='No' mod='prestatillhomedelivery'}</span>
								{/if}
							</td>
							<td class="text-center">
								<button class="btn btn-default btn-sm edit_zone_btn" data-zone-id="{$zone.id_delivery_zone|escape:'htmlall':'UTF-8'}" title="{l s='Edit' mod='prestatillhomedelivery'}">
									<i class="icon-edit"></i>
								</button>
								<button class="btn btn-danger btn-sm delete_zone_btn" data-zone-id="{$zone.id_delivery_zone|escape:'htmlall':'UTF-8'}" title="{l s='Delete' mod='prestatillhomedelivery'}">
									<i class="icon-trash"></i>
								</button>
							</td>
						</tr>
					{/foreach}
				{else}
					<tr id="no_zones_row">
						<td colspan="9" class="text-center">{l s='No zones configured yet. Click "Add New Zone" to create one.' mod='prestatillhomedelivery'}</td>
					</tr>
				{/if}
			</tbody>
		</table>
	{/if}
</div>

<!-- Zone Editor Modal -->
<div class="modal fade" id="zone_editor_modal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document" style="width: 90%; max-width: 1200px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' mod='prestatillhomedelivery'}">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="zone_editor_modal_title">{l s='Add Delivery Zone' mod='prestatillhomedelivery'}</h4>
			</div>
			<div class="modal-body">
				<form id="zone_editor_form">
					<input type="hidden" id="edit_zone_id" name="id_delivery_zone" value="0" />
					<input type="hidden" id="edit_zone_type" name="zone_type" value="polygon" />
					<input type="hidden" id="edit_zone_data" name="zone_data" value="" />

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="edit_zone_name">{l s='Zone Name' mod='prestatillhomedelivery'} <span class="required">*</span></label>
								<input type="text" class="form-control" id="edit_zone_name" name="zone_name" required />
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="edit_id_store">{l s='Restaurant' mod='prestatillhomedelivery'} <span class="required">*</span></label>
								<select class="form-control" id="edit_id_store" name="id_store" required>
									<option value="">{l s='-- Select Restaurant --' mod='prestatillhomedelivery'}</option>
									{foreach from=$stores item=store}
										<option value="{$store.id_store|escape:'htmlall':'UTF-8'}">{$store.name|escape:'htmlall':'UTF-8'} ({$store.city|escape:'htmlall':'UTF-8'})</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="edit_id_carrier">{l s='Carrier' mod='prestatillhomedelivery'}</label>
								<select class="form-control" id="edit_id_carrier" name="id_carrier">
									<option value="0">{l s='All carriers' mod='prestatillhomedelivery'}</option>
									{foreach from=$carriers item=carrier}
										<option value="{$carrier.id_reference|escape:'htmlall':'UTF-8'}">{$carrier.name|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="edit_zone_color">{l s='Color' mod='prestatillhomedelivery'}</label>
								<input type="color" class="form-control" id="edit_zone_color" name="zone_color" value="#FF0000" />
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="edit_priority">{l s='Priority' mod='prestatillhomedelivery'}</label>
								<input type="number" class="form-control" id="edit_priority" name="priority" value="1" min="0" />
								<p class="help-block">{l s='Higher = checked first' mod='prestatillhomedelivery'}</p>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label>
									<input type="checkbox" id="edit_active" name="active" value="1" checked /> {l s='Active' mod='prestatillhomedelivery'}
								</label>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<label>{l s='Draw Zone on Map' mod='prestatillhomedelivery'}</label>
							<div class="btn-group" role="group" style="margin-bottom: 10px;">
								<button type="button" class="btn btn-default" id="modal_draw_polygon_btn">
									<i class="icon-pencil"></i> {l s='Polygon' mod='prestatillhomedelivery'}
								</button>
								<button type="button" class="btn btn-default" id="modal_draw_circle_btn">
									<i class="icon-circle-o"></i> {l s='Circle' mod='prestatillhomedelivery'}
								</button>
								<button type="button" class="btn btn-default" id="modal_draw_rectangle_btn">
									<i class="icon-square-o"></i> {l s='Rectangle' mod='prestatillhomedelivery'}
								</button>
								<button type="button" class="btn btn-danger" id="modal_clear_zone_btn">
									<i class="icon-trash"></i> {l s='Clear' mod='prestatillhomedelivery'}
								</button>
							</div>
							<div id="zone_editor_map" style="width: 100%; height: 400px; border: 1px solid #ddd; border-radius: 4px;"></div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">{l s='Cancel' mod='prestatillhomedelivery'}</button>
				<button type="button" class="btn btn-primary" id="save_zone_btn">
					<i class="icon-save"></i> {l s='Save Zone' mod='prestatillhomedelivery'}
				</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	var googleMapsApiKey = '{$google_maps_api_key|escape:'javascript':'UTF-8'}';
	var defaultMapLat = {if isset($default_lat)}{$default_lat|escape:'javascript':'UTF-8'}{else}48.8566{/if};
	var defaultMapLng = {if isset($default_lng)}{$default_lng|escape:'javascript':'UTF-8'}{else}2.3522{/if};
	var moduleBaseUrl = '{Context::getContext()->link->getAdminLink('AdminModules', true)|escape:'javascript':'UTF-8'}&configure=prestatillhomedelivery';
	var ajaxUrl = '{Context::getContext()->link->getModuleLink('prestatillhomedelivery', 'validateordercarrier')|escape:'javascript':'UTF-8'}';
</script>
