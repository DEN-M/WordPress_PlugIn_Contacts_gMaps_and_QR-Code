<?php
/*
QRCode_WiFi_BA_DM_Plugin
erzeugt Eingabefelder in Back-End-Bereich für QR-Code WLAN und QR-Code WLAN mit Bildunterschrift in dem Front-End-Bereich
*/
class QRCode_WiFi_BA_DM_Plugin {

	private $qrCode_WiFi_size;			# String - Auflösung von QR-Code
	private $qrCode_WiFi_ecc_level;		# String - QR-Code ECC Level
	private $qrCode_WiFi_border;		# int - Rand von QR-Code
	private $qrCode_WiFi_name;			# String - NAme des WLAN-Netzwerks
	private $qrCode_WiFi_password;		# String - WLAN-Passwort
	private $qrCode_WiFi_network_type;	# String - WLAN-Verschlüsselungsart
	private $qrCode_WiFi_hidden;		# String - WLAN Sichtbarkeit

	/*
	fügt alle nötigen Bestandteile ins WordPress ein
	*/
	function start_QRCode_WiFi_BA_DM_Plugin() {
		add_shortcode('qrcode_wifi_ba_dm_plugin', array( &$this, 'add_frontend_content_for_QRCode_WiFi_BA_DM_Plugin'));
		add_action('admin_init', array( &$this, 'add_admin_metaBox_for_QRCode_WiFi_BA_DM_Plugin'));	
		add_action('save_post', array( &$this, 'save_QRCode_WiFi_BA_DM_Plugin'));
		add_filter('the_content', array(&$this, 'the_content_filter_for_QRCode_WiFi_BA_DM_Plugin'));
		add_filter('get_the_content', array(&$this, 'the_content_filter_for_QRCode_WiFi_BA_DM_Plugin'));
	}
	
	/*
	fügt eine Meta-Box in den Back-End-Bereich bei Erstellung einer neuen Seite in WordPress ein
	*/
	function add_admin_metaBox_for_QRCode_WiFi_BA_DM_Plugin() {
		add_meta_box("add_setting_for_QRCode_WiFi_BA_DM_Plugin", "QR-Code WLAN Login-Daten", array( &$this, "add_setting_for_QRCode_WiFi_BA_DM_Plugin"), "post", "normal", "high");
		add_meta_box("add_setting_for_QRCode_WiFi_BA_DM_Plugin", "QR-Code WLAN Login-Daten", array( &$this, "add_setting_for_QRCode_WiFi_BA_DM_Plugin"), "page", "normal", "high");
	}
	
