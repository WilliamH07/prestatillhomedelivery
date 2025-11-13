/*
* Prestatill Home Delivery Slots
*
* Home Delivery Module with slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

$(document).ready(function() {
    $('#active_js').addClass('disabled');
    $('button[name=processCarrier]').css("display", "block");
    $('#extra_carrier').css("display", "block");
    $('#delivery_options_address').css("display", "block");
    $('#HOOK_PAYMENT').css("display", "block");
    $('#form > div.order_carrier_content > div.box').css("display", "block");
    $('.delivery_options_address').css("display", "block");
    $('button[name=confirmDeliveryOption]').hide();
    $('#hd_nav_buttons').hide();
    $('#hd_prev_days').addClass('selected');
    var ps_url = $('#psd_base_url').val();
    var nb_colmuns;
    var current_start_column = 2;
    var current_nb_display_column = 7;
    var opc_checkout = $('body#order-opc').length;
    
    setTimeout(function(){
		_hideOrShow();
	},100);
    
    // On recharge la page si elle reste affichée plus de 10 minutes pour éviter que le créneau ne devienne obsolète si on reste sur la page
    refresh(600);
    
    $('input.delivery_option_radio:checked').parents('.delivery-option').next().append($('#hd_box'));
    
    $('body').on('click tap', '.modal-backdrop, .modal-content .close', function() {
        $('#modal_creneau,.modal-backdrop').removeClass('in').addClass('out').hide();
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
    
    $('body').on('click tap', '#hd_creneau td.dispo', function() {
        $('#hd_creneau td').removeClass('selected');
        $(this).addClass('selected');
        $('#hd_box').attr('data-creneau', '1');
        $.ajax({
            url: $('#hd_dispo').data('url'),
            action: 'assignSlot',
            type: 'json',
            method: 'post',
            data: {
            	slot: $(this).data(),
            	dist_max: $('#address_max_dist').val(),
            	action: 'assignSlot',
            	id_carrier: $('input.delivery_option_radio:checked').val()
            	},
        }).done(function(response) {
            if (response.success == true) {
                if(response.possible_delivery == 1)
            	{
            		$('#hd_creneau_day').html(response.msg);
                	$('#hd_creneau_selected').removeClass('alert-warning alert-danger hd_big_danger').addClass('alert-success');
                	$('#hd_legend, #hd_nav_buttons, #hd_dispo_head, #hd_dispo, #hd_box h3').slideUp();
            	}
            	else {
            		$('#hd_creneau_day').html(response.possible_delivery);
                	$('#hd_creneau_selected').removeClass('alert-success').addClass('hd_big_danger');
                	$('#hd_creneau span.selected').removeClass('dispo').addClass('red');
            	}
            	$([document.documentElement, document.body]).animate({
                	//scrollTop: $('#hd_creneau_selected').offset().top-105
                },1000);
            } else {
                $('#hd_creneau_day').html(response.error);
            	$('#hd_creneau_selected').removeClass('alert-success').addClass('hd_big_danger');
            	$('#hd_creneau td.selected').removeClass('dispo').addClass('red'); 
            }
        }).fail(function() {
            alert('Erroor');
        });
    });
    
    $('body').on('click tap', '#hd_creneau span.dispo', function() {
        $('#hd_creneau span').removeClass('selected');
        $(this).addClass('selected');
        $('#hd_box').attr('data-creneau', '1');
        $.ajax({
            url: $('#hd_dispo').data('url'),
            action: 'assignSlot',
            type: 'json',
            method: 'post',
            data: {
            	slot: $(this).data(),
            	dist_max: $('#address_max_dist').val(),
            	action: 'assignSlot',
            	id_carrier: $('input.delivery_option_radio:checked').val(),
            	},
        }).done(function(response) {
            if (response.success == true) {
                if(response.possible_delivery == 1)
            	{
            		$('#hd_creneau_day').html(response.msg);
                	$('#hd_creneau_selected').removeClass('alert-warning alert-danger hd_big_danger').addClass('alert-success');
                	$('#hd_legend, #hd_nav_buttons, #hd_dispo_head, #hd_dispo, #hd_box h3').slideUp();
            	}
            	else {
            		$('#hd_creneau_day').html(response.possible_delivery);
                	$('#hd_creneau_selected').removeClass('alert-success').addClass('hd_big_danger');
                	$('#hd_creneau td.selected').removeClass('dispo').addClass('red');
            	}
                $([document.documentElement, document.body]).animate({
                	//scrollTop: $('#hd_creneau_selected').offset().top-105
                },1000);
               
            } else {
                $('#hd_creneau_day').html(response.error);
            	$('#hd_creneau_selected').removeClass('alert-success').addClass('hd_big_danger');
            	$('#hd_creneau td.selected').removeClass('dispo').addClass('red'); 
            }
        }).fail(function() {
            alert('Erroor');
        });
    });
    
    $('body#order-opc').on('click', '#SubmitLogin, #submitAccount', function() {
    	setTimeout(function(){
    		$('input.delivery_option_radio:checked').parents('table').parent().append($('#hd_box'));
			_hideOrShow();
    	},1000);
    });
    
    $('#opc_payment_methods-content').on('click tap', '.payment_module a', function(e){
    	var link = this.href;
        var creneau = $('#hd_box').attr('data-creneau');
        var msg_error = null;
        
        if (_checkIdEC()) {
            if ($('#hd_box').attr('data-creneau') == 0) {
                $('#hd_creneau_selected').addClass('hd_big_danger');
                $([document.documentElement, document.body]).animate({
                    scrollTop: $('#carrier_area').offset().top-100
                },500);
                e.preventDefault();
            } else {
                e.preventDefault();
		    	$.ajax({
		            url: $('#hd_dispo').data('url'),
		            action: 'processCarrier',
		            type: 'json',
		            method: 'post',
		            data: {
		                action: 'processCarrier',
                        id_carrier: $('input.delivery_option_radio:checked').val()
		            },
		            success: function(data) {

		                if (data.status == 'success') {
		                    if (data.message.success == false) {
		                       $('#hd_creneau_selected').fancybox({
	                                content: data.message.alert,
	                            });
	                            $('#hd_creneau_selected').click();
		                    } else {
		                        window.location = link;
		                    }
		                } else {
		                    alert(data.message);
		                }
		                
		                $('#hd_dispo_overlay').hide();
		            }
		        });
            }
        }
    });
    
    $('#form').on('submit', function(e) {
        if ($(this).hasClass('ok')) { /*alert('tout est ok');*/
            return;
        }
        if (_checkIdEC()) {
	        var creneau = $('#hd_box').attr('data-creneau');
			if ($('#hd_box').attr('data-creneau') == 0) {
	            $('#hd_creneau_selected').addClass('hd_big_danger');
                $([document.documentElement, document.body]).animate({
                    scrollTop: $('#form').offset().top-100
                },500);
	            e.preventDefault();
	        } else {
	            e.preventDefault();
	            $.ajax({
	                url: $('#hd_dispo').data('url'),
	                action: 'processCarrier',
	                type: 'json',
	                method: 'post',
	                data: {
	                    action: 'processCarrier',
	                    id_carrier: $('input.delivery_option_radio:checked').val(),
	                },
	                success: function(data) {

	                    if (data.status == 'success') {
	                        if (data.message.success == false) { /*.preventDefault();	*/
	                            $('#hd_creneau_selected').fancybox({
	                                content: data.message.alert,
	                            });
	                            $('#hd_creneau_selected').click();
	                            $('#hd_shop_selected').removeClass('hd_big_danger').addClass('alert-success');
                                $('#hd_creneau_selected #hd_creneau_day').html('');
                                $('#hd_creneau_selected').removeClass('hd_big_danger').addClass('alert-warning');

	                        } else {
	                            $('#form').addClass('ok').submit();
	                        }
	                    } else {
	                        alert(data.message);
	                    }
	                }
	            });
	        }
        }
    });
    
    if(opc_checkout == 1)
	{
    	setTimeout(function(){
    		$('input.delivery_option_radio:checked').parents('table').parent().append($('#hd_box'));
			_hideOrShow();
    	},1000);
    }
    else
    { 
    	$('input.delivery_option_radio:checked').parents('table').parent().append($('#hd_box'));
		_hideOrShow();
    }

    function diplayOrHideColumns(current_start_column, current_nb_display_column, nb_colmuns) {
        for (var i = 2; i <= nb_colmuns + 1; i++) {
            if (i < current_start_column || i >= current_start_column + current_nb_display_column) {
                $('#hd_creneau tr td:nth-child(' + i + ')').hide();
            } else {
                $('#hd_creneau tr td:nth-child(' + i + ')').show();
            }
        }
        $('#hd_dispo_overlay').fadeOut();
    }
    
    if (_checkIdEC()) {
       _processCarrier(0);
    }
    
    $('#center_column').on('click tap', 'input.delivery_option_radio', function(e) {
    	if(opc_checkout == 1)
    	{
    		if (_checkIdEC()) {
        	setTimeout(function(){
		    		$('input.delivery_option_radio:checked').parents('table').parent().append($('#hd_box'));
		    		_processCarrier(0);
		    	},1000);
	    	}
	    	else {
	    		_reInitStore();
	    	}
        }
        else
        { 
        	$('input.delivery_option_radio:checked').parents('table').parent().append($('#hd_box'));
    		_hideOrShow();
    		if (!_checkIdEC()) {
    		//_initCookie();
    		_reInitStore();
    		}
    		else {
    			_processCarrier(0);
    		}
        }
    });
    
    function _processCarrier(force) {
    	$('#hd_dispo_overlay').show();
    	$.ajax({
            url: $('#hd_dispo').data('url'),
            action: 'processCarrier',
            type: 'json',
            method: 'post',
            data: {
                action: 'processCarrier',
                id_carrier: $('input.delivery_option_radio:checked').val(),
            },
            success: function(data) {

                if (data.status == 'success') {
 						if(Array.isArray(data.message.address_valid))
                    	{
                    		$('#address_max_dist').val(data.message.address_valid[0].distance);
                    		
                    		$('#hd_shop_selected').removeClass('hd_big_danger').addClass('alert-success');
                            $('#hd_creneau_selected #hd_creneau_day').html('');
                            $('#hd_box').attr('data-creneau', '0');
                            $('#hd_creneau_selected').removeClass('hd_big_danger alert-success').addClass('alert-warning');

                    		_createDaysTable(1);
                    		
                    		if(parseInt(data.message.nbr_to_display) > 30)
                            {
                                $('body').find('#hd_next_days, #hd_prev_days').hide().remove();
                                $('body').find('#hd_nav_buttons').addClass('calendar');
                            }
                            else
                            {
                                $('body').find('#delivery_date_calendar').hide().remove();
                            }
                    	}
                    	else {
                    		_createDaysTable(0);
                    		$('#hd_shop_selected').removeClass('alert-success').addClass('hd_big_danger');
                    		$('#hd_creneau_selected #hd_creneau_day').html(data.message.address_valid);
                    		$('#hd_creneau_selected').removeClass('alert-waring').addClass('hd_big_danger');
                    		$('#hd_dispo, #hd_dispo_head').html('');
                    		setTimeout(function(){
                    			$('#hd_legend, #hd_nav_buttons, #hd_dispo_head, #hd_dispo, #hd_box h3').slideUp();
                    		},500);
                    		$('#hd_dispo_overlay').fadeOut();
                    	}
					
                } else {
                    alert(data.message);
                }
            }
        });
    }
    
    function _checkIdEC() {
        if($('input.delivery_option_radio:checked').length > 0)
        {
            var id_selected_carrier = $('input.delivery_option_radio:checked').val();
            id_selected_carrier = id_selected_carrier.replace(",","");
            
            if($('body').find('#hd_id_carrier_'+id_selected_carrier).length > 0)
                return true;
                
            return false;
        }
    }

    function _createDaysTable(active) {
        $('#hd_dispo_overlay').show();
        if (_checkIdEC()) {
	        $.ajax({
	            url: $('#hd_dispo').data('url'),
	            action: 'initTable',
	            type: 'json',
	            method: 'post',
	            data: {
	                action: 'initTable',
	                id_carrier: $('input.delivery_option_radio:checked').val(),
	            },
	            success: function(data) {

	                if (data.status == 'success') {
	                	
	                	// On affiche en fonction du mode d'affichage retenu
	                	if(active == 1) {
	                		if(data.message.display_table == 1 || data.message.table_days.increment == 0)
		                	{
		                		_viewList(data.message.table_days);
		                	} 
		                	else
		                	{
		                		_viewTable(data.message.table_days);
		                		nb_colmuns = data.message.table_days.nb_days_view;
		                		diplayOrHideColumns(current_start_column, current_nb_display_column, nb_colmuns);
		                		changeclass(data.message.table_days);
		                    	carrence(data.message.table_days);
		                    	disabledButtonsDays();
		                    	reserved(data.message.table_days);
		                    	vacation(data.message.table_days);
		                    	//1.0.4 : delete the dist_max delivery
		                    	exclude_dist_max(data.message.table_days);
		                    	_adjustTableDisplay();
		                	}
		                	
		                    // On affiche en fonction du mode d'affichage retenu
		                	if(data.message.display_table == 1 || data.message.table_days.increment == 0)
		                	{
		                		changeclass(data.message.table_days);
		                		$('#hd_legend').hide().remove();
		                		$('#hd_nav_buttons').hide();
		                		reserved(data.message.table_days);
		                		vacation(data.message.table_days);
		                		//1.0.4 : delete the dist_max delivery
		                    	exclude_dist_max(data.message.table_days);
		                    	adjustListDisplay(data.message.table_days);
		                	}
		                	else {
		                		var nbr_days = Object.keys(data.message.table_days.days).length;
                                if(nbr_days > 7)
                                {
                                    $('#hd_nav_buttons').show();  
                                }
                                else
                                {
                                    $('#hd_nav_buttons').hide().remove();
                                }
		                	}
	                         
		                    if(data.message.creneau != null) {
		                        $('#hd_creneau_day').html(data.message.creneau);
		                        var creneau = data.message.creneau;
		                        var infos = creneau.split(" ");
		                        $('#hd_dispo td[data-date="'+infos[0]+' '+infos[1]+' '+infos[2]+'"][data-hour="'+infos[4]+'"]').addClass('selected');
		                        $('#hd_box').attr('data-creneau', '1');
		                    }
		                    
		                    if(data.message.display_table == 1 || data.message.table_days.increment == 0)
		                	{
			                    //1.2.1
			                    $([document.documentElement, document.body]).animate({
			                    	//scrollTop: $('#hd_creneau').offset().top-105
			                    },1000);
			                    
			                    var size = $('body').find('#hd_list_creneau td').length;
                                $('body').find('#hd_list_creneau').width(size*230+'px');
                                _adjustListScroll();
		                    }
		                    else
		                    {
		                    	//1.2.2
			                    $([document.documentElement, document.body]).animate({
			                    	//scrollTop: $('#hd_creneau').offset().top-65
			                    },1000);
		                    }
	                	}
	                	$('#hd_dispo_overlay').hide();
	                    
	                } else {
	                    alert(data.message);
	                }
	            }
	        });
        }

        $('#hd_creneau_selected, #hd_creneau, #hd_dispo, #hd_box').show();
    }
    
    function _adjustTableDisplay()
    {
    	// Adjut Table Display 
        $('body').find('#hd_creneau tbody tr').each(function(){
        	if($(this).children('td.dispo').length == 0)
        	{
        		$(this).css('height','1px').css('padding','0');
        		$(this).find('td').css('height','1px').css('padding','0');
        		$(this).find('td').text('');
        	}
        });
    }
    
    function _hideOrShow() {
    	if (_checkIdEC() && $('input.delivery_option_radio:checked').val() != 'undefined') {
	        $('#hd_shop_selected, #hd_creneau_selected, #hd_creneau, #hd_box, #hd_dispo_head').show();
	        $('#hd_box').slideDown();
       } else {
          	$('#hd_shop_selected, #hd_creneau_selected, #hd_creneau, #hd_box, #hd_dispo_head').hide();
          	$('#hd_box').attr('data-creneau','');
       }
       
       if (_checkIdEC()) {
	       $('body#order-opc').find('#opc_payment_methods #hd_creneau_day').text('');
	       $('body#order-opc').find('#opc_payment_methods #hd_creneau_selected').removeClass('alert-success').addClass('alert-warning');
	   }
	   
	   if($.trim($('body').find('#HOOK_TOP_PAYMENT #hd_creneau_day').text()) != '')
	    {
	    	$('body').find('#HOOK_TOP_PAYMENT #hd_creneau_selected').show();
	    }
    }
    
    function _reInitStore() {
   		//$('#hd_dispo_overlay').fadeIn();
        $.ajax({
            url: $('#hd_dispo').data('url'),
            action: 'reInitStore',
            type: 'json',
            method: 'post',
            data: {
                action: 'reInitStore',
            },
            success: function(data) {

                if (data.status == 'success') {
                    $('#hd_creneau_day').html('');
                    $('#hd_box').attr('data-creneau', '0');
                    $('#hd_creneau').remove();
                    $('#hd_creneau_selected').removeClass('alert-success').addClass('alert-warning');
                    $('#hd_creneau_selected').hide();
                    $('#hd_dispo_overlay').hide();
                    $('#hd_dispo_head').hide();
          			$('#hd_legend').hide();
					$('#hd_nav_buttons').hide();
                } else {
                    alert(data.message);
                }
            }
        });
    }

    $('body').on('click tap', '#hd_next_days', function() {
        current_start_column = Math.min(current_start_column + current_nb_display_column, nb_colmuns - current_nb_display_column + 2);
        diplayOrHideColumns(current_start_column, current_nb_display_column, nb_colmuns);
        if(parseInt(current_start_column+current_nb_display_column) >= nb_colmuns)
            $('body').find('#hd_next_days').addClass('selected');
            
        $('body').find('#hd_prev_days').removeClass('selected');
    });
    
    $('body').on('click tap', '#hd_prev_days', function() {
        current_start_column = Math.max(2, current_start_column - current_nb_display_column);
        diplayOrHideColumns(current_start_column, current_nb_display_column, nb_colmuns);
        if(current_start_column <= current_nb_display_column)
            $('body').find('#hd_prev_days').addClass('selected');
            
        $('body').find('#hd_next_days').removeClass('selected');
    });
    
    function disabledButtonsDays() {
        var date = new Date();
        var date_today = $.format.date(date, "E d MMM yyyy");
        $('#hd_prev_days, #hd_next_days').removeClass('disabled');
        if ($('#hd_creneau > thead > tr > td:nth-child(2)').html() == date_today) {
            $('#hd_prev_days').addClass('disabled');
        }
    }

    function _viewTable(message) {
        var html = '';
        html += '<table id="hd_creneau" class="col-sm-12"><thead>';
        html += '<td>H/J</td>';
        var size = Object.keys(message.days).length;
        for (var i = 1; i <= size; i++) {
            html += '<td data-col="'+ i +'" data-day="'+ message.days[i].dateen +'">' + message.days[i].day + " " + $.format.date("" + message.days[i].date, "d MMM yyyy") + '</td>';        }
        html += '</thead>';
        var sizeh = Object.keys(message.creneau).length;
        for (var h = 0; h < sizeh - 1; h++) {
            html += '<tr><td>' + $.format.date("0000-00-00 " + message.creneau[h] + "", "H:mm") + ' - ' + $.format.date("0000-00-00 " + message.creneau[h + 1] + "", "H:mm") + '</td>';
            for (var i = 1; i <= size; i++) {
                var additional_class = 'indispo';
                html += '<td class="creneau ' + additional_class + '" data-id-day="' + message.days[i].id_day + '" data-datetime="' + message.days[i].dateen + ' ' + message.creneau[h] + '" data-hour="' + message.creneau[h] + '" data-date="' + message.days[i].date + '" data-hour_end="00:00:00"></td>';
            }
            html += '</tr>';
        }
        html += '</table>';
        html += '<div class="clear"></div>';
            
        $('#hd_dispo').html(html);
    }
    
    function _viewList(message) {
        
        var html = '';
        var size = Object.keys(message.days).length;
        
        html += '<table id="hd_list_creneau" data-size="'+parseInt(size)+'" class="col-sm-12 viewList"><thead>';
        for (var i = 1; i <= size; i++) {
            html += '<td><span data-date="' + $.format.date("" + message.days[i].dateen, "d MMM yyyy") + '">' + message.days[i].day + " " + $.format.date("" + message.days[i].date, "d MMM yyyy") + '</span></td>';
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
            for (var i = 1; i <= size; i++) {
                // traiter dist_max également ?
                // traiter cas normal où créneau horaire
                if(message.days[i].op.length > 0)
                {
                    message.days[i].op = message.days[i].op.sort();
                    message.days[i].cl = message.days[i].cl.sort();
                    for(var j = 0; j < message.days[i].op.length; j++)
                    {
                        var additional_class = 'indispo';
                        html += '<span class="creneau ' + additional_class + '" data-date="'+message.days[i].dateen+'" data-id-day="' + message.days[i].id_day + '" data-datetime="' + message.days[i].dateen + ' ' + message.days[i].op[j] + ':00" data-hour="' + message.days[i].op[j] + ':00" data-hour_end="' + message.days[i].cl[j] + ':00" data-date="' + message.days[i].date + '" >';
                        //html += '' + $.format.date("0000-00-00 " + message.days[i].dateen + "", "H:mm") + ' - ' + $.format.date("0000-00-00 " + message.creneau[h + 1] + "", "H:mm") + '</span>';
                        html += '' + $.format.date("0000-00-00 " + message.days[i].op[j] + ":00", "H:mm") + ' - ' + $.format.date("0000-00-00 " + message.days[i].cl[j] + ":00", "H:mm") + '</span>';
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
        		//$(this).parent().remove();
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
        
        $('#hd_nav_buttons').hide().remove();
    }
    
    function _adjustListScroll() {
        // On affiche les premières dispos
        var first_date_available = $('body').find('#hd_dispo_head #hd_list_creneau tr td span:not(".disabled"):first');
        if(first_date_available.length > 0)
        {
            var distance_left = $(first_date_available).offset().left;
            var div_width = $(first_date_available).width();
            var dist_to_scroll = parseInt(distance_left-(div_width+230));
            // TEST FIX
            if(dist_to_scroll < 0)
            {
                $('#hd_dispo_head').scrollLeft(dist_to_scroll);
            }
            else
            {
                $('#hd_dispo_head').scrollLeft(dist_to_scroll);
            }
            $(first_date_available).trigger('click');
        }
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
                var date_creneau = new Date(datetime_creneau);
                var date_creneau_format = $.format.date(date_creneau, "yyyy-MM-dd");
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
                    if(message.creneau_limit > 0)
                    {
                        if(message.reserved[key]/message.creneau_limit >= 0.50)
                        {
                            $('[data-datetime="'+key+'"]').addClass('busy');
                        }
                    }
                } else if (message.reserved[key] >= message.creneau_limit) {
                    $('[data-datetime="'+key+'"]').removeClass('dispo').addClass('red indispo');
                }
		    }
        }
    }
    
    function refresh(time) {
        setTimeout(function () {
            // UPDATE 1.2.1 : Si on est sur l''étape de paiement, on force le choix d'un nouveau créneau après X min
            if($('body').find('#step_end').hasClass('step_current'))
            {
                $('body').find('#order_step li.four a').trigger('click');
            }
            else
            {
                window.location.reload(); 
            }    
            
        }, time*1000);
    }
    
     /*
     * IE FIX !
     */
    if (!String.prototype.padStart) {
	    String.prototype.padStart = function padStart(targetLength,padString) {
	        targetLength = targetLength>>0; //truncate if number or convert non-number to 0;
	        padString = String((typeof padString !== 'undefined' ? padString : ' '));
	        if (this.length > targetLength) {
	            return String(this);
	        }
	        else {
	            targetLength = targetLength-this.length;
	            if (targetLength > padString.length) {
	                padString += padString.repeat(targetLength/padString.length); //append to original to ensure we are longer than needed
	            }
	            return padString.slice(0,targetLength) + String(this);
	        }
	    };
	}
	
	function exclude_dist_max(message) {
    	
    	if(message.store_zip == 1)
    	{
    		$('#hd_creneau .creneau.dispo').each(function() {
	            	// On vérifie si la distance du jour est <= à la distance max
	        	var creneau_id_day = $(this).data('id-day');
	        	var hour_creneau = $(this).data('hour');
	        	
	        	for (var key in message.days) {
	        		if(creneau_id_day == message.days[key].id_day) {
	        			// On supprime déjà tous les jours non déservis par les CP
	        			if(creneau_id_day == message.days[key].id_day) {
		        			if(message.days[key].zips == 0) {
			                	$(this).removeClass('dispo');
			                   	$(this).addClass('indispo');
				            }
				            
				            // FIX : 1.0.5
                            if(message.days[key].op != undefined)
                            {
    				            var is_open = message.days[key].op.length;
    				            if(is_open > 0)
    				            {
    				            	for (var op in message.days[key].op) {
    						            // On test les périodes possibles sur la journée
    				    				if(hour_creneau < message.days[key].op[op] || hour_creneau >= message.days[key].cl[op]) {
    					                	is_open--;
    					        		}
    					        		if(is_open == 0)
    					        		{
    					        			$(this).removeClass('dispo');
    					                   	$(this).addClass('indispo');
    					        		}
    				        		}
    				            }
				            }
		        		}
	        		}
	            }
	        });
    	}
    	else
    	{
    		var dist_init = $('#address_max_dist').val();
	        $('#hd_creneau .creneau.dispo').each(function() {
	            	// On vérifie si la distance du jour est <= à la distance max
	        	var creneau_id_day = $(this).data('id-day');
	        	for (var key in message.days) {
	        		if(creneau_id_day == message.days[key].id_day) {
	        			if(dist_init > parseInt(message.days[key].dist_max)) {
		                	$(this).removeClass('dispo');
		                   	$(this).addClass('indispo');
			            }
	        		}
	            }
	        });
    	}
    }
    
    // Click sur CHANGE SLOT pour changer de créneau
    $('body').on('click tap', '#hd_creneau_selected .changeSlot', function(){
       $('#hd_legend, #hd_nav_buttons, #hd_dispo_head, #hd_dispo, #hd_box h3').slideDown();
       $('#hd_creneau_selected').removeClass('alert-success').addClass('alert-warning');
       $('#hd_creneau_selected #hd_creneau_day').html('');
       $('#hd_creneau .dispo').removeClass('selected');
       $('#hd_box').attr('data-creneau', 0);
    });
    
    // 1.2.0
    $('body').on('change','#delivery_date_calendar', function() {
        
        var new_date = $(this).val();
        current_start_column = $('body').find('#hd_creneau td[data-day="'+new_date+'"]').data('col');
        if(current_start_column != undefined)
        {
            $('#hd_dispo_overlay').show();
            setTimeout(function(){
                    diplayOrHideColumns(current_start_column+1, current_nb_display_column, nb_colmuns);
            },100);
        }
        else
        {
            $('body').find('#no_delivery_on_date').slideDown();
            $([document.documentElement, document.body]).animate({
                    scrollTop: $('#no_delivery_on_date').offset().top
                },1000
            );
            setTimeout(function(){
                $('body').find('#no_delivery_on_date').slideUp();
            }, 5000);
        }
    });
    
});
