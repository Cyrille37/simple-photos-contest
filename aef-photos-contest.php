<?php

/*
  Plugin Name: AEF Simple Photos Contest
  Plugin URI: https://github.com/Cyrille37/aef-photos-contest
  Description: A simple photos contest plugin for WordPress
  Author: Cyrille Giquello (Artéfacts), Marc Frèrebeau (Artéfacts)
  Author URI: http://www.artefacts.coop
  Version: 1.0

  Copyright (c) 2012 Artéfacts

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License along with this program;
  if not:
  - get it at http://www.gnu.org/licenses/gpl-3.0.en.html
  - or write to the Free Software Foundation, Inc.,
  51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

if (!defined('ABSPATH')) {
	header('Location:/');
	exit();
}

if (!class_exists('AefPhotosContest')) {

	class AefPhotosContest {

		const POST_TYPE = 'aef-photos-contest';
		const WP_MINIMAL_REQUIRED_VERSION = '3.5';
		const VERSION = '1.0';
		const DBVERSION = '1.0';

		public static $plugin_name;
		public static $plugin_file;
		public static $templates_folder;
		public static $images_folder;
		public static $adminConfigPageName;
		public static $settings_name;

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
		protected static $options_default = array(
			'photoFolder' => self::POST_TYPE,
			'dateFormat' => 1
		);

		/**
		 * @var array Plugin Options
		 */
		protected $options;
		protected $notices;
		protected $errors;

		public function __construct() {

			self::$plugin_name = __('Concours Photos', self::POST_TYPE);
			self::$plugin_file = basename(dirname(__FILE__)) . '/' . basename(__FILE__);
			self::$templates_folder = dirname(__FILE__) . '/templates/';
			self::$images_folder = dirname(__FILE__) . '/images/';

			self::$adminConfigPageName = self::POST_TYPE . '-configuration';
			self::$settings_name = self::POST_TYPE . '_settings';

			$this->loadOptions();

			register_activation_hook(self::$plugin_file, array($this, 'wp_activate'));
			register_deactivation_hook(self::$plugin_file, array($this, 'wp_deactivate'));

			add_action('init', array($this, 'wp_init'));

			if (is_admin()) {
				// Init de base de l'admin
				add_action('admin_init', array($this, 'wp_admin_init'));
				add_action('admin_menu', array($this, 'wp_admin_menu'));
				add_action('admin_enqueue_scripts', array($this, 'wp_admin_enqueue_scripts_and_styles'));
				add_action('admin_notices', array($this, 'wp_admin_notices'));
			}
		}

		/**
		 * Load option from wp_db,
		 * and affect default value if value not set.
		 */
		protected function loadOptions() {

			$this->options = get_option(self::$settings_name);

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

		public function formatDate($date) {
			$date = preg_replace(
				'#(\d{2,4})-(\d{1,2})-(\d{1,2})#', self::$dateFormats[$this->options['dateFormat']]['pattern_display'], $date);
			return $date;
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
			_log(__FUNCTION__);
		}

		public function wp_deactivate() {
			_log(__FUNCTION__);
		}

		public function wp_init() {

			// http://codex.wordpress.org/Function_Reference/register_post_type
			register_post_type(
				self::POST_TYPE,
				array(
				'label' => __('Photos'),
				'description' => __('Ensemble des photos du concours.'),
				'singular_label' => __('Photo'),
				'public' => true,
				'show_ui' => true,
				'exclude_from_search' => false,
				'capability_type' => 'post',
				'hierarchical' => false,
				// Note: When you use custom post type that use thumbnails remember to check
				// that the theme also supports thumbnails or use add_theme_support function. 
				'supports' => array('title', 'thumbnail', 'custom-fields', 'comments'),
				'labels' => array(
					'name' => __('Photos'),
					'singular_name' => __('Photo'),
					'add_new' => __('Nouvelle photo'),
					'add_new_item' => __('Ajouter une photo'),
					'edit_item' => __('Modifier la photo'),
					'new_item' => __('Nouvelle photo'),
					'all_items' => __('Toutes les photos'),
					'view_item' => __('Voir la photo'),
					'search_items' => __('Rechercher dans les photos'),
					'not_found' => __('Aucune photo ne correspond aux critères de recherche fournis.'),
					'not_found_in_trash' => __('Aucune photo dans la corbeille.'),
					'parent_item_colon' => '',
					'menu_name' => self::$plugin_name,
				),
				'menu_icon' => plugins_url('/images/aef-photos-contest.png', __FILE__),
				'rewrite' => array('slug' => __('ConcoursPhotos')),
				'has_archive' => false,
				)
			);
		}

		public static function is_page($pageName) {
			if (isset($_REQUEST['page']) && $_REQUEST['page'] == $pageName) {
				return true;
			}
			return false;
		}

		public function wp_admin_init() {

			if (self::is_page(self::$adminConfigPageName)) {

				if (!empty($_POST)) {
					$this->admin_configuration_save();
				}
			}
		}

		public function wp_admin_menu() {

			add_submenu_page(
				'edit.php?post_type=' . self::POST_TYPE, 'Configuration du module ' . self::$plugin_name, __('Configuration'),
				'edit_theme_options', self::$adminConfigPageName, array($this, 'admin_configuration_page')
			);
		}

		public function wp_admin_enqueue_scripts_and_styles() {

			if (!self::is_page(self::$adminConfigPageName))
				return;

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
			//wp_enqueue_style('jquery-ui', plugins_url(dirname(self::$plugin_file)) . '/css/jquery-ui-1.10.0.custom.min.css');
			wp_enqueue_style('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/smoothness/jquery-ui.css');
		}

		/**
		 * Print errors and notices if there's some.
		 */
		public function wp_admin_notices() {

			if (!self::is_page(self::$adminConfigPageName))
				return;

			if (count($this->errors) > 0) {
				echo '<div class="error">';
				foreach ($this->errors as $fieldName => $error) {
					echo '<p>' . $error . '</p>';
				}
				echo '</div>';
			}

			/*
			  <?php if( $aefPC->isVoteOpen() ) { ?>
			  <?php echo __('Vote is open since'),
			  ' ', $aefPC->formatDate( $aefPC->getOption('voteOpenDate')),
			  ' to ', $aefPC->formatDate( $aefPC->getOption('voteCloseDate'))
			  ; ?>
			  <?php } else if($aefPC->isVoteToCome() ) { ?>
			  <?php _e('Vote will be open as from'); echo ' ',$aefPC->formatDate( $aefPC->getOption('voteOpenDate')); ?>
			  <?php } else if($aefPC->isVoteFinished() ) { ?>
			  <?php _e('Vote completed since'); echo ' ',$aefPC->formatDate( $aefPC->getOption('voteCloseDate')); ?>
			  <?php } else  { ?>
			  <?php _e('Vote is not configured'); ?>
			  <?php } ?>

			 */

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

		public function admin_configuration_page() {

			global $aefPC;
			include( self::$templates_folder . '/configuration-page.php' );
		}

		/**
		 * http://codex.wordpress.org/Data_Validation
		 * http://wp.tutsplus.com/tutorials/creative-coding/data-sanitization-and-validation-with-wordpress/
		 */
		protected function admin_configuration_save() {

			check_admin_referer(self::POST_TYPE . '_configuration');

			foreach ($_POST as $k => $v) {
				_log('config [' . $k . '] = [' . $v . ']');
			}

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
					_log('date: [' . $date . ']');
					//$releaseDate = ereg_replace("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", "\\3-\\2-\\1",$date);
					//$date2 = preg_replace("#([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})#", "\\3-\\2-\\1",$date);
					if (!preg_match(self::$dateFormats[$this->options['dateFormat']]['pattern_in'], $date)) {
						_log('voteOpenDate is not a valid date: [' . $date . ']');
						$this->errors['voteOpenDate'] = __('Vote open date is not a valid date');
					}
					else {
						$date = preg_replace(self::$dateFormats[$this->options['dateFormat']]['pattern_in'],
							self::$dateFormats[$this->options['dateFormat']]['pattern_out'], $date);
						_log('date2: [' . $date . ']');
					}
				}

				if (!isset($this->errors['voteOpenDate'])) {
					_log('date3: [' . $date . ']');
					$this->options['voteOpenDate'] = $date;
				}
			}

			// voteCloseDate

			if (isset($_POST['voteCloseDate'])) {
				$date = trim($_POST['voteCloseDate']);

				if (!empty($date)) {
					_log('date: [' . $date . ']');
					if (!preg_match(self::$dateFormats[$this->options['dateFormat']]['pattern_in'], $date)) {
						_log('voteCloseDate is not a valid date: [' . $date . ']');
						$this->errors['voteCloseDate'] = __('Vote close date is not a valid date');
					}
					else {
						$date = preg_replace(self::$dateFormats[$this->options['dateFormat']]['pattern_in'],
							self::$dateFormats[$this->options['dateFormat']]['pattern_out'], $date);
						_log('date2: [' . $date . ']');
					}
				}

				if (!isset($this->errors['voteCloseDate'])) {
					_log('date3: [' . $date . ']');
					$this->options['voteCloseDate'] = $date;
				}
			}

			update_option(self::$settings_name, $this->options);
		}

		public function getVoteOpenDate() {
			return $this->options['voteOpenDate'];
		}

		public function getVoteCloseDate() {
			return $this->options['voteCloseDate'];
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

			$now = date('Y-m-d');
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

			$now = date('Y-m-d');
			if (strcmp($now, $vod) >= 0 && strcmp($now, $vcd) <= 0) {
				return true;
			}
			return false;
		}

	}

	global $aefPC;
	$aefPC = new AefPhotosContest();
}
