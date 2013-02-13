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
			<label class="<?php echo($aefPC->hasFieldError('photographer_name')?'error-message':'')?>">Nom du photographe<br/>
				<input type="text" name="photographer_name" class="regular-text" value="<?php echo $this->photo['photographer_name']; ?>">
			</label>
			<span class="description">Le nom du photographe tel qu´affiché sur le concours.</span>
		</p>
		<p>
			<label class="<?php echo($aefPC->hasFieldError('photographer_email')?'error-message':'')?>">Email du photographe<br/>
				<input type="text" name="photographer_email" class="regular-text" value="<?php echo $this->photo['photographer_email']; ?>">
			</label>
			<span class="description">
				L´adresse email du photographe.
				<a href="mailto:<?php echo $this->photo['photographer_email']; ?>">Envoyer un mail</a>.
			</span>
		</p>
		<p>
			<label class="<?php echo($aefPC->hasFieldError('photo_name')?'error-message':'')?>">Nom de la photo<br/>
				<input type="text" name="photo_name" class="regular-text" value="<?php echo $this->photo['photo_name']; ?>">
			</label>
			<span class="description">Le nom de la photo telle qu´affichée sur le concours.</span>
		</p>
		<p>
			<label class="<?php echo($aefPC->hasFieldError('notes')?'error-message':'')?>">Commentaires<br/>
				<textarea name="notes" cols="40" rows="5" ><?php echo $this->photo['notes']; ?></textarea>
			</label>
			<span class="description">Commentaire seulement visible ici.</span>
		</p>
		<?php if( !empty( $this->photo['id']) ) { ?>
		
			<?php if( !empty($this->photo['photo_user_filename']) ){ ?>
				<p>
					<label>Nom du fichier original<br/>
						<span><?php echo $this->photo['photo_user_filename']; ?></span>
					</label>
					<span class="description">Le nom du fichier téléchargé</span>
				</p>
				<table>
					<tr>
						<td rowspan="4" >
							<a href="<?php echo $aefPC->getPhotoUrl($this->photo, 'view')?>?t=<?php echo time() ?>">
							<img style="float: left" src="<?php echo $aefPC->getPhotoUrl($this->photo, 'thumb')?>?t=<?php echo time() ?>" />
							</a>
						</td>
					</tr>
					<tr>
						<td style="padding-left: 12px"><a href="<?php echo $aefPC->getPhotoUrl($this->photo, 'thumb')?>">fichier miniature</a></td>
					</tr>
					<tr>
						<td style="padding-left: 12px"><a href="<?php echo $aefPC->getPhotoUrl($this->photo, 'view')?>">fichier grande vue</a></td>
					</tr>
					<tr>
						<td style="padding-left: 12px"><a href="<?php echo $aefPC->getPhotoUrl($this->photo)?>">fichier originale</a></td>
					</tr>
				</table>
				
			<?php } ?>
			<p>
				<label>Téléverser une photo
					<br/>
					<input type="file" name="photo_file" size="35" class="imagefile"/>
				</label>
				<span class="description"><?php echo (empty($this->photo['photo_user_filename']) ? 'Fichier de la photo.': 'La nouvelle photo remplace l´actuelle.' ) ?></span>
			</p>
		<?php } ?>
	</div>
	
	<?php submit_button(); ?>

	</form>
	
	<?php } ?>
	
</div>
