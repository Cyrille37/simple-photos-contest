<?php
/*
 * admin-photos-order page
 * 
 * To order photos (field photo_order).
 */

$queryOptions = new SPCQueryOptions();
$queryOptions->orderBy('photo_order', 'ASC');
$photos = $this->getDaoPhotos()->getAll($queryOptions);

$nbPhotos = count($photos);

?>
<script type="text/javascript">

	jQuery(function($) {

		$("html").bind("ajaxStart", function(){  
			$(this).addClass('busy');  
		}).bind("ajaxStop", function(){  
			$(this).removeClass('busy');  
		});  

		jQuery( "#photos-list" ).sortable({
			start: function (event, ui) {
				ui.item.data("startindex", ui.item.index());
			},
			update: function(event, ui) {

				var i0 = ui.item.data("startindex");
				var i1 = ui.item.index() ;

				var targetId = null ;
				var insert = '' ;
				if( i0<i1)
				{
					// down, insertAfter, select previous
					insert = 'after';
					targetId = ui.item.prev().attr('data-id') ;
				}
				else
				{
					// up, insertBefore, select next one
					insert = 'before';
					targetId = ui.item.next().attr('data-id') ;
				}
				var photoId = ui.item.attr('data-id') ;

				var params = {};
				params.action = 'photo_order' ;
				params.insert = insert ;
				params.photoId = photoId ;
				params.targetId = targetId ;

				jQuery.post(
				'<?php echo admin_url('admin-ajax.php') ?>',
				params,
				function( json ) {
					var res = JSON.parse(json);
					if( res.command == 'order_ok' )
					{
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

			}
		}).disableSelection();

	});

</script>

<style>
	html.busy, html.busy * {  
		cursor: wait !important;  
	}

	#photos-liste {
		padding-left: 0;
		list-style: none;
	}
	.photos-list-item {
		display: inline-block;
	}
	.photos-list-item img {
		width: 80px;
	}
	.photos-list-item div {
		display: inline-block;
		text-align: center;
		margin: 4px;
	}
	.photos-list-item  {
		font-family: sans-serif;
		font-size: 8pt;
		line-height: 7pt;
	}
	.photos-list-item span {
		background-color: #505050;
		color: white;
	}

</style>
<div class="wrap">
	<div id="icon-options-general" class="icon32">
		<br>
	</div>

	<h2><?php _e('Photos contest - Order photos') ?></h2>

	<div >
		<ul id="photos-list">
			<?php
			for ($i = 0; $i < $nbPhotos; $i++) {
				$photo = $photos[$i];
				?>
				<li class="photos-list-item" data-id="<?php echo $photo['id'] ?>">
					<div>
						<img src="<?php echo $this->getPhotoUrl($photo, 'thumb')?>"
								 title="<?php echo $photo['photo_name'] . ' - ' . $photo['photographer_name'] ?>"
								 />
						<br/>
						<span><?php echo $photo['id'] ?></span>
					</div>
				</li>
			<?php } ?>
		</ul>
	</div>

	<?php /*
	<form>
		<input type="button" onclick="window.location='<?php echo admin_url('admin.php?page='.SimplePhotosContestAdmin::PAGE_PHOTOS_ORDER.'&action=force-reorder') ?>'"
					 value="Tri forcé"/>
	</form>
	*/ ?>

</div>
