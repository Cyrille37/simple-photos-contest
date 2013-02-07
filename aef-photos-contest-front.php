<?php

/*
 * Front part of plugin: AEF Simple Photos Contest
 */

require_once( __DIR__ . '/aef-photos-contest.php');

class AefPhotosContestFront extends AefPhotosContest {

	const SHORT_CODE_PHOTOS_CONTEST = 'aefPhotosContest';

	public function __construct() {

		parent::__construct();

		_log(__METHOD__ . ' session_id():' . (session_id() ? session_id() : 'null'));

		add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));

		add_shortcode(self::SHORT_CODE_PHOTOS_CONTEST, array($this, 'wp_shortcode_aefPhotosContest'));

		add_action('wp_ajax_nopriv_vote', array($this, 'wp_ajax_vote') );

	}

	public function wp_enqueue_scripts() {

		//_log(__METHOD__);

		wp_enqueue_style('wp-jquery-ui-dialog');

		wp_enqueue_script('jquery');
		// using jquery-ui
		wp_enqueue_script('jquery-ui-core');
		//wp_enqueue_script('jquery-ui-position');
		wp_enqueue_script('jquery-ui-dialog');
		// using AD Gallery
		wp_enqueue_style('ad-gallery-css', self::$javascript_url . '/AD_Gallery-1.2.7/jquery.ad-gallery.css');
		wp_enqueue_script('ad-gallery', self::$javascript_url . '/AD_Gallery-1.2.7/jquery.ad-gallery.min.js');

		//wp_enqueue_style('thickbox');
		//wp_enqueue_script('thickbox');
		// embed the javascript file that makes the AJAX request
		wp_enqueue_script('my-ajax-request', self::$javascript_url . '/aef.vote.js', array('jquery'));
		// declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
		//wp_localize_script('my-ajax-request', 'AefPC', array('ajaxurl' => admin_url('admin-ajax.php')));
		wp_localize_script('my-ajax-request', 'AefPC', array('ajaxurl' => self::$plugin_url.'/aef-wp-front-ajax.php'));
	}

	/**
	 * IMPORTANT: Don't use camelCase or UPPER-CASE for your attributes names,
	 * they are lower-cased during shortcode_atts()
	 * @param array $attrs
	 */
	public function wp_shortcode_aefPhotosContest($attrs) {

		$attrs = shortcode_atts(array(
			'foo' => 'something',
			'bar' => 'something else',
			), $attrs);

		include self::$templates_folder . '/front-gallery-shortcode.php';
		return '<h3>shortcode result:</h3><p>' . implode(',', $attrs) . '</p>';
	}

	public function wp_ajax_vote() {
		_log(__METHOD__);

		$response = array(
			'what' => 'foobar',
			'action' => 'update_something',
			'id' => '1',
			'data' => '<p><strong>Hello world!</strong></p>'
		);
		$xmlResponse = new WP_Ajax_Response($response);
		$xmlResponse->send();
	}

}
