<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script type="text/javascript">

// AD Gallery
// http://adgallery.codeplex.com/documentation

jQuery(document).ready( function() {
	
	jQuery( "#aef-vote-dialog" ).dialog({
		 dialogClass : 'wp-dialog',
		 modal: true, autoOpen: false, draggable: false ,
		 position: { my: "center", at: "center", of: window },
		 closeOnEscape : true,
		 show: {
			 effect: "bounce", duration: 500
		 },
		 hide: {
			 effect: "blind", duration: 500
		 },
		 buttons: [
			 { text: "Fermer", click: function() { jQuery( this ).dialog( "close" ); } },
			 { text: "testVote", click: function() { testVote(); } }
		 ]
	 });

	jQuery( "#aef-vote-opener" ).click(function() {
	 jQuery( "#aef-vote-dialog" ).dialog('open');
	});

	var gallery = jQuery('.ad-gallery').adGallery(
	{
		loader_image: '<?php echo AefPhotosContest::$javascript_url;?>AD_Gallery-1.2.7/loader.gif',
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

				/*jQuery("#aef-vote").position({
					my:        "left top",
					at:        "right top",
					of:        "#gallery",
					collision: "fit"
				});
				var iz = jQuery("#gallery img").zIndex() ;
				console.log(  );
				jQuery("#aef-vote").css( { zIndex: iz+1 } );*/

			}
		}
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
				$sql = 'SELECT * FROM ' . AefPhotosContest::$dbtable_photos.' order by id asc ';
				$rows = $wpdb->get_results($sql, ARRAY_A);
				$gallery_idx = 0 ;
				foreach ($rows as $row) {
					?>
					<li>
						<a href="<?php echo $this->getPhotoUrl($row, 'view'); ?>" >
							<img src="<?php echo $this->getPhotoUrl($row, 'thumb'); ?>"
									 class="image<?php echo $gallery_idx ++;?>"
									 alt="<?php echo htmlspecialchars($row['photographer_name']);?>"
									 title="<?php echo htmlspecialchars($row['photo_name']) ;?>">
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
  <p>This is an animated dialog which is useful for displaying information. The dialog window can be moved, resized and closed with the 'x' icon.</p>
</div>
