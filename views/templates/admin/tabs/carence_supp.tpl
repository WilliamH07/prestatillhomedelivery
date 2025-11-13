{*
* Home Delivery Slots
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

{if $carence_supp}<!-- <i class="fa fa-arrow-right"></i>-->
	{foreach from=$carence_supp item=supp key=id_reference}
		{foreach from=$supp item=carrence key=id_day}
			<tr class="carence_supp_td">
			{if $id_reference == 0}
				<td style="text-align:center;">{l s='All carriers' mod='prestatillhomedelivery'}</td>
				<td style="text-align:center;">{if $carrence.id_day > 0}{$formatted_days[$carrence.id_day]|escape:'htmlall':'UTF-8'}{else}{l s='All days' mod='prestatillhomedelivery'}{/if}</td>
				<td style="text-align:center;">{$carrence.hour_limit|escape:'htmlall':'UTF-8'}</td>
				<td style="text-align: center;">{if $carrence.id_day_end > 0 && $carrence.id_day_end != $carrence.id_day}{$formatted_days[$carrence.id_day_end]|escape:'htmlall':'UTF-8'}{else}<i class="fa fa-arrow-right"></i>{/if}</td>
				<td style="text-align: center;">{$carrence.hour_limit_end|escape:'htmlall':'UTF-8'}</td>
				{assign var='convert' value=$carrence.waiting_time/60}
				<td style="text-align:center;"><span class="success">+{$convert|escape:'htmlall':'UTF-8'|round:2} {l s='hours' mod='prestatillhomedelivery'}</span></td>
				<td style="text-align:center;">
					<button class="deleteCarenceSupp btn btn-default" data-id_reference="0" data-id_day="{$carrence.id_day|escape:'htmlall':'UTF-8'}" data-url="{Context::getContext()->link->getModuleLink('prestatillhomedelivery', 'validateordercarrier')|escape:'htmlall':'UTF-8'}">{l s='Delete' mod='prestatillhomedelivery'}</button>
				</td>
			{else}
				{foreach from=$carriers item=carrier key=k}
					{if $carrier.id_reference == $id_reference}
						<td style="text-align:center;">{$carrier.name|escape:'htmlall':'UTF-8'}</td>
						<td style="text-align:center;">{if $carrence.id_day > 0}{$formatted_days[$carrence.id_day]|escape:'htmlall':'UTF-8'}{else}{l s='All days' mod='prestatillhomedelivery'}{/if}</td>
						<td style="text-align:center;">{$carrence.hour_limit|escape:'htmlall':'UTF-8'}</td>
						<td style="text-align: center;">{if $carrence.id_day_end > 0 && $carrence.id_day_end != $carrence.id_day}{$formatted_days[$carrence.id_day_end]|escape:'htmlall':'UTF-8'}{else}<i class="fa fa-arrow-right"></i>{/if}</td>
						<td style="text-align: center;">{$carrence.hour_limit_end|escape:'htmlall':'UTF-8'}</td>
						{assign var='convert' value=$carrence.waiting_time/60}
						<td style="text-align:center;"><span class="success">+{$convert|escape:'htmlall':'UTF-8'|round:2} {l s='hours' mod='prestatillhomedelivery'}</span></td>
						<td style="text-align:center;">
							<button class="deleteCarenceSupp btn btn-default" data-id_reference="{$id_reference|escape:'htmlall':'UTF-8'}" data-id_day="{$carrence.id_day|escape:'htmlall':'UTF-8'}" data-url="{Context::getContext()->link->getModuleLink('prestatillhomedelivery', 'validateordercarrier')|escape:'htmlall':'UTF-8'}">{l s='Delete' mod='prestatillhomedelivery'}</button>
						</td>
					{/if}
				{/foreach}
			{/if}
			</tr>
		{/foreach}
	{/foreach}
{/if}