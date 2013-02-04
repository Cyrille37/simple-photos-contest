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

	<h2><?php _e('Add Photo', AefPhotosContest::PLUGIN); ?></h2>

	<?php if( isset($this->photo) ) { ?>
	
	<form name="configuration" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">
		<?php wp_nonce_field(AefPhotosContestAdmin::PAGE_PHOTO_EDIT.$this->photo['id'], AefPhotosContestAdmin::PAGE_PHOTO_EDIT.'_nonce') ?>

		<input type="hidden" name="id" value="<?php echo $this->photo['id']; ?>"/>

		<?php submit_button(); ?>

		<div class="width_full p_box">
		<p>
			<label class="<?php echo($aefPC->hasFieldError('photographer_name')?'error-message':'')?>"><?php _e('Photographer name'); ?><br/>
				<input type="text" name="photographer_name" class="regular-text" value="<?php echo $this->photo['photographer_name']; ?>">
			</label>
			<span class="description"><?php _e('This is the photographer lastname and firstname'); ?></span>
		</p>
		<p>
			<label class="<?php echo($aefPC->hasFieldError('photographer_email')?'error-message':'')?>"><?php _e('Photographer email'); ?><br/>
				<input type="text" name="photographer_email" class="regular-text" value="<?php echo $this->photo['photographer_email']; ?>">
			</label>
			<span class="description"><?php _e('This is the photographer email'); ?></span>
		</p>
		<p>
			<label class="<?php echo($aefPC->hasFieldError('photo_name')?'error-message':'')?>"><?php _e('Photo name'); ?><br/>
				<input type="text" name="photo_name" class="regular-text" value="<?php echo $this->photo['photo_name']; ?>">
			</label>
			<span class="description"><?php _e('This is the photo name'); ?></span>
		</p>
		<p>
			<label class="<?php echo($aefPC->hasFieldError('notes')?'error-message':'')?>"><?php _e('Notes'); ?><br/>
				<textarea name="notes" cols="40" rows="5" ><?php echo $this->photo['notes']; ?></textarea>
			</label>
			<span class="description"><?php _e('Here you can write any comment, it only be visible in admin page'); ?></span>
		</p>
		<?php if( !empty( $this->photo['id']) ) { ?>
		<p>
			<label><?php _e('Photo'); ?><br/>
				<input type="file" name="photo_file" size="35" class="imagefile"/>
			</label>
			<span class="description"><?php _e('This is the original photo'); ?></span>
		</p>
		<?php } ?>
	</div>
	
	<?php submit_button(); ?>

	</form>
	
	<?php } ?>
	
</div>
