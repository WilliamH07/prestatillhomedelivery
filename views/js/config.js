/*
* Prestatill Home Delivery Slots
*
* Home Delivery Module with slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

$(document).ready(function() 
{
	$('.menu_tab').click(function()
	{
        $('.menu_tab').removeClass('active');
        $('.tab-pane').removeClass('active');
   		$(this).addClass('active');
	});
	
	$('.hour_open tr>td>input').each(function()
	{
		var id = $(this).attr('data-id');	
		_updateinput(id,false);
	});
   
   $('.hour_open tr>td>input').click(function()
   {
		var id = $(this).attr('data-id');	
		_updateinput(id,false);
	});
	
	$('.hour_open tr td:first-child input[type=checkbox]').click(function()
   	{
   		var id = $(this).attr('data-id');
   		_updateinput(id);
   		
   	});
   		
   	$('.hour_open tr td:last-child input').each(function()
   	{
   		var id = $(this).attr('data-id');
   		_disabledInputWhenNonstopChecked(id);
   	});
   	
   	$('.hour_open tr td:last-child input').click(function()
   	{
   		var id = $(this).attr('data-id');
   		_disabledInputWhenNonstopChecked(id);
   	});	
   		
   $('#PRESTATILL_HD_OPEN').keyup(function()
   {
   		var value = $(this).val();
   		var valueformat = new Date(value);
   });		
   
   $('#add_vacation_button').click(function(e)
   {
   		$('#add_vacation').removeClass('disabled');
   		e.preventDefault();
   		$(this).hide();
   });
   
   $('#parameter_form input').on('click',function()
   {
   		if($(this).prop('checked') == true)
   		{
   			$(this).parent().parent().addClass('active');
   		}
   		else
   		{
   			$(this).parent().parent().removeClass('active');
   		}
   });
   
   $('#submitVacation').click(function(e)
   {
       if ($('#PRESTATILL_HD_VACATION_START').val() == '' && $('#PRESTATILL_HD_VACATION_END').val() == '')
       {
           e.preventDefault();
           alert('Start vacation day and End vacation day is empty');
       }
       else if ($('#PRESTATILL_HD_VACATION_START').val() == '')
       {
           e.preventDefault();
           alert('Start vacation day is empty');
       }  
       else if ($('#PRESTATILL_HD_VACATION_END').val() == '')
       {
           e.preventDefault();
           alert('End vacation day is empty');
       }    
   });
   
   // 1.0.4
   $('#deliveryarea .dbd_supp button').on('click',function(e){
		
   		$(this).prop('disabled',true);
   		e.preventDefault();
   		var id_day = $('#PRESTATILL_HD_DBD_SUPP_DAY').val();
   		var dbd_distance = $('#PRESTATILL_HD_DBD_SUPP').val();
   		var id_shop_group = $('#id_shop_group').val();
   		var id_shop = $('#id_shop').val();
   		// 1.2.0
        var id_carrier = $('#PRESTATILL_HD_DBD_SUPP_ID_CARRIER').val();
        var id_store = $('#PRESTATILL_HD_DBD_SUPP_ID_STORE').val();
        var id_zone = $('#PRESTATILL_HD_DBD_SUPP_ID_ZONE').val();
        
        var zone_name = null;
        if(id_zone == 0)
        {
            zone_name = $('#new_id_zone_dbd').val();
        }
   		
   		$('#PRESTATILL_HD_DBD_SUPP').removeClass('error');
   		
		if(dbd_distance <= 0 || ($('#PRESTATILL_DBD_SUPP_ID_ZONE').val() == 0 && $('#new_id_zone_dbd').val() == ''))
        {
   			$('#PRESTATILL_HD_DBD_SUPP').addClass('error');
   		}
   		else
   		{
   			$.ajax({
	            url: $(this).data('url'),
	            type: 'json',
	            action: 'addDBDSupp',
	            method: 'post',
	            data: {
	            	action: 'addDBDSupp',
		            id_day: id_day,
		            dbd_supp: dbd_distance,
		            id_shop_group: id_shop_group,
		            id_shop: id_shop,
                    id_carrier: id_carrier,
                    id_store: id_store,
                    id_zone: id_zone,
                    zone_name: zone_name,
	            },
	        }).done(function(data) {
	            if (data.status == 'success') {  
	            	$('#dbd_supp table tbody').html(data.message.tpl);
	            	//$('#deliveryarea .dbd_supp tbody tr').hide();
					$('#deliveryarea #dbd_supp'+' .dbd_supp_td'+'').show();
	            } else {
	                
	            }
	        }).fail(function() {
	            alert('Erroor');
	        });
   		}
   		$(this).prop('disabled',false);
   }); 
   
   $('#deliveryarea').on('click', '.deleteDBDSupp', function(e) {
   	
   		e.preventDefault();
   		var id_day = $(this).data('id_day');
   		var id_shop_group = $('#id_shop_group').val();
   		var id_shop = $('#id_shop').val();
   		// 1.2.0
        var id_carrier = $(this).data('id_carrier');
        var id_store = $('#PRESTATILL_HD_DBD_SUPP_ID_STORE').val();
   		
   		$.ajax({
            url: $(this).data('url'),
            type: 'json',
            action: 'deleteDBDSupp',
            method: 'post',
            data: {
            	action: 'deleteDBDSupp',
	            id_day: id_day,
	            id_shop_group: id_shop_group,
		        id_shop: id_shop,
                id_carrier: id_carrier,
                id_store: id_store,
            },
        }).done(function(data) {
            if (data.status == 'success') {  
            	$('#dbd_supp table tbody').html(data.message.tpl);
            	//$('#deliveryarea .dbd_supp tbody tr').hide();
				$('#deliveryarea #dbd_supp .dbd_supp_td'+'').show();
            } else {
                
            }
        }).fail(function() {
            alert('Erroor');
        });
   });
   
   // 1.0.4
   $('#deliveryarea .zip_supp button#zip_supp').on('click',function(e){
		
   		$(this).prop('disabled',true);
   		e.preventDefault();
   		if(parseInt($('#PRESTATILL_HD_ZIP_SUPP_DAY').val()) == 0)
   		{
            var id_day = 0;
   		}
   		else
   		{
            var id_day = $('#PRESTATILL_HD_ZIP_SUPP_DAY').val()+'_'+$('#PRESTATILL_HD_ZIP_OD').val().substring(0, 5)+'_'+$('#PRESTATILL_HD_ZIP_CL').val().substring(0, 5);
   		}
   		var zip = $('#PRESTATILL_HD_ZIP_SUPP').val();
   		var id_shop_group = $('#id_shop_group').val();
   		var id_shop = $('#id_shop').val();
   		// 1.2.0
        var id_carrier = $('#PRESTATILL_HD_ZIP_SUPP_ID_CARRIER').val();
        var id_zone = $('#PRESTATILL_HD_ZIP_SUPP_ID_ZONE').val();
        var zone_name = null;
        if(id_zone == 0)
        {
            zone_name = $('#new_id_zone').val();
        }
   		
   		$('#PRESTATILL_HD_ZIP_SUPP').removeClass('error');
   		
		if(zip == '' || ($('#PRESTATILL_HD_ZIP_SUPP_ID_ZONE').val() == 0 && $('#new_id_zone').val() == ''))
   		{
   			$('#PRESTATILL_HD_ZIP_SUPP').addClass('error');
   		}
   		else
   		{
   			$.ajax({
	            url: $(this).data('url'),
	            type: 'json',
	            action: 'addZipSupp',
	            method: 'post',
	            data: {
	            	action: 'addZipSupp',
		            id_day: id_day,
		            zip_supp: zip,
		            id_shop_group: id_shop_group,
		            id_shop: id_shop,
		            id_carrier: id_carrier,
		            id_zone: id_zone,
		            zone_name: zone_name,
	            },
	        }).done(function(data) {
	            if (data.status == 'success') {  
	            	$('#zip_supp table tbody').html(data.message.tpl);
					$('#deliveryarea #zip_supp'+' .zip_supp_td'+'').show();
					
	            } else {
	                
	            }
	        }).fail(function() {
	            alert('Erroor');
	        });
   		}
   		$(this).prop('disabled',false);
   }); 
   
   $('#deliveryarea').on('click', '.deleteZipSupp', function(e) {
   	
   		e.preventDefault();
   		var id_day = $(this).data('id_day');
   		var id_shop_group = $('#id_shop_group').val();
   		var id_shop = $('#id_shop').val();
   		// 1.2.0
        var id_carrier = $(this).data('id_carrier');
        var id_zone = $(this).data('id_zone');
   		
   		$.ajax({
            url: $(this).data('url'),
            type: 'json',
            action: 'deleteZipSupp',
            method: 'post',
            data: {
            	action: 'deleteZipSupp',
	            id_day: id_day,
	            id_shop_group: id_shop_group,
		        id_shop: id_shop,
		        id_carrier: id_carrier,
		        id_zone: id_zone,
            },
        }).done(function(data) {
            if (data.status == 'success') {  
            	$('#zip_supp table tbody').html(data.message.tpl);
				$('#deliveryarea #zip_supp .zip_supp_td'+'').show();
            } else {
                
            }
        }).fail(function() {
            alert('Erroor');
        });
   });
   
   $('#deliveryarea').on('click', '.editZipSupp', function(e) {
       
       e.preventDefault();
       
       var id_zone = $(this).data('id_zone');
       var id_carrier = $(this).data('id_carrier');
       var zip_supp_list = $(this).data('zip_supp_list');
       var id_day = $(this).data('id_day');
       var od = $(this).data('od').substring(0, 5);
       var cl = $(this).data('cl').substring(0, 5);
       
       // On complète le footer avec les informations à modifier
       $('#PRESTATILL_HD_ZIP_SUPP_ID_ZONE').val(id_zone);
       $('#PRESTATILL_HD_ZIP_SUPP_ID_CARRIER').val(id_carrier);
       $('#PRESTATILL_HD_ZIP_SUPP').val(zip_supp_list);
       $('#PRESTATILL_HD_ZIP_SUPP_DAY').val(id_day);
       
       if(id_day > 0)
       {
          $('#PRESTATILL_HD_ZIP_OD').val(od);
          $('#PRESTATILL_HD_ZIP_CL').val(cl);
       }
       else
       {
           $('#PRESTATILL_HD_ZIP_OD').val('00:00');
          $('#PRESTATILL_HD_ZIP_CL').val('00:00');
       }
       
       $('#zip_supp tfoot').addClass('flash')
       
       setTimeout(function(){
           $('#zip_supp tfoot').removeClass('flash');
       },500);
       
   });
   
   $('#openingdays').on('change', '.select_date_1', function(){
   		var id_day = parseInt($(this).val());
		$('.select_date_2 option').hide();
		if(id_day > 0)
		{
			if(id_day == 7)
			{
				$('.select_date_2 option[value="'+1+'"]').show();
				$('.select_date_2 option[value="'+7+'"]').show();
				$('.select_date_2 option[value="'+parseInt(1)+'"]').show().prop('selected',true);
			}
			else
			{
				$('.select_date_2 option[value="'+id_day+'"]').show();
				$('.select_date_2 option[value="'+parseInt(id_day+1)+'"]').show().prop('selected',true);
			}
			
		}
		else 
		{
			$('.select_date_2 option[value="0"]').show().prop('selected',true);
		}
		
		//$('.select_date_2 option[value="0"]').prop('disabled',true);
   });
   
   $('#openingdays .carence_supp button').on('click',function(e){
   		$(this).prop('disabled',true);
   		e.preventDefault();
   		var id_lang = $(this).data('id_lang');
   		var id_day = $('#PRESTATILL_HD_CARENCE_SUPP_DAY').val();
   		var id_reference = $('#PRESTATILL_HD_CARENCE_SUPP_ID_REFERENCE').val();
   		var id_day_end = $('#PRESTATILL_HD_CARENCE_SUPP_DAY_END').val();
   		var hour_limit = $('#PRESTATILL_HD_CARENCE_SUPP_HOUR_LIMIT').val();
   		var hour_limit_end = $('#PRESTATILL_HD_CARENCE_SUPP_HOUR_LIMIT_END').val();
   		var waiting_time = $('#PRESTATILL_HD_CARENCE_SUPP').val();
   		var id_shop_group = $('#id_shop_group').val();
   		var id_shop = $('#id_shop').val();
   		
   		$('#PRESTATILL_HD_CARENCE_SUPP_HOUR_LIMIT').removeClass('error');
   		$('#PRESTATILL_HD_CARENCE_SUPP').removeClass('error');
   		
   		if(hour_limit == '' || hour_limit == '00:00')
   		{
   			$('#PRESTATILL_HD_CARENCE_SUPP_HOUR_LIMIT').addClass('error');
   		}
   		else if(waiting_time <= 0)
   		{
   			$('#PRESTATILL_HD_CARENCE_SUPP').addClass('error');
   		}
   		else
   		{
   			$.ajax({
	            url: $(this).data('url'),
	            type: 'json',
	            action: 'addCarenceSupp',
	            method: 'post',
	            data: {
	            	action: 'addCarenceSupp',
		            id_day: id_day,
					id_reference: id_reference,
		            id_day_end: id_day_end,
		            hour_limit: hour_limit,
		            hour_limit_end: hour_limit_end,
		            waiting_time: waiting_time,
		            id_lang: id_lang,
		            id_shop_group: id_shop_group,
		            id_shop: id_shop,
	            },
	        }).done(function(data) {
	            if (data.status == 'success') {  
	            	$('#carence_supp table tbody').html(data.message.tpl);
	            	$('#openingdays .carence_supp tbody tr').hide();
					$('#openingdays #carence_supp'+' .carence_supp_td'+'').show();
	            } else {
	                
	            }
	        }).fail(function() {
	            alert('Erroor');
	        });
   		}
   		$(this).prop('disabled',false);
   });
   
   $('#openingdays').on('click', '.deleteCarenceSupp', function(e) {
   	
   		e.preventDefault();
   		var id_day = $(this).data('id_day');
   		var id_reference = $(this).data('id_reference');
   		var id_shop_group = $('#id_shop_group').val();
   		var id_shop = $('#id_shop').val();
   		
   		$.ajax({
            url: $(this).data('url'),
            type: 'json',
            action: 'deleteCarenceSupp',
            method: 'post',
            data: {
            	action: 'deleteCarenceSupp',
	            id_day: id_day,
				id_reference: id_reference,
	            id_shop_group: id_shop_group,
		        id_shop: id_shop,
            },
        }).done(function(data) {
            if (data.status == 'success') {  
            	$('#carence_supp table tbody').html(data.message.tpl);
            	$('#openingdays .carence_supp tbody tr').hide();
				$('#openingdays #carence_supp .carence_supp_td'+'').show();
            } else {
                
            }
        }).fail(function() {
            alert('Erroor');
        });
   });
   
   // 1.0.4
   $('#deliveryarea [name="PRESTATILL_SEARCH_HD_STORE"]').on('change', function(){
       if($(this).val()==1)
       {
       		$('#deliveryarea #PRESTATILL_SEARCH_HD_ZIP_off').trigger('click');
       		$('#zip_supp_box').hide();
       }
       else
       {
       		$('#deliveryarea #PRESTATILL_SEARCH_HD_ZIP_on').trigger('click');
       		$('#zip_supp_box').show();
       }
   	   
   });
   
    $('#deliveryarea [name="PRESTATILL_SEARCH_HD_ZIP"]').on('change', function(){
		if($(this).val()==1)
       {
       		$('#deliveryarea #PRESTATILL_SEARCH_HD_STORE_off').trigger('click');
       		$('#dbd_supp_box').hide();
       		$('#deliveryarea .main_store_box').slideUp();
       }
       else
       {
       		$('#deliveryarea #PRESTATILL_SEARCH_HD_STORE_on').trigger('click');
       		$('#dbd_supp_box').show();
       		$('#deliveryarea .main_store_box').slideDown();
       }
    });
    
   $('#PRESTATILL_HD_ZIP_SUPP_DAY').on('change', function(){
       var id_day = parseInt($(this).val());
	   _manage_zip_supp(id_day);
   });
   
   $('#PRESTATILL_HD_DBD_SUPP_DAY').on('change', function(){
       var id_day = parseInt($(this).val());
       _manage_dbd_supp(id_day);
   });
   
   $('#PRESTATILL_HD_ZIP_SUPP_ID_ZONE').on('change', function(){
       var id_zone = parseInt($(this).val());
       if(id_zone == 0)
       {
           $('#new_id_zone').slideDown();
       }
       else
       {
           $('#new_id_zone').slideUp();
       }
   });
   
   $('#PRESTATILL_HD_DBD_SUPP_ID_ZONE').on('change', function(){
       var id_zone = parseInt($(this).val());
       if(id_zone == 0)
       {
           $('#new_id_zone_dbd').slideDown();
       }
       else
       {
           $('#new_id_zone_dbd').slideUp();
       }
   });
   
   var z_supp = $('#PRESTATILL_HD_ZIP_SUPP_DAY').val();
   _manage_zip_supp(z_supp);
   
   var dbd_supp = $('#PRESTATILL_HD_DBD_SUPP_DAY').val();
   _manage_dbd_supp(dbd_supp);
   
   // 1.3.1 : Select manual slot 
    $('body').on('click tap', '#carrier_box li', function() {
        
        $('#carrier_box li').removeClass('active');
        
        var id_carrier = $(this).data('id_carrier');

        $(this).addClass('active');
        
        $('.carrier_box_detail').hide();
        $('#carrier_'+id_carrier).show();
        
        console.log(id_carrier);
    });
    
    // On masque les informations des carriers sauf du premier
    $('#carrier_box li:first').trigger('click');
     
});

function _manage_zip_supp(z_supp = 1)
{
	$('#hour_open_info tr').removeClass('active');
	$('#hour_open_info tr[data-id_day='+z_supp+']').addClass('active');
	$('#PRESTATILL_HD_ZIP_OD').val($('#hour_open_info tr.active .open:first').text());
	$('#PRESTATILL_HD_ZIP_OD').attr('min',$('#hour_open_info tr.active .open:first').text()).attr('max',$('#hour_open_info tr.active .open:last-child').text());
	$('#PRESTATILL_HD_ZIP_CL').val($('#hour_open_info tr.active .open:last-child').text());
	$('#PRESTATILL_HD_ZIP_CL').attr('min',$('#hour_open_info tr.active .open:first').text()).attr('max',$('#hour_open_info tr.active .open:last-child').text());
}

function _manage_dbd_supp(dbd_sup = 1)
{
    $('#hour_open_info tr').removeClass('active');
    $('#hour_open_info tr[data-id_day='+dbd_sup+']').addClass('active');
    $('#PRESTATILL_HD_DBD_OD').val($('#hour_open_info tr.active .open:first').text());
    $('#PRESTATILL_HD_DBD_OD').attr('min',$('#hour_open_info tr.active .open:first').text()).attr('max',$('#hour_open_info tr.active .open:last-child').text());
    $('#PRESTATILL_HD_DBD_CL').val($('#hour_open_info tr.active .open:last-child').text());
    $('#PRESTATILL_HD_DBD_CL').attr('min',$('#hour_open_info tr.active .open:first').text()).attr('max',$('#hour_open_info tr.active .open:last-child').text());
}

function _updateinput(id,check_all = true)
{
	   if ($('.hour_open tr>td>input[data-id='+id+']').prop("checked") == false)
	   {
	   		$('.hour_open .no input[data-id='+id+']').attr('disabled','disabled');
	   		$('.hour_open tr > td > input[data-id='+id+']').attr('value','--');
	   }
	   else if(check_all == true)
	   {
	   		$('.hour_open .no input[data-id='+id+']').prop('disabled', false);
	   		$('.hour_open tr > td > input[name=PRESTATILL_HD_OPENING_AM_'+id+']').attr('value','08:00:00');
	   		$('.hour_open tr > td > input[name=PRESTATILL_HD_CLOSING_AM_'+id+']').attr('value','12:00:00');
	   		$('.hour_open tr > td > input[name=PRESTATILL_HD_OPENING_PM_'+id+']').attr('value','14:00:00');
	   		$('.hour_open tr > td > input[name=PRESTATILL_HD_CLOSING_PM_'+id+']').attr('value','18:00:00');
	   		$('.hour_open tr > td > input[name=PRESTATILL_HD_OPENING_NONSTOP_'+id+']').prop('checked',false).val(0);
	   }
}

function _disabledInputWhenNonstopChecked(id)
{
	if($('#'+id+'_nonstop').prop("checked") == true)
	{
		$('#'+id+'_closing_am').attr('disabled','disabled').val("00:00:00");
		$('#'+id+'_opening_pm').attr('disabled','disabled').val("00:00:00");
	}
	else
	{
		$('#'+id+'_closing_am').prop('disabled', false);
		$('#'+id+'_opening_pm').prop('disabled', false);
	}
}