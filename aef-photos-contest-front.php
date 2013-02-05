<?php

/*
 * Front part of plugin: AEF Simple Photos Contest
 */

require_once( __DIR__ . '/aef-photos-contest.php');

class AefPhotosContestFront extends AefPhotosContest {

	const SHORT_CODE_PHOTOS_CONTEST = 'aefPhotosContest';

	public function __construct() {

		parent::__construct();

		add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));

		add_shortcode(self::SHORT_CODE_PHOTOS_CONTEST, array($this, 'wp_shortcode_aefPhotosContest'));
	}

	public function wp_enqueue_scripts()
	{

		//_log(__METHOD__);

		wp_enqueue_script('jquery');
		//wp_enqueue_style('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/smoothness/jquery-ui.css');
	
		wp_enqueue_style('ad-gallery-css',plugins_url( self::PLUGIN).'/js/AD_Gallery-1.2.7/jquery.ad-gallery.css');
		wp_enqueue_script('ad-gallery', plugins_url( self::PLUGIN).'/js/AD_Gallery-1.2.7/jquery.ad-gallery.min.js');
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

		include self::$templates_folder. '/front-gallery-shortcode.php';
		return '<h3>shortcode result:</h3><p>' . implode(',', $attrs) . '</p>';
	}

}
