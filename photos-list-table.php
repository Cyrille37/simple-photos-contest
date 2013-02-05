<?php
/**
 * Class Photos_List_Table to manage the photos list in admin "photos" page.
 */

if (!class_exists('WP_List_Table')) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Subclass of WP_List_Table
 * 
 * Doc:
 * http://codex.wordpress.org/Class_Reference/WP_List_Table
 * http://wp.smashingmagazine.com/2011/11/03/native-admin-tables-wordpress/
 * 
 * Styling:
 * http://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/
 */
class Photos_List_Table extends WP_List_Table {

	const DEFAULT_ORDERBY = 'id'; // 'photo_name';
	const DEFAULT_ORDER = 'asc';
	const DEFAULT_ITEMS_PER_PAGE = 10;
	const DEFAULT_COLUMN_TYPE = 'str';

	protected $columns;

	function __construct() {

		parent::__construct(array(
			'singular' => __('photo'), //singular name of the listed records
			'plural' => __('photos'), //plural name of the listed records
			'ajax' => false //does this table support ajax?
		));

		$this->columns = array(
			'id' => array('label' => __('Id'), 'type' => 'int', 'hiddenZ' => true),
			'photo_name' => array('label' => __('Photo name')),
			'photographer_name' => array('label' => __('Photographer name')),
			'photographer_email' => array('label' => __('Photographer email'))
		);
	}

	function get_columns() {

		$columns = array();
		foreach ($this->columns as $k => $v) {
			$columns[$k] = $v['label'];
		}
		array_splice($columns, 1, 0, array('thumb'=>'photo'));
		return $columns;
	}

	function get_sortable_columns() {

		$sortable_columns = array();
		foreach ($this->columns as $k => $v) {
			$sortable_columns[$k] = array($k, $k == self::DEFAULT_ORDERBY ? true : false);
		}
		return $sortable_columns;
	}

	function column_cb($item){
	
		global $aefPC;
		return
		'<a class="thickbox" href="'.$aefPC->getPhotoUrl($item, 'view').'">'
		.'<img src="'.$aefPC->getPhotoUrl($item, 'thumb').'" />'
		.'</a>';
	}

	function column_default($item, $column_name) {

		if ($column_name == 'id') {
			return '<a href="'
				. admin_url('admin.php?page=' . AefPhotosContestAdmin::PAGE_PHOTO_EDIT . '&id=' . $item['id'])
				. '" >'
				. $item['id'] . '</a>'
			;
		}

		if( $column_name == 'photo_name')
		{
			$item['photo_name'] .= '<br/><span class="span-photo_user_filename">'.$item['photo_user_filename'].'</span>';
		}
	
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

	function get_hidden_columns() {

		$columns = array();
		foreach ($this->columns as $k => $v) {
			if (isset($this->columns[$k]['hidden']))
				$columns[] = $k;
		}
		return $columns;
	}

	function prepare_items() {

		global $wpdb;

		$per_page = self::DEFAULT_ITEMS_PER_PAGE;

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$current_page = $this->get_pagenum();

		$dataCount = $wpdb->get_var('SELECT COUNT(*) FROM ' . AefPhotosContest::$dbtable_photos);

		$data = $wpdb->get_results(
			//'SELECT ' . implode(',', array_keys($this->columns)) . ' FROM ' . AefPhotosContest::$dbtable_photos
			'SELECT * FROM ' . AefPhotosContest::$dbtable_photos
			. ' ORDER BY ' . (!empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : self::DEFAULT_ORDERBY)
			. ' ' . (!empty($_REQUEST['order']) && ( $_REQUEST['order'] == 'asc' || $_REQUEST['order'] == 'desc') ? $_REQUEST['order'] : self::DEFAULT_ORDER)
			. ' LIMIT ' . $per_page . ' OFFSET ' . (($current_page - 1) * $per_page)
			, ARRAY_A);

		$this->items = $data;
		$total_items = $dataCount;

		$this->set_pagination_args(array(
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page' => $per_page, //WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items / $per_page) //WE have to calculate the total number of pages
		));
	}

}
