{*
* Home Delivery Slots
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

{if $dbd_supp}<!-- <i class="fa fa-arrow-right"></i>-->
    {foreach from=$dbd_supp item=dbd key=id_reference}
        <tr>
            <td colspan="6" style="background: #F4F4F4;" class="dbd_supp_td">&nbsp;
            </td>
        </tr>
    	{foreach from=$dbd item=supp key=k}
    	   {if $supp.id_reference == $id_reference}
    		<tr class="dbd_supp_td" style="width:20%;">
                <td style="text-align: left;" class="carrier_name"><i class="icon-truck"></i>  <span>{if isset($carriers[$id_reference])}{$carriers[$id_reference].name|escape:'htmlall':'UTF-8'}{else}--{/if}</span></td>
                <td style="text-align: left;" class="zone_name">
                    <i class="icon icon-check-square-o"></i>
                    {if isset($zones[$supp.id_zone])}{$zones[$supp.id_zone].name|escape:'htmlall':'UTF-8'}{else}{l s='Please redefine this zone.' mod='prestatillhomedelivery'}{/if}
                </td>
                <td style="text-align:center;width:20%;">{if isset($stores[$supp.id_store])}{$stores[$supp.id_store].name|escape:'htmlall':'UTF-8'} ({$stores[$supp.id_store].city|escape:'htmlall':'UTF-8'}){else}--{/if}</td>
                <td style="text-align:center;width:10%;">{if $supp.id_day > 0}{$formatted_days[$supp.id_day]|escape:'htmlall':'UTF-8'}{else}{l s='All days' mod='prestatillhomedelivery'}{/if}</td>
    			<td style="text-align:center;width:15%;"><span class="success">{$supp.dbd_supp|escape:'htmlall':'UTF-8'} km</span></td>
    			<td style="text-align:right;width:10%;">
    				<button class="deleteDBDSupp btn btn-default" data-id_zone="{if isset($zones[$supp.id_zone])}{$supp.id_zone|escape:'htmlall':'UTF-8'}{else}0{/if}" data-id_carrier="{$id_reference|escape:'htmlall':'UTF-8'}" data-id_day="{$supp.id_day|escape:'htmlall':'UTF-8'}" data-url="{Context::getContext()->link->getModuleLink('prestatillhomedelivery', 'validateordercarrier')|escape:'htmlall':'UTF-8'}">{l s='Delete' mod='prestatillhomedelivery'}</button>
    			</td>
    		</tr>
    		{/if}
    	{/foreach}
    {/foreach}
{/if}