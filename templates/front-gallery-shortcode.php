<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script type="text/javascript">

jQuery(document).ready(
	function() {
		var gallery = jQuery('.ad-gallery').adGallery();
    
	}
);
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
				$photos_folder_url = '/wp-content/'. $this->getOption('photoFolder');
				$sql = 'SELECT * FROM ' . AefPhotosContest::$dbtable_photos;
				$rows = $wpdb->get_results($sql, ARRAY_A);
				
				$gallery_idx = 0 ;
				foreach ($rows as $row) {
					$photo_url_prefix = $photos_folder_url.'/'.$row['id'] ;
					$ext=explode('/', $row['photo_mime_type']) ;
					$ext = $ext[1];
					?>
					<li>
						<a href="<?php  echo $photo_url_prefix,'-view.',$ext; ?>">
							<img src="<?php echo $photo_url_prefix,'-thumb.',$ext; ?>" class="image<?php echo $gallery_idx ++;?>">
						</a>
					</li>
					<?php
				}
				?>
				</li>
			</ul>
		</div>
	</div>
</div>
