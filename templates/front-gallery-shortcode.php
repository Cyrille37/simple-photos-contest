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
				}
			}
		});

		jQuery('.ad-gallery').on("click", ".ad-image", function() {

			var href = jQuery(this).find("img").attr("src");
			//console.dir( jQuery(this) );
			jQuery.fancybox({
				href : href,
				showCloseButton: true,
				hideOnContentClick: true,
				openEffect : 'none',
				titleShow: true,
				titlePosition  : 'inside',
				titleFormat		: function (title, currentArray, currentIndex, currentOpts) {
					var title = jQuery('#gallery .image'+gallery[0].current_index).attr('title') ;
					var alt = jQuery('#gallery .image'+gallery[0].current_index).attr('alt') ;
					return '<div id="fancybox-title" class="fancybox-title-over" style="display: block; margin-left: 10px; width: 100%; bottom: 10px;"><div id="fancybox-title-over">'
						+'' + (title && title.length ?  title  : '' )  
						+' ' + (alt && alt.length ?  alt : '' ) 
						+'</div></div>';
				},
				//openEffect : 'elastic',
				//openSpeed  : 150,
				closeEffect : 'none',
				//closeEffect : 'elastic',
				//closeSpeed  : 150,
				helpers : {
					overlay : null
				}
			});
		});

		jQuery("#aef-vote-opener").click(openVoteBox);

	});
	
	function getCurrentPhotoId()
	{
		return jQuery( '#gallery .image'+gallery[0].current_index).attr('data-photo_id');
	}

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
							<img src="<?php echo $this->getPhotoUrl($row, 'thumb'); ?>"
									 class="image<?php echo $gallery_idx++; ?>"
									 alt="<?php echo htmlspecialchars($row['photographer_name']); ?>"
									 title="<?php echo htmlspecialchars($row['photo_name']); ?>"
									 data-photo_id="<?php echo htmlspecialchars($row['id']); ?>"
									 />
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
	<span id="aef-vote-opener" >voter</span>
</div>
