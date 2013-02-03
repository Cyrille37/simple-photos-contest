<?php
/*
 * Plugin admin : Photos
 *  page
 */
?>
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
