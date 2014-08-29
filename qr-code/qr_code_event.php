<?php
/*
QRCode_vCard_BA_DM_Plugin
erzeugt Eingabefelder in Back-End-Bereich für QR-Code Event und QR-Code Event mit Bildunterschrift in dem Front-End-Bereich
*/
class QRCode_Event_BA_DM_Plugin {

	private $qrCode_event_size;			# String - Auflösung von QR-Code
	private $qrCode_event_ecc_level;	# String - QR-Code ECC Level
	private $qrCode_event_border; 		# int - Rand von QR-Code
	private $qrCode_event_name;			# String - Name für Event
	private $qrCode_event_start;		# String - Start Zeit und Datum
	private $qrCode_event_end;			# String - End Zeit und Datum
	private $qrCode_event_location;		# String - Ort für den Event
	private $qrCode_event_description;	# String - Beschreibung für den Event

	/*
	fügt alle nötigen Bestandteile ins WordPress ein
	*/
	function start_QRCode_Event_BA_DM_Plugin() {
		add_shortcode('qrcode_event_ba_dm_plugin', array( &$this, 'add_frontend_content_for_QRCode_Event_BA_DM_Plugin'));
		add_action('admin_init', array( &$this, 'add_admin_metaBox_for_QRCode_Event_BA_DM_Plugin'));	
		add_action('save_post', array( &$this, 'save_QRCode_Event_BA_DM_Plugin'));
		add_filter('the_content', array(&$this, 'the_content_filter_for_QRCode_Event_BA_DM_Plugin'));
		add_filter('get_the_content', array(&$this, 'the_content_filter_for_QRCode_Event_BA_DM_Plugin'));
	}
	
	/*
	fügt eine Meta-Box in den Back-End-Bereich bei Erstellung einer neuen Seite in WordPress ein
	*/
	function add_admin_metaBox_for_QRCode_Event_BA_DM_Plugin() {
		add_meta_box("add_admin_setting_for_QRCode_Event_BA_DM_Plugin", "QR-Code Termin", array( &$this, "add_admin_setting_for_QRCode_Event_BA_DM_Plugin"), "post", "normal", "high");
		add_meta_box("add_admin_setting_for_QRCode_Event_BA_DM_Plugin", "QR-Code Termin", array( &$this, "add_admin_setting_for_QRCode_Event_BA_DM_Plugin"), "page", "normal", "high");
	}
	
