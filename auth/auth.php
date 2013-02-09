<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once( dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php' );

function aef_auth_generate_signature($data) {
	return hash('SHA256', AUTH_KEY . $data);
}

function aef_auth_verify_signature($data, $signature) {
	$generated_signature = aef_auth_generate_signature($data);
	if ($generated_signature == $signature) {
		return true;
	}
	return false;
}

function aef_curl_get_contents($url) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

	$html = curl_exec($curl);

	curl_close($curl);

	return $html;
}
