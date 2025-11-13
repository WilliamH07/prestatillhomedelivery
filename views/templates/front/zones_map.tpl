{*
* Home Delivery Slots - Zones Map Frontend
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

{if $use_zones && !empty($delivery_zones)}
<div class="delivery-zones-container">
    <h3 class="delivery-zones-title">{l s='Delivery Zones' mod='prestatillhomedelivery'}</h3>

    <div class="alert alert-info delivery-zones-info">
        <i class="icon-info-circle"></i>
        {l s='Check if your address is in one of our delivery zones below:' mod='prestatillhomedelivery'}
    </div>

    <div id="delivery-zones-map" style="width: 100%; height: 400px; border: 1px solid #ddd; border-radius: 4px; margin: 15px 0;"></div>

    <div class="delivery-zones-legend">
        <h4>{l s='Available zones:' mod='prestatillhomedelivery'}</h4>
        <ul class="zones-list">
            {foreach from=$delivery_zones item=zone}
                <li class="zone-item">
                    <span class="zone-color-badge" style="background-color: {$zone.zone_color|escape:'htmlall':'UTF-8'};"></span>
                    <strong>{$zone.zone_name|escape:'htmlall':'UTF-8'}</strong>
                    {if isset($stores[$zone.id_store])}
                        - {l s='Restaurant:' mod='prestatillhomedelivery'} {$stores[$zone.id_store].name|escape:'htmlall':'UTF-8'} ({$stores[$zone.id_store].city|escape:'htmlall':'UTF-8'})
                    {/if}
                </li>
            {/foreach}
        </ul>
    </div>

    {if isset($customer_in_zone) && $customer_in_zone}
        <div class="alert alert-success">
            <i class="icon-check-circle"></i>
            {l s='Good news! Your delivery address is in our delivery zone.' mod='prestatillhomedelivery'}
            {if isset($customer_store)}
                {l s='Your order will be prepared by:' mod='prestatillhomedelivery'} <strong>{$customer_store.name|escape:'htmlall':'UTF-8'}</strong>
            {/if}
        </div>
    {else if isset($customer_in_zone)}
        <div class="alert alert-warning">
            <i class="icon-exclamation-triangle"></i>
            {l s='Unfortunately, your delivery address is not in our delivery zones.' mod='prestatillhomedelivery'}
        </div>
    {/if}
</div>

<script type="text/javascript">
    var deliveryZonesData = {$delivery_zones_json|escape:'none'};
    var googleMapsApiKey = '{$google_maps_api_key|escape:'javascript':'UTF-8'}';
    var customerAddress = {if isset($customer_coordinates)}{$customer_coordinates|escape:'none'}{else}null{/if};
    var defaultMapCenter = {
        lat: {if isset($default_lat)}{$default_lat|escape:'javascript':'UTF-8'}{else}48.8566{/if},
        lng: {if isset($default_lng)}{$default_lng|escape:'javascript':'UTF-8'}{else}2.3522{/if}
    };
</script>
{/if}
