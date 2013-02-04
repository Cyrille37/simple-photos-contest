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
			'ajax' => false //does this table support ajax?
		));
	}

	function get_columns() {

		$columns = array(
			'photo_name' => __('Photo name'),
			'photographer_name' => __('Photographer name'),
			'photographer_email' => __('Photographer email'),
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'photo_name' => array('photo_name', false), //true means it's already sorted
			'photographer_name' => array('photographer_name', false),
			'photographer_email' => array('photographer_email', false)
		);
		return $sortable_columns;
	}

	var $example_data = array(
		array(
			'ID' => 1,
			'photo_name' => '300',
			'photographer_email' => 'R',
			'photographer_name' => 'Zach Snyder'
		),
		array(
			'ID' => 2,
			'photo_name' => 'Eyes Wide Shut',
			'photographer_email' => 'R',
			'photographer_name' => 'Stanley Kubrick'
		),
		array(
			'ID' => 3,
			'photo_name' => 'Moulin Rouge!',
			'photographer_email' => 'PG-13',
			'photographer_name' => 'Baz Luhrman'
		),
		array(
			'ID' => 4,
			'photo_name' => 'Snow White',
			'photographer_email' => 'G',
			'photographer_name' => 'Walt Disney'
		),
		array(
			'ID' => 5,
			'photo_name' => 'Super 8',
			'photographer_email' => 'PG-13',
			'photographer_name' => 'JJ Abrams'
		),
		array(
			'ID' => 6,
			'photo_name' => 'The Fountain',
			'photographer_email' => 'PG-13',
			'photographer_name' => 'Darren Aronofsky'
		),
		array(
			'ID' => 7,
			'photo_name' => 'Watchmen',
			'photographer_email' => 'R',
			'photographer_name' => 'Zach Snyder'
		)
	);

	function column_default($item, $column_name) {

		_log('$column_name: '. $column_name);
		switch ($column_name) {
			case 'photo_name':
			case 'photographer_name':
			case 'photographer_email':
				return $item[$column_name];
			default:
				return print_r($item, true); //Show the whole array for troubleshooting purposes
		}
	}

	const DEFAULT_ORDERBY = 'photo_name' ;
	
	function prepare_items() {

		global $wpdb;

		$per_page = 5;

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$current_page = $this->get_pagenum();

		$data = $wpdb->get_results(
			'SELECT * FROM ' . AefPhotosContest::$dbtable_photos
			.' ORDER BY '.(!empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : self::DEFAULT_ORDERBY)
			.' '.(!empty($_REQUEST['order']) && ( $_REQUEST['order']=='asc' || $_REQUEST['order']=='desc') ? $_REQUEST['order'] : 'ASC')
			. ' LIMIT ' . $per_page . ' OFFSET ' . (($current_page - 1) * $per_page)
			, ARRAY_A);

		/*
		$data = $this->example_data;

       function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
		$data = array_slice($data, (($current_page - 1) * $per_page), $per_page);
 */

		$this->items = $data;
		$total_items = 7 ; //count($data);

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args(array(
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page' => $per_page, //WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items / $per_page) //WE have to calculate the total number of pages
		));
	}

}
