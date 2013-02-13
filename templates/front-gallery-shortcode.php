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
	
					var root = jQuery('.ad-image-wrapper','#gallery');
					var o = jQuery('#aef-vote-button') ;
					root.append(o);

					var o2 = jQuery('.aef-vote-opener','#gallery');
					o2.click(openVoteBox);
					o2.hover(
					function () {
						this.src = '<?php echo AefPhotosContest::$images_url . 'favoris-votez2.png' ?>';
					}, function () {
						this.src = '<?php echo AefPhotosContest::$images_url . 'favoris-votez.png' ?>';
					});

					o.css('position', 'relative');
					o.css('top', (root.height() - o.height() )+'px' );
					o.css('left', (root.width() - 90) +'px');
					o.css('z-index', jQuery('.ad-next').css('z-index') );

				},
				beforeImageVisible: function (){

					var o = jQuery('#aef-vote-button') ;
					o.show();
					var root = jQuery('.ad-image-wrapper','#gallery');
					o.css('top', (root.height() - o.height() )+'px' );
					o.css('left', (root.width() - 90) +'px');

				},
				afterImageVisible: function (){

					var o = jQuery('.ad-info', '#gallery');
					o.html( o.html()+' photos') ;
				}
			}
		});

		jQuery('.ad-gallery').on("click", ".ad-image", function() {

			var href = jQuery(this).find("img").attr("src");
			jQuery.fancybox({
				href : href,
				showCloseButton: true,
				hideOnContentClick: true,
				openEffect : 'none',
				transitionIn: 'fade',
				transitionOut: 'elastic',
				titlePosition  : 'inside',
				titleFormat		: function (title, currentArray, currentIndex, currentOpts) {
					var title = jQuery('.image'+gallery[0].current_index, '#gallery').attr('title') ;
					var alt = jQuery('.image'+gallery[0].current_index , '#gallery').attr('alt') ;

					return '<div id="fancybox-title" class="fancybox-title-over" style="display: block; margin-left: 10px; width: 100%; bottom: 10px;"><div id="fancybox-title-over">'
						+'' + (title && title.length ?  title  : '' )  
						+' ' + (alt && alt.length ?  alt : '' ) 
						+'</div></div>';
				}
			});
		});


	});
	
	function getCurrentPhotoId()
	{
		return jQuery( '.image'+gallery[0].current_index, '#gallery').attr('data-photo_id');
	}

</script>
<style type="text/css">

	/*
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
	*/
	/*
		.entry-content li {
			margin: 0px;
			padding: 0px;
		}
		.ad-thumbs {
			margin: 0px;
			padding: 0px;		
		}
		.ad-thumbs-list {
			margin: 0px;
			padding: 0px;		
		}
		.ad-thumbs-list li {
			margin: 0px;
			padding: 0px;		
		}
	*/

	/* if fancybox used, make the image seem clickable */
	.ad-image {
		cursor: pointer;
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
				$gallery_idx = 0;
				foreach ($photos as $row) {
					?>
					<li>
						<a href="<?php echo $this->getPhotoUrl($row, 'view'); ?>" >
							<img src="<?php echo $this->getPhotoUrl($row,
					'thumb');
					?>"
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

<div id="aef-vote-button" >
	<img class="aef-vote-opener" src="<?php echo AefPhotosContest::$images_url . 'favoris-votez.png' ?>"/>	
</div>
