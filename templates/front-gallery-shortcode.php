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

	var onVoteDone = function (photo_votes_count)
	{
		var img= jQuery('.spc-vote-opener', '#spc-vote-button') ;
		img.attr('src', '<?php echo SimplePhotosContest::$images_url . 'vote-off-cg41.jpg' ?>' );
		//img.unbind('click',openVoteBox);
		//img.css('cursor','auto');
		//img.css('cursor','pointer');
		b = jQuery('#votes-bulle', '#gallery');
		if( photo_votes_count > 1 ){
			b.html( photo_votes_count + '<br/>votes');
		}
		else{
			b.html( photo_votes_count + '<br/>vote');			
		}
	}

	//jQuery(document).ready( function() {
	jQuery(window).load( function() {

		jQuery("html").bind("ajaxStart", function(){  
			jQuery(this).addClass('busy');  
		}).bind("ajaxStop", function(){  
			jQuery(this).removeClass('busy');  
		});  

		jQuery('#spc-vote-button').hide();
		jQuery('#votes-bulle').hide();

		gallery = jQuery('.pg-gallery').adGallery(
		{
			loader_image: '<?php echo SimplePhotosContest::$javascript_url; ?>PG_Gallery-1.2.7/loader.gif',
			slideshow: {
				enable: false
			},
			callbacks: {
				init: function() {
					// We can do nothing here, afterImageVisible() could be called before init()
				},
				beforeImageVisible: function(new_image, old_image) {
					<?php if ($this->isVoteOpen()) { ?>
					jQuery('#spc-vote-button').hide();
					jQuery('#votes-bulle').hide();
					<?php } ?>
				},
				afterImageVisible : function (){
					<?php if ($this->isVoteOpen()) { ?>
					photo_id = jQuery( '#gallery .image'+this.current_index ).attr('data-photo_id') ;
					loadVoteStatus(photo_id, loadVoteStatusCallback);
					<?php } ?>
				}
			}
		});

		jQuery('.pg-gallery').on("click", ".pg-image", function() {
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
					var title = jQuery('.image'+gallery[0].current_index, '#gallery').attr('data-photo_name') ;
					var alt = jQuery('.image'+gallery[0].current_index , '#gallery').attr('data-photographer_name') ;

					return '<div id="fancybox-title" class="fancybox-title-over" style="display: block; margin-left: 10px; width: 100%; bottom: 10px;"><div id="fancybox-title-over">'
						+'' + (title && title.length ?  title  : '' )
						+' ' + (alt && alt.length ?  alt : '' )
						+'</div></div>';
				}
			});
		});

	});

	function loadVoteStatusCallback(can_vote, photo_votes_count)
	{
		var gal = jQuery('#gallery');
		var imgWrap = jQuery('.pg-image-wrapper', gal);
		var o ;
		var b = jQuery('#spc-vote-button', imgWrap );
		if( b.length == 0 ){
			b = jQuery('#spc-vote-button');
			imgWrap.append(b);
			b.css('position', 'relative');
			b.css('top', (imgWrap.height() - b.height() )+'px' );
			b.css('z-index', jQuery('.pg-next', gal).css('z-index') );
			o = jQuery('.spc-vote-opener',b);
			o.click(openVoteBox);
			o.css('cursor','pointer');
		} else {
			o = jQuery('.spc-vote-opener', b);
		}

		if( can_vote ){
			o.attr('src', '<?php echo SimplePhotosContest::$images_url . 'vote-cg41.jpg' ?>' );
			//o.hover( function () { this.src = 'hover.png'; }, function () { this.src = 'normal.png'; });
		} else {
			o.attr('src', '<?php echo SimplePhotosContest::$images_url . 'vote-off-cg41.jpg' ?>' );
			//o.hover( function () { this.src = 'hover.png'; }, function () { this.src = 'normal.png'; });
		}
		b.show();

		var b = jQuery('#votes-bulle', gal );
		if( b.length == 0 ){
			b = jQuery('#votes-bulle');
			imgWrap.prepend(b);
			b.css('position', 'relative');
			b.css('float', 'right');
			b.css('z-index', jQuery('.pg-next', gal).css('z-index')-1 );
		}
		if( photo_votes_count > 1 ){
			b.html( photo_votes_count + '<br/>votes');
		}
		else{
			b.html( photo_votes_count + '<br/>vote');			
		}
		b.show();

		var im = jQuery('.pg-image', gal);
		b.css('left', '-' + (gal.width() - (im.position().left + im.width() )) +'px');

	}

	function getCurrentPhotoId()
	{
				// Sur certains postes, l'appel à cette callback semble arriver trop tôt
				// car dans la fonction getCurrentPhotoId() "gallery" est "undefined".
		return jQuery( '.image'+gallery[0].current_index, '#gallery').attr('data-photo_id');
	}

	function sharePhoto(socialNetwork)
	{
		var url ;
		switch( socialNetwork)
		{
			case 'Facebook':
				url = 'https://www.facebook.com/dialog/feed?link='+encodeURIComponent(window.location)
					+ '&app_id='+gSPC.facebook_client_id
					+ '&picture='+ encodeURIComponent(jQuery('.image'+gallery[0].current_index).attr('src') )
					+ '&redirect_uri='+encodeURIComponent(window.location)
				;
				break;
			case 'Twitter':
				url = 'http://twitter.com/share'
					+ '?url='+encodeURIComponent(window.location)
					+'&text=Grand concours photo Loir-et-Cher: Votez pour la photo'
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

	html.busy, html.busy * {  
		cursor: wait !important;  
	}

	.pg-gallery * {
		font-family: Arial,​Helvetica,​sans-serif ;
	}

	.pg-gallery .pg-controls {
		margin-top: 4px;
		margin-bottom: 14px;
	}

	#spc-vote-button  {
		margin-right: 0px;
		text-align: right ;
	}

	#votes-bulle {
		width: 48px;
		height: 60px;
		padding-top: 7px;
		line-height: 14px ;
		background-size: contain;
		background-repeat: no-repeat;
		background-image: url('<?php echo SimplePhotosContest::$images_url . 'bulle.png' ?>') ;
		text-align: center;
		font-size: 12px;
		color: white;
	}

	/* if fancybox used, make the image seem clickable */
	.pg-image {
		cursor: pointer;
	}

	/* social sharing buttons */

	.ss-share {
		padding-left: 0;
		list-style: none;
	}

	.ss-share-item {
		display: inline;
		margin-right: 0.25em;
	}

	.ss-share-link {
		/* crude button styles */
		text-decoration: none !important;
		color: #444;
		padding: .01em .5em .05em 30px;
		background-color: #f5f5f5;
		border: 1px solid #ccc;
		border-radius: 2px;
	}

  .ss-share-link:hover, .ss-share-link:active, .ss-share-link:focus {
    color: #891434;
	}

	[class*="ico-"] {
		display: inline-block;
		background-size: 16px 16px;
		background-repeat: no-repeat;
		background-position: 4px center;
	}

	.ico-facebook {
		background-image: url("http://www.facebook.com/favicon.ico"); }

	.ico-twitter {
		background-image: url("http://twitter.com/favicons/favicon.ico"); }

	.ico-google {
		background-image: url("https://ssl.gstatic.com/s2/oz/images/faviconr2.ico"); }

</style>

<div id="gallery" class="pg-gallery">
	<div class="pg-image-wrapper">
	</div>
	<div class="pg-controls">

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
	<div class="pg-nav">
		<div class="pg-thumbs">
			<ul class="pg-thumb-list">
				<?php
				$gallery_idx = 0;
				foreach ($photos as $row) {
					?>
					<li>
						<a href="<?php echo $this->getPhotoUrl($row, 'view'); ?>" >
							<img src="<?php
					echo $this->getPhotoUrl($row, 'thumb');
					?>"
									 class="image<?php echo $gallery_idx++; ?>"
									 alt="<?php echo $this->truncatePhotoName(htmlspecialchars($row['photographer_name'])); ?>"
									 title="<?php echo $this->truncatePhotoName(htmlspecialchars($row['photo_name'])); ?>"
									 data-photographer_name="<?php echo htmlspecialchars($row['photographer_name']); ?>"
									 data-photo_name="<?php echo htmlspecialchars($row['photo_name']); ?>"
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

<div id="spc-vote-button" >
	<img class="spc-vote-opener" src="<?php echo SimplePhotosContest::$images_url . 'vote-cg41.jpg' ?>"/>	
</div>

<div id="votes-bulle" >
	999<br/>votes
</div>
