<?php

/*
 * Front part of plugin: AEF Simple Photos Contest
 */

require_once( __DIR__ . '/aef-photos-contest.php');

class AefPhotosContestFront extends AefPhotosContest {

	const SHORT_CODE_PHOTOS_CONTEST = 'aefPhotosContest';

	public function __construct() {

		parent::__construct();

		add_shortcode(self::SHORT_CODE_PHOTOS_CONTEST, array($this, 'wp_shortcode_aefPhotosContest'));
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

		return '<h3>shortcode result:</h3><p>' . implode(',', $attrs) . '</p>';
	}

}
