/*
* Prestatill Home Delivery Slots - Zones Tab Management
*
* Home Delivery Module with slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

var editorMap;
var editorDrawingManager;
var editorCurrentShape = null;
var editorSelectedColor = '#FF0000';
var currentEditingZoneId = 0;

$(document).ready(function() {
    // Bouton pour ajouter une nouvelle zone
    $('#add_new_zone_btn').on('click', function() {
        openZoneEditor(0);
    });

    // Bouton pour éditer une zone existante
    $(document).on('click', '.edit_zone_btn', function() {
        var zoneId = $(this).data('zone-id');
        openZoneEditor(zoneId);
    });

    // Bouton pour supprimer une zone
    $(document).on('click', '.delete_zone_btn', function() {
        var zoneId = $(this).data('zone-id');
        if (confirm('Êtes-vous sûr de vouloir supprimer cette zone ?')) {
            deleteZone(zoneId);
        }
    });

    // Bouton pour sauvegarder la zone
    $('#save_zone_btn').on('click', function() {
        saveZone();
    });

    // Gestion du changement de couleur
    $('#edit_zone_color').on('change', function() {
        editorSelectedColor = $(this).val();
        if (editorCurrentShape) {
            updateShapeColor(editorCurrentShape, editorSelectedColor);
        }
    });

    // Boutons de dessin dans la modale
    $('#modal_draw_polygon_btn').on('click', function() {
        setEditorDrawingMode(google.maps.drawing.OverlayType.POLYGON);
    });

    $('#modal_draw_circle_btn').on('click', function() {
        setEditorDrawingMode(google.maps.drawing.OverlayType.CIRCLE);
    });

    $('#modal_draw_rectangle_btn').on('click', function() {
        setEditorDrawingMode(google.maps.drawing.OverlayType.RECTANGLE);
    });

    $('#modal_clear_zone_btn').on('click', function() {
        clearEditorZone();
    });

    // Quand la modale se ferme, on nettoie
    $('#zone_editor_modal').on('hidden.bs.modal', function () {
        cleanupEditor();
    });

    // Quand la modale s'ouvre, on initialise la carte
    $('#zone_editor_modal').on('shown.bs.modal', function () {
        if (!editorMap) {
            initializeEditorMap();
        } else {
            google.maps.event.trigger(editorMap, 'resize');
        }
    });
});

function openZoneEditor(zoneId) {
    currentEditingZoneId = zoneId;

    if (zoneId > 0) {
        // Mode édition - Charger les données de la zone
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'getZone',
                id_delivery_zone: zoneId
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    populateEditorForm(response.zone);
                    $('#zone_editor_modal_title').text('Modifier la zone de livraison');
                    $('#zone_editor_modal').modal('show');
                } else {
                    alert('Erreur lors du chargement de la zone');
                }
            },
            error: function() {
                alert('Erreur de communication avec le serveur');
            }
        });
    } else {
        // Mode création - Formulaire vide
        resetEditorForm();
        $('#zone_editor_modal_title').text('Ajouter une zone de livraison');
        $('#zone_editor_modal').modal('show');
    }
}

function populateEditorForm(zone) {
    $('#edit_zone_id').val(zone.id_delivery_zone);
    $('#edit_zone_name').val(zone.zone_name);
    $('#edit_id_store').val(zone.id_store);
    $('#edit_id_carrier').val(zone.id_carrier);
    $('#edit_zone_color').val(zone.zone_color);
    $('#edit_priority').val(zone.priority);
    $('#edit_active').prop('checked', zone.active == 1);
    $('#edit_zone_type').val(zone.zone_type);
    $('#edit_zone_data').val(zone.zone_data);

    editorSelectedColor = zone.zone_color;

    // Charger la zone sur la carte après initialisation
    setTimeout(function() {
        if (editorMap) {
            loadExistingZoneInEditor(zone.zone_type, zone.zone_data);
        }
    }, 500);
}

function resetEditorForm() {
    $('#edit_zone_id').val(0);
    $('#edit_zone_name').val('');
    $('#edit_id_store').val('');
    $('#edit_id_carrier').val(0);
    $('#edit_zone_color').val('#FF0000');
    $('#edit_priority').val(1);
    $('#edit_active').prop('checked', true);
    $('#edit_zone_type').val('polygon');
    $('#edit_zone_data').val('');

    editorSelectedColor = '#FF0000';

    if (editorCurrentShape) {
        editorCurrentShape.setMap(null);
        editorCurrentShape = null;
    }
}

function cleanupEditor() {
    if (editorCurrentShape) {
        editorCurrentShape.setMap(null);
        editorCurrentShape = null;
    }
}

function initializeEditorMap() {
    var mapOptions = {
        center: new google.maps.LatLng(defaultMapLat, defaultMapLng),
        zoom: 12,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        mapTypeControl: true,
        streetViewControl: false,
    };

    editorMap = new google.maps.Map(document.getElementById('zone_editor_map'), mapOptions);

    var drawingOptions = {
        drawingMode: null,
        drawingControl: false,
        polygonOptions: {
            fillColor: editorSelectedColor,
            fillOpacity: 0.3,
            strokeWeight: 2,
            strokeColor: editorSelectedColor,
            clickable: true,
            editable: true,
            draggable: true,
        },
        circleOptions: {
            fillColor: editorSelectedColor,
            fillOpacity: 0.3,
            strokeWeight: 2,
            strokeColor: editorSelectedColor,
            clickable: true,
            editable: true,
            draggable: true,
        },
        rectangleOptions: {
            fillColor: editorSelectedColor,
            fillOpacity: 0.3,
            strokeWeight: 2,
            strokeColor: editorSelectedColor,
            clickable: true,
            editable: true,
            draggable: true,
        },
    };

    editorDrawingManager = new google.maps.drawing.DrawingManager(drawingOptions);
    editorDrawingManager.setMap(editorMap);

    google.maps.event.addListener(editorDrawingManager, 'overlaycomplete', function(event) {
        if (editorCurrentShape) {
            editorCurrentShape.setMap(null);
        }

        editorCurrentShape = event.overlay;
        editorDrawingManager.setDrawingMode(null);

        $('.btn-group button').removeClass('active');

        saveEditorZoneData(event.type, event.overlay);
        addEditorShapeListeners(event.type, event.overlay);
    });

    // Charge la zone existante si en mode édition
    var existingZoneData = $('#edit_zone_data').val();
    var existingZoneType = $('#edit_zone_type').val();
    if (existingZoneData && existingZoneData !== '') {
        loadExistingZoneInEditor(existingZoneType, existingZoneData);
    }
}

function setEditorDrawingMode(mode) {
    editorDrawingManager.setDrawingMode(mode);

    $('.btn-group button').removeClass('active');
    if (mode === google.maps.drawing.OverlayType.POLYGON) {
        $('#modal_draw_polygon_btn').addClass('active');
    } else if (mode === google.maps.drawing.OverlayType.CIRCLE) {
        $('#modal_draw_circle_btn').addClass('active');
    } else if (mode === google.maps.drawing.OverlayType.RECTANGLE) {
        $('#modal_draw_rectangle_btn').addClass('active');
    }
}

function clearEditorZone() {
    if (editorCurrentShape) {
        editorCurrentShape.setMap(null);
        editorCurrentShape = null;
        $('#edit_zone_data').val('');
        $('#edit_zone_type').val('polygon');
    }
}

function saveEditorZoneData(type, shape) {
    var zoneData = {};
    var zoneType = '';

    if (type === google.maps.drawing.OverlayType.POLYGON) {
        zoneType = 'polygon';
        var path = shape.getPath();
        var coordinates = [];
        for (var i = 0; i < path.getLength(); i++) {
            var point = path.getAt(i);
            coordinates.push({
                lat: point.lat(),
                lng: point.lng()
            });
        }
        zoneData = coordinates;
    } else if (type === google.maps.drawing.OverlayType.CIRCLE) {
        zoneType = 'circle';
        var center = shape.getCenter();
        var radius = shape.getRadius();
        zoneData = {
            center: {
                lat: center.lat(),
                lng: center.lng()
            },
            radius: radius
        };
    } else if (type === google.maps.drawing.OverlayType.RECTANGLE) {
        zoneType = 'rectangle';
        var bounds = shape.getBounds();
        zoneData = {
            bounds: {
                north: bounds.getNorthEast().lat(),
                south: bounds.getSouthWest().lat(),
                east: bounds.getNorthEast().lng(),
                west: bounds.getSouthWest().lng()
            }
        };
    }

    $('#edit_zone_data').val(JSON.stringify(zoneData));
    $('#edit_zone_type').val(zoneType);
}

function addEditorShapeListeners(type, shape) {
    if (type === google.maps.drawing.OverlayType.POLYGON) {
        google.maps.event.addListener(shape.getPath(), 'set_at', function() {
            saveEditorZoneData(type, shape);
        });
        google.maps.event.addListener(shape.getPath(), 'insert_at', function() {
            saveEditorZoneData(type, shape);
        });
    } else if (type === google.maps.drawing.OverlayType.CIRCLE) {
        google.maps.event.addListener(shape, 'center_changed', function() {
            saveEditorZoneData(type, shape);
        });
        google.maps.event.addListener(shape, 'radius_changed', function() {
            saveEditorZoneData(type, shape);
        });
    } else if (type === google.maps.drawing.OverlayType.RECTANGLE) {
        google.maps.event.addListener(shape, 'bounds_changed', function() {
            saveEditorZoneData(type, shape);
        });
    }
}

function loadExistingZoneInEditor(zoneType, zoneDataJson) {
    try {
        var zoneData = JSON.parse(zoneDataJson);

        if (zoneType === 'polygon') {
            var coordinates = [];
            for (var i = 0; i < zoneData.length; i++) {
                coordinates.push(new google.maps.LatLng(zoneData[i].lat, zoneData[i].lng));
            }

            editorCurrentShape = new google.maps.Polygon({
                paths: coordinates,
                fillColor: editorSelectedColor,
                fillOpacity: 0.3,
                strokeWeight: 2,
                strokeColor: editorSelectedColor,
                clickable: true,
                editable: true,
                draggable: true,
            });

            editorCurrentShape.setMap(editorMap);
            addEditorShapeListeners(google.maps.drawing.OverlayType.POLYGON, editorCurrentShape);

            var bounds = new google.maps.LatLngBounds();
            for (var i = 0; i < coordinates.length; i++) {
                bounds.extend(coordinates[i]);
            }
            editorMap.fitBounds(bounds);

        } else if (zoneType === 'circle') {
            var center = new google.maps.LatLng(zoneData.center.lat, zoneData.center.lng);

            editorCurrentShape = new google.maps.Circle({
                center: center,
                radius: zoneData.radius,
                fillColor: editorSelectedColor,
                fillOpacity: 0.3,
                strokeWeight: 2,
                strokeColor: editorSelectedColor,
                clickable: true,
                editable: true,
                draggable: true,
            });

            editorCurrentShape.setMap(editorMap);
            addEditorShapeListeners(google.maps.drawing.OverlayType.CIRCLE, editorCurrentShape);

            editorMap.setCenter(center);
            editorMap.fitBounds(editorCurrentShape.getBounds());

        } else if (zoneType === 'rectangle') {
            var bounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(zoneData.bounds.south, zoneData.bounds.west),
                new google.maps.LatLng(zoneData.bounds.north, zoneData.bounds.east)
            );

            editorCurrentShape = new google.maps.Rectangle({
                bounds: bounds,
                fillColor: editorSelectedColor,
                fillOpacity: 0.3,
                strokeWeight: 2,
                strokeColor: editorSelectedColor,
                clickable: true,
                editable: true,
                draggable: true,
            });

            editorCurrentShape.setMap(editorMap);
            addEditorShapeListeners(google.maps.drawing.OverlayType.RECTANGLE, editorCurrentShape);

            editorMap.fitBounds(bounds);
        }
    } catch (e) {
        console.error('Error loading existing zone:', e);
    }
}

function updateShapeColor(shape, color) {
    if (shape instanceof google.maps.Polygon) {
        shape.setOptions({
            fillColor: color,
            strokeColor: color
        });
    } else if (shape instanceof google.maps.Circle) {
        shape.setOptions({
            fillColor: color,
            strokeColor: color
        });
    } else if (shape instanceof google.maps.Rectangle) {
        shape.setOptions({
            fillColor: color,
            strokeColor: color
        });
    }
}

function saveZone() {
    var formData = {
        action: 'saveDeliveryZone',
        id_delivery_zone: $('#edit_zone_id').val(),
        zone_name: $('#edit_zone_name').val(),
        id_store: $('#edit_id_store').val(),
        id_carrier: $('#edit_id_carrier').val(),
        zone_type: $('#edit_zone_type').val(),
        zone_data: $('#edit_zone_data').val(),
        zone_color: $('#edit_zone_color').val(),
        priority: $('#edit_priority').val(),
        active: $('#edit_active').is(':checked') ? 1 : 0
    };

    // Validation
    if (!formData.zone_name) {
        alert('Veuillez entrer un nom pour la zone');
        return;
    }

    if (!formData.id_store) {
        alert('Veuillez sélectionner un restaurant');
        return;
    }

    if (!formData.zone_data) {
        alert('Veuillez dessiner une zone sur la carte');
        return;
    }

    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                alert('Zone sauvegardée avec succès');
                $('#zone_editor_modal').modal('hide');
                location.reload(); // Recharge la page pour afficher la mise à jour
            } else {
                alert('Erreur lors de la sauvegarde : ' + (response.message || 'Erreur inconnue'));
            }
        },
        error: function() {
            alert('Erreur de communication avec le serveur');
        }
    });
}

function deleteZone(zoneId) {
    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: {
            action: 'deleteDeliveryZone',
            id_delivery_zone: zoneId
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                alert('Zone supprimée avec succès');
                $('tr[data-zone-id="' + zoneId + '"]').fadeOut(300, function() {
                    $(this).remove();

                    // Si plus de zones, affiche le message
                    if ($('#zones_list_body tr').length === 0) {
                        $('#zones_list_body').html('<tr id="no_zones_row"><td colspan="9" class="text-center">Aucune zone configurée. Cliquez sur "Ajouter une nouvelle zone" pour en créer une.</td></tr>');
                    }
                });
            } else {
                alert('Erreur lors de la suppression');
            }
        },
        error: function() {
            alert('Erreur de communication avec le serveur');
        }
    });
}

// Global variable to store zones management data
var zonesManagementData = {
    zones: [],
    stores: {},
    carriers: {},
    map: null,
    shapes: []
};

/**
 * Safely parse zone data - handles both string and object inputs
 * @param {string|object} zoneData - The zone data to parse
 * @returns {object|null} Parsed zone data or null if parsing fails
 */
