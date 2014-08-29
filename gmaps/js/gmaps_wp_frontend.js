var berlin_position = new google.maps.LatLng(52.519171, 13.406091);
var bht = new google.maps.LatLng(52.544312, 13.352423);
var directionsDisplay;
var rendererOptions = {
	draggable : true
};
var directionsService = new google.maps.DirectionsService();
var userPostionActivated = false;
var end;
var geocoder;
var map;
var marker;
var map_layer;
var start;
var start_adr;
var selectedMode;

function initialize() {

	geocoder = new google.maps.Geocoder();

	// Wenn Position für Bibliothek gesetzt dann ist gMaps_lat_lng verfügbar, sonst wird der Mittelpunkt von Berlin genommen
	var current_position = ( typeof gMaps_lat_lng == 'undefined') ? berlin_position : new google.maps.LatLng(parseFloat(gMaps_lat_lng.lat), parseFloat(gMaps_lat_lng.lng));
	end = current_position;

	// Wenn Position verfügbar zeige kleinere Zoomstufe sonst Gesamtüberblick von Berlin
	var zoom = berlin_position != current_position ? 16 : 10;
	directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions);
	jQuery('input[name=allow-geolocation]').click(checkGeolocation);
	var mapOptions = {
		// Positon der Map
		center : current_position,
		mapTypeId : google.maps.MapTypeId.ROADMAP,
		zoom : zoom,
		disableDefaultUI: false,
		mapTypeControl : true,
		panControl : false,
		zoomControl : true,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.SMALL
		},
		scaleControl : false
	};
	
	// Hinwes falls keine Golocation in dem Browser möglich ist
	if (!Modernizr.geolocation) {
		jQuery('input[name=allow-geolocation]').remove();
		jQuery('#allow-geolocation').css('background', '#ffebaf');
		jQuery('#allow-geolocation i').removeClass('icon-info-sign').addClass('icon-warning-sign');
		jQuery('#allow-geolocation h6').text("Keine Geolokalisierung in Ihrem Browser");
		jQuery('#allow-geolocation span').text("Leider verfügt Ihr Browser nicht über die Möglichkeit Ihren Standort zu ermitteln. Bitte nutze das Formular unter der Karte.");
	}
	
	// Google Maps wird in div gMaps_WP_frontEnd eingefügt
	map = new google.maps.Map(document.getElementById('gMaps_WP_frontEnd'), mapOptions);

	// S-Bahn und U-Bahn auf der Karte anzeigen
	map_layer = new google.maps.TransitLayer();
	map_layer.setMap(map);
	
	// je nach Routenoption sollte sollte sich das Layout der Karte ändern
	jQuery("#travel-mode").change(function() {
		selectedMode = document.getElementById('travel-mode').value;
		if(selectedMode == 'DRIVING') {
			map_layer = new google.maps.TrafficLayer();
			map_layer.setMap(map);
		}
		
		if(selectedMode == 'BICYCLING') {
			map_layer.setMap(null);
			map_layer = new google.maps.BicyclingLayer();
			map_layer.setMap(map);
		}
	
		if(selectedMode == 'TRANSIT' || selectedMode == 'WALKING') {
			map_layer.setMap(null);
			map_layer = new google.maps.TransitLayer();
			map_layer.setMap(map);
		}
		
	});
	
	directionsDisplay.setMap(map);
	google.maps.event.addListenerOnce(map, 'tilesloaded', function() {
		if ( typeof gMaps_lat_lng != 'undefined')
			positionMarker(current_position, map);
		jQuery("#travel-mode").change(function() {
			calcUserPosition();
		});

	});

	// Autocomplete StartAdress
	var startAdress = /** @type {HTMLInputElement} */(document.getElementById('start-input'));
	
	// SuchFilder die Städte aus Deutschland werden vorgeschlagen
	var options = {
		componentRestrictions : {
			country : 'de'
		}
	};
	var autocomplete = new google.maps.places.Autocomplete(startAdress, options);
	// Informationsfenster
	var infowindow = new google.maps.InfoWindow();
	var marker = new google.maps.Marker({
		map : map,
		anchorPoint: new google.maps.Point(0, -29)
	});

	// RouteList anzeigen
	directionsDisplay.setMap(map);
	directionsDisplay.setPanel(document.getElementById('directions-panel'));


	google.maps.event.addListener(autocomplete, 'place_changed', function() {
		infowindow.close();
		marker.setVisible(true);
		// sollte raus wenn einfacher Maarker gesetzt werden soll den man NICHT verschoben werden kann
		// input.className = '';
		var place = autocomplete.getPlace();
		if (!place.geometry) {
			// sollte raus wenn einfacher Maarker gesetzt werden soll den man NICHT verschoben werden kann
			// input.className = 'notfound';
			return;
		}

		// If the place has a geometry, then present it on a map.
		if (place.geometry.viewport) {
			map.fitBounds(place.geometry.viewport);
		} else {
			map.setCenter(place.geometry.location);
			map.setZoom(13);
		}

		marker.setIcon(/** @type {google.maps.Icon} */( {
			url : place.icon,
			size : new google.maps.Size(71, 71),
			origin : new google.maps.Point(0, 0),
			anchor : new google.maps.Point(17, 34),
			scaledSize : new google.maps.Size(35, 35)
		}));
		marker.setPosition(place.geometry.location);
		marker.setVisible(true);

		var addresse = '';
		if (place.addresse_components) {
			addresse = [
				(place.addresse_components[0] && place.addresse_components[0].short_name || ''), 
				(place.addresse_components[1] && place.addresse_components[1].short_name || ''), 
				(place.addresse_components[2] && place.addresse_components[2].short_name || '')
			].join(' ');
		}

		infowindow.setContent('<div><strong>Ihre Postion</strong><br>' + place.name + '<br>' + addresse);
		infowindow.open(map, marker);
	});

}

