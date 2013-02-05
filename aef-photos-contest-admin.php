<?php

/*
 * Admin part of plugin: AEF Simple Photos Contest
 */

require_once( __DIR__ . '/aef-photos-contest.php');

class AefPhotosContestAdmin extends AefPhotosContest {

	const PAGE_OVERVIEW = 'aef-photos-contest_overview';
	const PAGE_PHOTOS = 'aef-photos-contest_photos';
	const PAGE_PHOTO_EDIT = 'aef-photos-contest_photo_edit';
	const PAGE_CONFIGURATION = 'aef-photos-contest_configuration';
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

	function is_wp_required_version() {

		global $wp_version;

		// Check for WP version installation
		$wp_ok = version_compare($wp_version, self::WP_MINIMAL_REQUIRED_VERSION, '>=');

		if (($wp_ok == FALSE)) {
			add_action(
				'admin_notices',
				create_function(
					'',
					'printf (\'<div id="message" class="error"><p><strong>\' . __(\'Sorry, ' . self::$plugin_name . ' version ' . self::VERSION . ' works only under WordPress %s or higher\', "nggallery" ) . \'</strong></p></div>\', "' . self::WP_MINIMAL_REQUIRED_VERSION . '" );'
				)
			);
			return false;
		}

		return true;
	}

	public function wp_activate() {

		_log(__METHOD__);

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		// TODO: Update database schema does not works
		// dbDelta génère des erreurs et ne fait pas le boulot de DIFF quand il y a des changements ...
		// Du coup j'ajoute "IF NOT EXISTS" ...

		$sql = 'CREATE TABLE IF NOT EXISTS `' . self::$dbtable_photos . '` (
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`photo_name` VARCHAR(255) NOT NULL,
				`photo_mime_type` VARCHAR(50) NOT NULL,
				`photo_user_filename` VARCHAR(255) NOT NULL,
				`photo_order` TINYINT UNSIGNED NOT NULL,
				`photographer_name` VARCHAR(255) NOT NULL,
				`photographer_email` VARCHAR(255) NOT NULL,
				`notes` TINYTEXT,
				`created_at` DATETIME,
				`updated_at` DATETIME,
				PRIMARY KEY (`id`),
				UNIQUE KEY `UQ_photo_name` (`photo_name`)
			) DEFAULT CHARSET=utf8 ;'; // DEFAULT CHARSET=utf8

		dbDelta($sql);

