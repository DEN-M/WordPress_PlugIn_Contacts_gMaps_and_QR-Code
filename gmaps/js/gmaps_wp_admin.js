var is_marker_set = false;
var geocoder;
var map;
var berlin_position = new google.maps.LatLng(52.519171, 13.406091);
var marker;

function initialize() {
	
	geocoder = new google.maps.Geocoder();
	
	// Anfangspostion der Map festlegen, fals keine gespeicherte Posetion gibt soll die von Berlin gesetzt werden
	var current_position = ( typeof gMaps_lat_lng == 'undefined') ? berlin_position : new google.maps.LatLng(parseFloat(gMaps_lat_lng.lat), parseFloat(gMaps_lat_lng.lng));
	var zoom = berlin_position == current_position ? 10 :16;
	var mapOptions = {
		zoom : zoom,
		// Positon der Map
		center : current_position,
		mapTypeId : google.maps.MapTypeId.ROADMAP
	};
	
	// Google-Maps = Map wird Initialisiert
	map = new google.maps.Map(document.getElementById('gMaps_WP_backEnd_admin'), mapOptions);
	
	
	if(typeof gMaps_lat_lng != 'undefined') positionMarker(current_position, map);
	google.maps.event.addListener(map, 'click', function(e) {
		positionMarker(e.latLng, map);
	});
	
	// Autocomplete StartAdress
	var startAdress = /** @type {HTMLInputElement} */(document.getElementById('gMaps_addresse'));
	// SuchFilder die Städte aus Deutschland werden vorgeschlagen
	var options = {
		componentRestrictions : {
			country : 'de'
		}
	};
	var autocomplete = new google.maps.places.Autocomplete(startAdress, options);
}

// Markerpostion festlegen
function positionMarker(position, map) {
    if (is_marker_set)
        return;
    marker = new google.maps.Marker({
        draggable : true,
        position : position,
        map : map
    });
    is_marker_set = true;
    refresh_position(position);

    google.maps.event.addListener(marker, "dragend", function(event) {

        var point = marker.getPosition();
        refresh_position(point);
    });

    google.maps.event.addListener(marker, "click", function(event) {
        marker.setMap(null);
        jQuery("[name=gMaps_geo_addresse_lat]").val('');
        jQuery("[name=gMaps_geo_addresse_lng]").val('');
        is_marker_set = false;
    });
}

// wenn die Postion geändet wird sollen die Längen- und Breitengrade aktualisiert werden
function refresh_position(position) {
    map.panTo(position);
    jQuery("[name=gMaps_geo_addresse_lat]").val(position.lat());
    jQuery("[name=gMaps_geo_addresse_lng]").val(position.lng());
}

// Postion anhand Adresse ermitteln
jQuery(document).ready(function($) {

    function position_by_address() {

        var address = $('[name=gMaps_addresse]').val();
        geocoder.geocode({
            'address' : address
        }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                var position = results[0].geometry.location;
                positionMarker(position, map)
                marker.setPosition(position);
                refresh_position(position);
            }
        });
    }

    $('[name=search_gMaps_addresse]').click(function(e) {
        e.preventDefault();
        position_by_address();

        return false;
    });

    jQuery('input#gMaps_addresse').keypress(function(e) {
        if (e.which == 13) {
            e.preventDefault();
            position_by_address();

            return false;
        }
    });

});

// wenn das Fenster geladen wurde sollte die Funktion initialize ausgeführt werden
google.maps.event.addDomListener(window, 'load', initialize);