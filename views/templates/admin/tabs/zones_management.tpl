{*
* Home Delivery Slots - Zones Management
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

<div class="clearfix"></div>
<h3><i class="icon-map-marker"></i> {l s='Delivery Zones Management' mod='prestatillhomedelivery'}</h3>

<div class="alert alert-info">
    <i class="icon-info-circle"></i>
    {l s='This section allows you to define delivery zones directly on a map. Each zone will be assigned to a specific restaurant/store.' mod='prestatillhomedelivery'}
    <br />
    <strong>{l s='Important:' mod='prestatillhomedelivery'}</strong> {l s='Make sure you have configured your Google Maps API Key in the "Parameters" tab.' mod='prestatillhomedelivery'}
</div>

{* Vérifier d'abord la clé Zones, puis la clé générale *}
{if (!isset($zones_api_key) || !$zones_api_key) && !$gg_api_key}
<div class="alert alert-danger">
    <i class="icon-warning"></i>
    <strong>{l s='Google Maps API Key Required!' mod='prestatillhomedelivery'}</strong>
    <br />
    {l s='Please configure your Google Maps API Key for Zones in the "Parameters" tab to use the delivery zones management.' mod='prestatillhomedelivery'}
    <br />
    <strong>{l s='Note:' mod='prestatillhomedelivery'}</strong> {l s='Use the "Google Maps API KEY (Zones)" field, not the Geocoding one.' mod='prestatillhomedelivery'}
    <br />
    <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank" class="btn btn-primary btn-sm">
        <i class="icon-external-link"></i> {l s='Get an API Key' mod='prestatillhomedelivery'}
    </a>
</div>
{else}

<form role="form" class="form-horizontal" action="#" method="POST" id="zones_management_form" name="zones_management_form">
    <input type="hidden" id="PRESTATILL_HD_ZONES_ENABLED" name="PRESTATILL_HD_ZONES_ENABLED" value="{if $zones_enabled}1{else}0{/if}" />

    <h4>{l s='Zones Configuration' mod='prestatillhomedelivery'}</h4>

    <!-- Activation du système de zones -->
    <div class="form-group">
        <label class="control-label col-lg-3" for="PRESTATILL_HD_USE_ZONES">
            <span class="label-tooltip" data-toggle="tooltip" title="{l s='Enable the zone-based delivery system instead of the distance-based system' mod='prestatillhomedelivery'}">
                {l s='Use zone-based delivery system' mod='prestatillhomedelivery'}
            </span>
        </label>
        <div class="col-lg-9">
            <div class="col-lg-4">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input class="slot_enabled" type="radio" name="PRESTATILL_HD_USE_ZONES" id="PRESTATILL_HD_USE_ZONES_on" value="1" {if $zones_enabled == 1} checked="checked"{/if}>
                        <label for="PRESTATILL_HD_USE_ZONES_on" class="radioCheck">
                            {l s='Enabled' mod='prestatillhomedelivery'}
                        </label>
                    <input class="slot_enabled" type="radio" name="PRESTATILL_HD_USE_ZONES" id="PRESTATILL_HD_USE_ZONES_off" value="0" {if $zones_enabled == 0} checked="checked"{/if}>
                        <label for="PRESTATILL_HD_USE_ZONES_off" class="radioCheck">
                            {l s='Disabled' mod='prestatillhomedelivery'}
                        </label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
    </div>

    <hr />

    <!-- Contrôles de la carte -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4><i class="icon-map"></i> {l s='Map Controls' mod='prestatillhomedelivery'}</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="zone_name">{l s='Zone Name' mod='prestatillhomedelivery'} <span class="required">*</span></label>
                                <input type="text" class="form-control" id="zone_name" name="zone_name" placeholder="{l s='e.g., North Zone, Downtown, etc.' mod='prestatillhomedelivery'}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="zone_store">{l s='Assigned Restaurant/Store' mod='prestatillhomedelivery'} <span class="required">*</span></label>
                                <select class="form-control" id="zone_store" name="zone_store">
                                    <option value="">{l s='-- Select a restaurant --' mod='prestatillhomedelivery'}</option>
                                    {if !empty($stores_array)}
                                        {foreach from=$stores_array item=store}
                                            <option value="{$store.id_store|escape:'htmlall':'UTF-8'}">
                                                {$store.name|escape:'htmlall':'UTF-8'} - {$store.city|escape:'htmlall':'UTF-8'}
                                            </option>
                                        {/foreach}
                                    {/if}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="zone_carrier">{l s='Carrier (Optional)' mod='prestatillhomedelivery'}</label>
                                <select class="form-control" id="zone_carrier" name="zone_carrier">
                                    <option value="0">{l s='-- All carriers --' mod='prestatillhomedelivery'}</option>
                                    {if !empty($carriers_array)}
                                        {foreach from=$carriers_array item=carrier}
                                            <option value="{$carrier.id_reference|escape:'htmlall':'UTF-8'}" {if $carrier.id_reference == 6}selected="selected"{/if}>
                                                {$carrier.name|escape:'htmlall':'UTF-8'}
                                            </option>
                                        {/foreach}
                                    {/if}
                                </select>
                                <small class="form-text text-muted">{l s='Default: Livraison à domicile (ID 6)' mod='prestatillhomedelivery'}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="zone_color">{l s='Zone Color' mod='prestatillhomedelivery'}</label>
                                <input type="color" class="form-control" id="zone_color" name="zone_color" value="#FF0000" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{l s='Drawing Tools' mod='prestatillhomedelivery'}</label>
                                <div class="btn-group btn-group-justified" role="group">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-default" id="draw_polygon">
                                            <i class="icon-pencil"></i> {l s='Draw Polygon' mod='prestatillhomedelivery'}
                                        </button>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-default" id="draw_circle">
                                            <i class="icon-circle-o"></i> {l s='Draw Circle' mod='prestatillhomedelivery'}
                                        </button>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-default" id="draw_rectangle">
                                            <i class="icon-square-o"></i> {l s='Draw Rectangle' mod='prestatillhomedelivery'}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{l s='Actions' mod='prestatillhomedelivery'}</label>
                                <div class="btn-group btn-group-justified" role="group">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-success" id="save_zone" disabled>
                                            <i class="icon-save"></i> {l s='Save Zone' mod='prestatillhomedelivery'}
                                        </button>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-warning" id="clear_drawing">
                                            <i class="icon-eraser"></i> {l s='Clear' mod='prestatillhomedelivery'}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="zone_days">{l s='Delivery Days' mod='prestatillhomedelivery'}</label>
                                <div class="checkbox-group">
                                    {foreach from=$formatted_days item=day_name key=day_id}
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="zone_days[]" value="{$day_id|escape:'htmlall':'UTF-8'}" class="zone_day_checkbox" />
                                            {$day_name|escape:'htmlall':'UTF-8'}
                                        </label>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="zone_priority">{l s='Priority (higher = priority)' mod='prestatillhomedelivery'}</label>
                                <input type="number" class="form-control" id="zone_priority" name="zone_priority" value="0" min="0" />
                                <p class="help-block">{l s='If zones overlap, the one with highest priority will be selected' mod='prestatillhomedelivery'}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="zone_active">{l s='Active' mod='prestatillhomedelivery'}</label>
                                <div>
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" name="zone_active" id="zone_active_on" value="1" checked="checked">
                                            <label for="zone_active_on" class="radioCheck">
                                                {l s='Yes' mod='prestatillhomedelivery'}
                                            </label>
                                        <input type="radio" name="zone_active" id="zone_active_off" value="0">
                                            <label for="zone_active_off" class="radioCheck">
                                                {l s='No' mod='prestatillhomedelivery'}
                                            </label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Google Maps -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4><i class="icon-globe"></i> {l s='Delivery Zones Map' mod='prestatillhomedelivery'}</h4>
                </div>
                <div class="panel-body">
                    <div id="zones_map" style="width: 100%; height: 600px; border: 2px solid #ddd; border-radius: 4px;"></div>
                    <p class="help-block">
                        <i class="icon-info-circle"></i>
                        {l s='Click on a drawing tool above, then draw your zone on the map. You can edit existing zones by clicking on them.' mod='prestatillhomedelivery'}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des zones existantes -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4><i class="icon-list"></i> {l s='Existing Zones' mod='prestatillhomedelivery'}</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-striped table-bordered" id="zones_list">
                        <thead>
                            <tr>
                                <th style="width: 5%;">{l s='ID' mod='prestatillhomedelivery'}</th>
                                <th style="width: 20%;">{l s='Name' mod='prestatillhomedelivery'}</th>
                                <th style="width: 20%;">{l s='Restaurant/Store' mod='prestatillhomedelivery'}</th>
                                <th style="width: 10%;">{l s='Type' mod='prestatillhomedelivery'}</th>
                                <th style="width: 15%;">{l s='Carrier' mod='prestatillhomedelivery'}</th>
                                <th style="width: 10%;">{l s='Priority' mod='prestatillhomedelivery'}</th>
                                <th style="width: 10%;">{l s='Active' mod='prestatillhomedelivery'}</th>
                                <th style="width: 10%;">{l s='Actions' mod='prestatillhomedelivery'}</th>
                            </tr>
                        </thead>
                        <tbody id="zones_list_body">
                            <tr>
                                <td colspan="8" class="text-center">
                                    <i class="icon-spinner icon-spin"></i> {l s='Loading zones...' mod='prestatillhomedelivery'}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="panel-footer">
        <div class="btn-group pull-right">
            <button name="submitZonesConfiguration" id="submitZonesConfiguration" type="submit" class="btn btn-default">
                <i class="process-icon-save"></i> {l s='Save Configuration' mod='prestatillhomedelivery'}
            </button>
        </div>
    </div>
</form>

{/if}

<input type="hidden" id="google_maps_api_key" value="{$gg_api_key|escape:'htmlall':'UTF-8'}" />
<input type="hidden" id="module_ajax_url" value="{Context::getContext()->link->getModuleLink('prestatillhomedelivery', 'validateordercarrier')|escape:'htmlall':'UTF-8'}" />
<input type="hidden" id="id_shop" value="{$id_shop|escape:'htmlall':'UTF-8'}" />
<input type="hidden" id="id_lang" value="{$id_lang|escape:'htmlall':'UTF-8'}" />

<script>
    var zonesManagementData = {
        apiKey: '{if isset($zones_api_key) && $zones_api_key}{$zones_api_key|escape:'javascript':'UTF-8'}{else}{$gg_api_key|escape:'javascript':'UTF-8'}{/if}',
        stores: {if !empty($stores_array)}{json_encode($stores_array)}{else}[]{/if},
        carriers: {if !empty($carriers_array)}{json_encode($carriers_array)}{else}[]{/if},
        formattedDays: {json_encode($formatted_days)},
        moduleAjaxUrl: '{Context::getContext()->link->getModuleLink("prestatillhomedelivery", "validateordercarrier")|escape:'javascript':'UTF-8'}',
        idShop: {$id_shop|escape:'javascript':'UTF-8'},
        idLang: {$id_lang|escape:'javascript':'UTF-8'}
    };
</script>

{* Charger l'API Google Maps avec la clé appropriée *}
<script>
    // Fonction callback vide pour Google Maps
    window.initGoogleMapsCallback = function() {
        console.log('Google Maps API loaded successfully');
    };
</script>
{if isset($zones_api_key) && $zones_api_key}
    <script src="https://maps.googleapis.com/maps/api/js?key={$zones_api_key|escape:'javascript':'UTF-8'}&libraries=drawing,geometry&callback=initGoogleMapsCallback" async defer></script>
{elseif $gg_api_key}
    <script src="https://maps.googleapis.com/maps/api/js?key={$gg_api_key|escape:'javascript':'UTF-8'}&libraries=drawing,geometry&callback=initGoogleMapsCallback" async defer></script>
{/if}

{* Charger le script de gestion des zones *}
<script src="{$base_dir|escape:'htmlall':'UTF-8'}modules/prestatillhomedelivery/views/js/zones_admin.js"></script>
