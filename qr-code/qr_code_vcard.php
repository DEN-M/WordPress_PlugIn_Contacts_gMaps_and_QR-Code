<?php
/*
QRCode_vCard_BA_DM_Plugin
erzeugt Eingabefelder in Back-End-Bereich und QR-Code vCard mit Bildunterschrift in dem Front-End-Bereich
*/
class QRCode_vCard_BA_DM_Plugin {

	private $qrCode_vCard_size; 		# String - Auflösung von QR-Code
	private $qrCode_vCard_ecc_level; 	# String - QR-Code ECC Level
	private $qrCode_vCard_border; 		# int - Rand von QR-Code
	private $qrCode_vCard_name; 		# String - Name 
	private $qrCode_vCard_adr; 			# String - Adresse (Straße 1, 12345 Berlin)
	private $qrCode_vCard_tel; 			# String - Telefonnumer(0170123456789)
	private $qrCode_vCard_email; 		# String - Email (mail@mail.com)
	private $qrCode_vCard_WebUrl; 		# String - WebURL www.web.com
	private $qrCode_vCard_note; 		# String - Text...

	/*
	fügt alle nötigen Bestandteile ins WordPress ein
	*/
	function start_QRCode_vCard_BA_DM_Plugin() {
		add_shortcode('qrcode_vcard_ba_dm_plugin', array( &$this, 'add_frontend_content_for_qrCode_vCard_BA_DM_Plugin'));
		add_action('admin_init', array( &$this, 'add_admin_metaBox_for_QRCode_vCard_BA_DM_Plugin'));	
		add_action('save_post', array( &$this, 'save_QRCode_vCard_BA_DM_Plugin'));
		add_filter('the_content', array(&$this, 'the_content_filter_for_QRCode_vCard_BA_DM_Plugin'));
		add_filter('get_the_content', array(&$this, 'the_content_filter_for_QRCode_vCard_BA_DM_Plugin'));
	}
	
	/*
	fügt eine Meta-Box in den Back-End-Bereich bei Erstellung einer neuen Seite in WordPress ein
	*/
	function add_admin_metaBox_for_QRCode_vCard_BA_DM_Plugin() {
		add_meta_box("add_admin_settings_for_QRCode_vCard_BA_DM_Plugin", "QR-Code vCard", array( &$this, "add_admin_settings_for_QRCode_vCard_BA_DM_Plugin"), "post", "normal", "high");
		add_meta_box("add_admin_settings_for_QRCode_vCard_BA_DM_Plugin", "QR-Code vCard", array( &$this, "add_admin_settings_for_QRCode_vCard_BA_DM_Plugin"), "page", "normal", "high");
	}
	
