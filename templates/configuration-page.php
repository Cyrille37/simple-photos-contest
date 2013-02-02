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
	
	<h2><?php _e('Configuration du concours photos', AefPhotosContest::POST_TYPE); ?></h2>

	<form name="configuration" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<?php wp_nonce_field(AefPhotosContest::POST_TYPE.'_configuration') ?>

		<?php submit_button(); ?>

		<h3><?php _e('Concours',AefPhotosContest::POST_TYPE); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th align="left">
					<?php _e('Vote open date', AefPhotosContest::POST_TYPE); ?>
				</th>
				<td>
					<input class="datepicker" type="text" size="10" name="voteOpenDate" value="<?php echo $aefPC->formatDate( $aefPC->getOption('voteOpenDate')); ?>" />
					<span class="setting-description">
						<?php _e('This is vote open date formated as ', AefPhotosContest::POST_TYPE); echo AefPhotosContest::$dateFormats[$aefPC->getOption('dateFormat')]['label'], '.'; ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Vote close date', AefPhotosContest::POST_TYPE); ?>
				</th>
				<td>
					<input class="datepicker" type="text" size="10" name="voteCloseDate" value="<?php echo $aefPC->formatDate( $aefPC->getOption('voteCloseDate')); ?>" />
					<span class="setting-description">
						<?php _e('This is vote close date formated as ', AefPhotosContest::POST_TYPE); echo AefPhotosContest::$dateFormats[$aefPC->getOption('dateFormat')]['label'], '.'; ?>
					</span>
				</td>
			</tr>
		</table>

		<h3><?php _e('Advanced options',AefPhotosContest::POST_TYPE); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th align="left">
					<?php _e('Photos folder', AefPhotosContest::POST_TYPE); ?>
				</th>
				<td>
					<input type="text" size="35" name="photoFolder" value="<?php echo $aefPC->getOption('photoFolder', 'wp-content/' . AefPhotosContest::POST_TYPE); ?>" />
					<span class="setting-description"><?php _e('This is the folder path for all contest photos, relative to the wordpress content folder (wp-content).', AefPhotosContest::POST_TYPE); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Date format', AefPhotosContest::POST_TYPE); ?>
				</th>
				<td>
					<select name="dateFormat">
						<?php foreach( AefPhotosContest::$dateFormats as $k => $v ){ ?>
						<option value="<?php echo $k ?>"
										<?php if($aefPC->getOption('dateFormat') == $k ) echo 'selected="selected"' ?>
										><?php echo $v['label']?></option>
						<?php } ?>
					</select>
					<span class="setting-description"><?php _e('This is the date format for vote date.', AefPhotosContest::POST_TYPE); ?></span>
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
			buttonImage: "<?php echo plugin_dir_url(__FILE__). '../images/vcalendar.png';?>",
			buttonImageOnly: true,
			dateFormat : "<?php echo AefPhotosContest::$dateFormats[$aefPC->getOption('dateFormat')]['format']; ?>"
		});
	});
</script>