jQuery(document).ready(function() {
    jQuery('#submit-route-btn').click(function(e) {
        e.preventDefault();
        calcUserPosition();
    });
});

function checkGeolocation() {
    if(!jQuery(this).is(':checked')) {
        jQuery('input[name=start]').val('');
        start = null;
        return;
    } else {
        calcUserPosition();
    }
}

// Rufft die Nuterpostion ab
function calcUserPosition() {

    //console.log('test');
    var address = jQuery('input[name=start]').val();
	start_adr = address;
	selectedMode = document.getElementById('travel-mode').value;
		geocoder.geocode({
			'address' : address
		}, function(results, status) {

			if (status == google.maps.GeocoderStatus.OK) {

				var position_user = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
				console.log(position_user != start);
				if (position_user != start) {
					calcRoute();
					return;
				}
			} 
			
			if(start_adr == '' && !jQuery('input[name=allow-geolocation]').is(':checked')) {
				alert("Bitte geben Sie die Startadresse ein oder aktevieren Sie die Geolokalisierung.");
			}
		});

		if (jQuery('input[name=allow-geolocation]').is(':checked') && navigator.geolocation && userPostionActivated == false) {

			navigator.geolocation.getCurrentPosition(function(position) {
				var pos = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
				start = pos;
				geocoder.geocode({
					'latLng' : start
				}, function(results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						var address = results[0].formatted_address;
						//TODO mobilen Nutzer mitteilen, dass er seine Einstellungen für Standorterfassung aktivieren soll
						jQuery('input[name=start]').val(address);
						start_adr = address;
						calcRoute();
					}
				});

			}, function() {
				alert("Geolocation fehlgeschlagen! Bitte verwenden Sie das Formular unter der Karte, um eine Route zu finden.");
			});
			userPostionActivated = true;
		} else {
			var address = jQuery('input[name=start]').val();
			start_adr = address;
			geocoder.geocode({
				'address' : address
			}, function(results, status) {

				if (status == google.maps.GeocoderStatus.OK) {

					start = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
					if(selectedMode == '') {
						alert("Bitte wählen Sie die Routenoption aus.");
					} else {
						calcRoute();
					}
				}
			});
		}
}

function calcRoute() {
	selectedMode = document.getElementById('travel-mode').value;

	// Wenn die Startadresse ermittelt wurde sollte sich der link unter "zu Google Maps" ändern. Die Startadresse wird zusammen mit der Zieladresse in den Link eingefügt
	var a_href_google_maps_url = $('#google_maps_url').attr('href', 
	'http://maps.google.de/maps?saddr='+start_adr+'&daddr='+document.getElementById('destination-input').value+'&dirflg=&hl=de&ie=UTF8');
	
	var request = {
		origin : start,
		destination : end,
		travelMode : google.maps.TravelMode[selectedMode]
	};
	
	directionsService.route(request, function(response, status) {
		if (status == google.maps.DirectionsStatus.OK) {
			directionsDisplay.setDirections(response);
		}
	});
	
	var directionsRequest = request;
	console.log(directionsRequest);
}

function positionMarker(position, map) {

	marker = new google.maps.Marker({
		animation : google.maps.Animation.DROP,
		draggable : false,
		map : map,
		position : position
	});
	
	var destination_input = document.getElementById('destination-input').value;
	var gMaps_titel = document.getElementById('gMaps_titel').value;
	var infowindow_content = '<div><strong>' + gMaps_titel + '</strong><br>' + destination_input;
	var infowindow_current_position = new google.maps.InfoWindow({
		content: infowindow_content
	});
	
	google.maps.event.addListener(marker, 'click', function() {
		infowindow_current_position.open(map,marker);
  });
}

// wenn das Fenster geladen wurde sollte die Funktion initialize ausgeführt werden
google.maps.event.addDomListener(window, 'load', initialize);