	/*
	füllt die Meta-Box in den Back-End-Bereich mit Eingabefeldern
	*/
	function add_frontend_content_for_QRCode_Event_BA_DM_Plugin($content=null) {
		global $post;
		
		# wenn die Auflösung nicht gesetzt wurden dann wird die Standartauflösung genohmen 250x250
		$qrCode_event_size = get_post_meta($post->ID,'qrCode_event_size',true);
		if (empty($qrCode_event_size) && $qrCode_event_size == '') {
			$qrCode_event_size = "250x250";
		}
		
		# wenn der ECC Level nicht gesetzt wurde, dann sollte L als ECC Level übernohmen wernden
		$qrCode_event_ecc_level = get_post_meta($post->ID,'qrCode_event_ecc_level',true);
		if (empty($qrCode_event_ecc_level) && $qrCode_event_ecc_level == '') {
			$qrCode_event_ecc_level = "L";
		}
		
		# Variablen werden mit aktuellen Werten initialisiert 
		$qrCode_event_border = 0;
		$qrCode_event_name = get_post_meta($post->ID,'qrCode_event_name',true);
		$qrCode_event_start = get_post_meta($post->ID,'qrCode_event_start',true);
		$qrCode_event_end = get_post_meta($post->ID,'qrCode_event_end',true);
		$qrCode_event_location = get_post_meta($post->ID,'qrCode_event_location',true);
		$qrCode_event_description = get_post_meta($post->ID,'qrCode_event_description',true);
		$string_Z = "Z";
		
		# erzeugt QR-Code URL mit allen Parameter
		$qrcode_url_event = "http://chart.apis.google.com/chart?cht=qr
		&chs=$qrCode_event_size
		&chld=$qrCode_event_ecc_level|$qrCode_event_border
		&choe=UTF-8
		&chl=BEGIN:VEVENT%0A
		SUMMARY:$qrCode_event_name%0A
		DTSTART:$qrCode_event_start$string_Z%0A
		DTEND:$qrCode_event_end$string_Z%0A
		LOCATION:$qrCode_event_location%0A
		DESCRIPTION:$qrCode_event_description%0A
		END:VEVENT";
		
		$id = 'QRCode-'.rand();
		# bildet aus der QR-Code-URL ein Bild welches zurückgegeben wird
		$content = '<style>#'.$id.'{height:'.$qrCode_event_size.';'.$qrCode_event_size.'}</style>
			<div id="'.$id.'" class="'.$id.'">
				<dl class="'.$id.'">
					<dt><img alt="QR-Code Termin: '.$qrCode_event_name.'" src="'.$qrcode_url_event.'"alt="QR Code Event" width="'.$qrCode_event_size.'" height="'.$qrCode_event_size.'"/></dt>
					<dd><strong>QR-Code Termin: '.$qrCode_event_name.'</strong></dd>
				</dl>
			</div>
		';
		
		# contetn wird auf leer gesetzt wenn ALLE Parameter leer sind
		if(empty($qrCode_event_name) && $qrCode_event_name == '' 
			&& empty($qrCode_event_location) && $qrCode_event_location == '' 
			&& empty($qrCode_event_description) && $qrCode_event_description == '') {
			$content = '';
		}

		return do_shortcode($content);	
	}
	