	/*
	füllt die Meta-Box in den Back-End-Bereich mit Eingabefeldern
	*/
	function add_frontend_content_for_QRCode_WiFi_BA_DM_Plugin($content=null) {
		global $post;
		
		# wenn die Auflösung nicht gesetzt wurden dann wird die Standartauflösung genohmen 250x250
		$qrCode_WiFi_size = get_post_meta($post->ID,'qrCode_WiFi_size',true);
		if (empty($qrCode_WiFi_size) && $qrCode_WiFi_size == '') {
			$qrCode_WiFi_size = 250;
		}
		
		# wenn der ECC Level nicht gesetzt wurde, dann sollte L als ECC Level übernohmen wernden
		$qrCode_WiFi_ecc_level = get_post_meta($post->ID,'qrCode_WiFi_ecc_level',true);
		if (empty($qrCode_WiFi_ecc_level) && $qrCode_WiFi_ecc_level == '') {
			$qrCode_WiFi_ecc_level = "L";
		}
		
		# Variablen werden mit aktuellen Werten initialisiert 
		$qrCode_WiFi_border = 0;
		$qrCode_WiFi_name = get_post_meta($post->ID,'qrCode_WiFi_name',true);
		$qrCode_WiFi_password = get_post_meta($post->ID,'qrCode_WiFi_password',true);
		$qrCode_WiFi_network_type = get_post_meta($post->ID,'qrCode_WiFi_network_type',true);
		$qrCode_WiFi_hidden = get_post_meta($post->ID,'qrCode_WiFi_hidden',true);
			
		# erzeugt QR-Code URL mit allen Parameter
		$qrCode_WiFi_url = "http://chart.apis.google.com/chart?
		cht=qr&chs=$qrCode_WiFi_size
		&chl=WIFI:
		S:$qrCode_WiFi_name;
		T:$qrCode_WiFi_network_type;
		P:$qrCode_WiFi_password;
		H:$qrCode_WiFi_hidden;;
		&chld=$qrCode_WiFi_ecc_level|$qrCode_WiFi_border";
		
		$id = 'QRCode-'.rand();
		# bildet aus der QR-Code-URL ein Bild welches zurückgegeben wird
		$content = '<style>#'.$id.'{height:'.$qrCode_WiFi_size.';'.$qrCode_WiFi_size.'}</style>
			<div id="'.$id.'" class="'.$id.'">
				<dl class="'.$id.'">
					<dt><img alt="QR-Code WiFi von dem WLAN-Netz '.$qrCode_WiFi_name.'" src="'.$qrCode_WiFi_url.'"alt="QR Code WiFi" width="'.$qrCode_WiFi_size.'" height="'.$qrCode_WiFi_size.'"/></dt>
					<dd><strong>QR-Code WLAN: '.$qrCode_WiFi_name.'</strong></dd>
				</dl>
			</div>
		';
		
		# contetn wird auf leer gesetzt wenn ALLE Parameter leer sind
		if(empty($qrCode_WiFi_name) && $qrCode_WiFi_name == '' 
			&& empty($qrCode_WiFi_password) && $qrCode_WiFi_password == '' 
			&& empty($qrCode_WiFi_network_type) && $qrCode_WiFi_network_type == '' 
			&& empty($qrCode_WiFi_hidden) && $qrCode_WiFi_hidden == '' ) {
			$content = '';
		}

		return do_shortcode($content);	
	}
	
