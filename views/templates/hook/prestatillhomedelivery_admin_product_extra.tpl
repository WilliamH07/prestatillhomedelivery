{*
* Home Delivery Slots
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}
{if $ps16}
    <div id="product-customization" class="panel product-tab">
        <h3>{l s='Additionnal preparation time' mod='prestatillhomedelivery'}</h3>
        <div class="form-group">
            <div class="col-md-12">
                <div class="alert alert-info mt-3" role="alert">
                    <p class="alert-text">
                        {l s='You can define an additionnal preparation time for this product' mod='prestatillhomedelivery'}
                    </p>
                </div>
            </div>
            <div class="col-lg-1"><span class="pull-right"></span></div>
            <label class="control-label col-lg-3" for="text_fields">
                {l s='Additionnal preparation time:' mod='prestatillhomedelivery'}
            </label>
            <div class="input-group col-lg-2">
                <input type="text" id="carence_supp" name="carence_supp" class="form-control" value="{$carence_supp|escape:'htmlall':'UTF-8'}">
                <span class="input-group-addon">{l s='hours' mod='prestatillhomedelivery'}</span>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" name="submitAddproduct" class="btn btn-default pull-right">{l s='Save' mod='prestatillhomedelivery'}</button>
            <button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right">{l s='Save & Stay' mod='prestatillhomedelivery'}</button>
        </div>
    </div>
{else}
    <div class="row form-group">
        <div class="col-md-12">
            <div class="alert alert-info mt-3" role="alert">
                <p class="alert-text">
                    {l s='You can define an additionnal preparation time for this product' mod='prestatillhomedelivery'}
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-control-label">{l s='Additionnal preparation time:' mod='prestatillhomedelivery'}</label>
            <div class="input-group">
                <input type="text" id="carence_supp" name="carence_supp" class="form-control" value="{$carence_supp|escape:'htmlall':'UTF-8'}">
                <div class="input-group-append">
                    <span class="input-group-text">{l s='hours' mod='prestatillhomedelivery'}</span>
                </div>
            </div>
        </div>
        <br />
        <div class="col-md-12">
            <div class="alert alert-info mt-3" role="alert">
                <p class="alert-text">
                    {l s='You can define one or more days where product is not available for Home Delivery.' mod='prestatillhomedelivery'}
                </p>
            </div>
        </div>
        <div class="availability_table col-md-12">
            <table data-id_product="{$id_product|escape:'htmlall':'UTF-8'}" id="product_days_availability" class="table" data-url="{Context::getContext()->link->getModuleLink('prestatillhomedelivery', 'validateordercarrier')|escape:'htmlall':'UTF-8'}">
                <tbody>
                    <tr>
                        <td width="12.5%">{l s='Weekdays' mod='prestatillhomedelivery'}</td>
                        {foreach from=$weekdays item=day}
                            <td width="12.5%" style="text-align: center">{$day|escape:'htmlall':'UTF-8'}</td>
                        {/foreach}
                    </tr>
                    <tr>
                        <td width="12.5%">{l s='Availability' mod='prestatillhomedelivery'}</td>
                        {foreach from=$weekdays item=day name=foo}
                            <td width="12.5%" data-id_day="{$smarty.foreach.foo.iteration|escape:'htmlall':'UTF-8'}" class="{if isset($product_availability[$smarty.foreach.foo.iteration])}unavailable {else} available{/if} psd" style="text-align: center">&nbsp;</td>
                        {/foreach}
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
{/if}
