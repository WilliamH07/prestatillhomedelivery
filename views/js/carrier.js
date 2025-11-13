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

    $('#HDverifyCreneau').appendTo('#js-delivery');

    $('#hd_nav_buttons').hide();

    $('#hd_prev_days').addClass('selected');

    var ps_url = $('#psd_base_url').val();

    var nb_colmuns;

    var current_start_column = 2;

    var current_nb_display_column = 7;

    

    setTimeout(function(){

        _hideOrShow();

    },500);

    

    // On recharge la page si elle reste affichée plus de 10 minutes pour éviter que le créneau ne devienne obsolète si on reste sur la page

    refresh(600);



    if($('#delivery_option_6:checked').length > 0) {
        $('#delivery_option_6:checked').parents('.delivery-option').next().append($('#hd_box'));
    }



    $('body').on('click tap', '#checkout-payment-step', function(e){

        if($(this).hasClass('no_slot_checked'))

        {

            $('#checkout-delivery-step span.step-edit').trigger('click');

        }

    });

    

    $('body').on('click tap', '#checkout-delivery-step span.step-edit', function() {

        _createDaysTable(1);

    });

    

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

                id_carrier: $('#delivery_option_6:checked').val()

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

            } else {

                $('#hd_creneau_day').html(response.error);

                $('#hd_creneau_selected').removeClass('alert-success').addClass('hd_big_danger');

                $('#hd_creneau td.selected').removeClass('dispo').addClass('red'); 

            }

            $([document.documentElement, document.body]).animate({

                    scrollTop: $('#hd_creneau_selected').offset().top-105

                },1000

            );

        }).fail(function() {

            //alert('Erroor');

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

                id_carrier: $('#delivery_option_6:checked').val()

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

                    scrollTop: $('#hd_creneau_selected').offset().top-105

                },1000);

            } else {

                //alert('error');

            }

        }).fail(function() {

            //alert('Erroor');

        });

    });

    

    $('#HDverifyCreneau').on('click tap', function(e) {

        var creneau = $('#hd_box').attr('data-creneau');

        var msg_error = null;

        

        if (_checkIdEC()) {

            if ($('#hd_box').attr('data-creneau') == 0) {

                $('#hd_creneau_selected').addClass('hd_big_danger');

                $([document.documentElement, document.body]).animate({

                    scrollTop: $('#hd_creneau').offset().top-100

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

                        id_carrier: $('#delivery_option_6:checked').val()

                    },

                    success: function(data) {
                        // DEBUG: Log complete response
                        console.log('=== PRESTATILL HOME DELIVERY DEBUG ===');
                        console.log('Complete AJAX Response:', data);

                        if (data.message && data.message.debug_logs) {
                            console.log('=== DEBUG LOGS ===');
                            console.log('ID Address:', data.message.debug_logs.id_address);
                            console.log('Address Loaded:', data.message.debug_logs.address_loaded);
                            console.log('Full Address:', data.message.debug_logs.full_address);
                            console.log('Address Details:', data.message.debug_logs.address_details);
                            console.log('Has Google API Key:', data.message.debug_logs.has_google_api_key);

                            if (data.message.debug_logs.has_google_api_key) {
                                console.log('Google API URL:', data.message.debug_logs.google_api_url);
                                console.log('Google API Response:', data.message.debug_logs.google_api_response);
                                console.log('Google Coordinates:', data.message.debug_logs.google_coordinates);
                                console.log('Google Status:', data.message.debug_logs.google_status);
                                console.log('Google Error:', data.message.debug_logs.google_error);
                            } else {
                                console.log('Nominatim URL:', data.message.debug_logs.nominatim_url);
                                console.log('Nominatim Raw Response:', data.message.debug_logs.nominatim_raw_response);
                                console.log('Nominatim JSON Response:', data.message.debug_logs.nominatim_json_response);
                                console.log('Nominatim Coordinates:', data.message.debug_logs.nominatim_coordinates);
                                console.log('Nominatim Error:', data.message.debug_logs.nominatim_error);
                            }

                            console.log('Final Coordinates:', data.message.debug_logs.final_coordinates);
                            console.log('ID Store Found:', data.message.debug_logs.id_store_found);
                            console.log('Error Type:', data.message.debug_logs.error_type);
                            console.log('Validation:', data.message.debug_logs.validation);
                            console.log('Error:', data.message.debug_logs.error);
                        }

                        console.log('Address Valid:', data.message.address_valid);
                        console.log('===================================');

                        if (data.status == 'success') {

                            if (data.message.success == false) {

                               $('.modal').addClass('in').show().find('.js-modal-content').html('Votre créneau de retrait n\'est plus disponible. <br />Merci d\'en choisir un autre.');

                               $('body').append('<div class="modal-backdrop in fade"></div>');

                            } else {

                               $('button[name=confirmDeliveryOption]').click();

                            }

                        } else {

                            //alert(data.message);

                        }

                    }

                });

            }

        }

    });

    

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

       _processCarrier();

    }

    

    function _processCarrier() {

        $('#hd_dispo_overlay').show();

        $.ajax({

            url: $('#hd_dispo').data('url'),

            action: 'processCarrier',

            type: 'json',

            method: 'post',

            data: {

                action: 'processCarrier',

                id_carrier: $('#delivery_option_6:checked').val(),

            },

            success: function(data) {
                // DEBUG: Log complete response
                console.log('=== PRESTATILL HOME DELIVERY DEBUG (MAIN LOAD) ===');
                console.log('Complete AJAX Response:', data);

                if (data.message && data.message.debug_logs) {
                    console.log('=== DEBUG LOGS ===');
                    console.log('Zones Enabled:', data.message.debug_logs.zones_enabled);
                    console.log('ID Address:', data.message.debug_logs.id_address);
                    console.log('Address Loaded:', data.message.debug_logs.address_loaded);
                    console.log('Full Address:', data.message.debug_logs.full_address);
                    console.log('Address Details:', data.message.debug_logs.address_details);

                    // Afficher les tentatives de géocodage
                    console.log('--- GEOCODING ATTEMPTS ---');
                    console.log('Attempts:', data.message.debug_logs.geocoding_attempts);

                    // Nominatim
                    if (data.message.debug_logs.nominatim_url) {
                        console.log('1️⃣ Nominatim URL:', data.message.debug_logs.nominatim_url);
                        console.log('Nominatim Response:', data.message.debug_logs.nominatim_raw_response);
                        console.log('Nominatim Coordinates:', data.message.debug_logs.nominatim_coordinates);
                        console.log('Nominatim Error:', data.message.debug_logs.nominatim_error);
                    }

                    // Geocode.Maps.Co
                    if (data.message.debug_logs.geocode_maps_url) {
                        console.log('2️⃣ Geocode.Maps.Co URL:', data.message.debug_logs.geocode_maps_url);
                        console.log('Geocode.Maps.Co Response:', data.message.debug_logs.geocode_maps_raw_response);
                        console.log('Geocode.Maps.Co Coordinates:', data.message.debug_logs.geocode_maps_coordinates);
                        console.log('Geocode.Maps.Co Error:', data.message.debug_logs.geocode_maps_error);
                    }

                    // Google Maps
                    if (data.message.debug_logs.google_api_url) {
                        console.log('3️⃣ Google Maps URL:', data.message.debug_logs.google_api_url);
                        console.log('Google Maps Response:', data.message.debug_logs.google_api_response);
                        console.log('Google Maps Coordinates:', data.message.debug_logs.google_coordinates);
                        console.log('Google Maps Error:', data.message.debug_logs.google_error);
                    }

                    console.log('--- FINAL RESULT ---');
                    console.log('Final Coordinates:', data.message.debug_logs.final_coordinates);

                    // Display zones search parameters
                    if (data.message.debug_logs.search_params) {
                        console.log('--- ZONES SEARCH PARAMETERS ---');
                        console.log('ID Shop:', data.message.debug_logs.search_params.id_shop);
                        console.log('ID Carrier:', data.message.debug_logs.search_params.id_carrier);
                        if (data.message.debug_logs.sql_query) {
                            console.log('SQL Query:', data.message.debug_logs.sql_query);
                        }
                    }

                    // Display zones information
                    if (data.message.debug_logs.active_zones !== undefined) {
                        console.log('--- DELIVERY ZONES ANALYSIS ---');
                        console.log('Total Active Zones Found:', data.message.debug_logs.zones_count);

                        data.message.debug_logs.active_zones.forEach(function(zone, index) {
                            console.log('\n🗺️ Zone ' + (index + 1) + ':', zone.zone_name);
                            console.log('  ID:', zone.id_delivery_zone);
                            console.log('  Type:', zone.zone_type);
                            console.log('  Store ID:', zone.id_store);
                            console.log('  Carrier ID:', zone.id_carrier);
                            console.log('  Active:', zone.active);
                            console.log('  Priority:', zone.priority);

                            if (zone.zone_type === 'polygon' && zone.zone_data) {
                                console.log('  Polygon Points (' + zone.zone_data.length + ' vertices):');

                                // Calculate polygon bounds
                                let minLat = Math.min(...zone.zone_data.map(p => p.lat));
                                let maxLat = Math.max(...zone.zone_data.map(p => p.lat));
                                let minLng = Math.min(...zone.zone_data.map(p => p.lng));
                                let maxLng = Math.max(...zone.zone_data.map(p => p.lng));

                                console.log('  📐 Polygon Bounds:');
                                console.log('    Latitude Range:', minLat.toFixed(7), 'to', maxLat.toFixed(7));
                                console.log('    Longitude Range:', minLng.toFixed(7), 'to', maxLng.toFixed(7));

                                // Compare with test point
                                if (data.message.debug_logs.final_coordinates) {
                                    let testLat = parseFloat(data.message.debug_logs.final_coordinates.latitude);
                                    let testLng = parseFloat(data.message.debug_logs.final_coordinates.longitude);

                                    console.log('  📍 Test Point:', testLat.toFixed(7), ',', testLng.toFixed(7));

                                    let latInRange = testLat >= minLat && testLat <= maxLat;
                                    let lngInRange = testLng >= minLng && testLng <= maxLng;

                                    if (latInRange && lngInRange) {
                                        console.log('  ✅ Test point is within polygon bounding box');
                                    } else {
                                        console.log('  ❌ Test point is OUTSIDE polygon bounding box:');
                                        if (!latInRange) {
                                            if (testLat < minLat) {
                                                console.log('    ⬇️ Point is', (minLat - testLat).toFixed(7), 'degrees SOUTH of polygon (≈', Math.round((minLat - testLat) * 111000), 'meters)');
                                            } else {
                                                console.log('    ⬆️ Point is', (testLat - maxLat).toFixed(7), 'degrees NORTH of polygon (≈', Math.round((testLat - maxLat) * 111000), 'meters)');
                                            }
                                        }
                                        if (!lngInRange) {
                                            if (testLng < minLng) {
                                                console.log('    ⬅️ Point is', (minLng - testLng).toFixed(7), 'degrees WEST of polygon (≈', Math.round((minLng - testLng) * 111000), 'meters)');
                                            } else {
                                                console.log('    ➡️ Point is', (testLng - maxLng).toFixed(7), 'degrees EAST of polygon (≈', Math.round((testLng - maxLng) * 111000), 'meters)');
                                            }
                                        }
                                    }
                                }

                                // Display all polygon points
                                console.log('  All Polygon Coordinates:');
                                zone.zone_data.forEach(function(point, idx) {
                                    console.log('    Point ' + (idx + 1) + ':', point.lat.toFixed(7), ',', point.lng.toFixed(7));
                                });
                            } else if (zone.zone_type === 'circle' && zone.zone_data) {
                                console.log('  Circle Center:', zone.zone_data.center.lat + ',' + zone.zone_data.center.lng);
                                console.log('  Radius:', zone.zone_data.radius, 'meters');
                            } else if (zone.zone_type === 'rectangle' && zone.zone_data) {
                                console.log('  Rectangle Bounds:', zone.zone_data.bounds);
                            }
                        });
                        console.log('\n--- END ZONES ANALYSIS ---\n');
                    }

                    console.log('ID Store Found:', data.message.debug_logs.id_store_found);

                    // Display store information if found
                    if (data.message.debug_logs.store_info) {
                        console.log('--- RESTAURANT INFORMATION ---');
                        console.log('🏪 Restaurant Name:', data.message.debug_logs.store_info.name);
                        console.log('Address:', data.message.debug_logs.store_info.address1);
                        if (data.message.debug_logs.store_info.address2) {
                            console.log('Address 2:', data.message.debug_logs.store_info.address2);
                        }
                        console.log('City:', data.message.debug_logs.store_info.postcode, data.message.debug_logs.store_info.city);
                        if (data.message.debug_logs.store_info.phone) {
                            console.log('Phone:', data.message.debug_logs.store_info.phone);
                        }
                        if (data.message.debug_logs.store_info.hours) {
                            console.log('Hours:', data.message.debug_logs.store_info.hours);
                        }
                    }

                    console.log('Error Type:', data.message.debug_logs.error_type);
                    console.log('Validation:', data.message.debug_logs.validation);
                    console.log('Error:', data.message.debug_logs.error);
                    console.log('Zones Disabled Message:', data.message.debug_logs.zones_disabled_message);
                }

                console.log('Address Valid:', data.message.address_valid);
                console.log('===================================');



                if (data.status == 'success') {

                    //console.log(data.message.hd_msg);

                    if($('#checkout-delivery-step').hasClass('-current') || data.message.hd_msg == '')

                    {

                        if(Array.isArray(data.message.address_valid))

                        {

                            $('#address_max_dist').val(data.message.address_valid[0].distance);



                            if(!$('#checkout-delivery-step').hasClass('-current')) {

                                $('#checkout-delivery-step span.step-edit').trigger('click');

                            }



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

                            if(!$('#checkout-delivery-step').hasClass('-current')) {

                                $('#checkout-delivery-step span.step-edit').trigger('click');

                            }

                            _createDaysTable(0);

                            $('#hd_shop_selected').removeClass('alert-success').addClass('hd_big_danger');

                            $('#hd_creneau_selected #hd_creneau_day').html(data.message.address_valid);

                            $('#hd_creneau_selected').removeClass('alert-warning alert-success').addClass('hd_big_danger');

                            $('#hd_legend, #hd_nav_buttons, #hd_dispo_head, #hd_dispo, #hd_box h3').slideUp();

                            $('#hd_dispo, #hd_dispo_head').html('');

                            $('#hd_box').attr('data-creneau', '0');

                            $('#hd_dispo_overlay').fadeOut();

                        }

                    }/*

                    else if(data.message.hd_msg != '')

                    {

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

                    }*/

                } else {

                    //alert(data.message);

                }

            }

        });

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

                    id_carrier: $('#delivery_option_6:checked').val(),

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

                            //_processCarrier();

                             

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

                                    scrollTop: $('#hd_creneau').offset().top-105

                                },1000);

                                var size = $('body').find('#hd_list_creneau td').length;

                                $('body').find('#hd_list_creneau').width(size*230+'px');

                                _adjustListScroll();



                            }

                            else

                            {

                                //1.2.2

                                $([document.documentElement, document.body]).animate({

                                    scrollTop: $('#hd_creneau').offset().top-65

                                },1000);

                            }

                        }

                        

                        $('#hd_dispo_overlay').hide();

                        

                    } else {

                        //alert(data.message);

                    }

                }

            });

        }



        $('#hd_creneau_selected, #hd_creneau, #hd_box, #HDverifyCreneau, #hd_dispo_head, #hd_dispo').show();

    }

    

    function _hideOrShow() {

        if (_checkIdEC()) {

            $('#hd_creneau_selected, #hd_dispo_head, #hd_creneau, #hd_box, #HDverifyCreneau,#hd_legend,#hd_shop_selected,#hd_creneau_selected, #hd_creneau_day').show();

            $('button[name=confirmDeliveryOption]').hide();

        } else {

           $('#hd_creneau_selected, #hd_dispo_head, #hd_creneau, #hd_box, #HDverifyCreneau,#hd_legend,#hd_shop_selected,#hd_creneau_selected, #hd_creneau_day').hide();

           if(_checkIdEC()) {

                $('button[name=confirmDeliveryOption]').hide();

           }

           else 

           {

                if($('body').find('#verifyCreneau').css('display') == 'none' || $('body').find('#verifyCreneau').css('display') == undefined)

                    $('button[name=confirmDeliveryOption]').show();

           }

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

                    $('#hd_creneau_day').html('').hide();

                    $('#hd_box').attr('data-creneau', '0').hide();

                    $('#hd_creneau').remove();

                    $('#hd_creneau_selected').removeClass('alert-success').addClass('alert-warning');

                    $('#hd_dispo_overlay').hide();

                    $('#hd_dispo_head').hide();

                    $('#hd_legend').hide();

                    $('#HDverifyCreneau').hide();



                } else {

                    //alert(data.message);

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

    

    /*

     * On click on delivery option when drive carrier not checked on loading

     */

    $('body').on('click tap', 'input[name="delivery_option[16]"]', function() {
            // Verifier d'abord si le transporteur selectionne correspond a ce module
            if ($(this).attr('id') === 'delivery_option_6') {
                $('#delivery_option_6:checked').parents('.delivery-option').next().append($('#hd_box'));
                _hideOrShow();
                _processCarrier();
            }
            else {
                _hideOrShow();
                _reInitStore();
            }
    });

    

    // 1.2.6

    $('body').on('click tap', '#checkout-delivery-step h1', function() {

        if (_checkIdEC()) {

            _processCarrier();

        }

        else

        {

            _reInitStore();

        }

    });

    

    function _checkIdEC() {

        // Vérifie simplement si le transporteur "Livraison à domicile" (ID 6) est coché

        return $('#delivery_option_6:checked').length > 0;

    }



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

            html += '<td data-col="'+ i +'" data-day="'+ message.days[i].dateen +'">' + message.days[i].day + '</td>';

        }

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

            for (var i = 1; i <= size; i++) {

                

                // on est dans le cadre des CP

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

                    /*console.log(

                        [message.open[e],

                        $(this).data('idDay'),

                        message.open[e].id_day,

                        message.open[e].nonstop],

                        $(this).data('hour'),

                        message.open[e].hour_open_am,

                        message.open[e].hour_close_pm

                       );*/

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

                $('#hd_dispo_head').scrollLeft(-dist_to_scroll);

            }

            else

            {

                $('#hd_dispo_head').scrollLeft(dist_to_scroll-230);

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

            if($('#checkout-payment-step').hasClass('-current') || $('#checkout-payment-step').hasClass('js-current-step'))

            {

                $('#checkout-delivery-step span.step-edit').trigger('click');

            }

            else

            {

                window.location.reload(); 

            }    

            

        }, time*1000);

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

                            

                            // FIX : 1.0.6

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

                    if(creneau_id_day == parseInt(message.days[key].id_day)) {

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