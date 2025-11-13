{*
* Home Delivery Slots
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

{if $zip_supp}<!-- <i class="fa fa-arrow-right"></i>-->
    {foreach from=$zip_supp item=id_zone key=id_reference}
        <tr>
            <td colspan="7" style="background: #F4F4F4;" class="zip_supp_td">&nbsp;
            </td>
        </tr>
		{foreach from=$id_zone item=zip}
			{foreach from=$zip item=supp key=k}
			{if $supp.id_reference|cat:'_':$supp.id_zone == $id_reference || $supp.id_reference == $id_reference}
				<tr class="zip_supp_td">
					<td style="text-align: left;" class="carrier_name"><i class="icon-truck"></i>  <span>{if isset($carriers[$supp.id_reference])}{$carriers[$supp.id_reference].name|escape:'htmlall':'UTF-8'}{/if}</span></td>
					<td style="text-align: left;" class="zone_name">
						<i class="icon icon-check-square-o"></i>
						{if isset($zones[$supp.id_zone])}{$zones[$supp.id_zone].name|escape:'htmlall':'UTF-8'}{else}{l s='Disable zone' mod='prestatillhomedelivery'}{/if}
					</td>
					<td style="text-align:center;">
						{if $supp.id_day > 0}
							{$formatted_days[$supp.id_day]|escape:'htmlall':'UTF-8'} 
						{else}
							{l s='All days' mod='prestatillhomedelivery'}
						{/if}  
					</td>
					<td class="text-center">{if isset($supp.op)}{if $supp.op != 0}{$supp.op|escape:'htmlall':'UTF-8'}{/if}{else}{$deliv_open|escape:'htmlall':'UTF-8'}{/if}</td>
					<td class="text-center">{if isset($supp.cl)}{if $supp.cl != 0}{$supp.cl|escape:'htmlall':'UTF-8'}{/if}{else}{$deliv_close|escape:'htmlall':'UTF-8'}{/if}</td>
					<td style="text-align:left;">
						<div class="zip_list_box">
							{assign var='zip_list' value=$supp.zip_supp}
							{assign var="parts" value=","|explode:$zip_list}
							{foreach from=$parts item=part}
								<span class="info">{$part|escape:'htmlall':'UTF-8'}</span>
							{/foreach}
						</div>
					</td>
					<td style="text-align:right;">
						<button class="editZipSupp btn btn-default" data-id_day="{$supp.id_day|escape:'htmlall':'UTF-8'}" data-od="{if isset($supp.op)}{if $supp.op != 0}{$supp.op|escape:'htmlall':'UTF-8'}{/if}{else}{$deliv_open|escape:'htmlall':'UTF-8'}{/if}" data-cl="{if isset($supp.cl)}{if $supp.cl != 0}{$supp.cl|escape:'htmlall':'UTF-8'}{/if}{else}{$deliv_close|escape:'htmlall':'UTF-8'}{/if}" data-id_zone="{$supp.id_zone|escape:'htmlall':'UTF-8'}" data-id_carrier="{$id_reference|escape:'htmlall':'UTF-8'}" data-zip_supp_list="{$supp.zip_supp|escape:'htmlall':'UTF-8'}" data-id_day="{$k|escape:'htmlall':'UTF-8'}" data-url="{Context::getContext()->link->getModuleLink('prestatillhomedelivery', 'validateordercarrier')|escape:'htmlall':'UTF-8'}">{l s='Edit' mod='prestatillhomedelivery'}</button>
						<button class="deleteZipSupp btn btn-danger" data-id_zone="{$supp.id_zone|escape:'htmlall':'UTF-8'}" data-id_carrier="{$id_reference|escape:'htmlall':'UTF-8'}" data-id_day="{$supp['id_day']|escape:'htmlall':'UTF-8'}" data-url="{Context::getContext()->link->getModuleLink('prestatillhomedelivery', 'validateordercarrier')|escape:'htmlall':'UTF-8'}">{l s='Delete' mod='prestatillhomedelivery'}</button>
					</td>
				</tr>
			{/if}
			{/foreach}
		{/foreach}
	{/foreach}
{/if}