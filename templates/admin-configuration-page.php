<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32">
		<br>
	</div>

	<h2><?php _e('Photos contest configuration', AefPhotosContest::PLUGIN); ?></h2>

	<form name="configuration" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<?php wp_nonce_field(AefPhotosContestAdmin::PAGE_CONFIGURATION, AefPhotosContestAdmin::PAGE_CONFIGURATION.'_nonce') ?>

		<?php submit_button(); ?>

		<h3><?php _e('Concours',AefPhotosContest::PLUGIN); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th align="left">
					<?php _e('Vote open date', AefPhotosContest::PLUGIN); ?>
				</th>
				<td>
					<input class="datepicker" type="text" size="10" name="voteOpenDate" value="<?php echo $aefPC->formatDate( $aefPC->getOption('voteOpenDate')); ?>" />
					<span class="setting-description">
						<?php _e('This is vote open date formated as ', AefPhotosContest::PLUGIN); echo AefPhotosContestAdmin::$dateFormats[$aefPC->getOption('dateFormat')]['label'], '.'; ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Vote close date', AefPhotosContest::PLUGIN); ?>
				</th>
				<td>
					<input class="datepicker" type="text" size="10" name="voteCloseDate" value="<?php echo $aefPC->formatDate( $aefPC->getOption('voteCloseDate')); ?>" />
					<span class="setting-description">
						<?php _e('This is vote close date formated as ', AefPhotosContest::PLUGIN); echo AefPhotosContestAdmin::$dateFormats[$aefPC->getOption('dateFormat')]['label'], '.'; ?>
					</span>
				</td>
			</tr>
		</table>
		
		<h3><?php _e('Photos',AefPhotosContest::PLUGIN); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th align="left">
					<?php _e('Thumbnails size', AefPhotosContest::PLUGIN); ?>
				</th>
				<td>
					<?php _e('width',AefPhotosContest::PLUGIN) ?><input class="" type="text" size="4" name="thumb_w" value="<?php echo $aefPC->getOption('thumbW'); ?>" />
					<?php _e('height',AefPhotosContest::PLUGIN) ?><input class="" type="text" size="4" name="thumb_h" value="<?php echo $aefPC->getOption('thumbH'); ?>" />
					<span class="setting-description">
						<?php _e('This is photos thumbnail size.', AefPhotosContest::PLUGIN); ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Views size', AefPhotosContest::PLUGIN); ?>
				</th>
				<td>
					<?php _e('width',AefPhotosContest::PLUGIN) ?><input class="" type="text" size="4" name="view_w" value="<?php echo $aefPC->getOption('viewW'); ?>" />
					<?php _e('height',AefPhotosContest::PLUGIN) ?><input class="" type="text" size="4" name="view_h" value="<?php echo $aefPC->getOption('viewH'); ?>" />
					<span class="setting-description">
						<?php _e('This is photo view size.', AefPhotosContest::PLUGIN); ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th>
					&nbsp;
				</th>
				<td>
					<input type="button" onclick="window.location='admin.php?page=<?php echo AefPhotosContestAdmin::PAGE_CONFIGURATION;?>&action=rebuildthumbs'" value="<?php _e('Rebuild thumbs') ?>"/>
				</td>
			</tr>
		</table>


		<h3><?php _e('Advanced options',AefPhotosContest::PLUGIN); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th align="left">
					<?php _e('Photos folder', AefPhotosContest::PLUGIN); ?>
				</th>
				<td>
					<input type="text" size="35" name="photoFolder" value="<?php echo $aefPC->getOption('photoFolder', 'wp-content/' . AefPhotosContest::PLUGIN); ?>" />
					<span class="setting-description"><?php _e('This is the folder path for all contest photos, relative to the wordpress content folder (wp-content).', AefPhotosContest::PLUGIN); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Date format', AefPhotosContest::PLUGIN); ?>
				</th>
				<td>
					<select name="dateFormat">
						<?php foreach( AefPhotosContestAdmin::$dateFormats as $k => $v ){ ?>
						<option value="<?php echo $k ?>"
										<?php if($aefPC->getOption('dateFormat') == $k ) echo 'selected="selected"' ?>
										><?php echo $v['label']?></option>
						<?php } ?>
					</select>
					<span class="setting-description"><?php _e('This is the date format for vote date.', AefPhotosContest::PLUGIN); ?></span>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>

	</form>
</div>

<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.datepicker').datepicker({
			showOn: "button",
			buttonImage: "<?php echo AefPhotosContestAdmin::$images_url. 'vcalendar.png';?>",
			buttonImageOnly: true,
			dateFormat : "<?php echo AefPhotosContestAdmin::$dateFormats[$aefPC->getOption('dateFormat')]['format']; ?>"
		});
	});
</script>
