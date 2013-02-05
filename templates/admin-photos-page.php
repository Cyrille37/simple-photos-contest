<?php
/*
 * Plugin admin : Photos page
 */
?>
<style type="text/css">
	.alternate { background-color: #f2f2f2}
	.wp-list-table tbody td {vertical-align: middle}
	
	.wp-list-table tbody th.check-column  {vertical-align: middle; padding: 2px}
	.wp-list-table tbody th.check-column img {width: 100%; height: 100%; vertical-align: middle; border:0; }

	.wp-list-table .column-id { width: 5%; }
	.wp-list-table .column-0 { width: 70px;}
	
	.span-photo_user_filename { font-style: italic; font-stretch: condensed; }
</style>

<div class="wrap">
	<div id="icon-options-general" class="icon32">
		<br>
	</div>

	<h2><?php _e('Photos', AefPhotosContest::PLUGIN); ?></h2>

	<form id="photos-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php $photosListTable->display() ?>
	</form>

</div>
