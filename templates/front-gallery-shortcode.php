<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script type="text/javascript">

	// AD Gallery
	// http://adgallery.codeplex.com/documentation
	jQuery.noConflict();
	var gallery ;

	jQuery(document).ready( function() {

		jQuery( "#aef-vote-dialog" ).dialog({
			dialogClass : 'wp-dialog',
			modal: true, autoOpen: false, draggable: false ,
			position: { my: "center", at: "center", of: window },
			closeOnEscape : true,
			open: aef_vote_dialog_onOpen,
			buttons: [
				{ text: "Fermer", click: function() { jQuery( this ).dialog( "close" ); } },
				{ text: "testVote", click: function() { initVote(); } }
			]
		});

		jQuery( "#aef-vote-opener" ).click(function() {
			jQuery( "#aef-vote-dialog" ).dialog('open');
		});

		gallery = jQuery('.ad-gallery').adGallery(
		{
			loader_image: '<?php echo AefPhotosContest::$javascript_url; ?>AD_Gallery-1.2.7/loader.gif',
			slideshow: {
				enable: false
			},
			callbacks: {
				init: function() {
					console.log('>>> callback init().');

					jQuery('#gallery .ad-controls').append(jQuery("#aef-vote-button"));
				},
				afterImageVisible: function() {
					console.log('>>> callback afterImageVisible(). this.current_index='+this.current_index);
					console.dir( this );

				}
			}
		});
	
		jQuery('.ad-gallery').on("click", ".ad-image", function() {
			jQuery.fancybox({
				href : jQuery(this).find("img").attr("src"),
				closeBtn: false,
				closeClick : true,
				//openEffect : 'elastic',
				//openSpeed  : 150,
				//closeEffect : 'elastic',
				//closeSpeed  : 150,
				helpers : {
					overlay : null
				}
			});
		});


	});


</script>
<style type="text/css">
	#gallery {
		padding: 30px;
		background: #e1eef5;
	}
	#descriptions {
		position: relative;
		height: 50px;
		background: #EEE;
		margin-top: 10px;
		width: 640px;
		padding: 10px;
		overflow: hidden;
	}
	#descriptions .ad-image-description {
		position: absolute;
	}
	#descriptions .ad-image-description .ad-description-title {
		display: block;
	}
	.ad-gallery .ad-nav .ad-thumbs {
		height: 150px;
	}
	.entry-content li {
		margin: 0px;
	}
	/* if fancybox used, make the image seem clickable */
	.ad-image {
		cursor: pointer;
	}
	#aef-vote-button {
		float: right ;
	}
</style>

<div id="gallery" class="ad-gallery">
	<div class="ad-image-wrapper">
	</div>
	<div class="ad-controls">
	</div>
	<div class="ad-nav">
		<div class="ad-thumbs">
			<ul class="ad-thumb-list">
				<?php
				global $wpdb;
				$sql = 'SELECT * FROM ' . AefPhotosContest::$dbtable_photos . ' order by id asc ';
				$rows = $wpdb->get_results($sql, ARRAY_A);
				$gallery_idx = 0;
				foreach ($rows as $row) {
					?>
					<li>
						<a href="<?php echo $this->getPhotoUrl($row, 'view'); ?>" >
							<img src="<?php echo $this->getPhotoUrl($row,
					'thumb');
					?>"
									 class="image<?php echo $gallery_idx++; ?>"
									 alt="<?php echo htmlspecialchars($row['photographer_name']); ?>"
									 title="<?php echo htmlspecialchars($row['photo_name']); ?>">
						</a>
					</li>
					<?php
				}
				?>
			</ul>
		</div>
	</div>
</div>

<div id="aef-vote-button">
	<button id="aef-vote-opener">vote</button>
</div>

<div id="aef-vote-dialog" title="Votre vote">
	<div id="aef-vote-loader">
		<img src="<?php echo AefPhotosContest::$images_url . 'wpspin-2x.gif' ?>" />
	</div>
	<div id="social-auth-form">
		<form title="Social Authentification">
			<div style="margin-bottom: 3px;"><label><?php _e('For voting you have to identify your self. You can use your preferred social network',
					AefPhotosContest::PLUGIN);
				?>:</label></div>
			<a href="javascript:void(0);" title="Facebook" class="social_auth_facebook"><img alt="Facebook" src="<?php echo AefPhotosContest::$images_url . 'facebook_32.png' ?>" /></a>
			<a href="javascript:void(0);" title="Google" class="social_auth_google"><img alt="Google" src="<?php echo AefPhotosContest::$images_url . 'google_32.png' ?>" /></a>
			<a href="javascript:void(0);" title="Yahoo" class="social_auth_yahoo"><img alt="Yahoo" src="<?php echo AefPhotosContest::$images_url . 'yahoo_32.png' ?>" /></a>
			<input type="hidden" class="social_auth_facebook_client_id" name="client_id" value="<?php echo $aefPC->getOption('facebookClientId'); ?>" />
			<input type="hidden" class="social_auth_facebook" name="redirect_uri" value="<?php echo urlencode(AefPhotosContest::$plugin_url . '/auth/facebook/callback.php'); ?>" />
			<input type="hidden" class="social_auth_google" name="redirect_uri" value="<?php echo( AefPhotosContest::$plugin_url . '/auth/google/connect.php' ); ?>" />
			<input type="hidden" class="social_auth_yahoo" name="redirect_uri" value="<?php echo( AefPhotosContest::$plugin_url . '/auth/yahoo/connect.php' ); ?>" />
		</form>
	</div>
	<div id="vote-form">
		<p><?php _e('You are identified as ') ?><span class="aef-vote-voter-email"></span></p>
		<p><?php _e('You can vote for this picture') ?>
		</p>
	</div>

</div>