	/*
	füllt die Meta-Box in den Back-End-Bereich mit Eingabefeldern
	*/
	function add_frontend_content_for_qrCode_vCard_BA_DM_Plugin($content=null) {
		global $post;
		
		# wenn die Auflösung nicht gesetzt wurden dann wird die Standartauflösung genohmen 250x250
		$qrCode_vCard_size = get_post_meta($post->ID,'qrCode_vCard_size',true);
		if (empty($qrCode_vCard_size) && $qrCode_vCard_size == '') {
			$qrCode_vCard_size = "250x250";
		}
		
		# wenn der ECC Level nicht gesetzt wurde, dann sollte L als ECC Level übernohmen wernden
		$qrCode_vCard_ecc_level = get_post_meta($post->ID,'qrCode_vCard_ecc_level',true);
		if (empty($qrCode_vCard_ecc_level) && $qrCode_vCard_ecc_level == '') {
			$qrCode_vCard_ecc_level = "L";
		}	
		
		# Variablen werden mit aktuellen Werten initialisiert 
		$qrCode_vCard_border = 0;
		$qrCode_vCard_name = get_post_meta($post->ID,'qrCode_vCard_name',true);
		$qrCode_vCard_adr = get_post_meta($post->ID,'qrCode_vCard_adr',true);
		$qrCode_vCard_tel = get_post_meta($post->ID,'qrCode_vCard_tel',true);
		$qrCode_vCard_email = get_post_meta($post->ID,'qrCode_vCard_email',true);
		$qrCode_vCard_WebUrl = get_post_meta($post->ID,'qrCode_vCard_WebUrl',true);
		$qrCode_vCard_note = get_post_meta($post->ID,'qrCode_vCard_note',true);
		
		# erzeugt QR-Code URL mit allen Parameter
		$qrCode_URL_vCard = 
			"http://chart.apis.google.com/chart?cht=qr&chs=
			$qrCode_vCard_size&chld=$qrCode_vCard_ecc_level|$qrCode_vCard_border&choe=UTF-8&chl=
			BEGIN:VCARD;VERSION:4.0%0A
			N:$qrCode_vCard_name%0A
			ADR;TYPE=WORK:;;$qrCode_vCard_adr%0A
			TEL;TYPE=WORK,VOICE:$qrCode_vCard_tel%0A
			EMAIL;TYPE=WORK:$qrCode_vCard_email%0A
			URL:$qrCode_vCard_WebUrl%0A
			NOTE:$qrCode_vCard_note%0A
			END:VCARD";
		
		$id = 'QRCode-'.rand();
		# bildet aus der QR-Code-URL ein Bild welches zurückgegeben wird
		$content = '<style>#'.$id.'{height:'.$qrCode_vCard_size.';'.$qrCode_vCard_size.'}</style>
			<div id="'.$id.'" class="'.$id.'">
				<dl class="'.$id.'">
					<dt><img alt="QR-Code vCard von '.$qrCode_vCard_name.'" src="'.$qrCode_URL_vCard.'" width="'.$qrCode_vCard_size.'" height="'.$qrCode_vCard_size.'"/></dt>
					<dd><strong>QR-Code vCard von '.$qrCode_vCard_name.'</strong></dd>
				</dl>
			</div>
		';
		
		# $contetn wird auf leer gesetzt wenn ALLE Parameter leer sind
		if(empty($qrCode_vCard_name) && $qrCode_vCard_name == '' && 
			empty($qrCode_vCard_adr) && $qrCode_vCard_adr == '' && 
			empty($qrCode_vCard_tel) && $qrCode_vCard_tel == '' && 
			empty($qrCode_vCard_email) && $qrCode_vCard_email == '' && 
			empty($qrCode_vCard_WebUrl) && $qrCode_vCard_WebUrl == '' && 
			empty($qrCode_vCard_note) && $qrCode_vCard_note == '') {
			$content = '';
		}
		
		return do_shortcode($content);	
	}
	
