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

		const PLUGIN = 'aef-photos-contest';
		const DBTABLE_PREFIX = 'aef_spc';
		const WP_MINIMAL_REQUIRED_VERSION = '3.5';
		const VERSION = '1.0';
		const DBVERSION = '1.0';

		public static $plugin_name;
		public static $plugin;
		public static $plugin_file;
		public static $templates_folder;
		public static $images_url;
		public static $styles_url;
		public static $adminConfigPageName;
		public static $options_name;
		public static $dbtable_pictures;
		public static $dbtable_votes;

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
			'photoFolder' => self::PLUGIN,
			'dateFormat' => 1
		);

		/**
		 * @var array Plugin Options
		 */
		protected $options;
		protected $notices;
		protected $errors;

		public function __construct() {

			global $wpdb;

			self::$plugin_name = __('Concours Photos', self::PLUGIN);
			self::$plugin_file = basename(dirname(__FILE__)) . '/' . basename(__FILE__);

			self::$adminConfigPageName = self::PLUGIN . '-configuration';
			self::$templates_folder = dirname(__FILE__) . '/templates/';
			self::$images_url = plugins_url(self::PLUGIN) . '/images/';
			self::$styles_url = plugins_url(self::PLUGIN) . '/css/';

			self::$dbtable_pictures = $wpdb->prefix . self::DBTABLE_PREFIX . '_pictures';
			self::$dbtable_votes = $wpdb->prefix . self::DBTABLE_PREFIX . '_votes';

			self::$options_name = self::PLUGIN;
			$this->loadOptions();

			add_action('init', array($this, 'wp_init'));

			/* if (is_admin()) {
			  require_once( __DIR__ . '/aef-photos-contest-admin.php');
			  $o = new AefPhotosContestAdmin();
			  }
			  else {
			  require_once( __DIR__ . '/aef-photos-contest-front.php');
			  $o = new AefPhotosContestFront();
			  } */
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

		public function wp_init() {
			
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

		public function formatDate($date) {
			$date = preg_replace(
				'#(\d{2,4})-(\d{1,2})-(\d{1,2})#', self::$dateFormats[$this->options['dateFormat']]['pattern_display'], $date);
			return $date;
		}

	}

	global $aefPC;

	if (is_admin()) {
		require_once( __DIR__ . '/aef-photos-contest-admin.php');
		$aefPC = new AefPhotosContestAdmin();
	}
	else {
		require_once( __DIR__ . '/aef-photos-contest-front.php');
		$aefPC = new AefPhotosContestFront();
	}
}