function parseZoneData(zoneData) {
    // If it's already an object/array, return it
    if (typeof zoneData === 'object') {
        return zoneData;
    }

    // If it's a string, try to parse it
    if (typeof zoneData === 'string') {
        try {
            return JSON.parse(zoneData);
        } catch (e) {
            console.error('Error parsing zone data:', e);
            console.log('Zone data:', zoneData);
            return null;
        }
    }

    return null;
}

/**
 * Load all zones and display them on map
 */
function loadZones() {
    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: {
            action: 'getZones'
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && response.zones) {
                zonesManagementData.zones = response.zones;
                displayZonesOnMap(response.zones);
                updateZonesList(response.zones);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading zones:', error);
        }
    });
}

/**
 * Load stores and carriers data
 */
function loadStoresAndCarriers() {
    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: {
            action: 'getStoresAndCarriers'
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                // Convert arrays to objects for easy lookup
                if (Array.isArray(response.stores)) {
                    zonesManagementData.stores = {};
                    response.stores.forEach(function(store) {
                        zonesManagementData.stores[store.id_store] = store;
                    });
                } else {
                    zonesManagementData.stores = response.stores || {};
                }

                if (Array.isArray(response.carriers)) {
                    zonesManagementData.carriers = {};
                    response.carriers.forEach(function(carrier) {
                        zonesManagementData.carriers[carrier.id_carrier] = carrier;
                    });
                } else {
                    zonesManagementData.carriers = response.carriers || {};
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading stores and carriers:', error);
        }
    });
}

