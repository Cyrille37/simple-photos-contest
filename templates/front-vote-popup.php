<?php
/*
 * vote popup
 */
?>
<style type="text/css">
	#spc-vote-dialog{
		width:480px;
		border-left: #000 solid thin;
		padding-left: 8px;
	}
	#spc-vote-dialog td {
		vertical-align: middle;
	}
	#spc-vote-dialog .hlist {
	}
	#spc-vote-dialog .hlist li  {
		padding-left: 12px;
		display: inline;  
    list-style: none; /* nécessaire pour IE7 */  
	}
	.vote_pour {
		margin-right: 10% ;
		margin-top: 14px ;
	}
	.vote_cancel {
		margin-top: 14px ;		
	}
	.photo_name{
		font-family: fantasy;
		font-size: 0.9em;
	}
	.photographer_name{
		font-family: fantasy;
		font-size: 0.9em;		
	}
</style>

<div id="spc-vote-dialog" title="Vote"  style="">
	<h3>Vote du concours photo</h3>
	<?php
	if (empty($voterEmail)) {
		// $voterEmail not set : need to authentify
		?>

		<div id="vote-auth-form">
			<form title="Social Authentification">
				<div style="margin-bottom: 3px;">
					<p >
						Pour l'équité des votes, merci de vous identifier avec votre compte de réseau social ou avec un code envoyé par email.
					</p>
					<p >
						Cliquez sur le service de votre choix:
					</p>
				</div>
				<ul class="hlist">
					<li><a href="javascript:void(0);" title="identifiez vous avec Facebook" class="spc-auth-facebook"><img alt="Facebook" src="<?php echo SimplePhotosContest::$images_url . 'facebook_32.png' ?>" /></a></li>
					<li><a href="javascript:void(0);" title="identifiez vous avec Google+" class="spc-auth-google"><img alt="Google" src="<?php echo SimplePhotosContest::$images_url . 'google_32.png' ?>" /></a></li>
					<!--li><a href="javascript:void(0);" title="identifiez vous avec Yahoo" class="spc-auth-yahoo"><img alt="Yahoo" src="<?php echo SimplePhotosContest::$images_url . 'yahoo_32.png' ?>" /></a></li-->
					<li><a href="javascript:void(0);" onclick="return auth_email();" title="identifiez vous avec un code envoyez par email" class="spc-auth-email"><img alt="eMail" src="<?php echo SimplePhotosContest::$images_url . 'email_32.jpg' ?>" /></a></li>
				</ul>
				<br/>
				<input type="hidden" class="spc-auth-facebook_client_id" name="client_id" value="<?php echo $gSPC->getOption('facebookClientId'); ?>" />
				<input type="hidden" class="spc-auth-facebook" name="redirect_uri" value="<?php echo urlencode(SimplePhotosContest::$plugin_url . 'auth/facebook/callback.php'); ?>" />
				<input type="hidden" class="spc-auth-google" name="redirect_uri" value="<?php echo( SimplePhotosContest::$plugin_url . 'auth/google/connect.php' ); ?>" />
				<input type="hidden" class="spc-auth-yahoo" name="redirect_uri" value="<?php echo( SimplePhotosContest::$plugin_url . 'auth/yahoo/connect.php' ); ?>" />
				<input type="hidden" class="spc-auth-email" name="redirect_uri" value="<?php echo( SimplePhotosContest::$plugin_url . 'auth/email/connect.php' ); ?>" />
				<input type="hidden" class="spc-auth-email-sign" name="auth_sign" value="" />
			</form>
		</div>
		<div id="vote-auth-email">
			<form title="Social Authentification">
				<p>Merci d'indiquer votre adresse email à laquelle nous allons vous expédier un code qui vous permettra de voter:</p>
				<p>
					Votre adresse email: <input type="text" name="email" size="30" />
					<input type="button" value="Envoyer le code" onclick="return auth_email('emailSend');" />
					<input type="button" value="Annuler" onclick="return auth_email('emailSend_cancel');" />
				</p>
			</form>
		</div>
		<div id="vote-auth-email-code">
			<form title="Social Authentification">
				<p>Indiquer le code que vous avez reçu à l'adresse email <span id="auth-email"></span></p>
				<p>
					Le code: <input type="text" name="emailCode" size="10" />
					<input type="button" value="Valider" onclick="return auth_email('codeConfirm');" />
					<input type="button" value="Annuler" onclick="return auth_email('codeConfirm_cancel');" />
				</p>
			</form>
		</div>

		<script type="text/javascript">
			jQuery(document).ready( function() {

				jQuery('#vote-auth-email').hide();
				jQuery('#vote-auth-email-code').hide();

				var form = jQuery('#vote-auth-form');
				jQuery('a.spc-auth-facebook', form).click(function() {
					var client_id = jQuery('input.spc-auth-facebook_client_id', form).val();
					var redirect_uri = jQuery('input.spc-auth-facebook', form).val();

					if(client_id == '') {
						alert('Error, the Facebook provider is not configured.')
					} else {
						window.open('https://graph.facebook.com/oauth/authorize?client_id=' + client_id + '&redirect_uri=' + redirect_uri + '&scope=email',
						'','scrollbars=yes,menubar=no,height=460,width=800,resizable=yes,toolbar=no,status=no');
					}
				});

				jQuery('a.spc-auth-google', form).click(function() {
					var redirect_uri = jQuery('input.spc-auth-google', form).val();
					window.open(redirect_uri,'','scrollbars=yes,menubar=no,height=460,width=800,resizable=yes,toolbar=no,status=no');
				});

				jQuery('a.spc-auth-yahoo', form).click( function() {
					var redirect_uri = jQuery('input.spc-auth-yahoo', form).val();
					window.open(redirect_uri,'','scrollbars=yes,menubar=no,height=460,width=800,resizable=yes,toolbar=no,status=no');
				});

			});

			function auth_email(step)
			{
				var root = jQuery('#spc-vote-dialog');
				switch(step){

					case undefined:
										
						jQuery('#vote-auth-form', root).hide();
						jQuery('#vote-auth-email', root).show();
						break;

					case 'emailSend_cancel':
							
						jQuery('#vote-auth-email', root).hide();
						jQuery('#vote-auth-form', root).show();
						break;
		
					case 'emailSend':

						var email = jQuery('input:text[name=email]', root).val(); 
						if( ! isValidEmail(email) )
						{
							return ;
						}
						var redirect_uri = jQuery('input.spc-auth-email', root).val();
						var params = {};
						params.action = 'emailSend' ;
						params.email = email ;
						jQuery.post( redirect_uri, params,
						function( jsonString ) {
							var res = JSON.parse(jsonString);
							if( res.command == 'mail_sent' )
							{
								jQuery('input.spc-auth-email-sign', root).val( res.social_auth_signature );
								jQuery('#vote-auth-email').hide();
								jQuery('#vote-auth-email-code').show();
							}
							else
							{
								if( res.command == 'error' ){
									alert( res.message );
								}else{
									alert('unknow result');
								}
							}
						});
						break;

					case 'codeConfirm_cancel' :
						jQuery('#vote-auth-email-code').hide();
						jQuery('input:text[name=emailCode]', root).val('');
						jQuery('#vote-auth-form', root).show();
						break;
		
					case 'codeConfirm':

						window.spc_vote_auth_callback({
							'social_auth_provider' : 'mail',
							'social_auth_email' : jQuery('input:text[name=email]', root).val(),
							'social_auth_signature' : jQuery('input.spc-auth-email-sign', root).val(),
							'social_auth_access_token' : jQuery('input:text[name=emailCode]', root).val()
						});
						break;

				}
				return false ;
			}
					
		</script>

		<?php
	}
	else if ($voterStatus->canVote && isset($photo)) {
		?>

		<div id="vote-form">
			<p>Vous votez pour cette photo:</p>
			<p style="text-align: center">
				<img
					src="<?php echo $this->getPhotoUrl($photo, 'thumb')	?>"
					alt="<?php echo htmlspecialchars($photo['photographer_name']); ?>"
					title="<?php echo htmlspecialchars($photo['photo_name']); ?>"
					/>
					<br/>
				<span class="photo_name"><?php echo $photo['photo_name'] ?></span>
				<br/>
				<span class="photographer_name"><?php echo $photo['photographer_name'] ?></span>
				<br/>
				<input class="vote_pour" type="button" value="Je vote pour" onclick="vote()" />
				<input class="vote_cancel" type="button" value="Annuler" onclick="jQuery.fancybox.close()" />
			</p>
			<p>
				Vous êtes identifié comme <span class="spc-vote-voter-email"><?php echo $voterEmail ?></span>.
				Si ce n'est pas vous, <a href="javascript:void(0);" onclick="voteLogout()">identifiez-vous</a>.
			</p>
			<script type="text/javascript">

				//jQuery('#vote-form .vote_pour').click(function() {
				function vote() {										
					var params = {
						action: 'vote',
						photo_id: <?php echo $photo['id']; ?>
					}; 

					jQuery.post(
					gSPC.ajaxurl,
					params,
					function( jsonString ) {
						var res = JSON.parse(jsonString);
						if( res.command == 'vote_ok' )
						{
							if( window.onVoteDone != undefined )
							{
								window.onVoteDone( res.photo_votes_count );
							}
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
			};
																		
			</script>
		</div>

		<?php
	}
	else if (!$voterStatus->canVote && isset($photo)) {
		?>
		<div id="spc-popup-already-voted">
			<p>Vous avez déjà voté pour la photo suivante:</p>
			<p style="text-align: center">
				<img
					src="<?php echo $this->getPhotoUrl($photo, 'thumb')?>"
					alt="<?php echo htmlspecialchars($photo['photographer_name'])?>"
					title="<?php echo htmlspecialchars($photo['photo_name'])?>"
					/>
				<br/>
				<span class="photo_name"><?php echo $photo['photo_name'] ?></span>
				<br/>
				<span class="photographer_name"><?php echo $photo['photographer_name'] ?></span>
			</p>
			<?php if (!empty($voterStatus->nextVoteDate)) { ?>
				<p>Vous pourrez de nouveau voter à partir du <?php echo $gSPC->formatDate($voterStatus->nextVoteDate)?></p>
			<?php } ?>
			<p>
				Vous êtes identifié comme <span class="spc-vote-voter-email"><?php echo $voterEmail ?></span>.
				Si ce n'est pas vous, <a href="javascript:void(0);" onclick="voteLogout()">identifiez-vous</a>.
			</p>
		</div>
		<?php
	}
	else {
		echo _e('Unknow error');
	}
	?>

</div>
