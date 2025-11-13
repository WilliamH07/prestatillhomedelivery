/*
* Prestatill Home Delivery Slots
*
* Home Delivery Module with slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

$(document).ready(function(){
	
	var drive_url = $('#hd_base_url').val();
	
	// Edition button
	$('body').on('click tap','.hd_order_creneau button[data-type="edit"]', function() {
		if(confirm($('#hd_confirm_msg').val()))
		{
			$('.hd_order_creneau_edit').slideDown();
			$('.hd_order_creneau').addClass('active');
			$(this).prop('disabled',true);
		}
		
	});
	
	// Delete button
	$('body').on('click tap','.hd_order_creneau button[data-type="delete"]', function() {
        if(confirm($('#hd_confirm_msg').val()))
        {
            $(this).prop('disabled',true);
            $.ajax({
                url: $('#hd_dispo').data('url'),
                type: 'json',
                action: 'adminDeleteSlot',
                method: 'post',
                data: {
                    action: 'adminDeleteSlot',
                    id_order: $('input[name="hd_oc_id_order"]').val(),
                },
                success: function(data) {
    
                    if (data.message.success == false) {
                    } else {
                        window.location.reload();
                    }
                } 
            });
        }
        
    });
	
	$('body').on('change','.hd_order_creneau_edit #id_carrier', function() {
		var id_order = $('input[name="hd_oc_id_order"]').val();
    	$('#hd_dispo_overlay').show();
    	_createDaysTable();
	});
	
	$('body').on('click tap','.hd_order_creneau_new_button button', function() {
		
		$('.hd_order_creneau_edit').slideToggle();
		$('.hd_order_creneau_new_button').toggleClass('active');
		
	});
	
	$('body.adminorders').on('click tap','.hd_order_creneau_edit button[name="submitHomeDeliverySlotCreate"]', function() {
		
		$.ajax({
            url: $('#hd_dispo').data('url'),
            type: 'json',
            action: 'adminCreateSlot',
            method: 'post',
            data: {
                action: 'adminCreateSlot',
                hour: $('#hd_slot_hour').val(),
                date: $('#hd_slot_date').val(),
                id_order: $('input[name="hd_oc_id_order"]').val(),
                send_email: $('[name="hd_order_send_mail_modif"]:checked').val(),
                type: $('.hd_order_creneau_new_button').length,
            },
            success: function(data) {

                if (data.message.success == false) {
                } else {
                	window.location.reload();
                }
            } 
        });
	});
	
	$('body.adminorders').on('click tap', '#hd_creneau td span.dispo', function() {
        $('#hd_creneau td span').removeClass('selected');
        var slot_date = $(this).data('date');
		var slot_hour = $(this).data('hour');
        $(this).addClass('selected');
        $.ajax({
            url: $('#hd_dispo').data('url'),
            action: 'assignSlotFromBO',
            type: 'json',
            method: 'post',
            data: {
            	slot: $(this).data(),
            	action: 'assignSlotFromBO',
            	id_order: $('input[name="hd_oc_id_order"]').val(),
            	manual: $('#manual_slot').length,
            	selected: $(this).hasClass('selected'),
                id_carrier: $('.hd_order_creneau_edit #id_carrier').val(),
            	}, 
        }).done(function(response) {
            if (response.success == true) {
				$('#hd_slot_date').val(slot_date);
				$('#hd_slot_hour').val(slot_hour);
				
				// 1.3.1
				$('#manual_slot').val(response.id_creneau);
            } else {
                alert('error');
            }
        }).fail(function() {
            alert('Erroor');
        });
    });
    
    // 1.3.1 : Select manual slot 
    $('body.adminprestatillhomedelivery').on('click tap', '#hd_creneau td span.dispo', function() {
        
        $('#hd_creneau td span').removeClass('selected');
        var slot_date = $(this).data('date');
        var slot_hour = $(this).data('hour');
        
        $('#hd_slot_date').val(slot_date);
        $('#hd_slot_hour').val(slot_hour);
                
        $(this).addClass('selected');
        console.log('click');
    });
    
    // 1.3.1 : Add manual slots
    $('body.adminprestatillhomedelivery').on('click tap','.hd_order_creneau_edit button[name="submitHomeDeliverySlotCreate"]', function() {
        
        if($('#hd_slot_hour').val() != '' && $('#hd_slot_date').val() != '')
        {
            $(this).prop('disabled', true);
            $.ajax({
                url: $('#hd_dispo').data('url'),
                type: 'json',
                action: 'assignManualSlotFromBO',
                method: 'post',
                data: {
                    action: 'assignManualSlotFromBO',
                    hour: $('#hd_slot_hour').val(),
                    date: $('#hd_slot_date').val(),
                    slot: $('.adminprestatillhomedelivery #hd_creneau td span.selected').data(),
                    id_reference: $('#id_carrier').val(),
                    type: $('.hd_order_creneau_new_button').length,
                    manual: $('#manual_slot').length,
                    manual_comment: $('#manual_comment').val(),
                },
                success: function(data) {
    
                    if (data.message.success == false) {
                    } else {
                        window.location.reload();
                    }
                } 
            });
        }
    });
    
    function _createDaysTable() {
    	
        $.ajax({
            url: $('#hd_dispo').data('url'),
            action: 'initTable',
            type: 'json',
            method: 'post',
            data: {
                action: 'initTable',
                id_carrier: $('.hd_order_creneau_edit #id_carrier').val(),
                id_order: $('input[name="hd_oc_id_order"]').val(),
                init_bo: 1,
            },
            success: function(data) {

                if (data.status == 'success') {
                	
	                _viewList(data.message.table_days);
	                		
            		var size = $('body').find('#hd_list_creneau').data('size');
            		$('body').find('#hd_list_creneau').width(size*230+'px');
            	
            		changeclass(data.message.table_days);
            		adjustListDisplay(data.message.table_days);
            		$('#hd_legend').hide().remove();
            		reserved(data.message.table_days);
            		vacation(data.message.table_days);
            		
            		$('#hd_list_creneau td span:not(".disabled"):first').trigger('click');
                	$('#hd_dispo_overlay').hide();
                    
                }
            }
        });
    }
    
    function _viewList(message) {
        
        var html = '';
        var size = Object.keys(message.days).length;
        
        html += '<table id="hd_list_creneau" data-size="'+parseInt(size)+'" class="col-sm-12 viewList"><thead>';
        for (var i = 1; i <= size; i++) {
            html += '<td><span data-date="' + $.format.date("" + message.days[i].dateen, "d MMM yyyy") + '">' + message.days[i].day + '</span></td>';
        }
        html += '</thead>';
        html += '</table>';
        
        $('#hd_dispo_head').html(html);
        
        html = '<table id="hd_creneau" data-size="'+parseInt(size)+'" class="col-sm-12 viewList">';
        
        var sizeh = Object.keys(message.creneau).length;
        html +='<tr><td>';
        if(message.increment > 0)
        {
            for (var h = 0; h < sizeh - 1; h++) {
                for (var i = 1; i <= size; i++) {
                    var additional_class = 'indispo';
                    html += '<span class="creneau ' + additional_class + '" data-date="'+message.days[i].dateen+'" data-id-day="' + message.days[i].id_day + '" data-datetime="' + message.days[i].dateen + ' ' + message.creneau[h] + '" data-hour="' + message.creneau[h] + '" data-date="' + message.days[i].date + '" data-hour_end="00:00:00" >';
                    html += '' + $.format.date("0000-00-00 " + message.creneau[h] + "", "H:mm") + ' - ' + $.format.date("0000-00-00 " + message.creneau[h + 1] + "", "H:mm") + '</span>';
                }
               
            }
        }
        else {
            // Création d'un Set pour suivre les créneaux déjà ajoutés
            var uniqueCreneaux = new Set();

            for (var i = 1; i <= size; i++) {
                // On est dans le cadre des CP
                if (message.days[i].op.length > 0) {
                    message.days[i].op = message.days[i].op.sort();
                    message.days[i].cl = message.days[i].cl.sort();

                    for (var j = 0; j < message.days[i].op.length; j++) {
                        var creneauKey = message.days[i].dateen + ' ' + message.days[i].op[j] + ':00' + '-' + message.days[i].cl[j] + ':00';

                        // Vérifie si le créneau est déjà présent dans le Set
                        if (!uniqueCreneaux.has(creneauKey)) {
                            // Ajoute le créneau au Set pour éviter les doublons
                            uniqueCreneaux.add(creneauKey);

                            var additional_class = 'indispo';
                            html += '<span class="creneau ' + additional_class + '" data-date="' + message.days[i].dateen + '" data-id-day="' + message.days[i].id_day + '" data-datetime="' + message.days[i].dateen + ' ' + message.days[i].op[j] + ':00" data-hour="' + message.days[i].op[j] + ':00" data-hour_end="' + message.days[i].cl[j] + ':00" data-date="' + message.days[i].date + '">';
                            html += '' + $.format.date("0000-00-00 " + message.days[i].op[j] + ":00", "H:mm") + ' - ' + $.format.date("0000-00-00 " + message.days[i].cl[j] + ":00", "H:mm") + '</span>';
                        }
                    }
                }
            }
        }
        html += '</td></tr>';
        html += '</tbody></table>';
        html += '<div class="clear"></div>';
        $('#hd_dispo').html(html);
    }

    function changeclass(message) {
        $('#hd_creneau .indispo').each(function() {
            for (var e = 1; e <= 7; e++) {
                if (message.open[e] != undefined && $(this).data('idDay') == message.open[e].id_day && message.open[e].nonstop == true && $(this).data('hour') >= message.open[e].hour_open_am && $(this).data('hour') < message.open[e].hour_close_pm) {
                    $(this).removeClass('indispo');
                    $(this).addClass('dispo');
                } else if (message.open[e] != undefined && $(this).data('idDay') == message.open[e].id_day && message.open[e].nonstop == false && (($(this).data('hour') >= message.open[e].hour_open_am && $(this).data('hour') < message.open[e].hour_close_am) || ($(this).data('hour') >= message.open[e].hour_open_pm && $(this).data('hour') < message.open[e].hour_close_pm))) {
                    $(this).removeClass('indispo');
                    $(this).addClass('dispo');
                }
            }
        });
    }
    
    function adjustListDisplay(message) {
    	
    	// On gère les carrences
        /*Date d'aujourd'hui à l'instant T*/
        var date_today = Date.now();        
        var carence = message.creneau_carence*60*1000;
        var add = date_today + carence; 
        var date_limit = new Date(add);
        datetime = date_limit.getFullYear()+'-'+((date_limit.getMonth()+1)+'').padStart(2,'0')+'-'+(date_limit.getDate()+'').padStart(2,'0')+' '+(date_limit.getHours()+'').padStart(2,'0')+':'+(date_limit.getMinutes()+'').padStart(2,'0')+':'+(date_limit.getSeconds()+'').padStart(2,'0');
        
        $($('#hd_creneau tbody tr span').get().filter(td=>td.dataset.datetime<=datetime)).removeClass('dispo').addClass('indispo');
        
        // On check les vacances 
        vacation(message);
        
        // On supprime les jours où il n'y a aucune dispo dans l'entête
        $('body').find('#hd_list_creneau tr td span').each(function(){
        	var the_day = $(this).data('date');
        	if($('#hd_creneau tr td span[data-date="'+the_day+'"].dispo').length == 0)
        		$(this).addClass('disabled');
        });
        
        $('#hd_dispo_head').scrollLeft(0);
        
        // On affiche les premières dispos
        $('body').find('#hd_dispo_head #hd_list_creneau tr td span').each(function()
        {
        	if($(this).hasClass('disabled') == false)
        	{
        		var distance_left = $(this).position().left;
        		var div_width = $(this).outerWidth();
        		$('#hd_dispo_head').scrollLeft(parseInt(distance_left-div_width));
        		$(this).trigger('click');
        		return false;
        	}
        		
        });
        
        $('body').on('click tap', '#hd_list_creneau.viewList td span', function(){
	    	if($(this).hasClass('disabled') == false)
	    	{
	    		$('#hd_list_creneau.viewList td span').removeClass('active');
		    	$(this).addClass('active');
		    	$('#hd_creneau tr td span').hide();
		    	var the_day = $(this).data('date');
		    	$('#hd_creneau tr td span[data-date="'+the_day+'"].dispo').fadeIn(250).css('display','inline-block');
		    	$('#hd_creneau tr td span[data-date="'+the_day+'"].red').fadeIn(250).css('display','inline-block');
	    	}
	    });
        
        $('#hd_nav_buttons').hide().remove();
    }
    
    function _adjustListScroll() {
    	// On affiche les premières dispos
        $('body').find('#hd_dispo_head #hd_list_creneau tr td span').each(function()
        {
        	if($(this).hasClass('disabled') == false)
        	{
        		var distance_left = $(this).position().left;
        		var div_width = $(this).outerWidth();
        		$('#hd_dispo_head').scrollLeft(parseInt(distance_left-div_width));
        		$(this).trigger('click');
        		return false;
        	}
        		
        });
    }

    function carrence(message) { 
    	/*Date d'aujourd'hui à l'instant T*/
        var date_today = Date.now();        
        var carence = message.creneau_carence*60*1000;
        var add = date_today + carence; 
        var date_limit = new Date(add);
        datetime = date_limit.getFullYear()+'-'+((date_limit.getMonth()+1)+'').padStart(2,'0')+'-'+(date_limit.getDate()+'').padStart(2,'0')+' '+(date_limit.getHours()+'').padStart(2,'0')+':'+(date_limit.getMinutes()+'').padStart(2,'0')+':'+(date_limit.getSeconds()+'').padStart(2,'0');
        
        $($('#hd_creneau tbody tr td').get().filter(td=>td.dataset.datetime<=datetime)).removeClass('dispo').addClass('indispo');
    }

    function vacation(message) {
        $('#hd_creneau .creneau').each(function() {
            var sizevacation = Object.keys(message.vacations).length;
            for (var j = 0; j < sizevacation; j++) {
                var datetime_creneau = $(this).data('datetime');
                var date_creneau_format = datetime_creneau.substr(0,10);
                //console.log([datetime_creneau,date_creneau_format,message.vacations[j].vacation_start,message.vacations[j].vacation_end]);
                if (date_creneau_format >= message.vacations[j].vacation_start && date_creneau_format <= message.vacations[j].vacation_end) {
                    $(this).addClass('red indispo');
                    $(this).removeClass('dispo');
                }
            }
        });
    }
    
    function reserved(message) {
    	// On ajoute les reservés
        var sizer = message.reserved!=null?Object.keys(message.reserved).length:0;
        if(sizer > 0)
        {
		    for (var key in message.reserved) {
			    if(message.reserved[key] < message.creneau_limit) {
			  	    $('[data-datetime="'+key+'"]').addClass('busy');
			    } else if (message.reserved[key] >= message.creneau_limit) {
			  	    $('[data-datetime="'+key+'"]').removeClass('dispo').addClass('red indispo');
			    }
		    }
        }
    }
});
