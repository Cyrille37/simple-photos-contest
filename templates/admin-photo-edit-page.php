<?php
/*
 * Plugin admin : Add photo
 *  page
 */
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32">
		<br>
	</div>

	<h2><?php _e('Add Picture', AefPhotosContest::PLUGIN); ?></h2>

	<?php if( isset($this->picture) ) { ?>
	
	<form name="configuration" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<?php wp_nonce_field(AefPhotosContestAdmin::PAGE_PICTURE_EDIT, AefPhotosContestAdmin::PAGE_PICTURE_EDIT.'_nonce') ?>

		<?php submit_button(); ?>

		<div class="width_full p_box">
		<p>
			<label><?php _e('Photographer name'); ?><br/>
				<input type="text" name="photographer_name" class="regular-text" value="<?php echo $this->picture['photographer_name']; ?>">
			</label>
			<span class="description"><?php _e('This is the photographer lastname and firstname'); ?></span>
		</p>
		<p>
			<label><?php _e('Photographer email'); ?><br/>
				<input type="text" name="photographer_email" class="regular-text" value="<?php echo $this->picture['photographer_email']; ?>">
			</label>
			<span class="description"><?php _e('This is the photographer email'); ?></span>
		</p>
		<p>
			<label><?php _e('Photo name'); ?><br/>
				<input type="text" name="picture_name" class="regular-text" value="<?php echo $this->picture['picture_name']; ?>">
			</label>
			<span class="description"><?php _e('This is the photo name'); ?></span>
		</p>
		<p>
			<label><?php _e('Notes'); ?><br/>
				<textarea name="notes" cols="40" rows="5" ><?php echo $this->picture['notes']; ?></textarea>
			</label>
			<span class="description"><?php _e('Here you can write any comment, it only be visible in admin page'); ?></span>
		</p>
		<p>
			<label><?php _e('Picture'); ?><br/>
				<input type="file" name="picture_file" size="35" class="imagefile"/>
			</label>
			<span class="description"><?php _e('This is the original picture'); ?></span>
		</p>
	</div>
	
	<?php submit_button(); ?>

	</form>
	
	<?php } ?>
	
</div>
