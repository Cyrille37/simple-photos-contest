<?php

/*
 * Front part of plugin: AEF Simple Photos Contest
 */

require_once( __DIR__ . '/aef-photos-contest.php');

class AefPhotosContestFront extends AefPhotosContest {

	const SHORT_CODE_PHOTOS_CONTEST = 'aefPhotosContest';
	const COOKIE_VOTER = 'aefPC_Voter';

	public function __construct() {

		parent::__construct();

		_log(__METHOD__ . ' session_id():' . (session_id() ? session_id() : 'null'));

		add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));

		add_shortcode(self::SHORT_CODE_PHOTOS_CONTEST, array($this, 'wp_shortcode_aefPhotosContest'));

		/*
		  if (!isset($_COOKIE[self::COOKIE_VOTER])) {
		  setcookie(self::COOKIE_VOTER, 1, 0);
		  }
		  else{
		  //setcookie(self::COOKIE_VOTER, null, -1);
		  }
		 */

		if (defined('DOING_AJAX') && DOING_AJAX) {

			add_action('send_headers', 'wp_send_headers_ajax');

			add_action('wp_ajax_nopriv_vote', array($this, 'wp_ajax_vote'));

			add_action('init_ajax_nopriv_vote_auth', array($this, 'init_ajax_vote_auth'));
			add_action('wp_ajax_nopriv_vote_auth', array($this, 'wp_ajax_vote_auth'));
		}
	}

	function wp_send_headers_ajax() {

		// The application/json Media Type for JavaScript Object Notation (JSON)
		// http://www.ietf.org/rfc/rfc4627.txt
		header('Content-Type: application/json; charset=' . get_option('blog_charset'));
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
		wp_enqueue_script('aef-ajax-vote', self::$javascript_url . '/aef.vote.js', array('jquery'));
		// declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
		//wp_localize_script('my-ajax-request', 'AefPC', array('ajaxurl' => admin_url('admin-ajax.php')));
		wp_localize_script('aef-ajax-vote', 'AefPC', array('ajaxurl' => self::$plugin_url . '/aef-wp-front-ajax.php'));
	}

	/**
	 * IMPORTANT: Don't use camelCase or UPPER-CASE for your attributes names,
	 * they are lower-cased during shortcode_atts()
	 * @param array $attrs
	 */
	public function wp_shortcode_aefPhotosContest($attrs) {

		global $aefPC;

		include self::$templates_folder . '/front-gallery-shortcode.php';
		//return ;
	}

	public function getVoterEmail() {

		if( !isset($_COOKIE[self::COOKIE_VOTER]))
			return null ;
		$c = explode('#', $_COOKIE[self::COOKIE_VOTER]);
		_log('c: '.print_r( $c,true));
		if (!isset($c[1]) || empty($c[1]))
			return null;
		$h2 = hash_hmac('SHA256', $c[0], AUTH_KEY);
		if ($h2 == $c[1])
			return $c[0];
		return null;
	}

	public function setVoterEmail($email) {
		$cookie_voter = $email . '#' . hash_hmac('SHA256', $email, AUTH_KEY);
		setcookie(self::COOKIE_VOTER, $cookie_voter, 0, '/');
	}

	public function wp_ajax_vote() {
		_log(__METHOD__);

		$out = array();

		$voterEmail = $this->getVoterEMail();
		if (empty($voterEmail)) {
			$out['command'] = 'show_auth_buttons';
		}
		else {

			$out = array(
				'command' => 'vote',
			);
		}
		echo json_encode($out);
	}

	protected $ajax_ouput_data;

	public function init_ajax_vote_auth() {
		_log(__METHOD__);

		require_once(__DIR__ . '/auth/auth.php' );

		foreach ($_REQUEST as $k => $v) {
			_log($k . '=' . print_r($v, true));
		}

		$social_auth_provider = $_REQUEST['social_auth_provider'];
		$social_auth_signature = $_REQUEST['social_auth_signature'];

		$email = null;
		$first_name = null;
		$last_name = null;

		$this->ajax_ouput_data = array();

		switch ($social_auth_provider) {
			case 'facebook':
				if (!social_auth_verify_signature($_REQUEST['social_auth_access_token'], $social_auth_signature)) {
					
					$this->ajax_ouput_data['command'] = 'error';
					$this->ajax_ouput_data['message'] = 'Failed signature verification';
				}
				else {
					$fb_json = json_decode(sc_curl_get_contents("https://graph.facebook.com/me?access_token=" . $_REQUEST['social_auth_access_token']));

					//_log('fb: ' . print_r($fb_json, true));
					// stdClass Object(
					// [id] => 1423058397
					// [name] => Cyrille Giquello
					// [first_name] => Cyrille
					// [last_name] => Giquello
					// [link] => http://www.facebook.com/cyrille.giquello
					// [username] => cyrille.giquello
					// [gender] => male
					// [email] => cyrille.facebook@giquello.fr
					// [timezone] => 1
					// [locale] => fr_FR
					// [verified] => 1
					// [updated_time] => 2013-02-07T18:58:00+0000
					// )

					$email = $fb_json->{'email'};
					$first_name = $fb_json->{'first_name'};
					$last_name = $fb_json->{'last_name'};

					$this->ajax_ouput_data['command'] = 'auth_ok';
					$this->ajax_ouput_data['email'] = $email;
					$this->ajax_ouput_data['first_name'] = $first_name;
					$this->ajax_ouput_data['last_name'] = $last_name;
				}
				break;
		}

		if (isset($email)) {
			$this->setVoterEmail($email);
		}
	}

	public function wp_ajax_vote_auth() {
		_log(__METHOD__);
		echo json_encode($this->ajax_ouput_data);
	}

}
