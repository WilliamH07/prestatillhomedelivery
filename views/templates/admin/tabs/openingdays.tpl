{*
* Home Delivery Slots
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

{if !empty($days)}
<form role="form" class="form-horizontal"  action="#" method="POST" id="config_form" name="config_form">
	<section id="config_form_1">
		<h3><i class="icon-calendar"></i> {l s='Delivery Days and hours' mod='prestatillhomedelivery'}</h3>	
		<div class="table-responsive {if $homedelivery_enabled == 0}homedelivery_disabled{/if}">	
			<table class="table data-table hour_open">
				<thead>
					<th></th>
					<th class="text-left">{l s='Days' mod='prestatillhomedelivery'}</th>
					<th class="text-left">{l s='Morning openning time' mod='prestatillhomedelivery'}</th>
					<th class="text-left">{l s='Morning closing time' mod='prestatillhomedelivery'}</th>
					<th class="text-left">{l s='Afternoon openning time' mod='prestatillhomedelivery'}</th>
					<th class="text-left">{l s='Afternoon closing time' mod='prestatillhomedelivery'}</th>
					<th class="text-left"v>{l s='Open non-stop' mod='prestatillhomedelivery'}</th>					
				</thead>
				<tbody>
					{foreach from=$days item=d }
					<tr>
						<td class="text-center">
							<input type="checkbox" data-id="{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}" id="PRESTATILL_HD_{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}" name="PRESTATILL_HD_{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}" value="{$d.openning|escape:'htmlall':'UTF-8'}" {if $d.openning|escape:'htmlall':'UTF-8' == 1}checked{/if}/>
						</td>
						<td class="text-left">
							{l s=$d.day mod='prestatillhomedelivery'}
						</td>
						<td class="text-left no">
							<input type="time" data-id="{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}" id="{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}_opening_am" name="PRESTATILL_HD_OPENING_AM_{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}" value="{$d.hour_open_am|escape:'htmlall':'UTF-8'}"/>
						</td>
						<td class="text-left no">
							<input type="time" data-id="{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}" id="{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}_closing_am" name="PRESTATILL_HD_CLOSING_AM_{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}" value="{$d.hour_close_am|escape:'htmlall':'UTF-8'}"/>
						</td>
						<td class="text-left no">
							<input type="time" data-id="{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}" id="{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}_opening_pm" name="PRESTATILL_HD_OPENING_PM_{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}" value="{$d.hour_open_pm|escape:'htmlall':'UTF-8'}"/>
						</td>
						<td class="text-left no">
							<input type="time" data-id="{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}" id="{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}_closing_pm" name="PRESTATILL_HD_CLOSING_PM_{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}" value="{$d.hour_close_pm|escape:'htmlall':'UTF-8'}"/>
						</td>
						<td class="text-center no">
							<input type="checkbox" data-id="{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}" id="{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}_nonstop" {if value==1} checked{/if} name="PRESTATILL_HD_OPENING_NONSTOP_{$d.id_prestatill_homedelivery|escape:'htmlall':'UTF-8'}" value="{$d.nonstop|escape:'htmlall':'UTF-8'}" {if $d.nonstop == 1}checked{/if}/>
						</td>
					</tr>
					{/foreach}				
				</tbody>
			</table>
			<br />
			<br />
			<h3><i class="fa fa-clock"></i> {l s='Additional waiting time depending on the current day and time' mod='prestatillhomedelivery'} <span class="store_name"></span></h3>	
			<div id="hd_info_locales" class="col-xs-12 alert alert-info">{l s='You can here define an additional waiting time depending on the current day and time your customers are placing an order.' mod='prestatillhomedelivery'}<br />
				{l s='For example: were are now ' mod='prestatillhomedelivery'} {l s='and your waiting time is currently about ' mod='prestatillhomedelivery'} <b>{$carence|escape:'htmlall':'UTF-8'} {l s='minutes' mod='prestatillhomedelivery'} ({$carence/60|escape:'htmlall':'UTF-8'} {if $carence/60 == 1}}{l s='hour' mod='prestatillhomedelivery'}{else}{l s='hours' mod='prestatillhomedelivery'}{/if})</b>. {l s='If you add an additional 24-hour (1440 minutes) waiting time for orders placed today after 20:00, your waiting time will be:' mod='prestatillhomedelivery'} <b>{$carence + 1440|escape:'htmlall':'UTF-8'} {l s='minutes' mod='prestatillhomedelivery'} ({($carence + 1440)/60|escape:'htmlall':'UTF-8'} {l s='hours' mod='prestatillhomedelivery'}).</b>
			</div>
			<div class="clearfix"></div>	
			<div id="carence_supp" class="carence_supp">
				<input type="hidden" id="id_shop_group" name="id_shop_group" value="{$id_shop_group|escape:'htmlall':'UTF-8'}" />
				<input type="hidden" id="id_shop" name="id_shop" value="{$id_shop|escape:'htmlall':'UTF-8'}" />
				<table class="table data-table hour_open">
					<thead>
						<th></th>
						<th style="text-align: center;">{l s='For orders placed beetween' mod='prestatillhomedelivery'}</th>
						<th style="text-align: center;" colspan="2">{l s='and' mod='prestatillhomedelivery'}</th>
						<th style="text-align: center;">{l s='Additional waiting time:' mod='prestatillhomedelivery'}</th>
						<th></th>
					</thead>
					<tbody>
						{include file="../tabs/carence_supp.tpl"}
					</tbody>
					<tfoot>
						<tr>
							<td style="text-align: center;width: 20%;">
								<select id="PRESTATILL_HD_CARENCE_SUPP_ID_REFERENCE" name="PRESTATILL_HD_CARENCE_SUPP_ID_REFERENCE" class="">
									<option value="0">{l s='All carriers' mod='prestatillhomedelivery'}</option>
									{foreach from=$carriers item=carrier}
										<option value="{$carrier.id_reference|escape:'htmlall':'UTF-8'}">{$carrier.name|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							</td>
							<td style="text-align: center;width: 20%;">
								<select id="PRESTATILL_HD_CARENCE_SUPP_DAY" name="PRESTATILL_HD_CARENCE_SUPP_DAY" class="select_date_1">
									<option value="0">{l s='All days' mod='prestatillhomedelivery'}</option>
									{foreach from=$days item=d }
										<option value="{$d.id_day|escape:'htmlall':'UTF-8'}">{$formatted_days[$d.id_day]|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							</td>
							<td style="text-align: center;width: 15%;">
								<input type="time" step="1" id="PRESTATILL_HD_CARENCE_SUPP_HOUR_LIMIT" name="PRESTATILL_HD_CARENCE_SUPP_HOUR_LIMIT" value="23:00" />
							</td>
							<td style="text-align: center;width: 20%;">
								<select id="PRESTATILL_HD_CARENCE_SUPP_DAY_END" name="PRESTATILL_HD_CARENCE_SUPP_DAY_END" class="select_date_2">
									<option value="0">{l s='All days' mod='prestatillhomedelivery'}</option>
									{foreach from=$days item=d }
										<option value="{$d.id_day|escape:'htmlall':'UTF-8'}">{$formatted_days[$d.id_day]|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							</td>
							<td style="text-align: center;width: 15%;">
								<input type="time" step="1" id="PRESTATILL_HD_CARENCE_SUPP_HOUR_LIMIT_END" name="PRESTATILL_HD_CARENCE_SUPP_HOUR_LIMIT_END" value="06:00" />
							</td>
							<td style="text-align: center;width: 10%;">
								<div class="input-group">
									<input type="number" name="PRESTATILL_HD_CARENCE_SUPP" style="text-align: center" id="PRESTATILL_HD_CARENCE_SUPP" value="1440">	
									<span class="input-group-addon">min</span>				
								</div>
							</td>
							<td style="text-align: center;width: 10%;">
								<button name="carrence_supp_validate" id="carrence_supp_validate" type="submit" class="btn btn-default" data-id_lang={$id_lang|escape:'htmlall':'UTF-8'} data-url="{Context::getContext()->link->getModuleLink('prestatillhomedelivery', 'validateordercarrier')|escape:'htmlall':'UTF-8'}">{l s='Add' mod='prestatillhomedelivery'}</button>
							</td>
						</tr>
					</tfoot>
				</table>
				<div class="clearfix"></div>
			</div>	
			<div id="hd_info_locales" class="col-xs-12 alert alert-info">{l s='If you want to display the dates in a specific languages in front-office, you must install the locales dates packages on your webserver.' mod='prestatillhomedelivery'}<br />
				{l s='For example on Ubuntu server : ' mod='prestatillhomedelivery'} <b>apt-get install language-pack-fr</b>. {l s='A server reboot could be necessary.' mod='prestatillhomedelivery'}
			</div>
			<div class="clearfix"></div>			
		</div>
		<div class="panel-footer">
			 <div class="btn-group pull-right">
	            <button name="submitConfigHomeDelivery" id="submitConfigHomeDelivery" type="submit" class="btn btn-default"><i class="process-icon-save"></i> {l s='Save' mod='prestatillhomedelivery'}</button>
	        </div>
	   </div>
	</section>
	</form>
{/if}
