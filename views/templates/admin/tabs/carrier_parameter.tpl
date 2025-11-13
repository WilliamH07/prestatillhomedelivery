{*
* Home Delivery Slots
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

<h3><i class="icon-truck"></i> {l s='Parameters by carriers' mod='prestatillhomedelivery'}</h3>
<form role="form" class="form-horizontal"  action="#" method="POST" id="parameter_form" name="parameter_form">
	<div class="alert alert-info">
		{l s='You can now define here some parameters carrier by carrier.' mod='prestatillhomedelivery'}</i>
	</div>
    <div id="carrier_box" class="col-md-2">
	    <h4>{l s='Carriers' mod='prestatillhomedelivery'}</h4>
	    <ul>
	        {foreach from=$carriers item=carrier}
                <li data-id_carrier="{$carrier.id_reference|escape:'htmlall':'UTF-8'}">{$carrier.name|escape:'htmlall':'UTF-8'}</li>
            {/foreach}
	    </ul>
	</div>
	<div id="carrier_box_detail" class="col-md-10">
    	{foreach from=$carriers item=carrier}
        	<div class="carrier_box_detail" id="carrier_{$carrier.id_reference|escape:'htmlall':'UTF-8'}">
                <h4>{l s='Parameters for carrier' mod='prestatillhomedelivery'} - <span class="carrier_name">{$carrier.name|escape:'htmlall':'UTF-8'}</span></h4> 
				<div class="form-group">
					<label class="control-label col-lg-5" for="PRESTATILL_HD_CARENCE_{$carrier.id_reference|escape:'htmlall':'UTF-8'}">
						<span class="label-tooltip" data-toggle="tooltip" title="{l s='Time between receiving the order and the first available delivery slot' mod='prestatillhomedelivery'}">
							{l s='Waiting time :' mod='prestatillhomedelivery'}
						</span>
					</label>
					<div class="col-lg-7">				
						<div class="col-lg-4">
							<div class="input-group">
								<input type="number" name="PRESTATILL_HD_CARENCE_{$carrier.id_reference|escape:'htmlall':'UTF-8'}" style="text-align: center" id="PRESTATILL_HD_CARENCE_{$carrier.id_reference|escape:'htmlall':'UTF-8'}" value="{if $carrier.carence > 0}{$carrier.carence|escape:'htmlall':'UTF-8'}{else}{$carence}{/if}" />	
								<span class="input-group-addon">min</span>				
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-lg-5" for="PRESTATILL_HD_DUREE_{$carrier.id_reference|escape:'htmlall':'UTF-8'}">
						<span class="label-tooltip" data-toggle="tooltip" title="Ex: 60 min">
							{l s='Duration of slot (in min) : ' mod='prestatillhomedelivery'}
						</span>
					</label>
					<div class="col-lg-7">				
						<div class="col-lg-4">
							<div class="input-group">
								<input type="number" name="PRESTATILL_HD_DUREE_{$carrier.id_reference|escape:'htmlall':'UTF-8'}" style="text-align: center" id="PRESTATILL_HD_DUREE_{$carrier.id_reference|escape:'htmlall':'UTF-8'}" value="{if $carrier.duration >= 0}{$carrier.duration|escape:'htmlall':'UTF-8'}{else}{$duree}{/if}"/>			
								<span class="input-group-addon">min</span>				
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
                    <label class="control-label col-lg-5" for="PRESTATILL_HD_NB_DISPO_{$carrier.id_reference|escape:'htmlall':'UTF-8'}">
                        <span class="label-tooltip" data-toggle="tooltip" title="{l s='Define maximum orders for a same delivery slot : ' mod='prestatillhomedelivery'}">
                            {l s='Number of orders for the same slot : ' mod='prestatillhomedelivery'}
                        </span>
                    </label>
                    <div class="col-lg-7">              
                        <div class="col-lg-4">
                            <div class="input-group">
                                <input type="number" name="PRESTATILL_HD_NB_DISPO_{$carrier.id_reference|escape:'htmlall':'UTF-8'}" style="text-align: center" id="PRESTATILL_HD_NB_DISPO_{$carrier.id_reference|escape:'htmlall':'UTF-8'}" value="{if $carrier.nb_dispo > 0}{$carrier.nb_dispo|escape:'htmlall':'UTF-8'}{else}{$nb_dispo}{/if}"/> 
                                <span class="input-group-addon">{l s='Orders' mod='prestatillhomedelivery'}</span>  
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    	{/foreach}
	</div>
	<div class="clearfix"></div>
			
	<div class="panel-footer">
			<div class="btn-group pull-right">
			<button name="sumbitCarrierParameters" id="submitCarrierParameters" type="submit" class="btn btn-default"><i class="process-icon-save"></i> {l s='Save' mod='prestatillhomedelivery'}</button>
		</div>
	</div>
</form>
