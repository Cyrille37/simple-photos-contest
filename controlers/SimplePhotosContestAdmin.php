<?php

/*
 * Admin part of plugin: Simple Photos Contest
 */

require_once( __DIR__ . '/SimplePhotosContest.php');

class SimplePhotosContestAdmin extends SimplePhotosContest {

	const PAGE_OVERVIEW = 'spc_overview';
	const PAGE_VOTES = 'spc_votes';
	const PAGE_PHOTOS = 'spc_photos';
	const PAGE_PHOTOS_ORDER = 'spc_photos-order';
	const PAGE_PHOTO_EDIT = 'spc_photo-edit';
	const PAGE_CONFIGURATION = 'spc_configuration';
	const WP_ROLE = 'edit_pages';

	public static $photo_valid_filetypes = array('image/jpeg', 'image/png', 'image/gif');

	/**
	 * The loaded photo, if there is one.
	 * Could be:
	 * - filled : photo found
	 * - keys but empty value : no photo id specified
	 * - null : photo id not found
	 * @var array|null
	 */
	protected $photo;

	public function __construct() {

		parent::__construct();

		if (defined('DOING_AJAX') && DOING_AJAX) {

			add_action('wp_ajax_photo_order', array($this, 'wp_ajax_photo_order'));
		}
		else {
			register_activation_hook(self::$plugin_file, array($this, 'wp_activate'));
			register_deactivation_hook(self::$plugin_file, array($this, 'wp_deactivate'));
			// Init de base de l'admin
			add_action('admin_init', array($this, 'wp_admin_init'));
			add_action('admin_menu', array($this, 'wp_admin_menu'));
			add_action('admin_notices', array($this, 'wp_admin_notices'));

			if (self::is_plugin_page()) {
				// THIS PLUGIN's pages only
				add_action('admin_enqueue_scripts', array($this, 'wp_admin_enqueue_scripts_and_styles'));
				//add_action('save_post', array($this, 'wp_post_type_save'));
			}
		}
	}

	public static function check_requirements() {

		global $wp_version;

		if (version_compare(PHP_VERSION, '5.3.3', '<')) {
			wp_die('Need Php > ' . self::REQUIRE_VERSION_PHP);
		}
		if (version_compare($wp_version, self::REQUIRE_VERSION_WP, '<')) {
			wp_die('Need Wordpress > ' . self::REQUIRE_VERSION_WP);
		}

		if (!function_exists('curl_version')) {
			wp_die('Need curl library');
		}
		if (!function_exists('hash')) {
			wp_die('Need hash() function');
		}
	}

	public function update_database() {

		global $wpdb;

		$old_tablesName = array(
			'wp_aef_spc_photos' => SPCPhotosDao::getTableName(),
			'wp_aef_spc_votes' => SPCVotesDao::getTableName()
		);

		$tablesName = $wpdb->get_results('SHOW TABLES');
		$todo = array(
			'rename' => array(),
			'create' => array(),
			'migrate' => array()
			);

		foreach ($old_tablesName as $otn => $ntn) {

			if (in_array($otn, $tablesName) && in_array($ntn, $tablesName)) {
				wp_die('The 2 tables exists (' . $otn . ', ' . $ntn);
			}
			else if (in_array($otn, $tablesName) && !in_array($ntn, $tablesName)) {
				// todo rename
				$todo['rename'][$otn] = $ntn;
				$todo['migrate'][$ntn];
			}
			else if (!in_array($otn, $tablesName) && in_array($ntn, $tablesName)) {
				// todo migrate
				$todo['migrate'][$ntn];
			}
			else {
				// todo create
				$todo['create'][$ntn];
			}
		}

		foreach( $todo['rename'] as $otn => $ntn)
		{
			$wpdb->query('RENAME TABLE '. $otn. ' TO ' . $ntn);
			$this->notices[] = 'Renamed db table from "'.$otn.' to '.$ntn ;
		}

		// FIXME: data schema migration not yet implemented
	}

	public function wp_activate() {

		global $wpdb;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		self::check_requirements();

		$this->update_database();

		// FIXME: Update database schema does not works every time, depends of changes ????
		// dbDelta génère des erreurs et ne fait pas le boulot de DIFF quand il y a des changements ...
		// Du coup j'ajoute "IF NOT EXISTS" ...
		$sql = 'create table IF NOT EXISTS ' . SPCPhotosDao::gettablename() . ' (
				id int unsigned NOT NULL AUTO_INCREMENT,
				photo_name varchar(255) NOT NULL,
				photo_mime_type varchar(50) NOT NULL,
				photo_user_filename varchar(255) NOT NULL,
				photo_order tinyint unsigned NOT NULL,
				photographer_name varchar(255) NOT NULL,
				photographer_email varchar(255) NULL,
				notes tinytext,
				created_at datetime,
				updated_at datetime,
				PRIMARY KEY (id),
				UNIQUE KEY uq_photo_name (photo_name),
				key ix_photos_photo_order (photo_order)
			) default charset=utf8 ;'; // default charset=utf8
		//$for_update = dbDelta($sql, true);
		//_log('for_update: ' . print_r($for_update, true));

