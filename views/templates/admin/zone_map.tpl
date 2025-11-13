{*
* Home Delivery Slots - Zone Map
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

<div class="zone-map-container">
    <div class="alert alert-info">
        <strong>{l s='Instructions:' mod='prestatillhomedelivery'}</strong>
        <ul>
            <li>{l s='Select a drawing tool from the toolbar on the map' mod='prestatillhomedelivery'}</li>
            <li>{l s='Draw your delivery zone on the map' mod='prestatillhomedelivery'}</li>
            <li>{l s='You can draw polygons, circles, or rectangles' mod='prestatillhomedelivery'}</li>
            <li>{l s='Click on the zone to edit or delete it' mod='prestatillhomedelivery'}</li>
            <li>{l s='Save the form to persist your changes' mod='prestatillhomedelivery'}</li>
        </ul>
    </div>

    <div class="zone-map-tools">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-default" id="draw-polygon-btn" title="{l s='Draw Polygon' mod='prestatillhomedelivery'}">
                <i class="icon-pencil"></i> {l s='Polygon' mod='prestatillhomedelivery'}
            </button>
            <button type="button" class="btn btn-default" id="draw-circle-btn" title="{l s='Draw Circle' mod='prestatillhomedelivery'}">
                <i class="icon-circle-o"></i> {l s='Circle' mod='prestatillhomedelivery'}
            </button>
            <button type="button" class="btn btn-default" id="draw-rectangle-btn" title="{l s='Draw Rectangle' mod='prestatillhomedelivery'}">
                <i class="icon-square-o"></i> {l s='Rectangle' mod='prestatillhomedelivery'}
            </button>
            <button type="button" class="btn btn-danger" id="clear-zone-btn" title="{l s='Clear Zone' mod='prestatillhomedelivery'}">
                <i class="icon-trash"></i> {l s='Clear' mod='prestatillhomedelivery'}
            </button>
        </div>
    </div>

    <div id="zone-map" style="width: 100%; height: 500px; margin-top: 15px; border: 1px solid #ddd;"></div>

    <input type="hidden" id="zone_data_input" name="zone_data" value="{$zone_data|escape:'htmlall':'UTF-8'}" />
    <input type="hidden" id="zone_type_input" name="zone_type" value="{$zone_type|escape:'htmlall':'UTF-8'}" />
</div>

<script type="text/javascript">
    var googleMapsApiKey = '{$google_maps_api_key|escape:'javascript':'UTF-8'}';
    var defaultLat = parseFloat('{$default_lat|escape:'javascript':'UTF-8'}');
    var defaultLng = parseFloat('{$default_lng|escape:'javascript':'UTF-8'}');
    var existingZoneData = '{$zone_data|escape:'javascript':'UTF-8'}';
    var existingZoneType = '{$zone_type|escape:'javascript':'UTF-8'}';
</script>