	function add_setting_for_QRCode_WiFi_BA_DM_Plugin() {
		global $post;
		
		# prüft welche Einstellung qrCode_WiFi_ecc_level in der Datenbank gespeichert wurde und setzt sie dann als ausgewählt
		function is_selected_qrCode_WiFi_ecc_level($value_ecc_level) {
			global $post;
			if(get_post_meta($post->ID,'qrCode_WiFi_ecc_level',true) == $value_ecc_level) {
				return "selected";
			}
		}	
		
		# prüft welche Einstellung qrCode_WiFi_ecc_level in der Datenbank gespeichert wurde und setzt sie dann als ausgewählt
		function is_selected_qrCode_WiFi_size($value_size) {
			global $post;
			$qrCode_WiFi_size = get_post_meta($post->ID,'qrCode_WiFi_size',true);
			if (empty($qrCode_WiFi_size) && $qrCode_WiFi_size == '') {
				$qrCode_WiFi_size = 250;
			}
			if($qrCode_WiFi_size == $value_size) {
				return "selected";
			}
		}
		
		# prüft welche Einstellung qrCode_WiFi_network_type in der Datenbank gespeichert wurde und setzt sie dann als ausgewählt
		function is_selected_network_type($value_size) {
			global $post;
			$qrCode_WiFi_network_type = get_post_meta($post->ID,'qrCode_WiFi_network_type',true);
			if($qrCode_WiFi_network_type == $value_size) {
				return "selected";
			}
		}		
		
		# prüft welche Einstellung qrCode_WiFi_network_type in der Datenbank gespeichert wurde und setzt sie dann als ausgewählt
		function is_selected_hidden($value_size) {
			global $post;
			$qrCode_WiFi_hidden = get_post_meta($post->ID,'qrCode_WiFi_hidden',true);
			if($qrCode_WiFi_hidden == $value_size) {
				return "selected";
			}
		}

		echo '
		<style type="text/css">input{width:100%}</style>
		<style type="text/css">th{vertical-align:top;text-align:left;padding:10px 5px 10px 0;width:15%}</style>
		<style type="text/css">td{width:80%}</style>		
		<table style="width:100%">
			<tr>
				<th>
					<label for="qrCode_WiFi_name">'.__('WLAN SSID:').'</label>
				</th>
				<td>
					<input type="text" size="20" maxlength="20" id="qrCode_WiFi_name" class="qrCode_WiFi_name" name="qrCode_WiFi_name" placeholder="WLAN_Netz" value="'.get_post_meta($post->ID,'qrCode_WiFi_name',true).'" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="qrCode_WiFi_password">'.__('WLAN Passwort:').'</label>
				</th>
				<td>
					<input type="text" size="30" maxlength="30" id="qrCode_WiFi_password" class="qrCode_WiFi_password" name="qrCode_WiFi_password" placeholder="12Ab!%#.34DF" value="'.get_post_meta($post->ID,'qrCode_WiFi_password',true).'" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="qrCode_WiFi_network_type">'.__('Verschlüsselungsart:').'</label>
				</th>
				<td>
					<select name="qrCode_WiFi_network_type" id="qrCode_WiFi_network_type" class="qrCode_WiFi_network_type" value="">
						<option value="" '.is_selected_network_type("").'></option>
						<option value="WEP" '.is_selected_network_type("WEP").'>WEP</option>
						<option value="WPA" '.is_selected_network_type("WPA").'>WPA</option>
						<option value="WPA2" '.is_selected_network_type("WPA2").'>WPA2</option>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<label for="qrCode_WiFi_hidden">'.__('Sichtbarkeit:').'</label>
				</th>
				<td>
					<select name="qrCode_WiFi_hidden" id="qrCode_WiFi_hidden" class="qrCode_WiFi_hidden" value="">
						<option value="" '.is_selected_hidden("").'></option>
						<option value="true" '.is_selected_hidden("true").'>AN</option>
						<option value="false" '.is_selected_hidden("false").'>AUS</option>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<label for="qrCode_WiFi_size">'.__('QR-Code Auflösung:').'</label>
				</th>
				<td>
					<select name="qrCode_WiFi_size" id="qrCode_WiFi_size" class="qrCode_WiFi_size" value="">
						<option value="" '.is_selected_qrCode_WiFi_size("").'></option>
						<option value="50" '.is_selected_qrCode_WiFi_size("50").'>50 x 50</option>
						<option value="100" '.is_selected_qrCode_WiFi_size("100").'>100 x 100</option>
						<option value="150" '.is_selected_qrCode_WiFi_size("150").'>150 x 150</option>
						<option value="200" '.is_selected_qrCode_WiFi_size("200").'>200 x 200</option>
						<option value="250" '.is_selected_qrCode_WiFi_size("250").'>250 x 250</option>
						<option value="300" '.is_selected_qrCode_WiFi_size("300").'>300 x 300</option>
						<option value="350" '.is_selected_qrCode_WiFi_size("350").'>350 x 350</option>
						<option value="400" '.is_selected_qrCode_WiFi_size("400").'>400 x 400</option>
						<option value="450" '.is_selected_qrCode_WiFi_size("450").'>450 x 450</option>
						<option value="500" '.is_selected_qrCode_WiFi_size("500").'>500 x 500</option>
					</select>
				</td>
			</tr>			
			<tr>
				<th>
					<label for="qrCode_WiFi_ecc_level">'.__('ECC Level:').'</label>
				</th>
				<td>
					<select name="qrCode_WiFi_ecc_level" id="qrCode_WiFi_ecc_level class="qrCode_WiFi_ecc_level" value="">
						<option value="L" '.is_selected_qrCode_WiFi_ecc_level("L").'>L (Korrektur bis zu 7%)</option>
						<option value="M" '.is_selected_qrCode_WiFi_ecc_level("M").'>M (Korrektur bis zu 15%)</option>
						<option value="Q" '.is_selected_qrCode_WiFi_ecc_level("Q").'>Q (Korrektur bis zu 25%)</option>
						<option value="H" '.is_selected_qrCode_WiFi_ecc_level("H").'>H (Korrektur bis zu 30%)</option>
					</select>
				</td>
			</tr>
		</table>
		<!-- #custom_setting_for_pages -->';
	}
	
