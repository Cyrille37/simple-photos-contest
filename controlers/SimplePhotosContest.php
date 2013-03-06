<?php

/*
 * 
 */
if (!defined('ABSPATH')) {
	header('Location:/');
	exit();
}

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


require_once( __DIR__ . '/../models/SPCVotesDao.php');
require_once( __DIR__ . '/../models/SPCPhotosDao.php');

class SimplePhotosContest {

	const PLUGIN = 'simple-photos-contest';
	const DBTABLE_PREFIX = 'spc';
	const REQUIRE_VERSION_WP = '3.5';
	const REQUIRE_VERSION_PHP = '5.1.2';
	const VERSION = '1.0';
	const DBVERSION = '1.0';
	const SHORT_CODE_PHOTOS_CONTEST = 'SimplePhotosContest';
	const COOKIE_VOTER = 'SPC_Voter';

	public static $plugin_name;
	public static $plugin;
	public static $plugin_file;
	public static $templates_folder;
	public static $plugin_url;
	public static $plugin_ajax_url;
	public static $images_url;
	public static $styles_url;
	public static $javascript_url;
	public static $adminConfigPageName;
	public static $options_name;

	/**
	 * @var array Plugin Options
	 */
	protected $options;
	protected $notices = array();
	protected $errors = array();

	/**
	 * @var SPCVotesDao
	 */
	protected $daoVotes;

	/**
	 *
	 * @var SPCPhotosDao
	 */
	protected $daoPhotos;

	/**
	 * Several date formats
	 * @var array
	 */
	public static $dateFormats = array(
		1 => array(
			'label' => 'dd/mm/yyyy (fr)',
			'format' => 'dd/mm/yy',
			'pattern_in' => '#(\d{1,2})/(\d{1,2})/(\d{2,4})#',
			'pattern_out' => '\\3-\\2-\\1',
			'pattern_display' => '\\3/\\2/\\1'
		),
		2 => array(
			'label' => 'mm/dd/yyyy (en)',
			'format' => 'mm/dd/yy',
			'pattern_in' => '#(\d{1,2})/(\d{1,2})/(\d{2,4})#',
			'pattern_out' => '\\3-\\1-\\2',
			'pattern_display' => '\\2/\\3/\\1'
		),
		3 => array(
			'label' => 'yyyy-mm-dd (iso)',
			'format' => 'yy-mm-dd',
			'pattern_in' => '#(\d{2,4})-(\d{1,2})-(\d{1,2})#',
			'pattern_out' => '\\1-\\2-\\3',
			'pattern_display' => '\\1-\\2-\\3'
		),
	);

	const VOTE_FREQ_ONEPERCONTEST = 'onePerContest';
	const VOTE_FREQ_ONEPERHOURS = 'onePerHours';
	const OPTION_VOTEFREQUENCY = 'voteFrequency';
	const OPTION_VOTEFREQUENCYHOURS = 'voteFrequencyHours';

	protected static $options_default = array(
		'photoFolder' => self::PLUGIN, // Default photos folder set to plugin's name
		'dateFormat' => 1,
		'thumbW' => 150,
		'thumbH' => 150,
		'viewW' => 1920,
		'viewH' => 1440,
		'photoDescLengthMax' => 32,
		self::OPTION_VOTEFREQUENCY => self::VOTE_FREQ_ONEPERCONTEST,
		self::OPTION_VOTEFREQUENCYHOURS => 24
	);

	public function __construct() {

		global $wpdb;

		self::$plugin_name = __('Photos Contest', self::PLUGIN);
		self::$plugin_url = plugins_url(self::PLUGIN) . '/';
		self::$plugin_ajax_url = self::$plugin_url . 'spc-wp-front-ajax.php';
		self::$images_url = self::$plugin_url . 'images/';
		self::$styles_url = self::$plugin_url . 'css/';
		self::$javascript_url = self::$plugin_url . 'js/';

		self::$adminConfigPageName = self::PLUGIN . '-configuration';
		self::$templates_folder = dirname(__FILE__) . '/../templates/';

		self::$options_name = self::PLUGIN;
		$this->loadOptions();

		add_action('init', array($this, 'base_wp_init'));

		$this->daoVotes = new SPCVotesDao($wpdb);
		$this->daoPhotos = new SPCPhotosDao($wpdb);
	}

	public function base_wp_init() {
		//$locale = apply_filters('plugin_locale', get_locale(), self::PLUGIN);
		//_log(__METHOD__.' locale : '.$locale);

		load_plugin_textdomain(self::PLUGIN, false, self::PLUGIN . '/i18n/');
	}

	/**
	 * Load option from wp_db,
	 * and affect default value if value not set.
	 */
	protected function loadOptions() {

		$this->options = get_option(self::$options_name);

		foreach (self::$options_default as $k => $v) {
			if (!isset($this->options[$k])) {
				$this->options[$k] = $v;
			}
		}
	}

