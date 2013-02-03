<?php

if (!class_exists('WP_List_Table')) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * http://codex.wordpress.org/Class_Reference/WP_List_Table
 * http://wp.smashingmagazine.com/2011/11/03/native-admin-tables-wordpress/
 */
class Photod_List_Table extends WP_List_Table {

	function __construct() {

		parent::__construct(array(
			'singular' => __('photo'), //singular name of the listed records
			'plural' => __('photos'), //plural name of the listed records
			'ajax' => false	//does this table support ajax?
		));
	}

	function get_columns() {

		$columns = array(
			'photo-name' => __('Photo name'),
			'photographer-name' => __('Photographer name'),
			'photographer-email' => __('Photographer email'),
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'photo-name' => array('photo_name', false), //true means it's already sorted
			'photographer-name' => array('photographer_name', false),
			'photographer-email' => array('photographer_email', false)
		);
		return $sortable_columns;
	}

	function prepare_items() {
		
		global $wpdb;
		
		$per_page = 5;
		$current_page = $this->get_pagenum();
		$data = $wpdb->get_results('select * from ' . AefPhotosContest::$dbtable_photos . ' LIMIT ' . $per_page . ' OFFSET ' . (($current_page - 1) * $per_page));
		$total_items = count($data);
		$this->items = $data;

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args(array(
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page' => $per_page, //WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items / $per_page)	 //WE have to calculate the total number of pages
		));
	}

}
