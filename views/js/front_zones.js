/*
* Prestatill Home Delivery Slots - Frontend Zones Display
*
* Home Delivery Module with slots for Prestashop
*
*  @author    Laurent Baumgartner contact@adametdev.fr
*  @copyright ADAM & DEV SAS
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

var zonesMap;
var deliveryZones = [];
var customerMarker = null;

$(document).ready(function() {
    if ($('#delivery-zones-map').length > 0 && typeof google !== 'undefined') {
        initializeZonesMap();
    }
});

function initializeZonesMap() {
    var mapOptions = {
        center: new google.maps.LatLng(defaultMapCenter.lat, defaultMapCenter.lng),
        zoom: 12,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        mapTypeControl: true,
        streetViewControl: false,
        zoomControl: true,
    };

    zonesMap = new google.maps.Map(document.getElementById('delivery-zones-map'), mapOptions);

    // Affiche les zones de livraison
    displayDeliveryZones();

    // Affiche la position du client s'il est connecté et a une adresse
    if (customerAddress && customerAddress.lat && customerAddress.lng) {
        displayCustomerLocation();
    }
}

function displayDeliveryZones() {
    if (!deliveryZonesData || deliveryZonesData.length === 0) {
        return;
    }

    var bounds = new google.maps.LatLngBounds();

    deliveryZonesData.forEach(function(zone) {
        var zoneData;
        try {
            zoneData = JSON.parse(zone.zone_data);
        } catch (e) {
            console.error('Error parsing zone data:', e);
            return;
        }

        var color = zone.zone_color || '#FF0000';
        var shape;

        if (zone.zone_type === 'polygon') {
            var coordinates = [];
            zoneData.forEach(function(point) {
                var latLng = new google.maps.LatLng(point.lat, point.lng);
                coordinates.push(latLng);
                bounds.extend(latLng);
            });

            shape = new google.maps.Polygon({
                paths: coordinates,
                fillColor: color,
                fillOpacity: 0.3,
                strokeWeight: 2,
                strokeColor: color,
                clickable: true,
                map: zonesMap
            });

        } else if (zone.zone_type === 'circle') {
            var center = new google.maps.LatLng(zoneData.center.lat, zoneData.center.lng);
            bounds.extend(center);

            shape = new google.maps.Circle({
                center: center,
                radius: zoneData.radius,
                fillColor: color,
                fillOpacity: 0.3,
                strokeWeight: 2,
                strokeColor: color,
                clickable: true,
                map: zonesMap
            });

        } else if (zone.zone_type === 'rectangle') {
            var rectangleBounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(zoneData.bounds.south, zoneData.bounds.west),
                new google.maps.LatLng(zoneData.bounds.north, zoneData.bounds.east)
            );

            bounds.union(rectangleBounds);

            shape = new google.maps.Rectangle({
                bounds: rectangleBounds,
                fillColor: color,
                fillOpacity: 0.3,
                strokeWeight: 2,
                strokeColor: color,
                clickable: true,
                map: zonesMap
            });
        }

        // Ajoute une info-bulle au clic
        if (shape) {
            var infoWindow = new google.maps.InfoWindow({
                content: '<div class="zone-info-window">' +
                    '<h4>' + zone.zone_name + '</h4>' +
                    (zone.store_name ? '<p><strong>Restaurant:</strong> ' + zone.store_name + '</p>' : '') +
                    '</div>'
            });

            google.maps.event.addListener(shape, 'click', function(event) {
                infoWindow.setPosition(event.latLng);
                infoWindow.open(zonesMap);
            });
        }
    });

    // Ajuste la carte pour afficher toutes les zones
    if (!bounds.isEmpty()) {
        zonesMap.fitBounds(bounds);
    }
}

function displayCustomerLocation() {
    var customerLatLng = new google.maps.LatLng(customerAddress.lat, customerAddress.lng);

    // Ajoute un marqueur pour la position du client
    customerMarker = new google.maps.Marker({
        position: customerLatLng,
        map: zonesMap,
        title: 'Votre adresse',
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
            scaledSize: new google.maps.Size(40, 40)
        },
        animation: google.maps.Animation.DROP
    });

    // Centre la carte sur l'adresse du client
    zonesMap.setCenter(customerLatLng);

    // Ajoute une info-bulle
    var infoWindow = new google.maps.InfoWindow({
        content: '<div class="customer-info-window">' +
            '<h4>Votre adresse de livraison</h4>' +
            '</div>'
    });

    customerMarker.addListener('click', function() {
        infoWindow.open(zonesMap, customerMarker);
    });

    // Ouvre automatiquement l'info-bulle
    setTimeout(function() {
        infoWindow.open(zonesMap, customerMarker);
    }, 500);
}

// Fonction pour vérifier si un point est dans une zone (utilisée côté client pour feedback visuel)
function isPointInZone(lat, lng) {
    if (!deliveryZonesData || deliveryZonesData.length === 0) {
        return false;
    }

    for (var i = 0; i < deliveryZonesData.length; i++) {
        var zone = deliveryZonesData[i];
        var zoneData;

        try {
            zoneData = JSON.parse(zone.zone_data);
        } catch (e) {
            continue;
        }

        if (zone.zone_type === 'polygon') {
            if (isPointInPolygon(lat, lng, zoneData)) {
                return true;
            }
        } else if (zone.zone_type === 'circle') {
            if (isPointInCircle(lat, lng, zoneData)) {
                return true;
            }
        } else if (zone.zone_type === 'rectangle') {
            if (isPointInRectangle(lat, lng, zoneData)) {
                return true;
            }
        }
    }

    return false;
}

function isPointInPolygon(lat, lng, polygon) {
    var inside = false;
    for (var i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
        var xi = polygon[i].lat, yi = polygon[i].lng;
        var xj = polygon[j].lat, yj = polygon[j].lng;

        var intersect = ((yi > lng) !== (yj > lng)) &&
            (lat < (xj - xi) * (lng - yi) / (yj - yi) + xi);
        if (intersect) inside = !inside;
    }
    return inside;
}

function isPointInCircle(lat, lng, circle) {
    var center = circle.center;
    var radius = circle.radius;

    var distance = google.maps.geometry.spherical.computeDistanceBetween(
        new google.maps.LatLng(lat, lng),
        new google.maps.LatLng(center.lat, center.lng)
    );

    return distance <= radius;
}

function isPointInRectangle(lat, lng, rectangle) {
    var bounds = rectangle.bounds;
    return lat >= bounds.south && lat <= bounds.north &&
        lng >= bounds.west && lng <= bounds.east;
}
