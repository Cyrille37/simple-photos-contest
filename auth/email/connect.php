<?php

/*
 * 
 */

/* AJAX check  */
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	die('Unknow what to do baby!');
}
define('DOING_AJAX', true);
define('WP_ADMIN', false);

/** Load WordPress Bootstrap */
//require_once( __DIR__ . '/../../../wp-load.php' );
require_once( dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/wp-load.php' );
require_once(__DIR__ . '/../auth.php' );

/** Allow for cross-domain requests (from the frontend). */
send_origin_headers();

//@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
@header('X-Robots-Tag: noindex');

send_nosniff_header();
nocache_headers();

require_once( __DIR__ . '/../../controlers/SimplePhotosContestFront.php');

$output['command'] = 'error';
$output['message'] = 'bad vote request';

if (isset($_REQUEST['action'])) {
	switch ($_REQUEST['action']) {

		case 'emailSend':

			$email = $_REQUEST['email'];

			if (!is_email($email)) {
				$output['message'] = 'invalid email';
			}
			else {
				$pincode = rand(1, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
				$signature = spc_auth_generate_signature($email . $pincode);
				$cookie_pincode = $email . '#' . $signature;

				//setcookie(SimplePhotosContestFront::COOKIE_PINCODE, $cookie_pincode, 0, '/');

				add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
				$admin_email = get_bloginfo('admin_email');
				$blog_name = get_bloginfo('name');
				$blog_url = get_bloginfo('url');
				$headers = 'From: ' . $blog_name . ' <' . $admin_email . '>' . "\r\n";
				$subject = 'Vote concours photo ' . $blog_name;
				$message = '<p>Voici votre code pour voter au concours photo: ' . $pincode . '</p>';
				$message.= '<p>Cordialement, l´équipe de <a href="' . $blog_url . '">' . $blog_url . '</a></p>';
				wp_mail($email, $subject, $message, $headers);

				$output['command'] = 'mail_sent';
				$output['message'] = $email;
				$output['social_auth_signature'] = $signature;
			}
			break;

		default:
			break;
	}
}

echo json_encode($output);

wp_die();