	function add_admin_setting_for_QRCode_Event_BA_DM_Plugin() {
		global $post;
		
		wp_register_style('css_style_for_datetimepicker_Event', plugins_url('css/jquery_datetimepicker.css',__FILE__));
		wp_enqueue_style('css_style_for_datetimepicker_Event');
		
		wp_deregister_script('jquery_for_Event');
		wp_enqueue_script('jquery_for_Event', plugins_url('js/jquery-1.11.0.min.js',__FILE__), array('jquery'));
		
		wp_deregister_script('jquery_datetimepicker_for_Event');
		wp_enqueue_script('jquery_datetimepicker_for_Event', plugins_url('js/jquery.datetimepicker.js',__FILE__), array('jquery'));
		
		wp_deregister_script('datetimepicker_for_Event');
		wp_enqueue_script('datetimepicker_for_Event', plugins_url('js/datetimepicker_for_event.js',__FILE__), array('jquery'));
		
		# prüft welche Einstellung qrCode_event_ecc_level in der Datenbank gespeichert wurde und setzt sie dann als ausgewählt
		function is_selected_qrCode_Event_ecc_level($value_ecc_level) {
			global $post;
			if(get_post_meta($post->ID,'qrCode_event_ecc_level',true) == $value_ecc_level) {
				return "selected";
			}
		}	
		
		# prüft welche Einstellung qrCode_event_ecc_level in der Datenbank gespeichert wurde und setzt sie dann als ausgewählt
		function is_selected_qrCode_Event_size($value_size) {
			global $post;
			$qrCode_event_size = get_post_meta($post->ID,'qrCode_event_size',true);
			if (empty($qrCode_event_size) && $qrCode_event_size == '') {
				$qrCode_event_size = "250x250";
			}
			if($qrCode_event_size == $value_size) {
				return "selected";
			}
		}	

		echo '
		<style type="text/css">input{width:100%}</style>
		<style type="text/css">th{vertical-align:top;text-align:left;padding:10px 5px 10px 0;width:15%}</style>
		<style type="text/css">td{width:40%}</style>		
		<table style="width:100%">
			<tr>
				<th>
					<label for="qrCode_event_name" width:20%; padding:40px;>'.__('Name:').'</label>
				</th>
				<td>
					<input type="text" size="100" maxlength="100" id="qrCode_event_name" class="qrCode_event_name" name="qrCode_event_name" placeholder="Termin Name" value="'.get_post_meta($post->ID,'qrCode_event_name',true).'" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="qrCode_event_start">'.__('Termin Start:').'</label>
				</th>
				<td>
					<input type="text" id="qrCode_event_start" class="qrCode_event_start" name="qrCode_event_start" placeholder="31.01.2014 14:00" value="'.get_post_meta($post->ID,'qrCode_event_start',true).'" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="qrCode_event_end">'.__('Termin Ende:').'</label>
				</th>
				<td>
					<input type="text" id="qrCode_event_end" class="qrCode_event_end" name="qrCode_event_end" placeholder="31.01.2014 15:00" value="'.get_post_meta($post->ID,'qrCode_event_end',true).'" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="qrCode_event_location" width:20%; padding:40px;>'.__('Ort:').'</label>
				</th>
				<td>
					<input type="text" size="100" maxlength="100" id="qrCode_event_location" class="qrCode_event_location" name="qrCode_event_location" placeholder="Muster Str. 1, 12345 Berlin" value="'.get_post_meta($post->ID,'qrCode_event_location',true).'" />
				</td>
			</tr>			
			<tr>
				<th>
					<label for="qrCode_event_description" width:20%; padding:40px;>'.__('Beschreibung:').'</label>
				</th>
				<td>
					<input type="text" size="100" maxlength="100" id="qrCode_event_description" class="qrCode_event_description" name="qrCode_event_description" placeholder="Beschreibung für den Termin" value="'.get_post_meta($post->ID,'qrCode_event_description',true).'" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="qrCode_event_size">'.__('QR-Code Auflösung:').'</label>
				</th>
				<td>
					<select name="qrCode_event_size" id="qrCode_event_size" class="qrCode_event_size" value="">
						<option value="" '.is_selected_qrCode_Event_size("").'></option>
						<option value="50x50" '.is_selected_qrCode_Event_size("50x50").'>50 x 50</option>
						<option value="100x100" '.is_selected_qrCode_Event_size("100x100").'>100 x 100</option>
						<option value="150x150" '.is_selected_qrCode_Event_size("150x150").'>150 x 150</option>
						<option value="200x200" '.is_selected_qrCode_Event_size("200x200").'>200 x 200</option>
						<option value="250x250" '.is_selected_qrCode_Event_size("250x250").'>250 x 250</option>
						<option value="300x300" '.is_selected_qrCode_Event_size("300x300").'>300 x 300</option>
						<option value="350x350" '.is_selected_qrCode_Event_size("350x350").'>350 x 350</option>
						<option value="400x400" '.is_selected_qrCode_Event_size("400x400").'>400 x 400</option>
						<option value="450x450" '.is_selected_qrCode_Event_size("450x450").'>450 x 450</option>
						<option value="500x500" '.is_selected_qrCode_Event_size("500x500").'>500 x 500</option>
					</select>
				</td>
			</tr>			
			<tr>
				<th>
					<label for="qrCode_event_ecc_level">'.__('ECC Level:').'</label>
				</th>
				<td>
					<select name="qrCode_event_ecc_level" id="qrCode_event_ecc_level class="qrCode_event_ecc_level" value="">
						<option value="L" '.is_selected_qrCode_Event_ecc_level("L").'>L (Korrektur bis zu 7%)</option>
						<option value="M" '.is_selected_qrCode_Event_ecc_level("M").'>M (Korrektur bis zu 15%)</option>
						<option value="Q" '.is_selected_qrCode_Event_ecc_level("Q").'>Q (Korrektur bis zu 25%)</option>
						<option value="H" '.is_selected_qrCode_Event_ecc_level("H").'>H (Korrektur bis zu 30%)</option>
					</select>
				</td>
			</tr>
		</table>
		<!-- #custom_setting_for_pages -->';
	}
	
