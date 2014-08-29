<?php
/*
GMaps_BA_DM_Plugin
erzeugt Eingabefelder mit einer Vorschau-Karte aber auch Filter und Speichern-Funktionen in Back-End  und Google-Maps in Front-End-Bereich
*/
class GMaps_BA_DM_Plugin {
	
	private $gMaps_titel; 				# String - Titel fürs Google-Maps 
	private $gMaps_addresse; 			# String - Google-Maps Adresse die auf der Karte Gesucht wird und als Zieladresse dient
	private $gMaps_geo_addresse_lat; 	# double - Längenbreite die aus der $gMaps_addresse ermittelt wurde
	private $gMaps_geo_addresse_lng; 	# double - Längenhöhe die aus der $gMaps_addresse ermittelt wurde
	
	/*
	fügt alle Bestandteile von Google-Maps ins WordPress ein
	*/
	function start_gMaps_BA_DM_Plugin() {
		add_shortcode('gmaps_ba_dm_plugin', array(&$this, 'add_frontend_content_for_gMaps_BA_DM_Plugin'));
		add_action('admin_init', array(&$this, 'add_admin_metaBox_for_gMaps_BA_DM_Plugin'));
		add_action('save_post', array(&$this, 'save_gMaps_addresse_BA_DM_Plugin'));
		add_filter('the_content', array(&$this, 'the_content_filter_for_gMaps_BA_DM_Plugin'));
		add_filter('get_the_content', array(&$this, 'the_content_filter_for_GMaps_BA_DM_Plugin'));
	}

	/*
	fügt eine Meta-Box in den Back-End-Bereich bei Erstellung einer neuen Seite in WordPress ein
	*/
	function add_admin_metaBox_for_gMaps_BA_DM_Plugin() {
		add_meta_box("add_admin_settings_for_gMaps_BA_DM_Plugin", "Google Maps mit Routenberechnung", array( &$this, "add_admin_settings_for_gMaps_BA_DM_Plugin"), "post", "normal", "high");
		add_meta_box("add_admin_settings_for_gMaps_BA_DM_Plugin", "Google Maps mit Routenberechnung", array( &$this, "add_admin_settings_for_gMaps_BA_DM_Plugin"), "page", "normal", "high");
	}
	
