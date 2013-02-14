<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if (!class_exists('AefListTable')) {
	require_once( __DIR__ . '/../models/AefListTable.php' );
}

class VotesListTable extends AefListTable {

	protected $columns;

	function __construct() {

		parent::__construct(array(
			'singular' => __('vote'), //singular name of the listed records
			'plural' => __('votes'), //plural name of the listed records
			'ajax' => false //does this table support ajax?
		));

		$this->columns = array(
			'id' => array('label' => __('Id'), 'type' => 'int', 'hidden' => true),
			'voter_email' => array('label' => 'EMail'),
			'vote_date' => array('label' => __('Date')),
			'photo_id' => array('label' => __('Photo')),
		);
	}

	function column_voter_email($item) {

		$name = $item['voter_email'];
		$actions = array(
			'delete' => sprintf('<a href="?page=%s&paged=%s&action=%s&id=%s" onclick="return confirm(\'%s\')">Delete</a>',
				$_REQUEST['page'], (isset($_REQUEST['paged']) ? $_REQUEST['paged'] : ''), 'delete', $item['id'],
				__('Confirm deletion of vote from ') . $item['voter_email'] . ' for photo id ' . $item['photo_id']),
		);
		return sprintf('%1$s %2$s', $name, $this->row_actions($actions));
	}

	function column_photo_id($item) {

		global $aefPC;

		$photo = array_merge($item);
		$photo['id'] = $item['photo_id'];
		
		return sprintf('<a href="?page=%s&id=%s" title="Edit photo">%d</a>', AefPhotosContestAdmin::PAGE_PHOTO_EDIT,
				$photo['id'], $photo['id'])
			.'<a class="thickbox" title="´' . $photo['photo_name'] . '´ by ´' . $photo['photographer_name'] . '´"'
			.' href="' . $aefPC->getPhotoUrl($photo,'view') . '">'
			. '<img src="' . $aefPC->getPhotoUrl($photo, 'thumb') . '" />'
			. '</a>';
	}

	/**
	 * Called befote each render
	 * 
	 * @global type $aefPC
	 */
	function prepare_items() {

		global $aefPC;

		$per_page = self::DEFAULT_ITEMS_PER_PAGE;

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$current_page = $this->get_pagenum();

		// First query : count all items
		$dataCountAll = $aefPC->getDaoVotes()->count();

		// Second query : select only items to display
		$queryOptions = new AefQueryOptions();
		$queryOptions
			->orderBy((!empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : self::DEFAULT_ORDERBY),
				(!empty($_REQUEST['order']) && ( $_REQUEST['order'] == 'asc' || $_REQUEST['order'] == 'desc') ? $_REQUEST['order'] : self::DEFAULT_ORDER))
			->limit($per_page, (($current_page - 1) * $per_page));

		$data = $aefPC->getDaoVotes()->getAllWithPhotoData($queryOptions);

		$this->items = $data;
		$total_items = $dataCountAll;

		$this->set_pagination_args(array(
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page' => $per_page, //WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items / $per_page) //WE have to calculate the total number of pages
		));
	}

}

$votesListTable = new VotesListTable();
$votesListTable->prepare_items();
?>
<style type="text/css">

	.alternate { background-color: #f2f2f2}
	.row-actions { display: inline;}
	.wp-list-table tbody td {vertical-align: middle}

	.wp-list-table .column-id { width: 5%; }
	.wp-list-table .column-photo_id { width: 100px; vertical-align: middle; text-align: right; }
	.wp-list-table .column-photo_id img { width: 50px; height: 40px; vertical-align: middle; margin-left: 4px}

</style>
<div class="wrap">
	<div id="icon-options-general" class="icon32">
		<br>
	</div>

	<h2><?php _e('Concours photo - Votes') ?></h2>

	<form id="votes-list" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php $votesListTable->display() ?>
	</form>
</div>