		$sql = 'CREATE TABLE IF NOT EXISTS `' . self::$dbtable_votes . '` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`voter_name` varchar(255) NOT NULL,
				`voter_email` varchar(255) NOT NULL,
				`vote_date` datetime,
				`photo_id` int(11),
				PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8 ;';
		dbDelta($sql);
	}

	/**
	 * TODO: implements something for plugin deactivate.
	 */
	public function wp_deactivate() {
		_log(__METHOD__);
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

		global $pagenow;

		// Add a dashboard widget
		if ($pagenow == 'index.php') {
			add_action('wp_dashboard_setup', array($this, 'wp_dashboard_setup'));
		}
		else if ($pagenow == 'admin.php') {
			switch ($_GET['page']) {

				case self::PAGE_CONFIGURATION :
					$this->configuration_save();
					break;

				case self::PAGE_PHOTO_EDIT :
					$this->page_photo_edit_init();
					break;

				case self::PAGE_OVERVIEW:
				default :
					break;
			}
		}
	}

	protected function page_photo_edit_init() {

		global $wpdb;

		if (empty($_POST)) {

			if (isset($_GET['id'])) {
				// Load the photo
				$this->photo = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . self::$dbtable_photos . ' WHERE id = %d',
						$_GET['id']), ARRAY_A);
				if (empty($this->photo))
					$this->errors[] = __('Requested photo not found');
			}
			else {
				// Init empty photo
				$this->photo = array();
				foreach ($wpdb->get_col('DESC ' . self::$dbtable_photos, 0) as $column_name) {
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
			foreach ($wpdb->get_col('DESC ' . self::$dbtable_photos, 0) as $column_name) {
				if (isset($_POST[$column_name]))
					$this->photo[$column_name] = $_POST[$column_name];
			}

			$this->photo_save();
		}
	}

	public function wp_admin_menu() {

		//_log(__METHOD__);

		add_menu_page(__('Concours photos', self::PLUGIN), __('Concours photos', self::PLUGIN), self::WP_ROLE,
			self::PAGE_OVERVIEW, array($this, 'wp_on_menu'));

		add_submenu_page(self::PAGE_OVERVIEW, __('Overview', self::PLUGIN), __('Overview', self::PLUGIN), self::WP_ROLE,
			self::PAGE_OVERVIEW, array($this, 'wp_on_menu'));

		add_submenu_page(self::PAGE_OVERVIEW, __('Photos', self::PLUGIN), __('Photos', self::PLUGIN), self::WP_ROLE,
			self::PAGE_PHOTOS, array($this, 'wp_on_menu'));

		add_submenu_page(self::PAGE_OVERVIEW, __('Add photo', self::PLUGIN), __('Add photo', self::PLUGIN), self::WP_ROLE,
			self::PAGE_PHOTO_EDIT, array($this, 'wp_on_menu'));

		add_submenu_page(self::PAGE_OVERVIEW, __('Configuration', self::PLUGIN), __('Configuration', self::PLUGIN),
			self::WP_ROLE, self::PAGE_CONFIGURATION, array($this, 'wp_on_menu'));
	}

	public function wp_on_menu() {

		/**
		 * Need for templates
		 */
		global $aefPC, $wpdb;

		switch ($_GET['page']) {

			case self::PAGE_CONFIGURATION:

				if( isset($_GET['action']) && $_GET['action'] == 'rebuildthumbs' )
				{
					$this->photos_build_thumbs();
				}

				include( self::$templates_folder . '/admin-configuration-page.php' );
				break;

			case self::PAGE_PHOTOS:

				require_once(__DIR__ . '/photos-list-table.php');
				$photosListTable = new Photod_List_Table();
				$photosListTable->prepare_items();
				include( self::$templates_folder . '/admin-photos-page.php' );
				break;

			case self::PAGE_PHOTO_EDIT:
				include( self::$templates_folder . '/admin-photo-edit-page.php' );
				break;

			case self::PAGE_OVERVIEW:
			default :
				include( self::$templates_folder . '/admin-overview-page.php' );
				break;
		}
	}

	public function wp_admin_enqueue_scripts_and_styles() {

		//_log(__METHOD__);

		wp_enqueue_script('jquery');
		// using jquery-ui
		wp_enqueue_script('jquery-ui-core');
		// using the jquery-ui datepicker
		wp_enqueue_script('jquery-ui-datepicker');

		// localizing the jquery-ui datepicker
		$language = get_bloginfo('language'); // ex: fr-FR
		$lang = substr($language, 0, 2); // ex: fr
		wp_enqueue_script('jquery.ui.datepicker-lang',
			'https://raw.github.com/jquery/jquery-ui/master/ui/i18n/jquery.ui.datepicker-' . $lang . '.js');
		wp_enqueue_script('jquery.ui.datepicker-language',
			'https://raw.github.com/jquery/jquery-ui/master/ui/i18n/jquery.ui.datepicker-' . $language . '.js');

		// stilizing the jquery-ui datepicker
		// http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/smoothness/jquery-ui.css
		//wp_enqueue_style('jquery-ui', plugins_url(dirname(self::plugin_file)) . '/css/jquery-ui-1.10.0.custom.min.css');
		wp_enqueue_style('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/smoothness/jquery-ui.css');
	}

	/**
	 * Print errors and notices if there's some.
	 */
	public function wp_admin_notices() {

		_log(__METHOD__);

		if (count($this->errors) > 0) {
			echo '<div class="error">';
			foreach ($this->errors as $name => $error) {
				echo '<p>' . $error . '</p>';
			}
			echo '</div>';
		}

		if (self::is_plugin_page()) {
			if ($this->isVoteOpen()) {
				$this->notices[] = __('Vote is open since')
					. ' ' . $this->formatDate($this->getVoteOpenDate())
					. ' to ' . $this->formatDate($this->getVoteCloseDate());
			}
			else if ($this->isVoteToCome()) {
				$this->notices[] = __('Vote will be open as from')
					. ' ' . $this->formatDate($this->getVoteOpenDate());
			}
			else if ($this->isVoteFinished()) {
				$this->notices[] = __('Vote completed since')
					. ' ' . $this->formatDate($this->getVoteCloseDate());
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

		//global $plugin_page;

		_log(__METHOD__);

		if (!isset($_POST[self::PAGE_CONFIGURATION . '_nonce']))
			return;
		check_admin_referer(self::PAGE_CONFIGURATION, self::PAGE_CONFIGURATION . '_nonce');

		/* foreach ($_POST as $k => $v) {
		  _log('config [' . $k . '] = [' . $v . ']');
		  } */

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
				//$releaseDate = ereg_replace("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", "\\3-\\2-\\1",$date);
				//$date2 = preg_replace("#([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})#", "\\3-\\2-\\1",$date);
				if (!preg_match(self::$dateFormats[$this->options['dateFormat']]['pattern_in'], $date)) {
					_log('voteOpenDate is not a valid date: [' . $date . ']');
					$this->errors['voteOpenDate'] = __('Vote open date is not a valid date');
				}
				else {
					$date = preg_replace(self::$dateFormats[$this->options['dateFormat']]['pattern_in'],
						self::$dateFormats[$this->options['dateFormat']]['pattern_out'], $date);
					//_log('date2: [' . $date . ']');
				}
			}

			if (!isset($this->errors['voteOpenDate'])) {
				//_log('date3: [' . $date . ']');
				$this->options['voteOpenDate'] = $date;
			}
		}

		// voteCloseDate

		if (isset($_POST['voteCloseDate'])) {
			$date = trim($_POST['voteCloseDate']);

			if (!empty($date)) {
				//_log('date: [' . $date . ']');
				if (!preg_match(self::$dateFormats[$this->options['dateFormat']]['pattern_in'], $date)) {
					_log('voteCloseDate is not a valid date: [' . $date . ']');
					$this->errors['voteCloseDate'] = __('Vote close date is not a valid date');
				}
				else {
					$date = preg_replace(self::$dateFormats[$this->options['dateFormat']]['pattern_in'],
						self::$dateFormats[$this->options['dateFormat']]['pattern_out'], $date);
					//_log('date2: [' . $date . ']');
				}
			}

			if (!isset($this->errors['voteCloseDate'])) {
				//_log('date3: [' . $date . ']');
				$this->options['voteCloseDate'] = $date;
			}
		}

		update_option(self::$options_name, $this->options);

		//wp_redirect(admin_url('admin.php?page=' . $plugin_page));
		//exit();
	}

	public function photo_save() {

		global $wpdb;

		_log(__METHOD__);

		$errors = array();

		if (isset($this->photo['photographer_name'])) {
			$v = htmlspecialchars(trim($this->photo['photographer_name']));
			if ($v == '') {
				_log('Photographer name could not be empty.');
				$errors['photographer_name'] = __('Photographer name could not be empty.');
			}
			$this->photo['photographer_name'] = $v;
		}
		else {
			_log('Photographer name could not be empty.');
			$errors['photographer_name'] = __('Photographer name could not be empty.');
		}

		if (isset($this->photo['photographer_email'])) {
			$v = sanitize_email(trim($this->photo['photographer_email']));
			if (!is_email($v)) {
				_log('Photographer email is not valid : [' . $v . ']');
				$errors['photographer_email'] = __('Photographer email is not valid.');
			}
			$this->photo['photographer_email'] = $v;
		}

		if (isset($this->photo['photo_name'])) {
			$v = htmlspecialchars(trim($this->photo['photo_name']));
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
			return;
		}

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
			$sql = 'UPDATE ' . self::$dbtable_photos . ' SET ' . $sql . ' WHERE id=%d';
			$res = $wpdb->query($wpdb->prepare($sql, array_merge(array_values($this->photo), array($this->photo['id']))));
			if ($res) {
				$this->notices[] = __('Photo updated');
			}
			else {
				$this->errors[] = __('Failed to update photo');
			}
		}
		else {
			// Create

			$this->photo['created_at'] = date("Y-m-d H:i:s");
			$res = $wpdb->query($wpdb->prepare('INSERT INTO ' . self::$dbtable_photos
					. '(' . implode(',', array_keys($this->photo)) . ')'
					. 'VALUES (' . implode(',', array_fill(0, count($this->photo), '%s')) . ')', array_values($this->photo)
				));
			$this->photo['id'] = $wpdb->insert_id;
			if ($res) {
				$this->notices[] = __('Photo saved');
			}
			else {
				$this->errors[] = __('Failed to save photo');
			}
		}
	}

	public function photo_save_file() {

		if (!isset($_FILES['photo_file']))
			return false;

		/*
		 * [name] => baum_80.jpg
		 * [type] => image/jpeg
		 * [tmp_name] => /tmp/phpdDMXfy
		 * [error] => 0
		 * [size] => 3216
		 */
		//_log('photo_file: ' . print_r($_FILES['photo_file'], true));

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

		$this->notices[] = __('Photo upload at path: ') . esc_html($dest_file);

		$this->photo['photo_user_filename'] = $file['name'];
		$this->photo['photo_mime_type'] = $ftype;

		$this->photo_build_thumbs($dest_file, $dest_file_without_ext, $dest_file_ext);

		return true;
	}

	public function photo_build_thumbs($image_file, $dest_file_without_ext, $dest_file_ext) {

		_log('#### #### #### #### #### #### #### #### ####');
		_log( 'building thumbs for: '.$image_file );
		_log( 'dest_file_without_ext: '.$dest_file_without_ext );
		_log( 'dest_file_ext: '.$dest_file_ext );

		$image_thumbs = wp_get_image_editor($image_file); // WP_Image_Editor
		if (!is_wp_error($image_thumbs)) {

			$size = $image_thumbs->get_size();
			$w0 = $size['width'];
			$h0 = $size['height'];
			_log('### thumb size: '. $w0.' x '.$h0 );

			$w = round($w0 * ( $this->getOption('thumbW') / $h0));
			$h = $this->getOption('thumbH');

			$image_thumbs->resize($w, $h, false);
			$image_thumbs->save($dest_file_without_ext . '-thumb.' . $dest_file_ext);
		}

		$image_view = wp_get_image_editor($image_file); // WP_Image_Editor
		if (!is_wp_error($image_view)) {
			_log('### view');
			$image_view->resize($this->getOption('viewW'), $this->getOption('viewH'), false);
			$image_view->save($dest_file_without_ext . '-view.' . $dest_file_ext);
		}
	}

	public function photos_build_thumbs() {

		global $wpdb;

		$photos_folder_path = $this->getPhotoFolderPath();
		$sql = 'SELECT * FROM ' . AefPhotosContest::$dbtable_photos;
		$rows = $wpdb->get_results($sql, ARRAY_A);

		foreach ($rows as $row) {

			$photo_path_prefix = $photos_folder_path . '/' . $row['id'];
			$ext = explode('/', $row['photo_mime_type']);
			$this->photo_build_thumbs($photo_path_prefix . '.' . $ext[1], $photo_path_prefix, $ext[1]);
		}

	}

	public function wp_dashboard_setup() {

		wp_enqueue_style('dashboard_widget_vote_style', self::$styles_url . 'dashboard_widget_vote.css');

		wp_add_dashboard_widget('custom_dashboard_widget_vote', __('Photos constest overview'),
			array($this, 'dashboard_widget_vote'));
	}

	public function dashboard_widget_vote() {

		$msg = '';

		if ($this->isVoteOpen()) {
			$class = 'approved';
			$msg.= __('Vote is open since')
				. ' ' . $this->formatDate($this->getVoteOpenDate())
				. ' to ' . $this->formatDate($this->getVoteCloseDate());
		}
		else if ($this->isVoteToCome()) {
			$class = 'waiting';
			$msg.= __('Vote will be open as from')
				. ' ' . $this->formatDate($this->getVoteOpenDate());
		}
		else if ($this->isVoteFinished()) {
			$class = 'waiting';
			$msg.= __('Vote completed since')
				. ' ' . $this->formatDate($this->getVoteCloseDate());
		}
		else {
			$class = 'unconfigured';
			$msg.= __('Vote is not configured');
		}
		echo '<span class="', $class, '" >', $msg, '</span>';
	}

}