	/*
	füllt die Meta-Box in den Back-End-Bereich mit Eingabefeldern und eine Google-Maps-Vorschaukarte
	*/
	function add_admin_settings_for_gMaps_BA_DM_Plugin() {
		global $post;
		
		# import von JavaScript-Dateien für den Back-End-Bereich
		wp_deregister_script('gMaps_api_v3');
		wp_enqueue_script('gMaps_api_v3', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=true&libraries=places', false, '3', false);
		
		wp_deregister_script('jquery_for_gMaps');
		wp_enqueue_script('jquery_for_gMaps', plugins_url('js/jquery-1.11.0.min.js',__FILE__), array('jquery'));
		
		wp_deregister_script('gMaps_WP_backEnd_admin');
		wp_enqueue_script('gMaps_WP_backEnd_admin', plugins_url('js/gmaps_wp_admin.js',__FILE__), array('jquery'));
		
		# Variablen werden mit aktuellen Werten initialisiert 
		$gMaps_titel = get_post_meta($post->ID,'gMaps_titel',true);
		$gMaps_geo_addresse_lat = get_post_meta($post->ID, 'gMaps_geo_addresse_lat', true);
		$gMaps_geo_addresse_lng = get_post_meta($post->ID, 'gMaps_geo_addresse_lng', true);
		
		# wenn die Variablen nicht leer sind, dann sollen die Werte (gMaps_geo_addresse_lat und gMaps_geo_addresse_lng) an die Variable in gmaps_wp_admin.js übergeben werden
		if(!empty($gMaps_geo_addresse_lat) && $gMaps_geo_addresse_lat != '' 
		&& !empty($gMaps_geo_addresse_lng) && $gMaps_geo_addresse_lng != '') {
			wp_localize_script( 'gMaps_WP_backEnd_admin', 'gMaps_lat_lng', array(
			'lat' => floatval($gMaps_geo_addresse_lat),
			'lng' => floatval($gMaps_geo_addresse_lng)));
		}

		# hier wird der Inhalt für den Back-End-Bereich als HTML generiert und als echo ausgegeben
		echo '
		<style type="text/css">.gMaps_titel{width:40%}</style>
		<style type="text/css">.gMaps_addresse{width:40%}</style>
		<style type="text/css">.search_gMaps_addresse{width:125px; letter-spacing: normal; word-spacing: normal; text-transform: none; text-indent: 0px; text-shadow: none; display: inline-block;}</style>
		<style type="text/css">#gMaps_WP_backEnd_admin{height: 400px; margin:20px 0;}</style>
		<div id="parent_gMaps_address" name="parent_gMaps_address" class="parent_gMaps_address">
			<table style="width:100%">	
				<tr>
					<th>
						<label for="gMaps_titel" width:20%; padding:40px;>'.__('Titel/Beschreibung:').'</label>
					</th>
					<td>
						<input type="text" id="gMaps_titel" name="gMaps_titel" class="gMaps_titel" placeholder="Titel/Beschreibung zur Adresse (Auslassen, falls nicht verfügbar)" value="'.$gMaps_titel.'"/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="gMaps_addresse" width:20%; padding:40px;>'.__('Adresse:').'</label>
					</th>
					<td>
						<input type="text" id="gMaps_addresse" name="gMaps_addresse" class="gMaps_addresse" placeholder="Straße Hausnummer, PLZ Ort (Auslassen, falls nicht verfügbar)" value="'.get_post_meta($post->ID,'gMaps_addresse',true).'"/>
						<button type="button" name="search_gMaps_addresse" class="search_gMaps_addresse">Adresse suchen</button><br>
						<span>Markierung kann durch Mauszeiger verschoben werden und durch denk Klick gesetzt werden.</span>
					</td>
				</tr>
			</table>
		</div>
		<div id="gMaps_WP_backEnd_admin"></div>
		<input id="gMaps_geo_addresse_lat" type="hidden" name="gMaps_geo_addresse_lat" class="gMaps_geo_addresse_lat" value="'.get_post_meta($post->ID, 'gMaps_geo_addresse_lat', true).'" />
		<input id="gMaps_geo_addresse_lng" name="gMaps_geo_addresse_lng" type="hidden" class="gMaps_geo_addresse_lng" value="'.get_post_meta($post->ID, 'gMaps_geo_addresse_lng', true).'" />
		<!-- #custom_setting_for_pages -->';
	}
	
	/*
	generiert den Inhalt von Google-Maps für den Front-End-Bereich und gibt den als $content zurück
	*/
	function add_frontend_content_for_gMaps_BA_DM_Plugin($content="") {
		global $post; # Zugriff auf ALLE Post's von WordPress
		
		# import von JavaScript-Dateien für den Fron-End-Bereich
		wp_deregister_script('gMaps_api_v3_front');
		wp_enqueue_script('gMaps_api_v3_front', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=true&libraries=places', false, '3', false);
		
		wp_deregister_script('jquery_for_gMaps');
		wp_enqueue_script('jquery_for_gMaps', plugins_url('js/jquery-1.11.0.min.js',__FILE__), array('jquery'));
		
		wp_deregister_script('modernizr_for_gMaps');
		wp_enqueue_script('modernizr_for_gMaps', plugins_url('js/modernizr-custom-min.js',__FILE__), array('jquery'));
		
		wp_deregister_script('gMaps_WP_frontEnd');
		wp_enqueue_script('gMaps_WP_frontEnd', plugins_url('js/gmaps_wp_frontend.js',__FILE__), array('jquery'));
		
		# Variablen werden mit aktuellen Werten initialisiert 
		$gMaps_titel = get_post_meta($post->ID,'gMaps_titel',true);
		$gMaps_addresse = get_post_meta($post->ID,'gMaps_addresse',true);
		$gMaps_geo_addresse_lat = get_post_meta($post->ID, 'gMaps_geo_addresse_lat', true);
		$gMaps_geo_addresse_lng = get_post_meta($post->ID, 'gMaps_geo_addresse_lng', true);
		
		# wenn die Variablen nicht leer sind, dann sollen die Werte (gMaps_geo_addresse_lat und gMaps_geo_addresse_lng) an die Variable in gmaps_wp_frontend.js übergeben werden
		if(!empty($gMaps_geo_addresse_lat) && $gMaps_geo_addresse_lat != '' 
		&& !empty($gMaps_geo_addresse_lng) && $gMaps_geo_addresse_lng != '') {
			wp_localize_script( 'gMaps_WP_frontEnd', 'gMaps_lat_lng', array(
			'lat' => floatval($gMaps_geo_addresse_lat),
			'lng' => floatval($gMaps_geo_addresse_lng),
			));
		}
		
		# hier wird der Inhalt für den Front-End-Bereich als HTML generiert und der $content-Variable zugewiesen
		$content = '
		<style type="text/css">#gMaps_WP_frontEnd{width:100%; height: 400px; margin:20px 0;}</style>
		<style type="text/css">.map_box{height: 100%; position: relative; width: 100%;}</style>
		<style type="text/css">#map_panel{background: #fff; left: 0%; margin-left: 5px; border: 1px solid #999; padding: 5px; position: absolute; top: 5px; z-index: 99;}</style>
		<style type="text/css">#travel-mode{width: 220px;}</style>
		<style type="text/css">.geolocation_box{position: relative;}</style>
		<style type="text/css">.geolocation_checkbox{top: 50%; position: absolute;}</style>
		<style type="text/css">.geolocation_inf{width: 90%; margin: 0 0 0 4%; display: inline-block;}</style>
		<style type="text/css">.adr{width:100%}</style>
		<style type="text/css">.directions-panel{width:100%; height: 100%;  max-height: 300px; float: center; width: auto; overflow: auto;}</style>
		<style type="text/css">.icon-info-sign{font-size: 200%; margin: -15px 0 0 0; position: absolute; top: 50%; right: 20px;}</style>
		<style type="text/css">.route_box{width:100%; margin:20px 0;}</style>
		<div class="map_box">
			<div id="map_panel">
				<b>Routenoption</b>
				<select id="travel-mode">
					<option value="TRANSIT">öffentlichen Verkehrsmittel</option>
					<option value="BICYCLING">Fahrrad</option>
					<option value="WALKING">Zu Fuß</option>
					<option value="DRIVING">Auto</option>
				</select>
				<a id="google_maps_url" href="http://maps.google.de/maps?saddr=&amp;daddr='.get_post_meta($post->ID,'gMaps_addresse',true).'&amp;dirflg=&amp;hl=de&amp;ie=UTF8" target="_blank">zu Google Maps</a>
			</div>
			<div id="gMaps_WP_frontEnd"></div>
			<div id="directions-panel" class="directions-panel style="direction: ltr;"></div>
		</div>
		<div id="geolocation_box" class="geolocation_box">
			<input type="checkbox" name="allow-geolocation" class="geolocation_checkbox">
			<div>
				<h6 class="geolocation_inf">Wir bitten um Ihre Erlaubnis</h6>
				<span class="geolocation_inf"> Wenn Sie uns erlauben Ihren aktuellen Standort zu erfassen, dann brauchen Sie nicht mehr Ihre Adresse auszufüllen. Wir versichern Ihnen keinerlei Standort bezogenen Daten in irgend einer Form zu speichern. </span>
			</div>
			<i class="icon-info-sign"></i>
        </div>
		<div class="route_box">
			<label for="start"><b>Startadresse </b><i class="icon-user"></i></label><br />
			<input type="text" name="start" id="start-input" class="adr" placeholder="Geben Sie bitte hier Ihren Standort ein (Straße 1, 12345 Stadt)"/>
			<label for="destination"><b>Zieladresse </b><i class="icon-flag"></i></label>
			<br />
			<input id="gMaps_titel" type="hidden" name="gMaps_titel" class="gMaps_titel" value="'.$gMaps_titel.'" />
			<input type="text" name="destination" id="destination-input" class="adr" value="'.$gMaps_addresse.'" disabled="disabled"/>
			<br />
			<input type="submit" name="submit-route" id="submit-route-btn" class="black-btn" value="Weg finden"/>
		</div>
		<!-- #custom_setting_for_pages -->';
		
		# sollte die $gMaps_addresse dann soll auch leeres $content zurückgegeben werden
		if (empty($gMaps_addresse) && $gMaps_addresse == '') {
			$content = '';
		} 
		
		return do_shortcode($content);		
	}
	
	/*
	speichert alle Eingaben ab
	*/
	function save_gMaps_addresse_BA_DM_Plugin() {
		global $post;
		# prüft zerst $post, ist er nicht vorhanden dann lohnen sich die unteren Abfragen nicht, da es ja noch keine Posts dazu gibt und es zu lauter Fehlermeldungen führen würde (wegen nicht vorhandener Post-Einträge)
		if (!empty($post)) {
			update_post_meta($post->ID, "gMaps_titel", $_POST['gMaps_titel']);
			update_post_meta($post->ID, "gMaps_addresse", $_POST['gMaps_addresse']);

			if(isset($_REQUEST['map-lat']) && !empty($_REQUEST['map-lat'])) {
				update_post_meta($_REQUEST['map-lat'], $_POST['gMaps_geo_addresse_lat']);
			} else {
				update_post_meta($post->ID, "gMaps_geo_addresse_lat", $_POST['gMaps_geo_addresse_lat']);
			}
			if(isset($_REQUEST['map-lng'] ) && !empty($_REQUEST['map-lng'])) {
				update_post_meta($_REQUEST['map-lng'], $_POST['gMaps_geo_addresse_lng']);
			} else {
				update_post_meta($post->ID, "gMaps_geo_addresse_lng", $_POST['gMaps_geo_addresse_lng']);
			}
		}
	}
	
	/*
	filtert alle Eingaben aus und gibt die als $content zurück
	*/
	function the_content_filter_for_gMaps_BA_DM_Plugin($content) {
		global $post;		
		
		$gMaps_addresse = get_post_meta($post->ID,'gMaps_addresse',true);
		if(!empty($gMaps_addresse) && $gMaps_addresse != ''){
			$content .= do_shortcode('[gmaps_ba_dm_plugin gMaps_addresse="'.$gMaps_addresse.'"]');
		}
		
		$gMaps_titel = get_post_meta($post->ID,'gMaps_titel',true);
		# Prüffe noch ob $contetn nicht bereits Daten enthält, da sonst der gleiche Inhalt an das $contetn eingefügt wird
		if(!empty($gMaps_titel) && $gMaps_titel != '' && empty($content)){
			$content .= do_shortcode('[gmaps_ba_dm_plugin gMaps_titel="'.$gMaps_titel.'"]');
		}
		
		$gMaps_geo_addresse_lat = get_post_meta($post->ID,'gMaps_geo_addresse_lat',true);
		if(!empty($gMaps_geo_addresse_lat) && $gMaps_geo_addresse_lat != '' && empty($content)){
			$content .= do_shortcode('[gmaps_ba_dm_plugin gMaps_geo_addresse_lat="'.$gMaps_geo_addresse_lat.'"]');
		}
		
		$gMaps_geo_addresse_lng = get_post_meta($post->ID,'gMaps_geo_addresse_lng',true);
		if(!empty($gMaps_geo_addresse_lng) && $gMaps_geo_addresse_lng != '' && empty($content)){
			$content .= do_shortcode('[gmaps_ba_dm_plugin gMaps_geo_addresse_lng="'.$gMaps_geo_addresse_lng.'"]');
		}
		return $content;
	}

}

?>