	# Füllt die Meta-Box mit den Inhalt (Eingabefelder)
	function add_admin_settings_for_QRCode_vCard_BA_DM_Plugin() {
		global $post;
		
		# prüft welche Einstellung qrCode_vCard_ecc_level in der Datenbank gespeichert wurde und setzt sie dann als ausgewählt
		function is_selected_qrCode_ecc_level($value_ecc_level) {
			global $post;
			if(get_post_meta($post->ID,'qrCode_vCard_ecc_level',true) == $value_ecc_level) {
				return "selected";
			}
		}		
		
		# prüft welche Einstellung qrCode_vCard_size in der Datenbank gespeichert wurde und setzt sie dann als ausgewählt
		function is_selected_qrCode_size($value_size) {
			global $post;
			$qrCode_vCard_size = get_post_meta($post->ID,'qrCode_vCard_size',true);
			if (empty($qrCode_vCard_size) && $qrCode_vCard_size == '') {
				$qrCode_vCard_size = "250x250";
			}
			if($qrCode_vCard_size == $value_size) {
				return "selected";
			}
		}

		# HTML-Inhalt für den Back-End-Bereich
		echo '
		<style type="text/css">input{width:100%}</style>
		<style type="text/css">th{vertical-align:top;text-align:left;padding:10px 5px 10px 0;width:15%}</style>
		<style type="text/css">td{width:80%}</style>		
		<table style="width:100%">
			<tr>
				<th>
					<label for="name_vorname" width:20%; padding:40px;>'.__('Name, Vorname / Firma:').'</label>
				</th>
				<td>
					<input type="text" size="50" maxlength="50" id="qrCode_vCard_name" class="qrCode_vCard_name" name="qrCode_vCard_name" placeholder="Name, Vorname (Auslassen, falls nicht verfügbar)" value="'.get_post_meta($post->ID,'qrCode_vCard_name',true).'" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="adresse">'.__('Adresse:').'</label>
				</th>
				<td>
					<input type="text" id="qrCode_vCard_adr" class="qrCode_vCard_adr" name="qrCode_vCard_adr" placeholder="Straße Hausnummer, PLZ Ort (Auslassen, falls nicht verfügbar)" value="'.get_post_meta($post->ID,'qrCode_vCard_adr',true).'" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="telefonnummer">'.__('Telefonnummer:').'</label>
				</th>
				<td>
					<input type="text" id="qrCode_vCard_tel" class="qrCode_vCard_tel" name="qrCode_vCard_tel" placeholder="004930123456789 (Auslassen, falls nicht verfügbar)" value="'.get_post_meta($post->ID,'qrCode_vCard_tel',true).'" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="email">'.__('E-Mailadresse:').'</label>
				</th>
				<td>
					<input type="text" id="qrCode_vCard_email" class="qrCode_vCard_email" name="qrCode_vCard_email" placeholder="email@email.com (Auslassen, falls nicht verfügbar)" value="'.get_post_meta($post->ID,'qrCode_vCard_email',true).'" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="webseite">'.__('Webseite:').'</label>
				</th>
				<td>
					<input type="text" id="qrCode_vCard_WebUrl" class="qrCode_vCard_WebUrl" name="qrCode_vCard_WebUrl" placeholder="www.web.com (Auslassen, falls nicht verfügbar)" value="'.get_post_meta($post->ID,'qrCode_vCard_WebUrl',true).'" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="notiz">'.__('Notiz:').'</label>
				</th>
				<td>
					<input type="text" size="100" maxlength="100" id="qrCode_vCard_note" class="qrCode_vCard_note" name="qrCode_vCard_note" placeholder="Text... (Auslassen, falls nicht verfügbar)" value="'.get_post_meta($post->ID,'qrCode_vCard_note',true).'" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="size">'.__('QR-Code Auflösung:').'</label>
				</th>
				<td>
					<select name="qrCode_vCard_size" id="qrCode_vCard_size" class="qrCode_vCard_size" value="">
						<option value="" '.is_selected_qrCode_size("").'></option>
						<option value="50x50" '.is_selected_qrCode_size("50x50").'>50 x 50</option>
						<option value="100x100" '.is_selected_qrCode_size("100x100").'>100 x 100</option>
						<option value="150x150" '.is_selected_qrCode_size("150x150").'>150 x 150</option>
						<option value="200x200" '.is_selected_qrCode_size("200x200").'>200 x 200</option>
						<option value="250x250" '.is_selected_qrCode_size("250x250").'>250 x 250</option>
						<option value="300x300" '.is_selected_qrCode_size("300x300").'>300 x 300</option>
						<option value="350x350" '.is_selected_qrCode_size("350x350").'>350 x 350</option>
						<option value="400x400" '.is_selected_qrCode_size("400x400").'>400 x 400</option>
						<option value="450x450" '.is_selected_qrCode_size("450x450").'>450 x 450</option>
						<option value="500x500" '.is_selected_qrCode_size("500x500").'>500 x 500</option>
					</select>
				</td>
			</tr>			
			<tr>
				<th>
					<label for="notiz">'.__('QR-Code ECC Level:').'</label>
				</th>
				<td>
					<select name="qrCode_vCard_ecc_level" id="qrCode_vCard_ecc_level class="qrCode_vCard_ecc_level" value="">
						<option value="L" '.is_selected_qrCode_ecc_level("L").'>L (Fehlerkorrektur bis zu 7%)</option>
						<option value="M" '.is_selected_qrCode_ecc_level("M").'>M (Fehlerkorrektur bis zu 15%)</option>
						<option value="Q" '.is_selected_qrCode_ecc_level("Q").'>Q (Fehlerkorrektur bis zu 25%)</option>
						<option value="H" '.is_selected_qrCode_ecc_level("H").'>H (Fehlerkorrektur bis zu 30%)</option>
					</select>
				</td>
			</tr>
		</table>
		<!-- #custom_setting_for_pages -->';
	}
	