	# speichert die jeweilige Varieablen in Datenbank als POST
	function save_QRCode_Event_BA_DM_Plugin(){
		global $post;
		# prüft zerst $post, ist er nicht vorhanden dann lohnen sich die unteren Abfragen nicht, da es ja noch keine Posts dazu gibt und es zu lauter Fehlermeldungen führen würde (wegen nicht vorhandener Post-Einträge)
		if (!empty($post)) {
			update_post_meta($post->ID, "qrCode_event_name", $_POST['qrCode_event_name']);
			update_post_meta($post->ID, "qrCode_event_start", $_POST['qrCode_event_start']);
			update_post_meta($post->ID, "qrCode_event_end", $_POST['qrCode_event_end']);
			update_post_meta($post->ID, "qrCode_event_location", $_POST['qrCode_event_location']);
			update_post_meta($post->ID, "qrCode_event_description", $_POST['qrCode_event_description']);
			update_post_meta($post->ID, "qrCode_event_size", $_POST['qrCode_event_size']);
			update_post_meta($post->ID, "qrCode_event_ecc_level", $_POST['qrCode_event_ecc_level']);
		}

	}
	
	# filtern den Inhalt und gibt ihn als contet zurück
	function the_content_filter_for_QRCode_Event_BA_DM_Plugin($content) {
		global $post;
		
		$qrCode_event_name = get_post_meta($post->ID,'qrCode_event_name',true);
		if(!empty($qrCode_event_name) && $qrCode_event_name != ''){
			$content .= do_shortcode('[qrcode_event_ba_dm_plugin qrCode_event_name="'.$qrCode_event_name.'"]');
		}
		
		$qrCode_event_start = get_post_meta($post->ID,'qrCode_event_start',true);
		# Prüffe noch ob $contetn nicht bereits Daten enthält, da sonst der gleiche Inhalt an das $contetn eingefügt wird
		if(!empty($qrCode_event_start) && $qrCode_event_start != '' && empty($content)){
			$content .= do_shortcode('[qrcode_event_ba_dm_plugin qrCode_event_start="'.$qrCode_event_start.'"]');
		}
		
		$qrCode_event_end = get_post_meta($post->ID,'qrCode_event_end',true);
		if(!empty($qrCode_event_end) && $qrCode_event_end != '' && empty($content)){
			$content .= do_shortcode('[qrcode_event_ba_dm_plugin qrCode_event_end="'.$qrCode_event_end.'"]');
		}
		
		$qrCode_event_location = get_post_meta($post->ID,'qrCode_event_location',true);
		if(!empty($qrCode_event_location) && $qrCode_event_location != '' && empty($content)){
			$content .= do_shortcode('[qrcode_event_ba_dm_plugin qrCode_event_location="'.$qrCode_event_location.'"]');
		}		
		
		$qrCode_event_description = get_post_meta($post->ID,'qrCode_event_description',true);
		if(!empty($qrCode_event_description) && $qrCode_event_description != '' && empty($content)){
			$content .= do_shortcode('[qrcode_event_ba_dm_plugin qrCode_event_description="'.$qrCode_event_description.'"]');
		}
		
		$qrCode_event_size = get_post_meta($post->ID,'qrCode_event_size',true);
		if(!empty($qrCode_event_size) && $qrCode_event_size != '' && empty($content)){
			$content .= do_shortcode('[qrcode_event_ba_dm_plugin qrCode_event_size="'.$qrCode_event_size.'"]');
		}		
		
		$qrCode_event_ecc_level = get_post_meta($post->ID,'qrCode_event_ecc_level',true);
		if(!empty($qrCode_event_ecc_level) && $qrCode_event_ecc_level != '' && empty($content)){
			$content .= do_shortcode('[qrcode_event_ba_dm_plugin qrCode_event_ecc_level="'.$qrCode_event_ecc_level.'"]');
		}
		
		return $content;
	}
	
}
?>