		$res = $wpdb->query($sql);
		if (!$res) {
			wp_die('Failed to create table ' . SPCPhotosDao::gettablename());
		}
		else {
			if ($wpdb->get_var('SHOW TABLES LIKE "' . SPCPhotosDao::gettablename() . '"') != SPCPhotosDao::gettablename())
				wp_die('Failed to create table ' . SPCPhotosDao::gettablename());
		}

		// ALTER TABLE `loiretcher-lemag.fr`.`wp_spc_votes` ADD COLUMN `voter_ip` VARCHAR(255) NULL  AFTER `voter_email` ;

		$sql = 'create table IF NOT EXISTS ' . SPCVotesDao::gettablename() . ' (
				id int unsigned not null auto_increment,
				voter_name varchar(255) not null,
				voter_email varchar(255) not null,
				voter_ip varchar(255) null,
				vote_date datetime,
				photo_id int unsigned,
				PRIMARY KEY (id),
				KEY ix_votes_photo_id (photo_id)
			) default charset=utf8 ;';
		//$for_update = dbDelta($sql, true);
		//_log('for_update: ' . print_r($for_update, true));

		$res = $wpdb->query($sql);
		if (!$res) {
			wp_die('Failed to create table ' . SPCVotesDao::gettablename());
		}
		else {
			if ($wpdb->get_var('SHOW TABLES LIKE "' . SPCVotesDao::gettablename() . '"') != SPCVotesDao::gettablename())
				wp_die('Failed to create table ' . SPCVotesDao::gettablename());
		}
	}

	/**
	 * TODO: implements something for plugin deactivate.
	 */
	public function wp_deactivate() {
		//_log(__METHOD__);
	}

	/**
	 * Is the current page is one of this plugin pages.
	 * @global string $plugin_page
	 * @return boolean
	 */
	public static function is_plugin_page() {

		global $plugin_page;
		if (!isset($plugin_page)) {
			// $plugin_page not yet defined, look in da url
			if (!isset($_GET['page']))
				return false;
			$p = $_GET['page'];
		}
		else {
			$p = $plugin_page;
		}
		if (strpos($p, self::PLUGIN) === 0)
			return true;
		return false;
	}

	public function wp_admin_init() {

		global $pagenow, $plugin_page, $wpdb;

		// Add a dashboard widget
		if ($pagenow == 'index.php') {
			add_action('wp_dashboard_setup', array($this, 'wp_dashboard_setup'));
		}
		else if ($pagenow == 'admin.php' && isset($_GET['page'])) {

			switch ($_GET['page']) {

				case self::PAGE_CONFIGURATION :

					if (isset($_GET['action'])) {

						switch ($_GET['action']) {

							case 'rebuildthumbs':
								$this->photos_build_thumbs();
								break;

							case 'buildFakePhotos':
								$this->photos_build_fake();
								break;

							default;
								$this->errors['action'] = __('Unknow action');
								break;
						}
					}

					$this->configuration_save();
					break;

				case self::PAGE_PHOTO_EDIT :
					$this->page_photo_edit_init();
					break;

				case self::PAGE_PHOTOS:

					if (isset($_GET['action'])) {
						switch ($_GET['action']) {

							case 'delete':
								$this->photo_delete(intval($_GET['id']));
								$current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
								$current_url = remove_query_arg(array('action', 'id'), $current_url);
								//wp_redirect(admin_url('admin.php?page=' . $plugin_page));
								wp_redirect($current_url);
								exit();
								break;

							case 'force-commentInPhotographername':

								$photos = $this->getDaoPhotos()->getAll();
								foreach ($photos as $photo) {
									if (!empty($photo['notes']) && empty($photo['photographer_name'])) {
										$this->getDaoPhotos()->updateById($photo['id'], array('photographer_name' => $photo['notes']));
									}
								}
								$current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
								$current_url = remove_query_arg(array('action'), $current_url);
								//wp_redirect(admin_url('admin.php?page=' . $plugin_page));
								wp_redirect($current_url);
								exit();
								break;

							default;
								$this->errors['action'] = __('Unknow action');
								break;
						}
					}
					break;

				case self::PAGE_VOTES:

					if (isset($_GET['action'])) {
						switch ($_GET['action']) {
							case 'delete':
								$this->getDaoVotes()->delete($_GET['id']);
								$current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
								$current_url = remove_query_arg(array('action', 'id'), $current_url);
								//wp_redirect(admin_url('admin.php?page=' . $plugin_page));
								wp_redirect($current_url);
								exit();
								break;

							default;
								$this->errors['action'] = __('Unknow action');
								break;
						}
					}
					break;

				case self::PAGE_PHOTOS_ORDER :

					if (isset($_GET['action'])) {
						switch ($_GET['action']) {
							case 'force-reorder':
								$this->photos_reorder_force();
								$this->notices[] = __('Forced order done.');
								$current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
								$current_url = remove_query_arg(array('action'), $current_url);
								//wp_redirect(admin_url('admin.php?page=' . $plugin_page));
								wp_redirect($current_url);
								break;
							default;
								$this->errors['action'] = __('Unknow action');
								break;
						}
					}
					break;

				case self::PAGE_OVERVIEW :
					if (isset($_GET['action'])) {
						switch ($_GET['action']) {
							case 'export':
								$this->export();
								$this->notices[] = __('Export done.');
								$current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
								$current_url = remove_query_arg(array('action'), $current_url);
								//wp_redirect(admin_url('admin.php?page=' . $plugin_page));
								wp_redirect($current_url);
								break;
							default;
								$this->errors['action'] = __('Unknow action');
								break;
						}
					}
					break;

				default :
					break;
			}
		}
	}

	protected function export() {

		require_once( __DIR__ . '/../models/SPCExport.php');
		$export = new SPCExport();

		// Photos Votes sheet

		$data = array(
			'headers' => array(),
			'rows' => array()
		);
		$queryOptions = new SPCQueryOptions();
		$queryOptions->orderBy('votes');
		$photos = $this->getDaoPhotos()->getAllWithVotesCount($queryOptions);
		foreach (array_keys($photos[0]) as $k) {
			$data['headers'][] = array('label' => $k);
		}
		foreach ($photos as $photo) {
			$data['rows'][] = array_values($photo);
		}
		$export->addSheet('photos', $data);

		//
		// Voters sheet

		$data = array(
			'headers' => array(),
			'rows' => array()
		);
		$votes = $this->getDaoVotes()->getVotesCountByVoters();
		foreach (array_keys($votes[0]) as $k) {
			$data['headers'][] = array('label' => $k);
		}
		foreach ($votes as $vote) {
			$data['rows'][] = array_values($vote);
		}
		$export->addSheet('votants', $data);

		// Raw Votes sheet

		$data = array(
			'headers' => array(),
			'rows' => array()
		);
		$votes = $this->getDaoVotes()->getAll();
		foreach (array_keys($votes[0]) as $k) {
			$data['headers'][] = array('label' => $k);
		}
		foreach ($votes as $vote) {
			$data['rows'][] = array_values($vote);
		}
		$export->addSheet('votes', $data);

		// Shot da file

		$temp_filename = tempnam(sys_get_temp_dir(), 'Tux');
		//$temp_filename = '/home/cyrille/Taf/CG41/20130124 - Concours photos/www/wp-content/plugins/simple-photos-contest/temp.xlsx' ;
		$export->save($temp_filename);

		//$finfo = new \finfo(FILEINFO_MIME);
		//_log( $finfo->file($temp_filename, FILEINFO_MIME_TYPE) );

		$out_filename = 'simple-photos-contest ' . date('Y-m-d_H-i') . '.xlsx';

		ob_end_clean();
		// disable browser caching -- the server may be doing this on its own
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="' . $out_filename . '"');
		readfile($temp_filename);
		exit();
	}

	protected function photos_reorder_force() {
		_log(__METHOD__);

		$qOptions = new SPCQueryOptions();
		$qOptions->orderBy('photo_order', 'ASC');
		$photos = $this->daoPhotos->getAll($qOptions);

		$i = 1;
		foreach ($photos as $photo) {
			$this->getDaoPhotos()->updateById($photo['id'], array('photo_order' => $i));
			$i++;
		}
	}

	protected function page_photo_edit_init() {

		global $wpdb;

		if (empty($_POST)) {

			if (isset($_GET['id'])) {
				// Load the photo
				$this->photo = $this->getDaoPhotos()->getById($_GET['id']);
				if (empty($this->photo))
					$this->errors[] = __('Requested photo not found');
			}
			else {
				// Init empty photo
				$this->photo = array();
				foreach ($wpdb->get_col('DESC ' . $this->getDaoPhotos()->getTableName(), 0) as $column_name) {
					$this->photo[$column_name] = null;
				}
			}
		}
		else {
			// Data sent

			if (!isset($_POST[self::PAGE_PHOTO_EDIT . '_nonce']))
				return;

			$photo_id = null;

			if (isset($_GET['id'])) {
				if (!isset($_POST['id']) || $_POST['id'] != $_GET['id']) {
					$this->errors[] = __('Requested photo not found');
					return;
				}
				$photo_id = $_GET['id'];
			}
			else if (!isset($_POST['id'])) {
				$this->errors[] = __('Requested photo not found');
				return;
			}
			else {
				$photo_id = $_POST['id'];
			}

			check_admin_referer(self::PAGE_PHOTO_EDIT . $photo_id, self::PAGE_PHOTO_EDIT . '_nonce');

			// Copy sent photo's fields into photo

			$this->photo = $this->getDaoPhotos()->getById($photo_id);

			if (!is_array($this->photo)) {
				$this->photo = array();
			}

			foreach ($wpdb->get_col('DESC ' . $this->getDaoPhotos()->getTableName(), 0) as $column_name) {
				if (isset($_POST[$column_name])) {
					$this->photo[$column_name] = stripslashes($_POST[$column_name]);
				}
				else if (!isset($this->photo[$column_name])) {
					$this->photo[$column_name] = null;
				}
			}

			$ok = $this->photo_save();
		}

		if (isset($this->photo['id']) && !empty($this->photo['id'])) {
			list($votesCount, $votersCount) = $this->getDaoPhotos()->getVotesAndVotersCounts($this->photo['id']);
			$this->notices[] = $votesCount . ' votes en ' . $votersCount . ' votants pour cette photo.';
		}
	}

	public function wp_admin_menu() {

		//_log(__METHOD__);

		add_menu_page(__('Photos contest', self::PLUGIN), __('Photos contest', self::PLUGIN), self::WP_ROLE,
			self::PAGE_OVERVIEW, array($this, 'wp_on_menu'));

		add_submenu_page(self::PAGE_OVERVIEW, __('Overview', self::PLUGIN), __('Overview', self::PLUGIN), self::WP_ROLE,
			self::PAGE_OVERVIEW, array($this, 'wp_on_menu'));

		add_submenu_page(self::PAGE_OVERVIEW, __('Votes', self::PLUGIN), __('Votes', self::PLUGIN), self::WP_ROLE,
			self::PAGE_VOTES, array($this, 'wp_on_menu'));

		add_submenu_page(self::PAGE_OVERVIEW, __('Photos', self::PLUGIN), __('Photos', self::PLUGIN), self::WP_ROLE,
			self::PAGE_PHOTOS, array($this, 'wp_on_menu'));

		add_submenu_page(self::PAGE_OVERVIEW, __('Add photo', self::PLUGIN), __('Add photo', self::PLUGIN), self::WP_ROLE,
			self::PAGE_PHOTO_EDIT, array($this, 'wp_on_menu'));

		add_submenu_page(self::PAGE_OVERVIEW, __('Configuration', self::PLUGIN), __('Configuration', self::PLUGIN),
			self::WP_ROLE, self::PAGE_CONFIGURATION, array($this, 'wp_on_menu'));

		// Not visible in menu: parent slug is null
		add_submenu_page(null, __('Photos order', self::PLUGIN), __('Photos order', self::PLUGIN), self::WP_ROLE,
			self::PAGE_PHOTOS_ORDER, array($this, 'wp_on_menu'));
	}

	public function wp_on_menu() {

		/**
		 * Need for templates
		 */
		global $gSPC, $wpdb;

		//_log(__METHOD__.' page='.$_GET['page']);

		switch ($_GET['page']) {

			case self::PAGE_CONFIGURATION:
				include( self::$templates_folder . '/admin-configuration-page.php' );
				break;

			case self::PAGE_PHOTOS:
				include( self::$templates_folder . '/admin-photos-page.php' );
				break;

			case self::PAGE_PHOTOS_ORDER:
				include( self::$templates_folder . '/admin-photos-order.php' );
				break;

			case self::PAGE_PHOTO_EDIT:
				include( self::$templates_folder . '/admin-photo-edit-page.php' );
				break;

			case self::PAGE_VOTES:
				include( self::$templates_folder . '/admin-votes-page.php' );
				break;

			case self::PAGE_OVERVIEW:
			default :
				include( self::$templates_folder . '/admin-overview-page.php' );
				break;
		}
	}

	public function wp_admin_enqueue_scripts_and_styles() {

		wp_enqueue_script('jquery');

		//wp_enqueue_script('spc-admin', self::$javascript_url . 'spc-admin.js', array('jquery'));

		wp_enqueue_style('thickbox');
		wp_enqueue_script('thickbox');

		// using jquery-ui
		wp_enqueue_script('jquery-ui-core');

		if ($_GET['page'] == self::PAGE_CONFIGURATION) {

			// using the jquery-ui datepicker
			wp_enqueue_script('jquery-ui-datepicker');

			// stilizing the jquery-ui 
			//wp_enqueue_style('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/smoothness/jquery-ui.css');
			wp_enqueue_style('jquery-ui', self::$styles_url . 'jquery-ui.css');

			// localizing the jquery-ui datepicker
			$language = get_bloginfo('language'); // ex: fr-FR
			$lang = substr($language, 0, 2); // ex: fr
			if ($lang == 'en') {
				// Native jquery.ui.datepicker language
			}
			else if ($lang == 'fr') {
				// Got it
				wp_enqueue_script('jquery.ui.datepicker-lang', self::$javascript_url . 'jquery.ui.datepicker-fr.js');
			}
			else {
				wp_enqueue_script('jquery.ui.datepicker-lang',
					'https://raw.github.com/jquery/jquery-ui/master/ui/i18n/jquery.ui.datepicker-' . $lang . '.js');
				wp_enqueue_script('jquery.ui.datepicker-language',
					'https://raw.github.com/jquery/jquery-ui/master/ui/i18n/jquery.ui.datepicker-' . $language . '.js');
			}
		}
		else if ($_GET['page'] == self::PAGE_PHOTOS_ORDER) {

			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_style('jquery-ui', self::$styles_url . 'jquery-ui.css');
		}
	}

	/**
	 * Print errors and notices if there's some.
	 */
	public function wp_admin_notices() {

		//_log(__METHOD__);

		if (count($this->errors) > 0) {
			echo '<div class="error">';
			foreach ($this->errors as $name => $error) {
				echo '<p>' . $error . '</p>';
			}
			echo '</div>';
		}

		if (self::is_plugin_page()) {

			if ($this->isVoteOpen()) {
				$this->notices[] = sprintf(
					__('Vote is open since %1s to %2s', self::PLUGIN), $this->formatDate($this->getVoteOpenDate()),
					$this->formatDate($this->getVoteCloseDate()));
			}
			else if ($this->isVoteToCome()) {
				$this->notices[] = sprintf(
					__('Vote will be open as from %s', self::PLUGIN), $this->formatDate($this->getVoteOpenDate()));
			}
			else if ($this->isVoteFinished()) {
				$this->notices[] = sprintf(
					__('Vote completed since %s', self::PLUGIN), $this->formatDate($this->getVoteCloseDate()));
			}
			else {
				$this->notices[] = __('Vote is not configured');
			}
		}

		if (count($this->notices) > 0) {
			echo '<div class="updated">';
			foreach ($this->notices as $notice) {
				echo '<p>' . $notice . '</p>';
			}
			echo '</div>';
		}
	}

	public function hasFieldError($fieldName) {

		if (isset($this->errors[$fieldName])) {
			return true;
		}
		return false;
	}

	/**
	 * http://codex.wordpress.org/Data_Validation
	 * http://wp.tutsplus.com/tutorials/creative-coding/data-sanitization-and-validation-with-wordpress/
	 */
	protected function configuration_save() {

		//_log(__METHOD__);

		if (!isset($_POST[self::PAGE_CONFIGURATION . '_nonce']))
			return;

		check_admin_referer(self::PAGE_CONFIGURATION, self::PAGE_CONFIGURATION . '_nonce');

		// photoFolder

		if (isset($_POST['photoFolder'])) {
			$photoFolder = trim($_POST['photoFolder']);

			if (empty($photoFolder)) {
				_log('photoFolderPath is empty');
				$this->errors['photoFolder'] = __('Photos folder is not set');
			}
			else {
				// Check if this relative path is writable
				$photoFolderPath = path_join(WP_CONTENT_DIR, $photoFolder);
				if (!is_writable($photoFolderPath)) {
					_log('photoFolderPath does not exists or is not writable: [' . $photoFolderPath . ']');
					$this->errors['photoFolder'] = __('Photos folder path does not exists or is not writable.') . '<br/>' . $photoFolderPath;
				}
			}

			if (!isset($this->errors['photoFolder']))
				$this->options['photoFolder'] = $photoFolder;
		}

		if (isset($_POST['photoDescLengthMax']) && is_numeric($_POST['photoDescLengthMax'])) {
			$this->options['photoDescLengthMax'] = $_POST['photoDescLengthMax'];
		}
		else {
			$this->errors['photoDescLengthMax'] = _('Photo name max length must be set');
		}

		if (isset($_POST['thumb_w']) && is_numeric($_POST['thumb_w'])) {
			$this->options['thumbW'] = $_POST['thumb_w'];
		}
		else {
			$this->errors['thumb_w'] = _('Thumbnail width must be set');
		}
		if (isset($_POST['thumb_h']) && is_numeric($_POST['thumb_h'])) {
			$this->options['thumbH'] = $_POST['thumb_h'];
		}
		else {
			$this->errors['thumb_h'] = _('Thumbnail height must be set');
		}

		if (isset($_POST['view_w']) && is_numeric($_POST['view_w'])) {
			$this->options['viewW'] = $_POST['view_w'];
		}
		else {
			$this->errors['view_w'] = _('View width must be set');
		}
		if (isset($_POST['view_h']) && is_numeric($_POST['view_h'])) {
			$this->options['viewH'] = $_POST['view_h'];
		}
		else {
			$this->errors['view_h'] = _('View height must be set');
		}

		if (isset($_POST['facebook_client_id'])) {
			$this->options['facebookClientId'] = $_POST['facebook_client_id'];
		}
		if (isset($_POST['facebook_secret_key'])) {
			$this->options['facebookSecretKey'] = $_POST['facebook_secret_key'];
		}

		// dateFormat

		if (isset($_POST['dateFormat'])) {
			$dateFormat = $_POST['dateFormat'];
			if (!isset(self::$dateFormats[$dateFormat])) {
				_log('unknown dateFormat: [' . $dateFormat . ']');
				$this->errors['dateFormat'] = __('Unknown dateFormat');
			}

			if (!isset($this->errors['dateFormat']))
				$this->options['dateFormat'] = $dateFormat;
		}

		// voteOpenDate

		if (isset($_POST['voteOpenDate'])) {
			$date = trim($_POST['voteOpenDate']);

			if (!empty($date)) {
				//_log('date: [' . $date . ']');
				if (!preg_match(self::$dateFormats[$this->options['dateFormat']]['pattern_in'], $date)) {
					_log('voteOpenDate is not a valid date: [' . $date . ']');
					$this->errors['voteOpenDate'] = __('Vote open date is not a valid date');
				}
				else {
					$date = preg_replace(self::$dateFormats[$this->options['dateFormat']]['pattern_in'],
						self::$dateFormats[$this->options['dateFormat']]['pattern_out'], $date);
				}
			}

			if (!isset($this->errors['voteOpenDate'])) {
				$this->options['voteOpenDate'] = $date;
			}
		}

		// voteCloseDate

		if (isset($_POST['voteCloseDate'])) {
			$date = trim($_POST['voteCloseDate']);

			if (!empty($date)) {

				if (!preg_match(self::$dateFormats[$this->options['dateFormat']]['pattern_in'], $date)) {
					_log('voteCloseDate is not a valid date: [' . $date . ']');
					$this->errors['voteCloseDate'] = __('Vote close date is not a valid date');
				}
				else {
					$date = preg_replace(self::$dateFormats[$this->options['dateFormat']]['pattern_in'],
						self::$dateFormats[$this->options['dateFormat']]['pattern_out'], $date);
				}
			}

			if (!isset($this->errors['voteCloseDate'])) {
				$this->options['voteCloseDate'] = $date;
			}
		}

		if (isset($_POST['voteFrequency'])) {
			switch ($_POST['voteFrequency']) {
				case self::VOTE_FREQ_ONEPERCONTEST:
					$this->options['voteFrequency'] = self::VOTE_FREQ_ONEPERCONTEST;
					break;
				case self::VOTE_FREQ_ONEPERHOURS:
					if (!isset($_POST['voteFrequencyHours']) || !is_numeric($_POST['voteFrequencyHours'])) {
						$this->errors['voteFrequency'] = __('Vote frequency error, number of hours must be set');
					}
					else {
						$this->options['voteFrequency'] = self::VOTE_FREQ_ONEPERHOURS;
						$this->options['voteFrequencyHours'] = intval($_POST['voteFrequencyHours']);
					}
					break;
			}
		}

		update_option(self::$options_name, $this->options);

		//wp_redirect(admin_url('admin.php?page=' . $plugin_page));
		//exit();
	}

	public function photo_save() {

		global $wpdb;

		//_log(__METHOD__);

		$errors = array();

		if (isset($this->photo['photographer_name'])) {
			//$v = htmlspecialchars(trim($this->photo['photographer_name']),ENT_NOQUOTES);
			$v = stripslashes(trim($this->photo['photographer_name']));
			//if ($v == '') {
			//	_log('Photographer name could not be empty.');
			//	$errors['photographer_name'] = __('Photographer name could not be empty.');
			//}
			$this->photo['photographer_name'] = $v;
			//}
			//else {
			//	_log('Photographer name could not be empty.');
			//	$errors['photographer_name'] = __('Photographer name could not be empty.');
		}

		if (isset($this->photo['photographer_email'])) {
			$v = sanitize_email(trim($this->photo['photographer_email']));
			if ($v != '' && !is_email($v)) {
				_log('Photographer email is not valid : [' . $v . ']');
				$errors['photographer_email'] = __('Photographer email is not valid.');
			}
			$this->photo['photographer_email'] = $v;
		}

		if (isset($this->photo['photo_name'])) {
			//$v = htmlspecialchars(trim($this->photo['photo_name']), ENT_NOQUOTES);
			$v = stripslashes(trim($this->photo['photo_name']));
			if ($v == '') {
				_log('Photo name could not be empty.');
				$errors['photo_name'] = __('Photo name could not be empty.');
			}
			$this->photo['photo_name'] = $v;
		}
		else {
			_log('Photo name could not be empty.');
			$errors['photo_name'] = __('Photo name could not be empty.');
		}

		if (count($errors) > 0) {
			$this->errors = array_merge($this->errors, $errors);
			return false;
		}

		$result = null;

		ksort($this->photo);

		if (!empty($this->photo['id'])) {
			// Update

			$this->photo['updated_at'] = date("Y-m-d H:i:s");

			$this->photo_save_file();

			$sql = '';
			foreach ($this->photo as $k => $v) {
				if ($sql != '')
					$sql.=',';
				$sql.= $k . '=%s';
			}
			$sql = 'UPDATE ' . $this->getDaoPhotos()->getTableName() . ' SET ' . $sql . ' WHERE id=%d';
			$res = $wpdb->query($wpdb->prepare($sql, array_merge(array_values($this->photo), array($this->photo['id']))));
			if ($res) {
				$this->notices[] = __('Photo updated');
				$result = true;
			}
			else {
				$this->errors[] = __('Failed to update photo');
				$result = false;
			}
		}
		else {
			// Create

			$this->photo['created_at'] = date("Y-m-d H:i:s");
			$this->photo['photo_order'] = 1 + $this->getDaoPhotos()->getPhotoOrderMax();

			$res = $this->daoPhotos->insert($this->photo);

			$this->photo['id'] = $wpdb->insert_id;
			if ($res) {
				$this->notices[] = __('Photo saved');
				$result = true;
			}
			else {
				$this->errors[] = __('Failed to save photo');
				$result = false;
			}
		}
		return $result;
	}

	public function photo_save_file() {

		if (!isset($_FILES['photo_file']))
			return false;

		$file = & $_FILES['photo_file'];

		if ($file['error'] != UPLOAD_ERR_OK) {
			switch ($file['error']) {
				case UPLOAD_ERR_NO_FILE:
					// No file, silently return
					//$this->errors['photo_file'] = __('UPLOAD_ERR_NO_FILE');
					break;
				case UPLOAD_ERR_INI_SIZE:
					$this->errors['photo_file'] = __('UPLOAD_ERR_INI_SIZE');
					break;
				case UPLOAD_ERR_FORM_SIZE:
					$this->errors['photo_file'] = __('UPLOAD_ERR_FORM_SIZE');
					break;
				case UPLOAD_ERR_PARTIAL:
					$this->errors['photo_file'] = __('UPLOAD_ERR_PARTIAL');
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$this->errors['photo_file'] = __('UPLOAD_ERR_NO_TMP_DIR');
					break;
				case UPLOAD_ERR_CANT_WRITE:
					$this->errors['photo_file'] = __('UPLOAD_ERR_CANT_WRITE');
					break;
				case UPLOAD_ERR_EXTENSION:
					$this->errors['photo_file'] = __('UPLOAD_ERR_EXTENSION');
					break;
			}
			return false;
		}

		// photo_user_filename

		$temp_file = $file['tmp_name'];

		$fi = new finfo(FILEINFO_MIME);
		$ftype = explode(';', $fi->file($temp_file));
		$ftype = $ftype[0];

		if (!in_array($ftype, self::$photo_valid_filetypes)) {
			_log('Photo file is not a valid format: [' . $ftype . ']');
			$this->errors['photo_file'] = __('Photo file is not a valid format: ', self::PLUGIN) . esc_html($ftype);
			return false;
		}

		$photoFolderPath = $this->getPhotoFolderPath();
		if (!is_writable($photoFolderPath)) {
			_log('photoFolderPath does not exists or is not writable: [' . $photoFolderPath . ']');
			$this->errors['photo_file'] = __('Photos path does not exists or is not writable: ', self::PLUGIN) . esc_html($photoFolderPath);
			return false;
		}

		$dest_file_ext = explode('/', $ftype);
		$dest_file_ext = $dest_file_ext[1];

		$dest_file_without_ext = path_join($photoFolderPath, $this->photo['id']);
		$dest_file = $dest_file_without_ext . '.' . $dest_file_ext;

		if (!@move_uploaded_file($temp_file, $dest_file)) {
			_log('Upload error, the file could not be moved to: [' . $photoFolderPath . ']');
			$this->errors['photo_file'] = __('Upload error, the file could not be moved to: ', self::PLUGIN) . esc_html($dest_file);
			return false;
		}

		$this->notices[] = __('Photo uploaded at path: ') . esc_html($dest_file);

		$this->photo['photo_user_filename'] = $file['name'];
		$this->photo['photo_mime_type'] = $ftype;

		$this->photo_build_thumbs($dest_file, $dest_file_without_ext, $dest_file_ext);

		return true;
	}

	public function photo_build_thumbs($image_file, $dest_file_without_ext, $dest_file_ext) {

		/**
		 * @var WP_Image_Editor
		 */
		$image_thumbs = wp_get_image_editor($image_file);
		if (!is_wp_error($image_thumbs)) {

			$size = $image_thumbs->get_size();
			$w0 = $size['width'];
			$h0 = $size['height'];
			// force height to configuration, compute homothety width
			$w = round($w0 * ( $this->getOption('thumbW') / $h0));
			$h = $this->getOption('thumbH');

			$image_thumbs->resize($w, $h, false);
			$image_thumbs->save($dest_file_without_ext . '-thumb.' . $dest_file_ext);
		}

		/**
		 * @var WP_Image_Editor
		 */
		$image_view = wp_get_image_editor($image_file); // WP_Image_Editor
		if (!is_wp_error($image_view)) {

			$image_view->resize($this->getOption('viewW'), $this->getOption('viewH'), false);
			$image_view->save($dest_file_without_ext . '-view.' . $dest_file_ext);
		}
	}

	public function photos_build_thumbs() {

		global $wpdb;

		$photos_folder_path = $this->getPhotoFolderPath();
		$rows = $this->daoPhotos->getAll();

		foreach ($rows as $row) {

			$photo_path_prefix = $photos_folder_path . '/' . $row['id'];
			$ext = explode('/', $row['photo_mime_type']);
			$this->photo_build_thumbs($photo_path_prefix . '.' . $ext[1], $photo_path_prefix, $ext[1]);
		}

		$this->notices[] = __('All photos thumbs were rebuilt');
	}

	public function photos_build_fake() {

		_log(__METHOD__);

		$nbPhotos = 20;
		$nowDate = date("Y-m-d H:i:s");

		$originalFiles = array(
			$this->getPhotoFolderPath() . '/000_fake01.jpg',
			$this->getPhotoFolderPath() . '/000_fake02.jpg',
			$this->getPhotoFolderPath() . '/000_fake03.jpg',
			$this->getPhotoFolderPath() . '/000_fake04.jpg',
			$this->getPhotoFolderPath() . '/000_fake05.jpg'
		);

		$cptPhotos = 0;
		for ($i = 0; $i < $nbPhotos; $i++) {

			$originalFile = $originalFiles[($i % count($originalFiles))];

			list($w, $h, $type, $attr) = getimagesize($originalFile);
			if (!isset($w)) {
				$this->errors['photos_build_fake'] = 'Failed to getimagesize';
				return;
			}

			$photo = array(
				'photo_name' => 'generated_' . $nowDate . '_' . $i,
				'photographer_name' => 'photographer_' . $i,
				'photographer_email' => 'photographer_' . $i . '@internet.com',
				'photo_mime_type' => 'image/jpeg',
				'photo_user_filename' => 'generated_' . $nowDate . '_' . $i,
				'created_at' => $nowDate
			);

			$this->daoPhotos->insert($photo);
			$photoId = $this->daoPhotos->getLastInsertId();
			$photo['id'] = $photoId;

			$imFilename = $this->getPhotoPath($photo);

			$im = imagecreatefromjpeg($originalFile);
			if ($im === false) {
				$this->errors['photos_build_fake'] = 'Failed to create image';
				return;
			}
			$white = imagecolorallocate($im, 255, 255, 255);
			$grey = imagecolorallocate($im, 100, 100, 100);
			$black = imagecolorallocate($im, 0, 0, 0);

			$y = 0;
			$x = 0;
			do {
				imagestring($im, 5, $x, $y, 'photoId ' . $photoId, $white);
				$y+=20;
				$x+=20;
			}
			while ($y < $h && $x < $w);

			imagejpeg($im, $imFilename);
			imagedestroy($im);

			$photo_path_prefix = $this->getPhotoFolderPath() . '/' . $photo['id'];
			$ext = explode('/', $photo['photo_mime_type']);
			$this->photo_build_thumbs($photo_path_prefix . '.' . $ext[1], $photo_path_prefix, $ext[1]);

			$cptPhotos++;
		}

		$this->notices[] = 'Generated ' . $cptPhotos . ' over ' . $nbPhotos;
	}

	public function wp_ajax_photo_order() {

		$output = array();
		$output['command'] = 'order_ok';

		$insert = $_REQUEST['insert'];
		$srcId = $_REQUEST['photoId'];
		$targetId = $_REQUEST['targetId'];

		if ($insert == 'before') {
			$this->getDaoPhotos()->orderInsertBefore($srcId, $targetId);
		}
		else if ($insert == 'after') {
			$this->getDaoPhotos()->orderInsertAfter($srcId, $targetId);
		}
		else {
			$output['command'] = 'error';
			$output['message'] = 'Unknow insert: ' . htmlspecialchars($insert, ENT_NOQUOTES);
		}

		echo json_encode($output);
		wp_die();
	}

	public function wp_dashboard_setup() {

		wp_enqueue_style('dashboard_widget_vote_style', self::$styles_url . 'dashboard_widget_vote.css');

		wp_add_dashboard_widget('custom_dashboard_widget_vote', 'Concours photos', array($this, 'dashboard_widget_vote'));
	}

	public function dashboard_widget_vote() {

		$msg = '';

		echo '<p>';
		if ($this->isVoteOpen()) {
			$class = 'approved';
			$msg.= 'Le vote est ouvert depuis le '
				. $this->formatDate($this->getVoteOpenDate())
				. ' jusqu´au ' . $this->formatDate($this->getVoteCloseDate()) . '.';
		}
		else if ($this->isVoteToCome()) {
			$class = 'waiting';
			$msg.= 'Le vote ouvrira le ' . $this->formatDate($this->getVoteOpenDate()) . '.';
		}
		else if ($this->isVoteFinished()) {
			$class = 'waiting';
			$msg.= 'Le vote est fermé depuis le ' . $this->formatDate($this->getVoteCloseDate()) . '.';
		}
		else {
			$class = 'unconfigured';
			$msg.= 'Le vote n´est pas configuré.';
		}
		echo '<span class="', $class, '" >', $msg, '</span>';
		echo '</p>';
		echo '<p>';
		$votesCount = $this->daoVotes->count();
		$votersCount = $this->daoVotes->getVotersCount();
		echo 'Il y a ' . $votesCount . ' votes pour ' . $votersCount . ' ' . ' votants.';
		echo '</p>';
	}

	public function photo_delete($photo_id) {

		global $wpdb;

		$row = $this->daoPhotos->getById($photo_id);
		if (empty($row)) {
			$this->errors['action'] = __('Photo not found.') . ' (id=' . $photo_id . ')';
			return;
		}

		$ok = $this->daoPhotos->delete($photo_id);

		if (!$ok) {
			$this->errors['action'] = __('Failed to delete photo.') . ' (id=' . $photo_id . ')';
			return;
		}

		unlink($this->getPhotoPath($row, 'view'));
		unlink($this->getPhotoPath($row, 'thumb'));
		unlink($this->getPhotoPath($row));

		$this->notices[] = __('Photo deleted.') . ' (id=' . $photo_id . ')';
	}

}