/**
 * Display zones on map
 * @param {Array} zones - Array of zone objects
 */
function displayZonesOnMap(zones) {
    if (!zonesManagementData.map || !zones) {
        return;
    }

    // Clear existing shapes
    zonesManagementData.shapes.forEach(function(shape) {
        shape.setMap(null);
    });
    zonesManagementData.shapes = [];

    // Display each zone
    zones.forEach(function(zone) {
        try {
            var zoneData = parseZoneData(zone.zone_data);

            if (!zoneData) {
                console.error('Error parsing zone data for zone ' + zone.id_delivery_zone + ': Invalid data');
                console.log('Zone data:', zone.zone_data);
                return;
            }

            var shape = null;

            if (zone.zone_type === 'polygon' && Array.isArray(zoneData)) {
                var coordinates = zoneData.map(function(point) {
                    return new google.maps.LatLng(point.lat, point.lng);
                });

                shape = new google.maps.Polygon({
                    paths: coordinates,
                    fillColor: zone.zone_color || '#FF0000',
                    fillOpacity: 0.3,
                    strokeWeight: 2,
                    strokeColor: zone.zone_color || '#FF0000',
                    clickable: true,
                    editable: false,
                    draggable: false
                });
            } else if (zone.zone_type === 'circle' && zoneData.center && zoneData.radius) {
                shape = new google.maps.Circle({
                    center: new google.maps.LatLng(zoneData.center.lat, zoneData.center.lng),
                    radius: zoneData.radius,
                    fillColor: zone.zone_color || '#FF0000',
                    fillOpacity: 0.3,
                    strokeWeight: 2,
                    strokeColor: zone.zone_color || '#FF0000',
                    clickable: true,
                    editable: false,
                    draggable: false
                });
            } else if (zone.zone_type === 'rectangle' && zoneData.bounds) {
                var bounds = new google.maps.LatLngBounds(
                    new google.maps.LatLng(zoneData.bounds.south, zoneData.bounds.west),
                    new google.maps.LatLng(zoneData.bounds.north, zoneData.bounds.east)
                );

                shape = new google.maps.Rectangle({
                    bounds: bounds,
                    fillColor: zone.zone_color || '#FF0000',
                    fillOpacity: 0.3,
                    strokeWeight: 2,
                    strokeColor: zone.zone_color || '#FF0000',
                    clickable: true,
                    editable: false,
                    draggable: false
                });
            }

            if (shape) {
                shape.setMap(zonesManagementData.map);
                zonesManagementData.shapes.push(shape);

                // Add info window on click
                google.maps.event.addListener(shape, 'click', function() {
                    var storeName = getStoreName(zone.id_store);
                    var infoContent = '<div><strong>' + zone.zone_name + '</strong><br>' +
                                    'Restaurant: ' + storeName + '<br>' +
                                    'Type: ' + zone.zone_type + '</div>';

                    var infoWindow = new google.maps.InfoWindow({
                        content: infoContent,
                        position: getShapeCenter(shape, zone.zone_type)
                    });

                    infoWindow.open(zonesManagementData.map);
                });
            }
        } catch (e) {
            console.error('Error displaying zone ' + zone.id_delivery_zone + ':', e);
        }
    });
}

