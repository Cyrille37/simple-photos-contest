<?php

/*
 * Front part of plugin: Simple Photos Contest
 */

require_once( __DIR__ . '/SimplePhotosContest.php');

class SimplePhotosContestFront extends SimplePhotosContest {

	public function __construct() {

		parent::__construct();

		//_log(__METHOD__);
		// To early, does not work
		//if( $this->has_shortcode(self::SHORT_CODE_PHOTOS_CONTEST) )

		add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));

		add_shortcode(self::SHORT_CODE_PHOTOS_CONTEST, array($this, 'wp_shortcode_SimplePhotosContest'));

		if (defined('DOING_AJAX') && DOING_AJAX) {

			add_action('send_headers', 'wp_send_headers_ajax');

			add_action('init_ajax_nopriv_can_vote', array($this, 'wp_ajax_can_vote'));

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

		if (!$this->has_shortcode(self::SHORT_CODE_PHOTOS_CONTEST)) {
			return;
		}

		wp_enqueue_script('jquery');
		// using AD Gallery
		//wp_enqueue_style('ad-gallery-css', self::$javascript_url . 'PG_Gallery-1.2.7/jquery.pg-gallery.css');
		wp_enqueue_style('ad-gallery-css', self::$styles_url . 'pg-gallery/pg-gallery.css');
		wp_enqueue_script('ad-gallery', self::$javascript_url . 'PG_Gallery-1.2.7/jquery.pg-gallery.min.js');

		// Fancybox
		wp_enqueue_style('fancybox-css', self::$javascript_url . 'fancybox-1.3.4/jquery.fancybox-1.3.4.css');
		wp_enqueue_script('fancybox', self::$javascript_url . 'fancybox-1.3.4/jquery.fancybox-1.3.4.pack.js');

		// embed the javascript file that makes the AJAX request
		wp_enqueue_script('spc-ajax-vote', self::$javascript_url . 'spc.vote.js', array('jquery'));
		// declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
		//wp_localize_script('my-ajax-request', 'gSPC', array('ajaxurl' => admin_url('admin-ajax.php')));
		wp_localize_script('spc-ajax-vote', 'gSPC',
			array(
			'ajaxurl' => self::$plugin_ajax_url,
			'facebook_client_id' => $this->getOption('facebookClientId'),
			'bloginfo_url' => get_bloginfo('url'),
			'bloginfo_name' => get_bloginfo('name')
		));
	}

	/**
	 * IMPORTANT: Don't use camelCase or UPPER-CASE for your attributes names,
	 * they are lower-cased during shortcode_atts()
	 * @param array $attrs
	 */
	public function wp_shortcode_SimplePhotosContest($attrs) {

		global $gSPC, $wpdb;

		//_log(__METHOD__);

		remove_filter('the_content', 'lab_add_rel_to_linked_img', 99);

		$qOptions = new SPCQueryOptions();
		$qOptions->orderBy('photo_order', 'ASC');
		$photos = $this->daoPhotos->getAll($qOptions);

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
	public function setVoterEmail($email = null) {

		if ($email == null) {
			setcookie(self::COOKIE_VOTER, null, -1, '/');
		}
		else {
			$cookie_voter = $email . '#' . hash_hmac('SHA256', $email, AUTH_KEY);
			setcookie(self::COOKIE_VOTER, $cookie_voter, 0, '/');
		}
	}

	public function wp_ajax_can_vote() {

		$photo_id = isset($_REQUEST['photo_id']) ? $_REQUEST['photo_id'] : null;
		$voterEmail = $this->getVoterEMail();
		//_log(__METHOD__. ' voterEmail='.$voterEmail.', photo_id='.$photo_id);

		$output = array();

		if (empty($voterEmail)) {
			$output['command'] = 'can_vote';
			$output['can_vote'] = true;
		}
		else {
			$voterStatus = $this->getVoterStatusByEmail($voterEmail, $photo_id);
			$output['command'] = 'can_vote';
			$output['can_vote'] = $voterStatus->canVote;
		}
		//_log(__METHOD__. ' can_vote = '. ($output['can_vote']==true?'TRUE':'FALSE') );

		// Add image votes count to response
		if( ! empty($photo_id) )
		{
			$output['photo_votes_count'] = $this->getDaoVotes()->getVotesCountByPhoto($photo_id);
		}
		else{
			$output['photo_votes_count'] = null ;
		}

		echo json_encode($output);
		wp_die();
	}

	public function wp_ajax_vote_init() {

		global $gSPC;

		$voterEmail = null;

		if (!$this->isVoteOpen()) {
			echo '';
			return;
		}

		if (isset($_REQUEST['logout'])) {
			$this->setVoterEmail();
		}
		else {
			$voterEmail = $this->getVoterEMail();
		}

		$photo_id = isset($_REQUEST['photo_id']) ? $_REQUEST['photo_id'] : null;

		$voterStatus = $this->getVoterStatusByEmail($voterEmail, $photo_id);

		if (!$voterStatus->canVote && !empty($voterStatus->lastVotedPhotoId)) {
			$photo_id = $voterStatus->lastVotedPhotoId;
		}

		if (!empty($photo_id)) {
			$photo = $this->daoPhotos->getById($photo_id);
		}

		include( self::$templates_folder . 'front-vote-popup.php');
	}

	public function wp_ajax_vote() {

		//_log(__METHOD__);

		$voterEmail = $this->getVoterEMail();
		$photo_id = isset($_REQUEST['photo_id']) ? $_REQUEST['photo_id'] : null;
		$voterStatus = $this->getVoterStatusByEmail($voterEmail, $photo_id);

		$this->ajax_ouput_data = array();

		if ($voterStatus->canVote) {
			if (isset($_REQUEST['photo_id']) && ($photoId = intval($_REQUEST['photo_id'])) > 0) {

				$votes = $this->getDaoVotes();
				$votes->addVote($voterEmail, $photoId);
				$this->ajax_ouput_data['command'] = 'vote_ok';
				$this->ajax_ouput_data['photo_votes_count'] = $this->getDaoVotes()->getVotesCountByPhoto($photo_id);
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
		exit();
	}

	protected $ajax_ouput_data;

	/**
	 * Must be at INIT time because it set a COOKIE.
	 */
	public function init_ajax_vote_auth() {

		//_log(__METHOD__);

		require_once(__DIR__ . '/../auth/auth.php' );

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

				if (!spc_auth_verify_signature($_REQUEST['social_auth_access_token'], $social_auth_signature)) {
					$this->ajax_ouput_data['command'] = 'error';
					$this->ajax_ouput_data['message'] = 'Failed signature verification';
				}
				else {
					$fb_json = json_decode(spc_curl_get_contents("https://graph.facebook.com/me?access_token=" . $_REQUEST['social_auth_access_token']));
					$email = $fb_json->{'email'};
					$this->ajax_ouput_data['command'] = 'auth_ok';
					$this->ajax_ouput_data['email'] = $email;
					$this->ajax_ouput_data['first_name'] = $fb_json->{'first_name'};
					$this->ajax_ouput_data['last_name'] = $fb_json->{'last_name'};
				}
				break;

			case 'google':

				$sc_provider_identity = $_REQUEST['social_auth_openid_identity'];
				if (!spc_auth_verify_signature($sc_provider_identity, $social_auth_signature)) {
					$this->ajax_ouput_data['command'] = 'error';
					$this->ajax_ouput_data['message'] = 'Failed signature verification';
				}
				else {
					$email = $_REQUEST['social_auth_email'];
					$this->ajax_ouput_data['command'] = 'auth_ok';
					$this->ajax_ouput_data['email'] = $email;
					$this->ajax_ouput_data['first_name'] = $_REQUEST['social_auth_first_name'];
					$this->ajax_ouput_data['last_name'] = $_REQUEST['social_auth_last_name'];
				}
				break;

			case 'mail' :

				$data = $_REQUEST['social_auth_email'] . $_REQUEST['social_auth_access_token'];
				if (!spc_auth_verify_signature($data, $social_auth_signature)) {
					$this->ajax_ouput_data['command'] = 'error';
					$this->ajax_ouput_data['message'] = 'Le code est erroné ou un problème technique a empêché votre identification';
				}
				else {
					$email = $_REQUEST['social_auth_email'];
					$this->ajax_ouput_data['command'] = 'auth_ok';
					$this->ajax_ouput_data['email'] = $email;
				}
				break;

			default:
				wp_die('Unknow provider ' . (isset($social_auth_provider) ? $social_auth_provider : 'null'));
		}

		if (isset($email)) {
			$this->setVoterEmail($email);
		}
	}

	/**
	 * output data was computed at init_ajax_vote_auth().
	 */
	public function wp_ajax_vote_auth() {

		if (!$this->isVoteOpen()) {

			$this->ajax_ouput_data = array();
			$this->ajax_ouput_data['command'] = 'error';
			$this->ajax_ouput_data['message'] = 'Le vote est fermé';
		}

		echo json_encode($this->ajax_ouput_data);
	}

}
