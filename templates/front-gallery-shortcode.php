<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script type="text/javascript">

jQuery(document).ready(
	function() {
		var gallery = jQuery('.ad-gallery').adGallery(
		{loader_image: '<?php echo AefPhotosContest::$javascript_url;?>AD_Gallery-1.2.7/loader.gif'} );
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
.entry-content li {
	margin: 0px;
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
					_log('photo '.$row['id']);
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
