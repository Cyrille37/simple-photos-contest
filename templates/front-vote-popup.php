<?php
/*
 * vote popup
 */
?>

<div id="aef-vote-dialog" title="Vote">
	<?php
	if (empty($voterEmail)) {
		// $voterEmail not set : need to authentify
		?>

		<div id="vote-auth-form">
			<form title="Social Authentification">
				<div style="margin-bottom: 3px;">
					<label>
						<?php
						_e('To vote you have to identify yourselves. You can use your preferred social network',
							AefPhotosContest::PLUGIN);
						?>:
					</label>
				</div>
				<a href="javascript:void(0);" title="Facebook" class="aef-auth-facebook"><img alt="Facebook" src="<?php echo AefPhotosContest::$images_url . 'facebook_32.png' ?>" /></a>
				<a href="javascript:void(0);" title="Google" class="aef-auth-google"><img alt="Google" src="<?php echo AefPhotosContest::$images_url . 'google_32.png' ?>" /></a>
				<a href="javascript:void(0);" title="Yahoo" class="aef-auth-yahoo"><img alt="Yahoo" src="<?php echo AefPhotosContest::$images_url . 'yahoo_32.png' ?>" /></a>
				<input type="hidden" class="aef-auth-facebook_client_id" name="client_id" value="<?php echo $aefPC->getOption('facebookClientId'); ?>" />
				<input type="hidden" class="aef-auth-facebook" name="redirect_uri" value="<?php echo urlencode(AefPhotosContest::$plugin_url . '/auth/facebook/callback.php'); ?>" />
				<input type="hidden" class="aef-auth-google" name="redirect_uri" value="<?php echo( AefPhotosContest::$plugin_url . '/auth/google/connect.php' ); ?>" />
				<input type="hidden" class="aef-auth-yahoo" name="redirect_uri" value="<?php echo( AefPhotosContest::$plugin_url . '/auth/yahoo/connect.php' ); ?>" />
			</form>
		</div>

		<script type="text/javascript">
			jQuery(document).ready( function() {
				
				var form = jQuery('#vote-auth-form');
				jQuery('a.aef-auth-facebook', form).click(function() {
					var client_id = jQuery('input.aef-auth-facebook_client_id', form).val();
					var redirect_uri = jQuery('input.aef-auth-facebook', form).val();

					if(client_id == '') {
						alert('Error, the Facebook provider is not configured.')
					} else {
						window.open('https://graph.facebook.com/oauth/authorize?client_id=' + client_id + '&redirect_uri=' + redirect_uri + '&scope=email',
							'','scrollbars=no,menubar=no,height=400,width=800,resizable=yes,toolbar=no,status=no');
					}
				});

				jQuery('a.aef-auth-google', form).click(function() {
					var redirect_uri = jQuery('input.aef-auth-google', form).val();
					window.open(redirect_uri,'','scrollbars=no,menubar=no,height=400,width=800,resizable=yes,toolbar=no,status=no');
				});

				jQuery('a.aef-auth-yahoo', form).click( function() {
					var redirect_uri = jQuery('input.aef-auth-yahoo', form).val();
					window.open(redirect_uri,'','scrollbars=no,menubar=no,height=400,width=800,resizable=yes,toolbar=no,status=no');
				});

			});

		</script>

		<?php
	}
	else if ($voterStatus->canVote && isset($photo)) {
		?>

		<div id="vote-form">
			<p><?php _e('You are identified as ') ?><span class="aef-vote-voter-email"><?php echo $voterEmail ?></span></p>
			<p><?php _e('You can vote for this picture') ?>
				<img
					src="<?php echo $this->getPhotoUrl($photo,'thumb') ?>"
					alt="<?php echo htmlspecialchars($photo['photographer_name']); ?>"
					title="<?php echo htmlspecialchars($photo['photo_name']); ?>"
					/>
			</p>
			<p>
				<span class="vote_pour">Pour</span>
				<span class="vote_cancel">Fermer</span>
			</p>
			<script type="text/javascript">

				jQuery('#vote-form .vote_pour').click(function() {
					
					var params = {
						action: 'vote',
						photo_id: <?php echo $photo['id']; ?>
					}; 

					jQuery.post(
					AefPC.ajaxurl,
					params,
					function( jsonString ) {
						console.dir( jsonString );
						var res = JSON.parse(jsonString);
						if( res.command == 'vote_ok' )
						{
							jQuery.fancybox.close();
						}
						else
						{
							if( res.command == 'error' ){
								alert( res.message );
							}else{
								alert('unknow result');
							}
						}
					}
				);
				});
								
			</script>
		</div>

		<?php
	}
	else if (!$voterStatus->canVote && isset($photo)) {
		?>
		<div id="aef-popup-already-voted">
			<p><?php _e('You are identified as ') ?><span class="aef-vote-voter-email"><?php echo $voterEmail ?></span></p>
			<p><?php _e('You have already voted for this photo') ?>
				<img
					src="<?php echo $this->getPhotoUrl($photo,
			'thumb') ?>"
					alt="<?php echo htmlspecialchars($photo['photographer_name']); ?>"
					title="<?php echo htmlspecialchars($photo['photo_name']); ?>"
					/>
			</p>
			<?php if (!empty($voterStatus->nextVoteDate)) { ?>
				<p><?php _e('You will be able to vote one more time from ');
		echo $aefPC->formatDate($voterStatus->nextVoteDate) ?></p>
		<?php } ?>
		</div>
		<?php
	}
	else {
		echo _e('Unknow error');
	}
	?>

</div>