/**
 * Get center point of a shape for info window
 */
function getShapeCenter(shape, zoneType) {
    if (zoneType === 'circle') {
        return shape.getCenter();
    } else if (zoneType === 'rectangle') {
        return shape.getBounds().getCenter();
    } else if (zoneType === 'polygon') {
        var bounds = new google.maps.LatLngBounds();
        shape.getPath().forEach(function(point) {
            bounds.extend(point);
        });
        return bounds.getCenter();
    }
    return null;
}

/**
 * Get store name by ID
 * @param {number} idStore - Store ID
 * @returns {string} Store name
 */
function getStoreName(idStore) {
    if (zonesManagementData.stores && zonesManagementData.stores[idStore]) {
        var store = zonesManagementData.stores[idStore];
        return store.name + (store.city ? ' (' + store.city + ')' : '');
    }
    return '--';
}

/**
 * Get carrier name by ID
 * @param {number} idCarrier - Carrier ID
 * @returns {string} Carrier name
 */
function getCarrierName(idCarrier) {
    if (idCarrier == 0) {
        return 'All carriers';
    }
    if (zonesManagementData.carriers && zonesManagementData.carriers[idCarrier]) {
        return zonesManagementData.carriers[idCarrier].name;
    }
    return '--';
}

/**
 * Update zones list table
 * @param {Array} zones - Array of zone objects
 */
