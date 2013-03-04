<?php
/*
 * Plugin admin : Photos page
 */

if (!class_exists('SPCListTable')) {
	require_once( __DIR__ . '/../models/SPCListTable.php' );
}

/**
 * Class Photos_List_Table to manage the photos list in admin "photos" page.
 * Subclass of SPCListTable
 * 
 * Doc:
 * http://codex.wordpress.org/Class_Reference/WP_List_Table
 * http://wp.smashingmagazine.com/2011/11/03/native-admin-tables-wordpress/
 * 
 * Styling:
 * http://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/
 */
class Photos_List_Table extends SPCListTable {

	function __construct() {

		parent::__construct(array(
			'singular' => __('photo'), //singular name of the listed records
			'plural' => __('photos'), //plural name of the listed records
			'ajax' => false //does this table support ajax?
		));

		$this->columns = array(
			'id' => array('label' => __('Id'), 'type' => 'int', 'hiddenZ' => true, 'sortableZ' => false),
			'thumb' => array('label' => 'Photo'),
			'votes' => array('label' => 'Votes'),
			'photo_name' => array('label' => __('Photo name')),
			'photographer_name' => array('label' => __('Photographer name')),
			'photographer_email' => array('label' => __('Photographer email'))
		);
	}

	function column_id($item) {

		return sprintf('<a href="?page=%s&id=%s" title="Edit photo">%d</a>', SimplePhotosContestAdmin::PAGE_PHOTO_EDIT,
				$item['id'], $item['id']);
	}

	function column_thumb($item) {

		global $gSPC;

		return
			'<a class="thickbox" title="´' . $item['photo_name'] . '´ by ´' . $item['photographer_name'] . '´" href="' . $gSPC->getPhotoUrl($item,
				'view') . '">'
			. '<img src="' . $gSPC->getPhotoUrl($item, 'thumb') . '" />'
			. '</a>';
	}

	function column_votes($item) {

		return $item['votes'];
	}

	function column_photo_name($item) {

		$name =
			$item['photo_name'] . '<br/><span class="span-photo_user_filename">' . $item['photo_user_filename'] . '</span>'

		;
		$actions = array(
			'edit' => sprintf('<a href="?page=%s&id=%s">Edit</a>', SimplePhotosContestAdmin::PAGE_PHOTO_EDIT, $item['id']),
			'delete' => sprintf('<a href="?page=%s&paged=%s&action=%s&id=%s" onclick="return confirm(\'%s\')">Delete</a>',
				$_REQUEST['page'], (isset($_REQUEST['paged']) ? $_REQUEST['paged'] : ''), 'delete', $item['id'],
				__('Confirm deletion of photo id ') . $item['id']),
		);
		return sprintf('%1$s<br/>%2$s', $name, $this->row_actions($actions));
	}

	/**
	 * Called befote each render
	 * 
	 * @global type $gSPC
	 */
	function prepare_items() {

		global $gSPC;

		$per_page = self::DEFAULT_ITEMS_PER_PAGE;

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$current_page = $this->get_pagenum();

		// First query : count all items
		$dataCountAll = $gSPC->getDaoPhotos()->count();

		// Second query : select only items to display
		$queryOptions = new SPCQueryOptions();
		$queryOptions
			->orderBy((!empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : self::DEFAULT_ORDERBY),
				(!empty($_REQUEST['order']) && ( $_REQUEST['order'] == 'asc' || $_REQUEST['order'] == 'desc') ? $_REQUEST['order'] : self::DEFAULT_ORDER))
			->limit($per_page, (($current_page - 1) * $per_page));

		$data = $gSPC->getDaoPhotos()->getAllWithVotesCount($queryOptions);

		$this->items = $data;
		$total_items = $dataCountAll;

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => ceil($total_items / $per_page)
		));
	}

}

$photosListTable = new Photos_List_Table();
$photosListTable->prepare_items();

?>
<style type="text/css">

	.alternate { background-color: #f2f2f2}
	.wp-list-table tbody td {vertical-align: middle}

	.wp-list-table .column-id { width: 5%; }
	.wp-list-table .column-thumb { width: 70px;}
	.wp-list-table .column-thumb img { width: 60px; height: 50px;}
	.wp-list-table .column-votes { width: 7%;  text-align: center;}

	.span-photo_user_filename { font-style: italic; font-stretch: condensed; }
	
	#photos-order {
		margin-left: 5%;
	}
</style>

<div class="wrap">
	<div id="icon-options-general" class="icon32">
		<br>
	</div>

	<h2><?php _e('Photos contest - Photos list', SimplePhotosContest::PLUGIN)?></h2>

	<form id="photos-order">
		<input type="button" onclick="window.location='<?php echo admin_url('admin.php?page='.SimplePhotosContestAdmin::PAGE_PHOTOS_ORDER) ?>'" value="Trier les photos"/>
	</form>

	<form id="photos-list" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php $photosListTable->display() ?>
	</form>
	

	<?php /*
	<form>
		<input type="button" onclick="window.location='<?php echo admin_url('admin.php?page='.SimplePhotosContestAdmin::PAGE_PHOTOS.'&action=force-commentInPhotographername') ?>'"
					 value="Copy comment into photographer name"/>
	</form>
	 */ ?>


</div>