	# speichert die jeweilige Varieablen in Datenbank als POST
	function save_QRCode_vCard_BA_DM_Plugin(){
		global $post;
		# prüft zerst $post, ist er nicht vorhanden dann lohnen sich die unteren Abfragen nicht, da es ja noch keine Posts dazu gibt und es zu lauter Fehlermeldungen führen würde (wegen nicht vorhandener Post-Einträge)
		if (!empty($post)) {
			update_post_meta($post->ID, "qrCode_vCard_name", $_POST['qrCode_vCard_name']);
			update_post_meta($post->ID, "qrCode_vCard_adr", $_POST['qrCode_vCard_adr']);
			update_post_meta($post->ID, "qrCode_vCard_tel", $_POST['qrCode_vCard_tel']);
			update_post_meta($post->ID, "qrCode_vCard_email", $_POST['qrCode_vCard_email']);
			update_post_meta($post->ID, "qrCode_vCard_WebUrl", $_POST['qrCode_vCard_WebUrl']);		
			update_post_meta($post->ID, "qrCode_vCard_note", $_POST['qrCode_vCard_note']);
			update_post_meta($post->ID, "qrCode_vCard_size", $_POST['qrCode_vCard_size']);
			update_post_meta($post->ID, "qrCode_vCard_ecc_level", $_POST['qrCode_vCard_ecc_level']);
		}
	}
	
	# filtern den Inhalt und gibt ihn als contet zurück
	function the_content_filter_for_QRCode_vCard_BA_DM_Plugin($content) {
		global $post;
		
		$qrCode_vCard_name = get_post_meta($post->ID,'qrCode_vCard_name',true);
		if(!empty($qrCode_vCard_name) && $qrCode_vCard_name != ''){
			$content .= do_shortcode('[qrcode_vcard_ba_dm_plugin qrCode_vCard_name="'.$qrCode_vCard_name.'"]');
		}
		
		$qrCode_vCard_adr = get_post_meta($post->ID,'qrCode_vCard_adr',true);
		# Prüffe noch ob $contetn nicht bereits Daten enthält, da sonst der gleiche Inhalt an das $contetn eingefügt wird
		if(!empty($qrCode_vCard_adr) && $qrCode_vCard_adr != '' && empty($content)){
			$content .= do_shortcode('[qrcode_vcard_ba_dm_plugin qrCode_vCard_adr="'.$qrCode_vCard_adr.'"]');
		}
		
		$qrCode_vCard_tel = get_post_meta($post->ID,'qrCode_vCard_tel',true);
		if(!empty($qrCode_vCard_tel) && $qrCode_vCard_tel != '' && empty($content)){
			$content .= do_shortcode('[qrcode_vcard_ba_dm_plugin qrCode_vCard_tel="'.$qrCode_vCard_tel.'"]');
		}
		
		$qrCode_vCard_email = get_post_meta($post->ID,'qrCode_vCard_email',true);
		if(!empty($qrCode_vCard_email) && $qrCode_vCard_email != '' && empty($content)){
			$content .= do_shortcode('[qrcode_vcard_ba_dm_plugin qrCode_vCard_email="'.$qrCode_vCard_email.'"]');
		}		
		
		$qrCode_vCard_WebUrl = get_post_meta($post->ID,'qrCode_vCard_WebUrl',true);
		if(!empty($qrCode_vCard_WebUrl) && $qrCode_vCard_WebUrl != '' && empty($content)){
			$content .= do_shortcode('[qrcode_vcard_ba_dm_plugin qrCode_vCard_WebUrl="'.$qrCode_vCard_WebUrl.'"]');
		}		
		
		$qrCode_vCard_note = get_post_meta($post->ID,'qrCode_vCard_note',true);
		if(!empty($qrCode_vCard_note) && $qrCode_vCard_note != '' && empty($content)){
			$content .= do_shortcode('[qrcode_vcard_ba_dm_plugin qrCode_vCard_note="'.$qrCode_vCard_note.'"]');
		}
		
		$qrCode_vCard_size = get_post_meta($post->ID,'qrCode_vCard_size',true);
		if(!empty($qrCode_vCard_size) && $qrCode_vCard_size != '' && empty($content)){
			$content .= do_shortcode('[qrcode_vcard_ba_dm_plugin qrCode_vCard_size="'.$qrCode_vCard_size.'"]');
		}		
		
		$qrCode_vCard_ecc_level = get_post_meta($post->ID,'qrCode_vCard_ecc_level',true);
		if(!empty($qrCode_vCard_ecc_level) && $qrCode_vCard_ecc_level != '' && empty($content)){
			$content .= do_shortcode('[qrcode_vcard_ba_dm_plugin qrCode_vCard_ecc_level="'.$qrCode_vCard_ecc_level.'"]');
		}
		
		return $content;
	}
	
}
?>