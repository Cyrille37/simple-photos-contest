<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/* AJAX check  */
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	die('Unknow what to do baby!');
}
define('DOING_AJAX', true);
define('WP_ADMIN', false);

/** Load WordPress Bootstrap */
//require_once( __DIR__ . '/../../../wp-load.php' );
require_once( dirname(dirname(dirname( dirname( __FILE__ ))) ) . '/wp-load.php' );

/** Allow for cross-domain requests (from the frontend). */
send_origin_headers();

// Require an action parameter
if (empty($_REQUEST['action']))
	die('0');

//@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
@header( 'X-Robots-Tag: noindex' );

send_nosniff_header();
nocache_headers();

require_once( __DIR__ . '/aef-photos-contest-front.php');

do_action( 'init' );
do_action( 'init_ajax_nopriv_' . $_REQUEST['action'] );
do_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] ); // Non-admin actions

wp_die();
