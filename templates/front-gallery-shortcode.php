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

		jQuery('#aef-vote-button').hide();

		gallery = jQuery('.ad-gallery').adGallery(
		{
			loader_image: '<?php echo AefPhotosContest::$javascript_url; ?>AD_Gallery-1.2.7/loader.gif',
			slideshow: {
				enable: false
			},
			callbacks: {
				init: function() {
	
					<?php if($this->isVoteOpen() ) { ?>

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

					<?php } else { ?>
					<?php } ?>

				},
				beforeImageVisible: function (){

					<?php if($this->isVoteOpen() ) { ?>

					var o = jQuery('#aef-vote-button') ;
					o.show();
					var root = jQuery('.ad-image-wrapper','#gallery');
					o.css('top', (root.height() - o.height() )+'px' );
					o.css('left', (root.width() - 90) +'px');

					<?php } ?>

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

	function sharePhoto(socialNetwork)
	{
		var url ;
		switch( socialNetwork)
		{
			case 'Facebook':
				url = 'https://www.facebook.com/dialog/feed?link='+encodeURIComponent(window.location)
					+ '&app_id='+AefPC.facebook_client_id
					+ '&picture='+ encodeURIComponent(jQuery('.image'+gallery[0].current_index).attr('src') )
					+ '&redirect_uri='+encodeURIComponent(window.location)
				;
				break;
			case 'Twitter':
				url = 'http://twitter.com/share'
					+ '?url='+encodeURIComponent(window.location)
					+'&text=Superbe photo sur '+ encodeURIComponent(AefPC.bloginfo_name)
					+'&hashtags=CG41,Concours,Photo'
				;
				break;
			case 'Google':
				url = 'http://plus.google.com/share'
					+ '?url='+encodeURIComponent(window.location)
					;
				break;
		}

		window.open(url,'Partager','scrollbars=yes,menubar=no,height=420,width=700,resizable=yes,toolbar=no,status=no');
		return false ;
	}
	
</script>
<style type="text/css">
	
.ad-gallery .ad-controls {
	margin-top: 0;
	margin-bottom: 14px;
}

	/* if fancybox used, make the image seem clickable */
	.ad-image {
		cursor: pointer;
	}

/* social sharing buttons */

.ss-share {
  padding-left: 0;
  list-style: none; }

.ss-share-item {
  display: inline;
  margin-right: 0.25em; }

.ss-share-link {
  /* crude button styles */
  text-decoration: none !important;
  color: #444;
  padding: .01em .5em .05em 30px;
  background-color: #f5f5f5;
  border: 1px solid #ccc;
  border-radius: 2px; }
  .ss-share-link:hover, .ss-share-link:active, .ss-share-link:focus {
    color: #891434; }

[class*="ico-"] {
  display: inline-block;
  background-size: 16px 16px;
  background-repeat: no-repeat;
  background-position: 4px center; }

.ico-facebook {
  background-image: url("http://www.facebook.com/favicon.ico"); }

.ico-twitter {
  background-image: url("http://twitter.com/favicons/favicon.ico"); }

.ico-google {
  background-image: url("https://ssl.gstatic.com/s2/oz/images/faviconr2.ico"); }

</style>

<div id="gallery" class="ad-gallery">
	<div class="ad-image-wrapper">
	</div>
	<div class="ad-controls">

			<ul class="ss-share">Partager cette photo sur
				<li class="ss-share-item">
					<a class="ss-share-link ico-facebook"
						 href="javascript:void(0);" onclick="sharePhoto('Facebook')"
						 rel="nofollow"
						 >Facebook</a>
				</li>
				<li class="ss-share-item">
					<a class="ss-share-link ico-twitter"
						 href="javascript:void(0);" onclick="sharePhoto('Twitter')"
						 rel="nofollow"
						 >Twitter</a>
				</li>
				<li class="ss-share-item">
					<a class="ss-share-link ico-google"
						 href="javascript:void(0);" onclick="sharePhoto('Google')"
						 rel="nofollow"
						 >Google+</a>
				</li>
			</ul>
	
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
								title="<?php echo $this->truncatePhotoName(htmlspecialchars($row['photo_name'])); ?>"
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
