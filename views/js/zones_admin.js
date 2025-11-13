/**
 * Prestatill Home Delivery - Zones Management (Admin)
 *
 * @author    Laurent Baumgartner
 * @copyright ADAM & DEV SAS
 * @license   https://opensource.org/licenses/AFL-3.0
 */

var ZonesManagement = (function() {
    'use strict';

    var map;
    var drawingManager;
    var currentShape = null;
    var currentEditingZone = null;
    var zones = [];
    var shapes = {};
    var infoWindow;
    var defaultCenter = { lat: 44.833328, lng: -0.56667 }; // Bordeaux par défaut

    /**
     * Initialise la carte Google Maps
     */
    function initMap() {
        if (typeof google === 'undefined' || !google.maps) {
            console.error('Google Maps API not loaded');
            return;
        }

        map = new google.maps.Map(document.getElementById('zones_map'), {
            center: defaultCenter,
            zoom: 12,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            mapTypeControl: true,
            streetViewControl: true,
            fullscreenControl: true
        });

        infoWindow = new google.maps.InfoWindow();

        // Initialiser le Drawing Manager
        drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: null,
            drawingControl: false,
            polygonOptions: {
                fillColor: '#FF0000',
                fillOpacity: 0.35,
                strokeWeight: 2,
                strokeColor: '#FF0000',
                clickable: true,
                editable: true,
                zIndex: 1
            },
            circleOptions: {
                fillColor: '#FF0000',
                fillOpacity: 0.35,
                strokeWeight: 2,
                strokeColor: '#FF0000',
                clickable: true,
                editable: true,
                zIndex: 1
            },
            rectangleOptions: {
                fillColor: '#FF0000',
                fillOpacity: 0.35,
                strokeWeight: 2,
                strokeColor: '#FF0000',
                clickable: true,
                editable: true,
                zIndex: 1
            }
        });

        drawingManager.setMap(map);

        // Événement lorsqu'une forme est dessinée
        google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
            handleShapeDrawn(event);
        });

        // Centrer la carte sur le premier store si disponible
        if (zonesManagementData.stores && zonesManagementData.stores.length > 0) {
            var firstStore = zonesManagementData.stores[0];
            if (firstStore.latitude && firstStore.longitude) {
                map.setCenter({
                    lat: parseFloat(firstStore.latitude),
                    lng: parseFloat(firstStore.longitude)
                });
            }
        }

        // Charger les zones existantes
        loadExistingZones();
    }

    /**
     * Gère une forme qui vient d'être dessinée
     */
    function handleShapeDrawn(event) {
        // Supprimer la forme précédente si elle existe
        if (currentShape) {
            currentShape.setMap(null);
        }

        currentShape = event.overlay;
        currentShape.type = event.type;

        // Appliquer la couleur sélectionnée
        var color = document.getElementById('zone_color').value;
        currentShape.setOptions({
            fillColor: color,
            strokeColor: color
        });

        // Rendre la forme éditable
        currentShape.setEditable(true);

        // Activer le bouton de sauvegarde
        document.getElementById('save_zone').disabled = false;

        // Désactiver le mode de dessin
        drawingManager.setDrawingMode(null);

        // Ajouter un listener pour la suppression
        google.maps.event.addListener(currentShape, 'rightclick', function() {
            if (confirm('Supprimer cette zone?')) {
                currentShape.setMap(null);
                currentShape = null;
                document.getElementById('save_zone').disabled = true;
            }
        });
    }

    /**
     * Charge les zones existantes depuis la base de données
     */
    function loadExistingZones() {
        $.ajax({
            url: zonesManagementData.moduleAjaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'getZones',
                ajax: true,
                id_shop: zonesManagementData.idShop
            },
            success: function(response) {
                console.log('Load zones response:', response);

                // La réponse est dans response.message
                var result = response.message || response;

                if (result.success && result.zones) {
                    zones = result.zones;
                    displayZonesOnMap(zones);
                    updateZonesList(zones);
                } else {
                    console.error('No zones found or error:', result);
                    updateZonesList([]);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading zones:', error);
                console.error('Response:', xhr.responseText);
                updateZonesList([]);
            }
        });
    }

    /**
     * Affiche les zones sur la carte
     */
    function displayZonesOnMap(zonesData) {
        // Supprimer les formes existantes
        for (var key in shapes) {
            if (shapes.hasOwnProperty(key)) {
                shapes[key].setMap(null);
            }
        }
        shapes = {};

        // Ajouter chaque zone à la carte
        zonesData.forEach(function(zone) {
            var shape;
            var zoneData;

            try {
                // Les données peuvent être échappées - essayer de les nettoyer
                var cleanData = zone.zone_data;

                // Vérifier si les données sont déjà un objet
                if (typeof cleanData === 'object') {
                    zoneData = cleanData;
                } else if (typeof cleanData === 'string') {
                    // Si les données contiennent des \" au lieu de ", les remplacer
                    if (cleanData.indexOf('\\"') !== -1) {
                        cleanData = cleanData.replace(/\\"/g, '"');
                    }
                    // Essayer de parser le JSON
                    zoneData = JSON.parse(cleanData);
                } else {
                    throw new Error('Zone data is neither object nor string');
                }
            } catch (e) {
                console.error('Error parsing zone data for zone ' + zone.id_delivery_zone + ':', e);
                console.error('Zone data type:', typeof zone.zone_data);
                console.error('Zone data value:', zone.zone_data);
                // Passer à la zone suivante
                return;
            }

            switch(zone.zone_type) {
                case 'polygon':
                    var paths = zoneData.map(function(point) {
                        return { lat: parseFloat(point.lat), lng: parseFloat(point.lng) };
                    });
                    shape = new google.maps.Polygon({
                        paths: paths,
                        strokeColor: zone.zone_color || '#FF0000',
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: zone.zone_color || '#FF0000',
                        fillOpacity: 0.35,
                        editable: false,
                        clickable: true,
                        zIndex: parseInt(zone.priority) || 0
                    });
                    break;

                case 'circle':
                    shape = new google.maps.Circle({
                        center: {
                            lat: parseFloat(zoneData.center.lat),
                            lng: parseFloat(zoneData.center.lng)
                        },
                        radius: parseFloat(zoneData.radius),
                        strokeColor: zone.zone_color || '#FF0000',
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: zone.zone_color || '#FF0000',
                        fillOpacity: 0.35,
                        editable: false,
                        clickable: true,
                        zIndex: parseInt(zone.priority) || 0
                    });
                    break;

                case 'rectangle':
                    shape = new google.maps.Rectangle({
                        bounds: {
                            north: parseFloat(zoneData.bounds.north),
                            south: parseFloat(zoneData.bounds.south),
                            east: parseFloat(zoneData.bounds.east),
                            west: parseFloat(zoneData.bounds.west)
                        },
                        strokeColor: zone.zone_color || '#FF0000',
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: zone.zone_color || '#FF0000',
                        fillOpacity: 0.35,
                        editable: false,
                        clickable: true,
                        zIndex: parseInt(zone.priority) || 0
                    });
                    break;
            }

            if (shape) {
                shape.setMap(map);
                shape.zoneId = zone.id_delivery_zone;
                shapes[zone.id_delivery_zone] = shape;

                // Ajouter un écouteur de clic pour éditer la zone
                google.maps.event.addListener(shape, 'click', function() {
                    editZone(zone);
                });

                // Ajouter une info-bulle au survol
                google.maps.event.addListener(shape, 'mouseover', function(event) {
                    var storeName = getStoreName(zone.id_store);
                    infoWindow.setContent(
                        '<div class="zone-info">' +
                        '<h4>' + zone.zone_name + '</h4>' +
                        '<p><strong>Restaurant:</strong> ' + storeName + '</p>' +
                        '<p><strong>Type:</strong> ' + zone.zone_type + '</p>' +
                        '<p><strong>Priority:</strong> ' + zone.priority + '</p>' +
                        '</div>'
                    );
                    infoWindow.setPosition(event.latLng);
                    infoWindow.open(map);
                });

                google.maps.event.addListener(shape, 'mouseout', function() {
                    infoWindow.close();
                });
            }
        });
    }

    /**
     * Récupère le nom d'un restaurant à partir de son ID
     */
    function getStoreName(idStore) {
        // Vérifier que stores est bien un tableau
        if (!zonesManagementData.stores || !Array.isArray(zonesManagementData.stores)) {
            console.error('Stores data is not an array:', zonesManagementData.stores);
            return 'Unknown';
        }

        var store = zonesManagementData.stores.find(function(s) {
            return s.id_store == idStore;
        });
        return store ? store.name + ' - ' + store.city : 'Unknown';
    }

    /**
     * Récupère le nom d'un transporteur à partir de son ID
     */
    function getCarrierName(idCarrier) {
        if (idCarrier == 0) return 'Tous les transporteurs';

        // Vérifier que carriers est bien un tableau
        if (!zonesManagementData.carriers || !Array.isArray(zonesManagementData.carriers)) {
            console.error('Carriers data is not an array:', zonesManagementData.carriers);
            return 'Unknown';
        }

        var carrier = zonesManagementData.carriers.find(function(c) {
            return c.id_reference == idCarrier;
        });
        return carrier ? carrier.name : 'Unknown';
    }

    /**
     * Met à jour la liste des zones dans le tableau
     */
    function updateZonesList(zonesData) {
        var tbody = document.getElementById('zones_list_body');
        tbody.innerHTML = '';

        if (zonesData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No zones defined yet</td></tr>';
            return;
        }

        zonesData.forEach(function(zone) {
            var tr = document.createElement('tr');
            tr.dataset.zoneId = zone.id_delivery_zone;

            var activeStatus = zone.active == 1
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>';

            tr.innerHTML =
                '<td>' + zone.id_delivery_zone + '</td>' +
                '<td>' + zone.zone_name + '</td>' +
                '<td>' + getStoreName(zone.id_store) + '</td>' +
                '<td><span class="badge">' + zone.zone_type + '</span></td>' +
                '<td>' + getCarrierName(zone.id_carrier) + '</td>' +
                '<td>' + zone.priority + '</td>' +
                '<td>' + activeStatus + '</td>' +
                '<td>' +
                '   <div class="btn-group">' +
                '       <button type="button" class="btn btn-default btn-xs edit-zone" data-zone-id="' + zone.id_delivery_zone + '">' +
                '           <i class="icon-edit"></i> Edit' +
                '       </button>' +
                '       <button type="button" class="btn btn-danger btn-xs delete-zone" data-zone-id="' + zone.id_delivery_zone + '">' +
                '           <i class="icon-trash"></i> Delete' +
                '       </button>' +
                '   </div>' +
                '</td>';

            tbody.appendChild(tr);
        });

        // Ajouter les événements aux boutons
        attachZoneActions();
    }

    /**
     * Attache les événements aux boutons d'action des zones
     */
    function attachZoneActions() {
        $('.edit-zone').on('click', function() {
            var zoneId = $(this).data('zone-id');
            var zone = zones.find(function(z) {
                return z.id_delivery_zone == zoneId;
            });
            if (zone) {
                editZone(zone);
            }
        });

        $('.delete-zone').on('click', function() {
            var zoneId = $(this).data('zone-id');
            if (confirm('Are you sure you want to delete this zone?')) {
                deleteZone(zoneId);
            }
        });
    }

    /**
     * Édite une zone existante
     */
    function editZone(zone) {
        currentEditingZone = zone;

        // Remplir le formulaire
        document.getElementById('zone_name').value = zone.zone_name;
        document.getElementById('zone_store').value = zone.id_store;
        document.getElementById('zone_carrier').value = zone.id_carrier;
        document.getElementById('zone_color').value = zone.zone_color || '#FF0000';
        document.getElementById('zone_priority').value = zone.priority;

        // Activer/désactiver
        if (zone.active == 1) {
            document.getElementById('zone_active_on').checked = true;
        } else {
            document.getElementById('zone_active_off').checked = true;
        }

        // Jours de livraison
        $('.zone_day_checkbox').prop('checked', false);
        if (zone.delivery_days) {
            var days = zone.delivery_days.split(',');
            days.forEach(function(day) {
                $('input[name="zone_days[]"][value="' + day + '"]').prop('checked', true);
            });
        }

        // Mettre la forme en mode édition
        var shape = shapes[zone.id_delivery_zone];
        if (shape) {
            shape.setEditable(true);
            currentShape = shape;
            currentShape.type = zone.zone_type;

            // Centrer la carte sur la zone
            if (zone.zone_type === 'circle') {
                map.setCenter(shape.getCenter());
            } else if (zone.zone_type === 'polygon') {
                var bounds = new google.maps.LatLngBounds();
                shape.getPath().forEach(function(latLng) {
                    bounds.extend(latLng);
                });
                map.fitBounds(bounds);
            } else if (zone.zone_type === 'rectangle') {
                map.fitBounds(shape.getBounds());
            }

            document.getElementById('save_zone').disabled = false;
        }

        // Scroll vers le formulaire
        $('html, body').animate({
            scrollTop: $('#zones_management_form').offset().top - 100
        }, 500);
    }

    /**
     * Supprime une zone
     */
    function deleteZone(zoneId) {
        $.ajax({
            url: zonesManagementData.moduleAjaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'deleteZone',
                ajax: true,
                id_zone: zoneId,
                id_shop: zonesManagementData.idShop
            },
            success: function(response) {
                // La réponse est dans response.message
                var result = response.message || response;

                if (result.success) {
                    // Supprimer la forme de la carte
                    if (shapes[zoneId]) {
                        shapes[zoneId].setMap(null);
                        delete shapes[zoneId];
                    }

                    // Recharger les zones
                    loadExistingZones();

                    alert('Zone deleted successfully!');
                } else {
                    alert('Error deleting zone: ' + (result.error || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Error deleting zone:', error);
                alert('Error deleting zone. Please try again.');
            }
        });
    }

    /**
     * Sauvegarde la zone actuelle
     */
    function saveZone() {
        console.log('=== SAVE ZONE DEBUG ===');
        console.log('Current shape:', currentShape);

        if (!currentShape) {
            alert('Please draw a zone first!');
            return;
        }

        // Validation
        var zoneName = document.getElementById('zone_name').value.trim();
        var zoneStore = document.getElementById('zone_store').value;

        console.log('Zone name:', zoneName);
        console.log('Zone store:', zoneStore);

        if (!zoneName) {
            alert('Please enter a zone name!');
            return;
        }

        if (!zoneStore) {
            alert('Please select a restaurant/store!');
            return;
        }

        // Récupérer les données de la forme
        var zoneData;
        var zoneType = currentShape.type;

        console.log('Zone type:', zoneType);

        switch(zoneType) {
            case google.maps.drawing.OverlayType.POLYGON:
                var paths = [];
                currentShape.getPath().forEach(function(latLng) {
                    paths.push({ lat: latLng.lat(), lng: latLng.lng() });
                });
                zoneData = JSON.stringify(paths);
                zoneType = 'polygon';
                break;

            case google.maps.drawing.OverlayType.CIRCLE:
                zoneData = JSON.stringify({
                    center: {
                        lat: currentShape.getCenter().lat(),
                        lng: currentShape.getCenter().lng()
                    },
                    radius: currentShape.getRadius()
                });
                zoneType = 'circle';
                break;

            case google.maps.drawing.OverlayType.RECTANGLE:
                var bounds = currentShape.getBounds();
                zoneData = JSON.stringify({
                    bounds: {
                        north: bounds.getNorthEast().lat(),
                        south: bounds.getSouthWest().lat(),
                        east: bounds.getNorthEast().lng(),
                        west: bounds.getSouthWest().lng()
                    }
                });
                zoneType = 'rectangle';
                break;

            default:
                if (currentShape.type === 'polygon') {
                    var paths = [];
                    currentShape.getPath().forEach(function(latLng) {
                        paths.push({ lat: latLng.lat(), lng: latLng.lng() });
                    });
                    zoneData = JSON.stringify(paths);
                    zoneType = 'polygon';
                } else if (currentShape.type === 'circle') {
                    zoneData = JSON.stringify({
                        center: {
                            lat: currentShape.getCenter().lat(),
                            lng: currentShape.getCenter().lng()
                        },
                        radius: currentShape.getRadius()
                    });
                    zoneType = 'circle';
                } else if (currentShape.type === 'rectangle') {
                    var bounds = currentShape.getBounds();
                    zoneData = JSON.stringify({
                        bounds: {
                            north: bounds.getNorthEast().lat(),
                            south: bounds.getSouthWest().lat(),
                            east: bounds.getNorthEast().lng(),
                            west: bounds.getSouthWest().lng()
                        }
                    });
                    zoneType = 'rectangle';
                }
                break;
        }

        // Récupérer les jours de livraison
        var deliveryDays = [];
        $('.zone_day_checkbox:checked').each(function() {
            deliveryDays.push($(this).val());
        });

        console.log('Zone data (before JSON):', zoneData);
        console.log('Delivery days:', deliveryDays);

        // Préparer les données
        var postData = {
            action: currentEditingZone ? 'updateZone' : 'saveZone',
            ajax: true,
            zone_name: zoneName,
            id_store: zoneStore,
            id_carrier: document.getElementById('zone_carrier').value,
            zone_type: zoneType,
            zone_data: zoneData,
            zone_color: document.getElementById('zone_color').value,
            priority: document.getElementById('zone_priority').value,
            active: $('input[name="zone_active"]:checked').val(),
            delivery_days: deliveryDays.join(','),
            id_shop: zonesManagementData.idShop,
            id_lang: zonesManagementData.idLang
        };

        if (currentEditingZone) {
            postData.id_zone = currentEditingZone.id_delivery_zone;
        }

        console.log('Post data:', postData);
        console.log('AJAX URL:', zonesManagementData.moduleAjaxUrl);

        // Envoyer la requête AJAX
        $.ajax({
            url: zonesManagementData.moduleAjaxUrl,
            type: 'POST',
            dataType: 'json',
            data: postData,
            success: function(response) {
                console.log('AJAX Response:', response);
                console.log('Response type:', typeof response);

                // La réponse est dans response.message
                var result = response.message || response;

                if (result.success) {
                    alert(currentEditingZone ? 'Zone updated successfully!' : 'Zone saved successfully!');

                    // Réinitialiser le formulaire
                    resetForm();

                    // Recharger les zones
                    loadExistingZones();
                } else {
                    console.error('Error response:', response);
                    alert('Error saving zone: ' + (result.error || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('XHR:', xhr);
                console.error('Status:', status);
                console.error('Response Text:', xhr.responseText);
                alert('Error saving zone. Please check console for details.');
            }
        });
    }

    /**
     * Réinitialise le formulaire
     */
    function resetForm() {
        document.getElementById('zone_name').value = '';
        document.getElementById('zone_store').value = '';
        document.getElementById('zone_carrier').value = '0';
        document.getElementById('zone_color').value = '#FF0000';
        document.getElementById('zone_priority').value = '0';
        document.getElementById('zone_active_on').checked = true;
        $('.zone_day_checkbox').prop('checked', false);

        if (currentShape) {
            currentShape.setMap(null);
            currentShape = null;
        }

        currentEditingZone = null;
        document.getElementById('save_zone').disabled = true;

        // Réafficher toutes les zones en mode non-éditable
        for (var key in shapes) {
            if (shapes.hasOwnProperty(key)) {
                shapes[key].setEditable(false);
            }
        }
    }

    /**
     * Initialisation au chargement du document
     */
    function init() {
        // Charger l'API Google Maps
        if (!window.google || !window.google.maps) {
            loadGoogleMapsAPI();
        } else {
            initMap();
        }

        // Événements des boutons de dessin
        $('#draw_polygon').on('click', function() {
            drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
            var color = $('#zone_color').val();
            drawingManager.polygonOptions.fillColor = color;
            drawingManager.polygonOptions.strokeColor = color;
        });

        $('#draw_circle').on('click', function() {
            drawingManager.setDrawingMode(google.maps.drawing.OverlayType.CIRCLE);
            var color = $('#zone_color').val();
            drawingManager.circleOptions.fillColor = color;
            drawingManager.circleOptions.strokeColor = color;
        });

        $('#draw_rectangle').on('click', function() {
            drawingManager.setDrawingMode(google.maps.drawing.OverlayType.RECTANGLE);
            var color = $('#zone_color').val();
            drawingManager.rectangleOptions.fillColor = color;
            drawingManager.rectangleOptions.strokeColor = color;
        });

        $('#clear_drawing').on('click', function() {
            if (confirm('Clear the current drawing?')) {
                resetForm();
            }
        });

        $('#save_zone').on('click', function(e) {
            e.preventDefault();
            saveZone();
        });

        // Changement de couleur
        $('#zone_color').on('change', function() {
            var color = $(this).val();
            if (currentShape) {
                currentShape.setOptions({
                    fillColor: color,
                    strokeColor: color
                });
            }
        });

        // Soumettre la configuration
        $('#zones_management_form').on('submit', function(e) {
            e.preventDefault();

            // Sauvegarder le paramètre d'activation
            var useZones = $('input[name="PRESTATILL_HD_USE_ZONES"]:checked').val();

            $.ajax({
                url: zonesManagementData.moduleAjaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'saveZonesConfiguration',
                    ajax: true,
                    use_zones: useZones,
                    id_shop: zonesManagementData.idShop
                },
                success: function(response) {
                    console.log('Save config response:', response);

                    // La réponse est dans response.message
                    var result = response.message || response;

                    if (result.success) {
                        alert('Configuration saved successfully!');
                    } else {
                        alert('Error saving configuration: ' + (result.error || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error saving configuration:', error);
                    console.error('Response:', xhr.responseText);
                    alert('Error saving configuration. Please try again.');
                }
            });
        });
    }

    /**
     * Charge l'API Google Maps dynamiquement
     */
    function loadGoogleMapsAPI() {
        var script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key=' + zonesManagementData.apiKey + '&libraries=drawing&callback=ZonesManagement.initMap';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    }

    // API publique
    return {
        init: init,
        initMap: initMap
    };

})();

// Initialiser au chargement de la page
$(document).ready(function() {
    if (typeof zonesManagementData !== 'undefined') {
        console.log('Zones Management Data:', zonesManagementData);
        // Initialiser même si pas de clé API (pour debug)
        if (zonesManagementData.apiKey) {
            ZonesManagement.init();
        } else {
            console.warn('Google Maps API Key is missing. Please configure it in Parameters tab.');
            // Afficher un message d'erreur à l'utilisateur
            if ($('#zones_map').length > 0) {
                $('#zones_map').html('<div class="alert alert-warning" style="margin: 20px;"><i class="icon-warning"></i> ' +
                    'Google Maps API Key manquante. Veuillez la configurer dans l\'onglet Paramètres.</div>');
            }
        }
    } else {
        console.error('zonesManagementData is not defined');
    }
});
