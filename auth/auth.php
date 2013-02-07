<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once( dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php' );

//define('WP_DEBUG', true);
//define('WP_DEBUG_DISPLAY', false);
//define('WP_DEBUG_LOG', false);
if (!function_exists('_log')) {

	function _log($message) {
		if (WP_DEBUG === true) {
			if (is_array($message) || is_object($message)) {
				error_log(print_r($message, true));
			}
			else {
				error_log($message);
			}
		}
	}

}

if (!function_exists('social_connect_get_user_by_meta')) {
	function social_connect_get_user_by_meta($meta_key, $meta_value) {
		global $wpdb;

		$sql = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '%s' AND meta_value = '%s'";
		return $wpdb->get_var($wpdb->prepare($sql, $meta_key, $meta_value));
	}

}

if (!function_exists('social_connect_generate_signature')) {
function social_connect_generate_signature($data) {
	return hash('SHA256', AUTH_KEY . $data);
}
}

function social_auth_verify_signature($data, $signature) {
	$generated_signature = social_connect_generate_signature($data);
	if ($generated_signature == $signature) {
		return true;
	}
	return false;
}

if (!function_exists('sc_curl_get_contents')) {
function sc_curl_get_contents($url) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

	$html = curl_exec($curl);

	curl_close($curl);

	return $html;
}
}