	public function getOption($key, $defaultValue = null) {

		if (isset($this->options[$key])) {
			return $this->options[$key];
		}
		return $defaultValue;
	}

	/**
	 * Is the current page is $pageName
	 * @global string $plugin_page
	 * @param string $pageName
	 * @return boolean
	 */
	public static function is_page($pageName) {
		global $plugin_page;
		if ($plugin_page == $pageName) {
			return true;
		}
		return false;
	}

	/**
	 * Check the current post for the existence of a short code.
	 * @param string $shortcode the shortcode
	 * @return boolean return true if shortcode found
	 */
	public static function has_shortcode($shortcode) {

		if (!get_post())
			return false;

		$post_to_check = get_post(get_the_ID());
		if (!$post_to_check)
			return false;

		// check the post content for the short code  
		if (stripos($post_to_check->post_content, '[' . $shortcode) !== false) {
			// we have found the short code  
			return true;
		}
		// not found
		return false;
	}

	public function getPhotoFolderUrl() {

		return WP_CONTENT_URL . '/' . $this->getOption('photoFolder');
	}

	/**
	 * Compute the url for a photo.
	 * @param array $photo_row The photo's data
	 * @param type $type The type of photo's view : null, view or thumb
	 * @return string The photo's url
	 */
	public function getPhotoUrl(array $photo_row, $type = null) {

		if (!isset($photo_row['photo_mime_type']) || empty($photo_row['photo_mime_type'])) {
			return '';
		}

		$ext = explode('/', $photo_row['photo_mime_type']);

		$photo_url = $this->getPhotoFolderUrl() . '/' . $photo_row['id']
			. (!empty($type) ? '-' . $type : '' ) . '.' . $ext[1];
		return $photo_url;
	}

	public function truncatePhotoName($photoName) {

		$mLen = $this->getOption('photoDescLengthMax');
		if (strlen($photoName) > $mLen) {
			return substr($photoName, 0, $mLen - 2) . '...';
		}
		return $photoName;
	}

	public function getPhotoFolderPath() {

		return path_join(WP_CONTENT_DIR, $this->getOption('photoFolder'));
	}

	public function getPhotoPath(array $photo_row, $type = null) {

		if (!isset($photo_row['photo_mime_type']) || empty($photo_row['photo_mime_type'])) {
			return '';
		}

		$ext = explode('/', $photo_row['photo_mime_type']);
		$photo_url = $this->getPhotoFolderPath() . '/' . $photo_row['id']
			. (!empty($type) ? '-' . $type : '' ) . '.' . $ext[1];
		return $photo_url;
	}

	public function getVoteOpenDate() {
		if (isset($this->options['voteOpenDate']))
			return $this->options['voteOpenDate'];
		return null;
	}

	public function getVoteCloseDate() {
		if (isset($this->options['voteCloseDate']))
			return $this->options['voteCloseDate'];
		return null;
	}

	public function isVoteToCome() {

		$vod = $this->getVoteOpenDate();
		if ($vod == '')
			return false;
		$vcd = $this->getVoteCloseDate();
		if ($vcd == '')
			return false;

		$now = date('Y-m-d');
		if (strcmp($now, $vod) < 0) {
			return true;
		}
		return false;
	}

	public function isVoteFinished() {

		$vod = $this->getVoteOpenDate();
		if ($vod == '')
			return false;
		$vcd = $this->getVoteCloseDate();
		if ($vcd == '')
			return false;

		$now = date("Y-m-d");
		if (strcmp($now, $vcd) > 0) {
			return true;
		}
		return false;
	}

	public function isVoteOpen() {

		$vod = $this->getVoteOpenDate();
		if ($vod == '')
			return false;
		$vcd = $this->getVoteCloseDate();
		if ($vcd == '')
			return false;

		$now = date("Y-m-d");
		if (strcmp($now, $vod) >= 0 && strcmp($now, $vcd) <= 0) {
			return true;
		}
		return false;
	}

	/**
	 * @return SPCVotesDao
	 */
	public function getDaoVotes() {
		return $this->daoVotes;
	}

	/**
	 * @return SPCPhotosDao
	 */
	public function getDaoPhotos() {
		return $this->daoPhotos;
	}

	public function getVoterStatusByEmail($email, $photo_id) {

		require_once(__DIR__ . '/../models/SimplePhotosContestVoterStatus.php');

		return SimplePhotosContestVoterStatus::getVoterStatus($this, $email, $photo_id);
	}

	public function formatDate($date) {

		$date = preg_replace(
			'#(\d{2,4})-(\d{1,2})-(\d{1,2})#', self::$dateFormats[$this->options['dateFormat']]['pattern_display'], $date);
		return $date;
	}

}
