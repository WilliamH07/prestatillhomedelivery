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
<h3><i class="icon-truck"></i> {l s='Delivery parameters' mod='prestatillhomedelivery'}</h3>
<form role="form" class="form-horizontal"  action="#" method="POST" id="delivery_by_day_form" name="delivery_by_day_form">
	<input type="hidden" id="PRESTATILL_HD_ID_MAIN_STORES" name="PRESTATILL_HD_ID_MAIN_STORES" value="0" />
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_SEARCH_HD_STORE">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='If you choose NO, the delivery serive will be proposed to all your cusotmers, based on your Prestashop ZONES' mod='prestatillhomedelivery'}">
				{l s='Limit delivery zone to a zone around your store' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-lg-4">
				<span class="switch prestashop-switch fixed-width-lg">
					<input class="slot_enabled" type="radio" name="PRESTATILL_SEARCH_HD_STORE" id="PRESTATILL_SEARCH_HD_STORE_on" value="1" {if $store_search == 1} checked="checked"{/if}>
						<label for="PRESTATILL_SEARCH_HD_STORE_on" class="radioCheck">
							{l s='Enabled' mod='prestatillhomedelivery'}
						</label>
					<input class="slot_enabled" type="radio" name="PRESTATILL_SEARCH_HD_STORE" id="PRESTATILL_SEARCH_HD_STORE_off" value="0" {if $store_search == 0} checked="checked"{/if}>
						<label for="PRESTATILL_SEARCH_HD_STORE_off" class="radioCheck">
							{l s='Disabled' mod='prestatillhomedelivery'}
						</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-lg-3" for="PRESTATILL_SEARCH_HD_ZIP">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='If you choose YES, the module will only allows delivery for customer\'s delivery address which is in the zip codes list below' mod='prestatillhomedelivery'}">
				{l s='Limit delivery zone to zip codes' mod='prestatillhomedelivery'}
			</span>
		</label>
		<div class="col-lg-9">
			<div class="col-lg-4">
				<span class="switch prestashop-switch fixed-width-lg">
					<input class="slot_enabled" type="radio" name="PRESTATILL_SEARCH_HD_ZIP" id="PRESTATILL_SEARCH_HD_ZIP_on" value="1" {if $store_zip == 1} checked="checked"{/if}>
						<label for="PRESTATILL_SEARCH_HD_ZIP_on" class="radioCheck">
							{l s='Enabled' mod='prestatillhomedelivery'}
						</label>
					<input class="slot_enabled" type="radio" name="PRESTATILL_SEARCH_HD_ZIP" id="PRESTATILL_SEARCH_HD_ZIP_off" value="0" {if $store_zip == 0} checked="checked"{/if}>
						<label for="PRESTATILL_SEARCH_HD_ZIP_off" class="radioCheck">
							{l s='Disabled' mod='prestatillhomedelivery'}
						</label>
					<a class="slide-button btn"></a>
				</span>
			</div>
		</div>
	</div>
	<hr />
    <h4>{l s='Carrier zones parameters' mod='prestatillhomedelivery'}</h4>
    <div class="alert alert-warning">
        {l s='If some carriers aren\'t displayed anymore on your website, check if all your carrier zones defined below are checked for each of your active carriers :' mod='prestatillhomedelivery'}
        <br />
        <a href="{$carriers_link|escape:'htmlall':'UTF-8'}" target="_blank" class="hd_bt_drive_link">
            <i class="icon-external-link-sign"></i>
            {l s='Go to carriers page' mod='prestatillhomedelivery'}
        </a>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3" for="PRESTATILL_HD_AVAILABLE_ZONE_ONLY">
            <span class="label-tooltip" data-toggle="tooltip" title="{l s='Do you want that only the carriers zones defined below will be available on checkout for the customer' mod='prestatillhomedelivery'}">
                {l s='Show only available carrier zones defined below on checkout' mod='prestatillhomedelivery'}
            </span>
        </label>
        <div class="col-lg-9">
            <div class="col-lg-4">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input class="slot_enabled" type="radio" name="PRESTATILL_HD_AVAILABLE_ZONE_ONLY" id="PRESTATILL_HD_AVAILABLE_ZONE_ONLY_on" value="1" {if $only_av_zone == 1} checked="checked"{/if}>
                        <label for="PRESTATILL_HD_AVAILABLE_ZONE_ONLY_on" class="radioCheck">
                            {l s='Yes' mod='prestatillhomedelivery'}
                        </label>
                    <input class="slot_enabled" type="radio" name="PRESTATILL_HD_AVAILABLE_ZONE_ONLY" id="PRESTATILL_HD_AVAILABLE_ZONE_ONLY_off" value="0" {if $only_av_zone == 0} checked="checked"{/if}>
                        <label for="PRESTATILL_HD_AVAILABLE_ZONE_ONLY_off" class="radioCheck">
                            {l s='No' mod='prestatillhomedelivery'}
                        </label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
    </div>
	<hr>
	<div id="dbd_supp_box" {if $store_search == 0} style="display:none"{/if}>
		<input type="hidden" id="PRESTATILL_SEARCH_HD_RADIUS" name="PRESTATILL_SEARCH_HD_RADIUS" value="0" />
		<div class="alert alert-info">{l s='You can define below an delivery area according to the delivery day for each carrier. Indicate for each desired day, the additional distance authorized.' mod='prestatillhomedelivery'}</div>
		<div id="dbd_supp" class="dbd_supp">
		<table class="table data-table hour_open">
			<thead>
                <th style="text-align: center;width:20%;">{l s='Carrier' mod='prestatillhomedelivery'}</th>
                <th style="text-align: center;width:20%;" class="zone_name">{l s='Zone' mod='prestatillhomedelivery'}</th>
                <th style="text-align: center;width:10%;">{l s='Departure store' mod='prestatillhomedelivery'}</th>
				<th style="text-align: center;width:15%;">{l s='For delivery on' mod='prestatillhomedelivery'}</th>
				<th style="text-align: center;width:15%;">{l s='Delivery distance' mod='prestatillhomedelivery'}</th>
				<th style="width:10%;"></th>
			</thead>
			<tbody>
				{include file="../tabs/delivery_supp.tpl"}
			</tbody>
			<tfoot>
				<tr>
			        <td style="text-align: left;width:20%;">
                        <select id="PRESTATILL_HD_DBD_SUPP_ID_CARRIER" name="PRESTATILL_HD_DBD_SUPP_ID_CARRIER" class="">
                            {foreach from=$carriers item=carrier}
                                <option value="{$carrier.id_reference|escape:'htmlall':'UTF-8'}">{$carrier.name|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </td>
                    <td style="text-align: left;width:20%;">
                        <select id="PRESTATILL_HD_DBD_SUPP_ID_ZONE" name="PRESTATILL_HD_DBD_SUPP_ID_ZONE" class="">
                            {foreach from=$zones item=zone}
                                <option value="{$zone.id_zone|escape:'htmlall':'UTF-8'}">{$zone.name|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                            <option value="0">+ {l s='New zone' mod='prestatillhomedelivery'}</option>
                        </select>
                        <input type="text" id="new_id_zone_dbd" name="new_id_zone_dbd" placeholder="{l s='Enter zone name' mod='prestatillhomedelivery'}" value="" />
                    </td>
                    <td style="text-align: left;width: 10%;">
                        {if !empty($stores)}
                        <select name="PRESTATILL_HD_DBD_SUPP_ID_STORE" style="text-align: center" id="PRESTATILL_HD_DBD_SUPP_ID_STORE">
                            {foreach from=$stores item=store}
                                <option value="{$store.id_store|escape:'htmlall':'UTF-8'}">{$store.name|escape:'htmlall':'UTF-8'} ({$store.city|escape:'htmlall':'UTF-8'})</option>
                            {/foreach}
                         </select>    
                        {else}
                            <div class="alert alert-warning">{l s='To work, you just create at least one store with correct lat / long' mod='prestatillhomedelivery'}</div>
                        {/if}
                       
                    </td>
					<td style="text-align: left;width:10%;">
						<select id="PRESTATILL_HD_DBD_SUPP_DAY" name="PRESTATILL_HD_DBD_SUPP_DAY" class="select_date_1">
							<option value="0">{l s='All days' mod='prestatillhomedelivery'}</option>
							{foreach from=$days item=d }
                                {if $d.openning == 1}
                                    <option value="{$d.id_day|escape:'htmlall':'UTF-8'}">{$formatted_days[$d.id_day]|escape:'htmlall':'UTF-8'}</option>
                                {/if}
                            {/foreach}
						</select>
					</td>
					<td style="text-align: left;width:20%;">
						<div class="input-group">
							<input type="number" name="PRESTATILL_HD_DBD_SUPP" style="text-align: center" id="PRESTATILL_HD_DBD_SUPP" value="10">	
							<span class="input-group-addon">km</span>				
						</div>
					</td>
					<td style="text-align: right;width:10%;">
						<button name="delivery_supp" id="delivery_supp" type="submit" class="btn btn-default" data-id_lang={$id_lang|escape:'htmlall':'UTF-8'} data-url="{Context::getContext()->link->getModuleLink('prestatillhomedelivery', 'validateordercarrier')|escape:'htmlall':'UTF-8'}">{l s='Add' mod='prestatillhomedelivery'}</button>
					</td>
				</tr>
			</tfoot>
		</table>
		</div>
		<div class="clearfix"></div>
	</div>
	<hr />
	<div id="zip_supp_box" {if $store_zip == 0} style="display:none"{/if}>
		<input type="hidden" id="PRESTATILL_SEARCH_HD_POSTCODES" name="PRESTATILL_SEARCH_HD_POSTCODES" value="null" />
        <div class="alert alert-info">{l s='You can define below a list of postal codes according to the delivery day, for each carrier or for several zones of for a same carrier. Separate each postal code with a comma (ex: 00001.00002 etc ...)' mod='prestatillhomedelivery'}</div>
        <div class="new"><span>{l s='NEW' mod='prestatillhomedelivery'}</span></div>
        <div class="alert alert-info">{l s='NEW : You can now define a range of postal codes directly with * (example : 75* for all post codes from 75000 to 75999)' mod='prestatillhomedelivery'}</div>
		<div id="zip_supp" class="zip_supp">
			<table class="table data-table hour_open">
				<thead>
                    <th style="text-align: center;width:20%;">{l s='Carrier' mod='prestatillhomedelivery'}</th>
                    <th style="text-align: center;width:15%;" class="zone_name">{l s='Zone' mod='prestatillhomedelivery'}</th>
                    <th style="text-align: center;width:10%;">{l s='For a delivery on' mod='prestatillhomedelivery'}</th>
					<th style="text-align: center;width:10%;">{l s='between' mod='prestatillhomedelivery'}</th>
					<th style="text-align: center;width:10%;">{l s='and' mod='prestatillhomedelivery'}</th>
					<th style="text-align: center;width:25%;">{l s='Zip codes' mod='prestatillhomedelivery'}</th>
					<th style="width:10%;"></th>
				</thead>
				<tbody>
					{include file="../tabs/zip_supp.tpl"}
				</tbody>
				<tfoot>
					<tr>
					    <td style="text-align: left;width:20%;">
                            <select id="PRESTATILL_HD_ZIP_SUPP_ID_CARRIER" name="PRESTATILL_HD_ZIP_SUPP_ID_CARRIER" class="">
                                {foreach from=$carriers item=carrier}
                                    <option value="{$carrier.id_reference|escape:'htmlall':'UTF-8'}">{$carrier.name|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                            </select>
                        </td>
                        <td style="text-align: left;width:15%;">
                            <select id="PRESTATILL_HD_ZIP_SUPP_ID_ZONE" name="PRESTATILL_HD_ZIP_SUPP_ID_ZONE" class="">
                                
                                {foreach from=$zones item=zone}
                                    <option value="{$zone.id_zone|escape:'htmlall':'UTF-8'}">{$zone.name|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                                <option value="0">+ {l s='New zone' mod='prestatillhomedelivery'}</option>
                            </select>
                            <input type="text" id="new_id_zone" name="new_id_zone" placeholder="{l s='Enter zone name' mod='prestatillhomedelivery'}" value="" />
                        </td>
						<td style="text-align: left;width:10%;">
							<select id="PRESTATILL_HD_ZIP_SUPP_DAY" name="PRESTATILL_HD_ZIP_SUPP_DAY" class="select_date_1">
								<option value="0">{l s='All days' mod='prestatillhomedelivery'}</option>
								{foreach from=$days item=d }
									{if $d.openning == 1}
										<option value="{$d.id_day|escape:'htmlall':'UTF-8'}">{$formatted_days[$d.id_day]|escape:'htmlall':'UTF-8'}</option>
									{/if}
								{/foreach}
							</select>
						</td>
						<td style="text-align: left;width:10%;">
							<input type="time" id="PRESTATILL_HD_ZIP_OD" name="PRESTATILL_HD_ZIP_OD" value="08:30">
						</td>
						<td style="text-align: left;width:10%;">
							<input type="time" id="PRESTATILL_HD_ZIP_CL" name="PRESTATILL_HD_ZIP_CL" value="18:30">
						</td>
						<td style="text-align: left;width:25%;">
							<div>
								<textarea name="PRESTATILL_HD_ZIP_SUPP" id="PRESTATILL_HD_ZIP_SUPP" value="" placeholder="75001,75002 OR 75* OR 751*,752* ..." /></textarea>
							</div>
						</td>
						<td style="text-align: right;width:10%;">
							<button name="zip_supp" id="zip_supp" type="submit" class="btn btn-default" data-id_lang={$id_lang|escape:'htmlall':'UTF-8'} data-url="{Context::getContext()->link->getModuleLink('prestatillhomedelivery', 'validateordercarrier')|escape:'htmlall':'UTF-8'}">{l s='Add' mod='prestatillhomedelivery'}</button>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
		<hr />
		</div>
		<div class="alert alert-info">{l s='For information, here is a reminder of your daily delivery schedules. Be careful to respect opening and closing hours when adding your postal codes' mod='prestatillhomedelivery'}</div>
		<table id="hour_open_info" class="table data-table">
			<thead>
				<th class="text-center" style="width:20%;">{l s='Days' mod='prestatillhomedelivery'}</th>
				<th class="text-center" style="width:20%;">{l s='Morning openning time' mod='prestatillhomedelivery'}</th>
				<th class="text-center" style="width:20%;">{l s='Morning closing time' mod='prestatillhomedelivery'}</th>
				<th class="text-center" style="width:20%;">{l s='Afternoon openning time' mod='prestatillhomedelivery'}</th>
				<th class="text-center" style="width:20%;">{l s='Afternoon closing time' mod='prestatillhomedelivery'}</th>
			</thead>
			<tbody>
				{foreach from=$days item=d }
				<tr data-id_day="{$d.id_day|escape:'htmlall':'UTF-8'}">
					<td class="text-center id_day">{l s=$d.day mod='prestatillhomedelivery'}</td>
					<td class="text-center op_am {if $d.hour_open_am != '00:00:00'}open{else}closed{/if}">{if $d.hour_open_am != '00:00:00'}{$d.hour_open_am|escape:'htmlall':'UTF-8'}{/if}</td>
					<td class="text-center cl_am {if $d.hour_close_am != '00:00:00' || ($d.hour_open_pm == '00:00:00' && $d.nonstop != 0)}open{else}closed{/if}">{if $d.hour_close_am != '00:00:00'}{$d.hour_close_am|escape:'htmlall':'UTF-8'}{/if}</td>
					<td class="text-center op_pm {if $d.hour_open_pm != '00:00:00' || ($d.hour_open_pm == '00:00:00' && $d.nonstop != 0)}open{else}closed{/if}">{if $d.hour_open_pm != '00:00:00'}{$d.hour_open_pm|escape:'htmlall':'UTF-8'}{/if}</td>
					<td class="text-center cl_pm {if $d.hour_close_pm != '00:00:00'}open{else}closed{/if}">{if $d.hour_close_pm != '00:00:00'}{$d.hour_close_pm|escape:'htmlall':'UTF-8'}{/if}</td>
				</tr>
				{/foreach}				
			</tbody>
		</table>
	<div class="clearfix"></div>
	<div class="panel-footer">
		 <div class="btn-group pull-right">
	        <button name="submitDeliveryParameters" id="submitDeliveryParameters" type="submit" class="btn btn-default"><i class="process-icon-save"></i> {l s='Save' mod='prestatillhomedelivery'}</button>
	    </div>
	</div>
</form>