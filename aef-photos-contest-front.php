<?php

/*
 * Front part of plugin: AEF Simple Photos Contest
 */

require_once( __DIR__ . '/aef-photos-contest.php');

class AefPhotosContestFront extends AefPhotosContest {

	const SHORT_CODE_PHOTOS_CONTEST = 'aefPhotosContest';
	const COOKIE_VOTER = 'aefPC_Voter';

	public function __construct() {

		global $wpdb;

		parent::__construct();

		//_log(__METHOD__);

		add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));

		add_shortcode(self::SHORT_CODE_PHOTOS_CONTEST, array($this, 'wp_shortcode_aefPhotosContest'));

		if (defined('DOING_AJAX') && DOING_AJAX) {

			add_action('send_headers', 'wp_send_headers_ajax');

			add_action('wp_ajax_nopriv_vote_init', array($this, 'wp_ajax_vote_init'));
			add_action('wp_ajax_nopriv_vote', array($this, 'wp_ajax_vote'));

			add_action('init_ajax_nopriv_vote_auth', array($this, 'init_ajax_vote_auth'));
			add_action('wp_ajax_nopriv_vote_auth', array($this, 'wp_ajax_vote_auth'));
		}
	}

	function wp_send_headers_ajax() {

		// The application/json Media Type for JavaScript Object Notation (JSON)wp_enqueue_scriptswp_enqueue_scripts
		// http://www.ietf.org/rfc/rfc4627.txt
		header('Content-Type: application/json; charset=' . get_option('blog_charset'));
	}

	public function wp_enqueue_scripts() {

		//_log(__METHOD__);

		if ($this->has_shortcode(self::SHORT_CODE_PHOTOS_CONTEST)) {

			wp_enqueue_script('jquery');
			// using AD Gallery
			//wp_enqueue_style('ad-gallery-css', self::$javascript_url . 'AD_Gallery-1.2.7/jquery.ad-gallery.css');
			wp_enqueue_style('ad-gallery-css', self::$styles_url . 'ad-gallery/ad-gallery.css');
			wp_enqueue_script('ad-gallery', self::$javascript_url . 'AD_Gallery-1.2.7/jquery.ad-gallery.min.js');
			// Fancybox
			wp_enqueue_style('fancybox-css', self::$javascript_url . 'fancybox-1.3.4/jquery.fancybox-1.3.4.css');
			wp_enqueue_script('fancybox', self::$javascript_url . 'fancybox-1.3.4/jquery.fancybox-1.3.4.pack.js');

			// embed the javascript file that makes the AJAX request
			wp_enqueue_script('aef-ajax-vote', self::$javascript_url . 'aef.vote.js', array('jquery'));
			// declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
			//wp_localize_script('my-ajax-request', 'AefPC', array('ajaxurl' => admin_url('admin-ajax.php')));
			wp_localize_script('aef-ajax-vote', 'AefPC', array('ajaxurl' => self::$plugin_ajax_url));
		}
	}

	/**
	 * IMPORTANT: Don't use camelCase or UPPER-CASE for your attributes names,
	 * they are lower-cased during shortcode_atts()
	 * @param array $attrs
	 */
	public function wp_shortcode_aefPhotosContest($attrs) {

		global $aefPC, $wpdb;

		//_log(__METHOD__);

		$qOptions = new AefQueryOptions();
		$qOptions->orderBy('id', 'ASC');
		$photos = $this->daoPhotos->find($qOptions);

		ob_start();
		include self::$templates_folder . '/front-gallery-shortcode.php';
		return ob_get_clean();
	}

	/**
	 * Retreive voter's email from the cookie self::COOKIE_VOTER
	 * @return string|null
	 */
	public function getVoterEmail() {

		if (!isset($_COOKIE[self::COOKIE_VOTER]))
			return null;
		$c = explode('#', $_COOKIE[self::COOKIE_VOTER]);

		if (!isset($c[1]) || empty($c[1]))
			return null;

		$h2 = hash_hmac('SHA256', $c[0], AUTH_KEY);
		if ($h2 == $c[1])
			return $c[0];

		return null;
	}

	/**
	 * Store the voter's email in the cookie self::COOKIE_VOTER
	 * @param string $email
	 */
	public function setVoterEmail($email) {
		$cookie_voter = $email . '#' . hash_hmac('SHA256', $email, AUTH_KEY);
		setcookie(self::COOKIE_VOTER, $cookie_voter, 0, '/');
	}

	public function wp_ajax_vote_init() {

		global $aefPC;

		//_log(__METHOD__);

		$voterEmail = $this->getVoterEMail();
		$voterStatus = $this->getVoterStatusByEmail($voterEmail);

		if (!$voterStatus->canVote && !empty($voterStatus->lastVotedPhotoId)) {
			$photo_id = $voterStatus->lastVotedPhotoId;
		}
		else if (isset($_REQUEST['photo_id'])) {
			$photo_id = $_REQUEST['photo_id'];
		}

		if (!empty($photo_id)) {
			$photo = $this->daoPhotos->getById($photo_id);
		}

		include( self::$templates_folder . 'front-vote-popup.php');
	}

	public function wp_ajax_vote() {

		//_log(__METHOD__);

		$voterEmail = $this->getVoterEMail();
		$voterStatus = $this->getVoterStatusByEmail($voterEmail);

		$this->ajax_ouput_data = array();

		if ($voterStatus->canVote) {
			if (isset($_REQUEST['photo_id']) && ($photoId = intval($_REQUEST['photo_id'])) > 0) {

				$votes = $this->getDaoVotes();
				$votes->addVote($voterEmail, $photoId);
				$this->ajax_ouput_data['command'] = 'vote_ok';
			}
			else {
				$this->ajax_ouput_data['command'] = 'error';
				$this->ajax_ouput_data['message'] = 'bad vote request';
			}
		}
		else {
			$this->ajax_ouput_data['command'] = 'error';
			$this->ajax_ouput_data['message'] = 'you are not allowed to vote';
		}

		echo json_encode($this->ajax_ouput_data);
	}

	protected $ajax_ouput_data;

	/**
	 * Must be at INIT time because it set a COOKIE.
	 */
	public function init_ajax_vote_auth() {

		_log(__METHOD__);

		require_once(__DIR__ . '/auth/auth.php' );

		//foreach ($_REQUEST as $k => $v) {
		//	_log($k . '=' . print_r($v, true));
		//}

		$social_auth_provider = $_REQUEST['social_auth_provider'];
		$social_auth_signature = $_REQUEST['social_auth_signature'];

		$email = null;
		$first_name = null;
		$last_name = null;

		$this->ajax_ouput_data = array();

		switch ($social_auth_provider) {

			case 'facebook':

				if (!aef_auth_verify_signature($_REQUEST['social_auth_access_token'], $social_auth_signature)) {
					$this->ajax_ouput_data['command'] = 'error';
					$this->ajax_ouput_data['message'] = 'Failed signature verification';
				}
				else {
					$fb_json = json_decode(aef_curl_get_contents("https://graph.facebook.com/me?access_token=" . $_REQUEST['social_auth_access_token']));

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

			case 'google':

				$sc_provider_identity = $_REQUEST['social_auth_openid_identity'];
				if (!aef_auth_verify_signature($sc_provider_identity, $social_auth_signature)) {
					$this->ajax_ouput_data['command'] = 'error';
					$this->ajax_ouput_data['message'] = 'Failed signature verification';
				}
				else {

					$email = $_REQUEST['social_auth_email'];
					$first_name = $_REQUEST['social_auth_first_name'];
					$last_name = $_REQUEST['social_auth_last_name'];

					$this->ajax_ouput_data['command'] = 'auth_ok';
					$this->ajax_ouput_data['email'] = $email;
					$this->ajax_ouput_data['first_name'] = $first_name;
					$this->ajax_ouput_data['last_name'] = $last_name;
				}
				break;

			default:
				wp_die('Unknow provider ' . (isset($social_auth_provider) ? $social_auth_provider : 'null'));
		}

		if (isset($email)) {
			$this->setVoterEmail($email);
		}
	}

	public function wp_ajax_vote_auth() {

		//_log(__METHOD__);

		echo json_encode($this->ajax_ouput_data);
	}

}
