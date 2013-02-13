<?php

/*
  Plugin Name: AEF Simple Photos Contest
  Plugin URI: https://github.com/Cyrille37/aef-photos-contest
  Description: A simple photos contest plugin for WordPress
  Author: Artéfacts & Conseil Général de Loir-et-Cher
  Author URI: http://www.artefacts.coop, http://cg41.fr
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

if (!defined('AEF_PHOTOS_CONTEST')) {

	define('AEF_PHOTOS_CONTEST', true);
		require_once( __DIR__ . '/controlers/AefPhotosContest.php');

	global $aefPC;

	AefPhotosContest::$plugin_file = basename(dirname(__FILE__)) . '/' . basename(__FILE__);

	if (is_admin()) {
		require_once( __DIR__ . '/controlers/AefPhotosContestAdmin.php');
		$aefPC = new AefPhotosContestAdmin();
	}
	else {
		require_once( __DIR__ . '/controlers/AefPhotosContestFront.php');
		$aefPC = new AefPhotosContestFront();
	}
}