function updateZonesList(zones) {
    var tbody = $('#zones_list_body');
    if (!tbody.length) {
        return;
    }

    tbody.empty();

    if (!zones || zones.length === 0) {
        tbody.html('<tr id="no_zones_row"><td colspan="9" class="text-center">No zones configured yet. Click "Add New Zone" to create one.</td></tr>');
        return;
    }

    zones.forEach(function(zone) {
        var storeName = getStoreName(zone.id_store);
        var carrierName = getCarrierName(zone.id_carrier);
        var activeLabel = zone.active == 1
            ? '<span class="label label-success"><i class="icon-check"></i> Yes</span>'
            : '<span class="label label-danger"><i class="icon-times"></i> No</span>';

        var row = '<tr data-zone-id="' + zone.id_delivery_zone + '">' +
            '<td>' + zone.id_delivery_zone + '</td>' +
            '<td><strong>' + zone.zone_name + '</strong></td>' +
            '<td>' + storeName + '</td>' +
            '<td>' + carrierName + '</td>' +
            '<td>' + zone.zone_type + '</td>' +
            '<td class="text-center"><span style="display:inline-block;width:30px;height:20px;background-color:' + zone.zone_color + ';border:1px solid #ccc;border-radius:3px;"></span></td>' +
            '<td class="text-center">' + zone.priority + '</td>' +
            '<td class="text-center">' + activeLabel + '</td>' +
            '<td class="text-center">' +
            '<button class="btn btn-default btn-sm edit_zone_btn" data-zone-id="' + zone.id_delivery_zone + '" title="Edit"><i class="icon-edit"></i></button> ' +
            '<button class="btn btn-danger btn-sm delete_zone_btn" data-zone-id="' + zone.id_delivery_zone + '" title="Delete"><i class="icon-trash"></i></button>' +
            '</td>' +
            '</tr>';

        tbody.append(row);
    });
}

/**
 * Initialize zones overview map
 */
function initializeZonesOverviewMap() {
    var mapElement = document.getElementById('zones_overview_map');
    if (!mapElement) {
        return;
    }

    var mapOptions = {
        center: new google.maps.LatLng(defaultMapLat, defaultMapLng),
        zoom: 12,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        mapTypeControl: true,
        streetViewControl: false
    };

    zonesManagementData.map = new google.maps.Map(mapElement, mapOptions);

    // Load zones after map is initialized
    loadZones();
    loadStoresAndCarriers();
}
