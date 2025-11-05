{*
* Home Delivery Slots
*
* Home Delivery Module & Slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

<div class="panel col-lg-12">
    <div class="col-xs-9">
        <div id="hd_dispo_overlay" ></div>
        <div class="alert alert-info">
            {l s='You can manually block some slots by adding them below. This blocks the entire slot for customers.' mod='prestatillhomedelivery'}
        </div>
        <div class="hd_order_creneau_new_button">
            <i class="fa fa-clock"></i><span>{l s='Home Delivery Slot' mod='prestatillhomedelivery'}</span>
            <button class="btn btn-default">{l s='Add a Delivery Slot' mod='prestatillhomedelivery'}</button>   
            <div class="clearfix"></div>    
        </div>
        <input type="hidden" id="manual_slot" name="manual_slot" />
        {include file="./admin_edit_slot.tpl"}
        {* SLOTS MANUELS : preparation for 1.4.0 *}
        {* include file="./manual_creneau.tpl" *}
    </div>

    <div class="col-lg-3">
        Afficher ici le détail d'un jour de livraison si commandes / créneaux manuels sur la journée, au clic sur le jour
    </div>
</div>
