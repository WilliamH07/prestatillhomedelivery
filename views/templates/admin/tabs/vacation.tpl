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
	<h3><i class="icon-sun-o"></i> {l s='Vacation' mod='prestatillhomedelivery'}</h3>
<form role="form" class="form-horizontal"  action="#" method="POST" id="vacation_form" name="vacation_form">
	<div class="col-xs-12">
		<button name="add_vacation_button" style="text-align: center;" id="add_vacation_button" class="btn btn-primary">{l s='Add new vacation period' mod='prestatillhomedelivery'}</button>
	</div>
	<div id="add_vacation" class="disabled">
		<div class="form-group">
			<label class="control-label col-lg-3" for="PRESTATILL_HD_VACATION_START">
				<span class="label-tooltip" data-toggle="tooltip" title="{l s='First day of vacation' mod='prestatillhomedelivery'}">
					{l s='Vacation Start Day :' mod='prestatillhomedelivery'}
				</span>
			</label>
			<div class="col-lg-9">				
				<div class="col-lg-3">
					<div class="input-group">
						<input type="date" name="PRESTATILL_HD_VACATION_START" style="text-align: center" id="PRESTATILL_HD_VACATION_START" {*{if $vacation_start}value="{$vacation_start|escape:'htmlall':'UTF-8'}"{/if}*} />						
					</div>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-lg-3" for="PRESTATILL_HD_VACATION_END">
				<span class="label-tooltip" data-toggle="tooltip" title="{l s='Last day of vacation' mod='prestatillhomedelivery'}">
					{l s='Vacation End Day :' mod='prestatillhomedelivery'}
				</span>
			</label>
			<div class="col-lg-9">				
				<div class="col-lg-3">
					<div class="input-group">
						<input type="date" name="PRESTATILL_HD_VACATION_END" style="text-align: center" id="PRESTATILL_HD_VACATION_END" {*{if $vacation_end}value="{$vacation_end|escape:'htmlall':'UTF-8'}"{/if}*} />						
					</div>
				</div>
			</div>
		</div>
		<div class="panel-footer">
			<div class="btn-group pull-right">
			    <button name="submitVacation" id="submitVacation" type="submit" class="btn btn-default"><i class="process-icon-save"></i> {l s='Save' mod='prestatillhomedelivery'}</button>
			</div>
		</div>
	</div>
<div class="table-responsive">	
	<table class="table data-table" id="vacation_list">
		<thead>
			<th class="text-left">{l s='Vacation Start Day' mod='prestatillhomedelivery'}</th>
			<th class="text-left">{l s='Vacation End Day' mod='prestatillhomedelivery'}</th>	
			<th></th>	
		</thead>
		<tbody>	                             						
			{foreach from=$vacations item=vacation}
			<tr>	
				<td class="text-left">
					{$vacation.vacation_start|escape:'htmlall':'UTF-8'|date_format:"%d/%m/%Y"}
				</td>	
				<td class="text-left">
					{$vacation.vacation_end|escape:'htmlall':'UTF-8'|date_format:"%d/%m/%Y"}
				</td>		
				<td>
					<button name="deleteVacation_{$vacation.id_vacation|escape:'htmlall':'UTF-8'}" id="deleteVacation_{$vacation.id_vacation|escape:'htmlall':'UTF-8'}" type="submit" class="btn btn-default">{l s='Delete' mod='prestatillhomedelivery'}</button>
				</td>					
			</tr>
			{/foreach}				
		</tbody>		
	</table>			
</div>
</form>