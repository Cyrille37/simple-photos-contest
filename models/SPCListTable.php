<?php

/*
 * 
 */

if (!class_exists('WP_List_Table')) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class SPCListTable to manage lists in admin pages.
 * Subclass of WP_List_Table.
 * 
 * Doc:
 * http://codex.wordpress.org/Class_Reference/WP_List_Table
 * http://wp.smashingmagazine.com/2011/11/03/native-admin-tables-wordpress/
 * 
 * Styling:
 * http://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/
 * 
 * @author cyrille
 */
abstract class SPCListTable extends WP_List_Table {

	const DEFAULT_ORDERBY = 'id'; // 'photo_name';
	const DEFAULT_ORDER = 'asc';
	const DEFAULT_ITEMS_PER_PAGE = 25;
	const DEFAULT_COLUMN_TYPE = 'str';

	protected $columns;

	public function get_columns() {

		$columns = array();
		foreach ($this->columns as $k => $v) {
			$columns[$k] = $v['label'];
		}
		return $columns;
	}

	public function get_sortable_columns() {

		$sortable_columns = array();
		foreach ($this->columns as $k => $v) {
			if (isset($v['sortable']) && $v['sortable'] === false) {
				
			}
			else {
				$sortable_columns[$k] = array($k, $k == self::DEFAULT_ORDERBY ? true : false);
			}
		}
		return $sortable_columns;
	}

	function get_hidden_columns() {

		$columns = array();
		foreach ($this->columns as $k => $v) {
			if (isset($v['hidden']) && $v['hidden'] === true)
				$columns[] = $k;
		}
		return $columns;
	}

	function column_default($item, $column_name) {

		$cType = self::DEFAULT_COLUMN_TYPE;
		if (isset($this->columns[$column_name]['type'])) {
			$cType = $this->columns[$column_name]['type'];
		}
		switch ($cType) {
			case 'int':
			case 'str':
			default:
				return $item[$column_name];
		}
	}

}
