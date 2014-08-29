<?php
/*
Plugin Name: BA DM Plugin
Plugin URI: 
Description: WordPress PlugIn - Contacts with gMaps (GeoLoc. & Route)  & QR-Code (Contact, WiFi, Calendar)
Version: 1.0
Author: Denis Martin
Author URI: 
Update Server: 
Min WP Version: 3.7
Max WP Version: 3.8.1
License: GPL2
*/


$new_BA_DM_Plugin = new BA_DM_Plugin();

/*
Erstellt Instanzen von anderen Klassen und führ jeweils die Start-Funktion aus
*/
class BA_DM_Plugin {

	private $qrc_obj_vcard_ba_dm_plugin; 	# Object für QR-Code vCard
	private $qrc_wifi_obj_ba_dm_plugin; 	# Object für QR-Code WLAN
	private $qrc_event_obj_ba_dm_plugin; 	# Object für QR-Code Termin
	private $gmpas_obj_ba_dm_plugin; 		# Object für QR-Code Google-Maps

	# eine Konstruktor von BA_DM_Plugin
	function __construct() {
		/*
		Erweiterung für das Einstelungsmenü von Plug-in. 
		Ist noch in der Arbeit [DM]
		*/
		// # Start für Erzeugung eins Links zu den Einstellungen bei Plugins
		// add_action('init', array(&$this,'add_ba_plugin_settings_link'));
		// # Startet # Einstellungs Menü-Inhalt
		// add_action('init', array(&$this, 'add_ba_dm_plugin_optionen'));
		
		# startet QR-Code vCard
		$this->start_QRCode_vCard_BA_DM_Plugin();
		
		# startet QR-Code WLAN
		$this->start_QRCode_WiFi_BA_DM_Plugin();
		
		# startet QR-Code Termin
		$this->start_QRCode_Event_BA_DM_Plugin();
		
		# startet Google-Maps
		$this->start_gMaps_BA_DM_Plugin();
	}
	
	/*
	bindet die eingebundene PHP-Dateien ein und initialisiert bereits erstellte Variable mit dem jeweiligen neuen Objekt mit der Klasse aus der eingebundenen PHP-Datei
	zum Schluss ruft die Objektvariable Start-Funktion der Klasse auf
	*/
	private function start_QRCode_vCard_BA_DM_Plugin() {
		include_once '/qr-code/qr_code_vcard.php';
		$qrc_obj_vcard_ba_dm_plugin = new QRCode_vCard_BA_DM_Plugin();
		$qrc_obj_vcard_ba_dm_plugin->start_QRCode_vCard_BA_DM_Plugin();
	}
	
	/*
	bindet die eingebundene PHP-Dateien ein und initialisiert bereits erstellte Variable mit dem jeweiligen neuen Objekt mit der Klasse aus der eingebundenen PHP-Datei
	zum Schluss ruft die Objektvariable Start-Funktion der Klasse auf
	*/
	private function start_QRCode_WiFi_BA_DM_Plugin() {
		include_once '/qr-code/qr_code_wifi.php';
		$qrc_wifi_obj_ba_dm_plugin = new QRCode_WiFi_BA_DM_Plugin();
		$qrc_wifi_obj_ba_dm_plugin->start_QRCode_WiFi_BA_DM_Plugin();
	}
	
	/*
	bindet die eingebundene PHP-Dateien ein und initialisiert bereits erstellte Variable mit dem jeweiligen neuen Objekt mit der Klasse aus der eingebundenen PHP-Datei
	zum Schluss ruft die Objektvariable Start-Funktion der Klasse auf
	*/
	private function start_QRCode_Event_BA_DM_Plugin() {
		include_once '/qr-code/qr_code_event.php';
		$qrc_event_obj_ba_dm_plugin = new QRCode_Event_BA_DM_Plugin();
		$qrc_event_obj_ba_dm_plugin->start_QRCode_Event_BA_DM_Plugin();
	}
	
	/*
	bindet die eingebundene PHP-Dateien ein und initialisiert bereits erstellte Variable mit dem jeweiligen neuen Objekt mit der Klasse aus der eingebundenen PHP-Datei
	zum Schluss ruft die Objektvariable Start-Funktion der Klasse auf
	*/
	private function start_gMaps_BA_DM_Plugin() {
		include_once '/gmaps/gmaps.php';
		$gmpas_obj_ba_dm_plugin = new GMaps_BA_DM_Plugin;
		$gmpas_obj_ba_dm_plugin->start_gMaps_BA_DM_Plugin();
	}

	
#-------------------------------------------------------------------------------------------------------------

	/*
	Erzeugt ein Link zu den Einstellungen bei Plugins hinzu
	*/
	private function add_ba_plugin_settings_link() {
		function ba_plugin_settings_link($links, $file) {
			if ( $file == plugin_basename( dirname(__FILE__).'/ba-dm-plugin.php' ) ) {	
				# Einstelungs-Menü hinten
				$links[] = '<a href="'.admin_url('admin.php?page=ba_dm_plugin_menu').'">'.__('Einstellungen', 'BA DM Plugin Menü').'</a>';
				
				# Einstelungs-Menü vorne
				#$link = '<a href="options-general.php?page=ba_dm_plugin_menu">'.__('Einstellungen', 'BA DM Plugin Menü').'</a>';
				#array_unshift($links, $link);	
			}
			return $links;
		}
		
		# meldet ein Filter für ein Einstellungs-Link bei Plugins hinzu
		add_filter( 'plugin_action_links', 'ba_plugin_settings_link', 10, 2);
	}

#-------------------------------------------------------------------------------------------------------------
	
	/*
	Einstellungs Menü-Inhalt
	*/
	private function add_ba_dm_plugin_optionen() {
		function ba_dm_plugin_optionen() {
			$werte = (get_option('meinplugin_werte') != false) ? stripslashes(get_option('meinplugin_werte')) : "";
			echo '<div class="wrap">
					<h2>BA DM Plugin für WP - Optionen</h2>
					<tr valign="top">
						<label for="startInfo_on_off">Start Information: </label>
						<input type="checkbox" name="startInfo_on_off" id="startInfo_on_off" value="true"  />
						<span class="description">Wenn AN dann startet die startInfo mit</span>
					</tr>
					<td>
						<p class="submit"><input type="s
						ubmit" name="submit" id="submit" class="button button-primary" value="Änderungen speichern"  /></p>
				</td>
				</div>
			';
		}

		# Erzeugt ein Link bei Einstellungen 'BA Plugin' bei den Einstellungen hinzu
		# Um eigene options-SEITE.php einzufügen muss man die regestrireren. Unten steht wie!!!
		# http://codex.wordpress.org/Function_Reference/add_submenu_page
		# http://codex.wordpress.org/Function_Reference/add_options_page
		function plugin_control_menu() {
			add_submenu_page('options-general.php', 'wpautop-control', 'BA DM Plugin', 'manage_options', 'ba_dm_plugin_menu', 'ba_dm_plugin_optionen');
		}
		
		# melder ein Action für Menü mit den Namne 'BA Plugi' bei den Einstellungen hinzu
		add_action('admin_menu', 'plugin_control_menu');
	}
	
#-------------------------------------------------------------------------------------------------------------

}

?>