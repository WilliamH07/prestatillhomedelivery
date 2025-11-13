/*
* Prestatill Home Delivery Slots - Admin Zones Management
*
* Home Delivery Module with slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

var map;
var drawingManager;
var currentShape = null;
var selectedColor = '#FF0000';

$(document).ready(function() {
    // Initialise la carte uniquement si l'élément existe
    if ($('#zone-map').length > 0) {
        initializeMap();
    }

    // Gère le changement de couleur
    $('input[name="zone_color"]').on('change', function() {
        selectedColor = $(this).val();
        if (currentShape) {
            updateShapeColor(currentShape, selectedColor);
        }
    });

    // Récupère la couleur initiale
    if ($('input[name="zone_color"]').val()) {
        selectedColor = $('input[name="zone_color"]').val();
    }

    // Gère les boutons de dessin
    $('#draw-polygon-btn').on('click', function() {
        setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
    });

    $('#draw-circle-btn').on('click', function() {
        setDrawingMode(google.maps.drawing.OverlayType.CIRCLE);
    });

    $('#draw-rectangle-btn').on('click', function() {
        setDrawingMode(google.maps.drawing.OverlayType.RECTANGLE);
    });

    $('#clear-zone-btn').on('click', function() {
        clearZone();
    });
});

function initializeMap() {
    var mapOptions = {
        center: new google.maps.LatLng(defaultLat, defaultLng),
        zoom: 12,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        mapTypeControl: true,
        streetViewControl: false,
    };

    map = new google.maps.Map(document.getElementById('zone-map'), mapOptions);

    // Configuration du Drawing Manager
    var drawingOptions = {
        drawingMode: null,
        drawingControl: false,
        polygonOptions: {
            fillColor: selectedColor,
            fillOpacity: 0.3,
            strokeWeight: 2,
            strokeColor: selectedColor,
            clickable: true,
            editable: true,
            draggable: true,
        },
        circleOptions: {
            fillColor: selectedColor,
            fillOpacity: 0.3,
            strokeWeight: 2,
            strokeColor: selectedColor,
            clickable: true,
            editable: true,
            draggable: true,
        },
        rectangleOptions: {
            fillColor: selectedColor,
            fillOpacity: 0.3,
            strokeWeight: 2,
            strokeColor: selectedColor,
            clickable: true,
            editable: true,
            draggable: true,
        },
    };

    drawingManager = new google.maps.drawing.DrawingManager(drawingOptions);
    drawingManager.setMap(map);

    // Événement lorsqu'une forme est complétée
    google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
        // Supprime la forme précédente
        if (currentShape) {
            currentShape.setMap(null);
        }

        currentShape = event.overlay;
        drawingManager.setDrawingMode(null);

        // Active les boutons
        $('.zone-map-tools button').removeClass('active');

        // Sauvegarde les données de la zone
        saveZoneData(event.type, event.overlay);

        // Ajoute des listeners pour les modifications
        addShapeListeners(event.type, event.overlay);
    });

    // Charge la zone existante si elle existe
    if (existingZoneData && existingZoneData !== '') {
        loadExistingZone();
    }
}

function setDrawingMode(mode) {
    drawingManager.setDrawingMode(mode);

    // Mise à jour visuelle des boutons
    $('.zone-map-tools button').removeClass('active');
    if (mode === google.maps.drawing.OverlayType.POLYGON) {
        $('#draw-polygon-btn').addClass('active');
    } else if (mode === google.maps.drawing.OverlayType.CIRCLE) {
        $('#draw-circle-btn').addClass('active');
    } else if (mode === google.maps.drawing.OverlayType.RECTANGLE) {
        $('#draw-rectangle-btn').addClass('active');
    }
}

function clearZone() {
    if (currentShape) {
        currentShape.setMap(null);
        currentShape = null;
        $('#zone_data_input').val('');
        $('#zone_type_input').val('polygon');
    }
}

function saveZoneData(type, shape) {
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

    $('#zone_data_input').val(JSON.stringify(zoneData));
    $('#zone_type_input').val(zoneType);
}

function addShapeListeners(type, shape) {
    if (type === google.maps.drawing.OverlayType.POLYGON) {
        google.maps.event.addListener(shape.getPath(), 'set_at', function() {
            saveZoneData(type, shape);
        });
        google.maps.event.addListener(shape.getPath(), 'insert_at', function() {
            saveZoneData(type, shape);
        });
    } else if (type === google.maps.drawing.OverlayType.CIRCLE) {
        google.maps.event.addListener(shape, 'center_changed', function() {
            saveZoneData(type, shape);
        });
        google.maps.event.addListener(shape, 'radius_changed', function() {
            saveZoneData(type, shape);
        });
    } else if (type === google.maps.drawing.OverlayType.RECTANGLE) {
        google.maps.event.addListener(shape, 'bounds_changed', function() {
            saveZoneData(type, shape);
        });
    }
}

function loadExistingZone() {
    try {
        var zoneData = JSON.parse(existingZoneData);
        var zoneType = existingZoneType;

        if (zoneType === 'polygon') {
            var coordinates = [];
            for (var i = 0; i < zoneData.length; i++) {
                coordinates.push(new google.maps.LatLng(zoneData[i].lat, zoneData[i].lng));
            }

            currentShape = new google.maps.Polygon({
                paths: coordinates,
                fillColor: selectedColor,
                fillOpacity: 0.3,
                strokeWeight: 2,
                strokeColor: selectedColor,
                clickable: true,
                editable: true,
                draggable: true,
            });

            currentShape.setMap(map);
            addShapeListeners(google.maps.drawing.OverlayType.POLYGON, currentShape);

            // Centre la carte sur le polygone
            var bounds = new google.maps.LatLngBounds();
            for (var i = 0; i < coordinates.length; i++) {
                bounds.extend(coordinates[i]);
            }
            map.fitBounds(bounds);

        } else if (zoneType === 'circle') {
            var center = new google.maps.LatLng(zoneData.center.lat, zoneData.center.lng);

            currentShape = new google.maps.Circle({
                center: center,
                radius: zoneData.radius,
                fillColor: selectedColor,
                fillOpacity: 0.3,
                strokeWeight: 2,
                strokeColor: selectedColor,
                clickable: true,
                editable: true,
                draggable: true,
            });

            currentShape.setMap(map);
            addShapeListeners(google.maps.drawing.OverlayType.CIRCLE, currentShape);

            // Centre la carte sur le cercle
            map.setCenter(center);
            map.fitBounds(currentShape.getBounds());

        } else if (zoneType === 'rectangle') {
            var bounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(zoneData.bounds.south, zoneData.bounds.west),
                new google.maps.LatLng(zoneData.bounds.north, zoneData.bounds.east)
            );

            currentShape = new google.maps.Rectangle({
                bounds: bounds,
                fillColor: selectedColor,
                fillOpacity: 0.3,
                strokeWeight: 2,
                strokeColor: selectedColor,
                clickable: true,
                editable: true,
                draggable: true,
            });

            currentShape.setMap(map);
            addShapeListeners(google.maps.drawing.OverlayType.RECTANGLE, currentShape);

            // Centre la carte sur le rectangle
            map.fitBounds(bounds);
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
