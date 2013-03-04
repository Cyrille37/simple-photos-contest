<?php
/*
 * admin-configuration-page
 * 
 * Changes plugin's options
 * Rebuild thumbs
 */
?>
<style type="text/css">
</style>
<script type="text/javascript">

	jQuery(document).ready( function() {
		jQuery('#rebuildThumbsSpin').hide();
    
	});

	function rebuildThumbs()
	{
		jQuery('#rebuildThumbsSpin').show();
		setTimeout(function () {
			window.location='admin.php?page=<?php echo SimplePhotosContestAdmin::PAGE_CONFIGURATION; ?>&action=rebuildthumbs';	
		}, 2);
		
	}

	function buildFakePhotos()
	{
		jQuery('#rebuildThumbsSpin').show();
		setTimeout(function () {
			window.location='admin.php?page=<?php echo SimplePhotosContestAdmin::PAGE_CONFIGURATION; ?>&action=buildFakePhotos';	
		}, 2);
		
	}

</script>
<div class="wrap">
	<div id="icon-options-general" class="icon32">
		<br>
	</div>

	<h2><?php _e('Photos contest - Configuration', SimplePhotosContest::PLUGIN); ?></h2>

	<form name="configuration" id="configuration" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<?php wp_nonce_field(SimplePhotosContestAdmin::PAGE_CONFIGURATION, SimplePhotosContestAdmin::PAGE_CONFIGURATION.'_nonce') ?>

		<?php submit_button(); ?>

		<h3><?php _e('Contest',SimplePhotosContest::PLUGIN); ?></h3>

		<table class="form-table">
			<tr valign="top">
				<th align="left">
					<?php _e('Vote open date', SimplePhotosContest::PLUGIN); ?>
				</th>
				<td>
					<input class="datepicker" type="text" size="10" name="voteOpenDate" value="<?php echo $gSPC->formatDate( $gSPC->getOption('voteOpenDate')); ?>" />
					<span class="setting-description">
						<?php _e('This is vote open date formated as ', SimplePhotosContest::PLUGIN); echo SimplePhotosContestAdmin::$dateFormats[$gSPC->getOption('dateFormat')]['label'], '.'; ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Vote close date', SimplePhotosContest::PLUGIN); ?>
				</th>
				<td>
					<input class="datepicker" type="text" size="10" name="voteCloseDate" value="<?php echo $gSPC->formatDate( $gSPC->getOption('voteCloseDate')); ?>" />
					<span class="setting-description">
						<?php _e('This is vote close date formated as ', SimplePhotosContest::PLUGIN); echo SimplePhotosContestAdmin::$dateFormats[$gSPC->getOption('dateFormat')]['label'], '.'; ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Vote frequency', SimplePhotosContest::PLUGIN); ?>
				</th>
				<td>
					<label>
						<input type="radio" name="<?php echo SimplePhotosContest::OPTION_VOTEFREQUENCY ?>" onchange="enableVoteFrequencyHours()"
									 value="<?php echo SimplePhotosContest::VOTE_FREQ_ONEPERCONTEST ?>"
									 <?php checked( $gSPC->getOption(SimplePhotosContest::OPTION_VOTEFREQUENCY), SimplePhotosContest::VOTE_FREQ_ONEPERCONTEST ); ?>
									 />
						<span class="setting-description">
							<?php _e('only one vote by contest.', SimplePhotosContest::PLUGIN) ?>
						</span>
					</label>
					<br/>
					<label>
						<input type="radio" name="<?php echo SimplePhotosContest::OPTION_VOTEFREQUENCY ?>" onchange="enableVoteFrequencyHours()"
									 value="<?php echo SimplePhotosContest::VOTE_FREQ_ONEPERHOURS ?>"
									<?php checked( $gSPC->getOption(SimplePhotosContest::OPTION_VOTEFREQUENCY), SimplePhotosContest::VOTE_FREQ_ONEPERHOURS ); ?>
									 />
						<span class="setting-description">
							<?php _e('only one vote by a given time.', SimplePhotosContest::PLUGIN) ?>
						</span>
					</label>
					<br/>
					<label id="setting-voteFrequencyHours">
						<input type="text" size="3" name="<?php echo SimplePhotosContest::OPTION_VOTEFREQUENCYHOURS ?>" value="<?php echo $gSPC->getOption(SimplePhotosContest::OPTION_VOTEFREQUENCYHOURS); ?>" />
						<span class="setting-description">
							<?php _e('How many hours between votes, when vote frequency is time limited.', SimplePhotosContest::PLUGIN) ?>
						</span>
					</label>
					<script type="text/javascript">
						function enableVoteFrequencyHours()
						{
							var val = jQuery('input[name=voteFrequency]:checked', '#configuration').val() ;
							if( val=='<?php echo SimplePhotosContest::VOTE_FREQ_ONEPERHOURS ?>' )
							{
								jQuery('#setting-voteFrequencyHours input', '#configuration').removeAttr('disabled');
							}
							else
							{
								jQuery('#setting-voteFrequencyHours input', '#configuration').attr('disabled', 'disabled');								
							}
						}
						enableVoteFrequencyHours();
					</script>
				</td>
			</tr>
		</table>
		
		<h3><?php _e('Photos',SimplePhotosContest::PLUGIN); ?></h3>

		<table class="form-table">
			<tr valign="top">
				<th align="left">
					<?php _e('Photo name display length', SimplePhotosContest::PLUGIN); ?>
				</th>
				<td>
					<input class="" type="text" size="4" name="photoDescLengthMax" value="<?php echo $gSPC->getOption('photoDescLengthMax'); ?>" />
					<span class="setting-description">
						<?php _e('This is photo name max length after which name is truncated.', SimplePhotosContest::PLUGIN); ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Thumbnails size', SimplePhotosContest::PLUGIN); ?>
				</th>
				<td>
					<?php _e('width',SimplePhotosContest::PLUGIN) ?><input class="" type="text" size="4" name="thumb_w" value="<?php echo $gSPC->getOption('thumbW'); ?>" />
					<?php _e('height',SimplePhotosContest::PLUGIN) ?><input class="" type="text" size="4" name="thumb_h" value="<?php echo $gSPC->getOption('thumbH'); ?>" />
					<span class="setting-description">
						<?php _e('This is photos thumbnails size.', SimplePhotosContest::PLUGIN); ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Views size', SimplePhotosContest::PLUGIN); ?>
				</th>
				<td>
					<?php _e('width',SimplePhotosContest::PLUGIN) ?><input class="" type="text" size="4" name="view_w" value="<?php echo $gSPC->getOption('viewW'); ?>" />
					<?php _e('height',SimplePhotosContest::PLUGIN) ?><input class="" type="text" size="4" name="view_h" value="<?php echo $gSPC->getOption('viewH'); ?>" />
					<span class="setting-description">
						<?php _e('This is photos views size.', SimplePhotosContest::PLUGIN); ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th>
					&nbsp;
				</th>
				<td>
					<input type="button" onclick="rebuildThumbs();" value="<?php _e('Rebuild thumbs') ?>"/>
					<img id="rebuildThumbsSpin" src="<?php echo plugins_url(SimplePhotosContest::PLUGIN);?>/images/wpspin-2x.gif" style="vertical-align: middle"/>

					<?php /*
					<br/>
					<input type="button" onclick="buildFakePhotos();" value="<?php _e('Build fake photos') ?>"/>
					 */ ?>
				</td>
			</tr>
		</table>

		<h3><?php _e('Advanced options',SimplePhotosContest::PLUGIN); ?></h3>

		<table class="form-table">
			<tr valign="top">
				<th align="left">
					<?php _e('Facebook client id', SimplePhotosContest::PLUGIN); ?>
				</th>
				<td>
					<input type="text" size="20" name="facebook_client_id" value="<?php echo $gSPC->getOption('facebookClientId'); ?>" />
					<span class="setting-description">
						<?php _e('This is the Facebook client id', SimplePhotosContest::PLUGIN);?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Facebook secret key', SimplePhotosContest::PLUGIN); ?>
				</th>
				<td>
					<input type="text" size="20" name="facebook_secret_key" value="<?php echo $gSPC->getOption('facebookSecretKey'); ?>" />
					<span class="setting-description">
						<?php _e('This is the Facebook secret key', SimplePhotosContest::PLUGIN);?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Photos folder', SimplePhotosContest::PLUGIN); ?>
				</th>
				<td>
					<input type="text" size="35" name="photoFolder" value="<?php echo $gSPC->getOption('photoFolder'); ?>" />
					<span class="setting-description"><?php _e('This is the folder path for all contest photos, relative to the wordpress content folder (wp-content).', SimplePhotosContest::PLUGIN); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Date format', SimplePhotosContest::PLUGIN); ?>
				</th>
				<td>
					<select name="dateFormat">
						<?php foreach( SimplePhotosContestAdmin::$dateFormats as $k => $v ){ ?>
						<option value="<?php echo $k ?>"
										<?php if($gSPC->getOption('dateFormat') == $k ) echo 'selected="selected"' ?>
										><?php echo $v['label']?></option>
						<?php } ?>
					</select>
					<span class="setting-description"><?php _e('This is the date format for vote date.', SimplePhotosContest::PLUGIN); ?></span>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>

	</form>
</div>

<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.datepicker').datepicker({
			showOn: "button",
			buttonImage: "<?php echo SimplePhotosContestAdmin::$images_url. 'vcalendar.png';?>",
			buttonImageOnly: true,
			dateFormat : "<?php echo SimplePhotosContestAdmin::$dateFormats[$gSPC->getOption('dateFormat')]['format']; ?>"
		});
	});
</script>
