<?php

/*
Plugin Name: Easy Sheet Insert
Plugin URI: https://sysloventures.com
Description: Adds shortcode to easily insert text from a Google sheet.
Version: 1.0
Author: Richard Gonzalez
Author URI: https://sysloventures.com
License: GPLv2 or later
Text Domain: easy-sheet-insert
*/


/*
######################
# ADMIN MENU FOLLOWS #
######################
*/


add_action( 'admin_menu', 'esi_admin_menu' );

function esi_admin_menu() {
	add_management_page( 'Easy Sheet Insert', 'Easy Sheet Insert', 'manage_options', 'easy-sheet-insert/esi-admin-page.php', 'esi_admin_page', 'dashicons-tickets');
}

function esi_admin_page( $atts ){
	//Get the API key from the URL Parameter
	extract( shortcode_atts( array('param0' => 'API',), $atts ) );
	$API = stripslashes(esc_attr(esc_html($_GET[$param0])));
	if ( $API != ''){
		update_option( 'esi_api', $API);
		echo "<h3>API Saved</h3>";
	}
	//Get the Sheet ID from the URL Parameter
	extract( shortcode_atts( array('param1' => 'SID',), $atts ) );
	$SID = stripslashes(esc_attr(esc_html($_GET[$param1])));
	if ( $SID != ''){
		update_option( 'esi_sid', $SID);
		echo "<h3>Sheet ID Saved</h3>";
	}
	?>
	<div class="wrap">
		<h2>Easy Sheet Insert</h2>
		<h3>Uses for this plugin</h3>
		<p>
			This plugin can be used to easily allow for changing data on a page (such as prices) without even needing to enter the WordPress dashboard. One can just edit the data in a Google sheet and have the data instantly be reflected on their website.
		</p>
		<h3>How to use this plugin</h3>
		<p>
			This plugin adds a shortcode which allows the insertion of data from a cell in a Google Sheet to be displayed on a page. To use this, you will need 3 things: <ul>
		<li>•The ID of a Google Sheet that has sharing enabled (anyone with link can view)</li>
		<li>•An API key from the Google account the sheet is in with the Sheets API enabled</li>
		<li>•The ID of the cell where the data is</li>
		</ul>
		The shortcode needs one parameter to function: The cell you want to pull data from. It looks like this:<br><br>
		<span style="font-weight: bold;">[esi_sheetpull cell="[CELL HERE]"]</span><br><br>
		Simply replace the bracketed text (leave the quotes) with the appropriate data, eg. [esi_sheetpull cell="A1"].<br><br>
		Cells are identified with  an uppercase letter and a number (eg "A1" or "C5"). Formulas can be placed in the cells in the spreadsheet and the plugin will pull the result.
		</p><br>
		<!--FORM FOR GETTING THE API-->
		<?php
			echo "<p style='font-weight: bold;'>API Key: " .get_option('esi_api'). "</p>";
		?>
		<input class='input' type='text' id='dataPass' value=''>
		<button class='button' onclick='passData()'>Enter API</button>
		<script>
		function passData() {
		  var API = document.getElementById('dataPass').value;
		  target = '/wp-admin/tools.php?page=easy-sheet-insert%2Fesi-admin-page.php&API=';
		  window.location.href = target + API;
		}
		</script>
		<br><br>
		<!--FORM FOR GETTING THE SHEET ID-->
		<?php
			echo "<p style='font-weight: bold;'>Sheet ID: " .get_option('esi_sid'). "</p>";
		?>
		<input class='input' type='text' id='dataPass2' value=''>
		<button class='button' onclick='passData2()'>Enter Sheet ID</button>
		<script>
		function passData2() {
		  var SID = document.getElementById('dataPass2').value;
		  target = '/wp-admin/tools.php?page=easy-sheet-insert%2Fesi-admin-page.php&SID=';
		  window.location.href = target + SID;
		}
		</script>
	</div>
	<?php
}


/*
######################################
# ACTUAL PLUGIN FUNCTIONS BEGIN HERE #
######################################
*/


//Pull data from google sheet and display
function esi_pullSheetData( $attr ) {
	$test = 'test';
	$args = shortcode_atts( array(
		'cell' => 'A1'
	), $attr );
	$API = get_option('esi_api');
	$SID = get_option('esi_sid');
	$output = false;
	//Make the connection with the Google sheet
	$connection = wp_remote_get( "https://sheets.googleapis.com/v4/spreadsheets/{$SID}/values/{$args['cell']}?key={$API}");
	//Check cache first
	if ( ! wp_cache_get( $args['cell'] ) ){
	//Ensure connection was made
		if ( ! is_wp_error( $connection ) ) {
			$connection = json_decode( wp_remote_retrieve_body( $connection ), true);	
			//Check if values were entered
			if ( isset ( $connection['values'] ) ){
				$output = $connection['values'];
				//Add to cache
				wp_cache_add( $args['cell'], $output);
			}
			else {
				echo "ERROR";
				return;
			}
		}
	}
	else {
		//Pull from cache if already there
		return wp_cache_get( $args['cell'] );
	}
	return $output[0][0];
}
add_shortcode( 'esi_sheetpull', 'esi_pullSheetData' );