	# speichert die jeweilige Varieablen in Datenbank als POST
	function save_QRCode_WiFi_BA_DM_Plugin(){
		global $post;
		# prüft zerst $post, ist er nicht vorhanden dann lohnen sich die unteren Abfragen nicht, da es ja noch keine Posts dazu gibt und es zu lauter Fehlermeldungen führen würde (wegen nicht vorhandener Post-Einträge)
		if (!empty($post)) {
			update_post_meta($post->ID, "qrCode_WiFi_name", $_POST['qrCode_WiFi_name']);
			update_post_meta($post->ID, "qrCode_WiFi_password", $_POST['qrCode_WiFi_password']);
			update_post_meta($post->ID, "qrCode_WiFi_network_type", $_POST['qrCode_WiFi_network_type']);
			update_post_meta($post->ID, "qrCode_WiFi_hidden", $_POST['qrCode_WiFi_hidden']);
			update_post_meta($post->ID, "qrCode_WiFi_size", $_POST['qrCode_WiFi_size']);
			update_post_meta($post->ID, "qrCode_WiFi_ecc_level", $_POST['qrCode_WiFi_ecc_level']);
		}

	}
	
	# filtern den Inhalt und gibt ihn als contet zurück
	function the_content_filter_for_QRCode_WiFi_BA_DM_Plugin($content) {
		global $post;
		
		$qrCode_WiFi_name = get_post_meta($post->ID,'qrCode_WiFi_name',true);
		if(!empty($qrCode_WiFi_name) && $qrCode_WiFi_name != ''){
			$content .= do_shortcode('[qrcode_wifi_ba_dm_plugin qrCode_WiFi_name="'.$qrCode_WiFi_name.'"]');
		}
		
		$qrCode_WiFi_password = get_post_meta($post->ID,'qrCode_WiFi_password',true);
		# Prüffe noch ob $contetn nicht bereits Daten enthält, da sonst der gleiche Inhalt an das $contetn eingefügt wird
		if(!empty($qrCode_WiFi_password) && $qrCode_WiFi_password != '' && empty($content)){
			$content .= do_shortcode('[qrcode_wifi_ba_dm_plugin qrCode_WiFi_password="'.$qrCode_WiFi_password.'"]');
		}
		
		$qrCode_WiFi_network_type = get_post_meta($post->ID,'qrCode_WiFi_network_type',true);
		if(!empty($qrCode_WiFi_network_type) && $qrCode_WiFi_network_type != '' && empty($content)){
			$content .= do_shortcode('[qrcode_wifi_ba_dm_plugin qrCode_WiFi_network_type="'.$qrCode_WiFi_network_type.'"]');
		}
		
		$qrCode_WiFi_hidden = get_post_meta($post->ID,'qrCode_WiFi_hidden',true);
		if(!empty($qrCode_WiFi_hidden) && $qrCode_WiFi_hidden != '' && empty($content)){
			$content .= do_shortcode('[qrcode_wifi_ba_dm_plugin qrCode_WiFi_hidden="'.$qrCode_WiFi_hidden.'"]');
		}
		
		$qrCode_WiFi_size = get_post_meta($post->ID,'qrCode_WiFi_size',true);
		if(!empty($qrCode_WiFi_size) && $qrCode_WiFi_size != '' && empty($content)){
			$content .= do_shortcode('[qrcode_wifi_ba_dm_plugin qrCode_WiFi_size="'.$qrCode_WiFi_size.'"]');
		}		
		
		$qrCode_WiFi_ecc_level = get_post_meta($post->ID,'qrCode_WiFi_ecc_level',true);
		if(!empty($qrCode_WiFi_ecc_level) && $qrCode_WiFi_ecc_level != '' && empty($content)){
			$content .= do_shortcode('[qrcode_wifi_ba_dm_plugin qrCode_WiFi_ecc_level="'.$qrCode_WiFi_ecc_level.'"]');
		}
		
		return $content;
	}
	
}

?>