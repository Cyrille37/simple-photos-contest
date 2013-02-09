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
	var o = jQuery('#rebuildThumbsSpin');
	//var s = o.attr("src");
	//o.css('display', ''); 
	//o.attr("src", s+"?"+new Date().getTime());
	o.show();
	setTimeout(rebuildThumbs_callback, 4);

}
function rebuildThumbs_callback()
{
	window.location='admin.php?page=<?php echo AefPhotosContestAdmin::PAGE_CONFIGURATION;?>&action=rebuildthumbs';	
}
</script>
<div class="wrap">
	<div id="icon-options-general" class="icon32">
		<br>
	</div>

	<h2><?php _e('Photos contest configuration', AefPhotosContest::PLUGIN); ?></h2>

	<form name="configuration" id="configuration" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<?php wp_nonce_field(AefPhotosContestAdmin::PAGE_CONFIGURATION, AefPhotosContestAdmin::PAGE_CONFIGURATION.'_nonce') ?>

		<?php submit_button(); ?>

		<h3><?php _e('Concours',AefPhotosContest::PLUGIN); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th align="left">
					<?php _e('Vote open date', AefPhotosContest::PLUGIN); ?>
				</th>
				<td>
					<input class="datepicker" type="text" size="10" name="voteOpenDate" value="<?php echo $aefPC->formatDate( $aefPC->getOption('voteOpenDate')); ?>" />
					<span class="setting-description">
						<?php _e('This is vote open date formated as ', AefPhotosContest::PLUGIN); echo AefPhotosContestAdmin::$dateFormats[$aefPC->getOption('dateFormat')]['label'], '.'; ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Vote close date', AefPhotosContest::PLUGIN); ?>
				</th>
				<td>
					<input class="datepicker" type="text" size="10" name="voteCloseDate" value="<?php echo $aefPC->formatDate( $aefPC->getOption('voteCloseDate')); ?>" />
					<span class="setting-description">
						<?php _e('This is vote close date formated as ', AefPhotosContest::PLUGIN); echo AefPhotosContestAdmin::$dateFormats[$aefPC->getOption('dateFormat')]['label'], '.'; ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Vote frequency', AefPhotosContest::PLUGIN); ?>
				</th>
				<td>
					<label>
						<input type="radio" name="<?php echo AefPhotosContest::OPTION_VOTEFREQUENCY ?>" onchange="enableVoteFrequencyHours()"
									 value="<?php echo AefPhotosContest::VOTE_FREQ_ONEPERCONTEST ?>"
									 <?php checked( $aefPC->getOption(AefPhotosContest::OPTION_VOTEFREQUENCY), AefPhotosContest::VOTE_FREQ_ONEPERCONTEST ); ?>
									 />
						<span class="setting-description">
							<?php _e('only one vote by contest.', AefPhotosContest::PLUGIN) ?>
						</span>
					</label>
					<br/>
					<label>
						<input type="radio" name="<?php echo AefPhotosContest::OPTION_VOTEFREQUENCY ?>" onchange="enableVoteFrequencyHours()"
									 value="<?php echo AefPhotosContest::VOTE_FREQ_ONEPERHOURS ?>"
									<?php checked( $aefPC->getOption(AefPhotosContest::OPTION_VOTEFREQUENCY), AefPhotosContest::VOTE_FREQ_ONEPERHOURS ); ?>
									 />
						<span class="setting-description">
							<?php _e('only one vote by a given time.', AefPhotosContest::PLUGIN) ?>
						</span>
					</label>
					<br/>
					<label id="setting-voteFrequencyHours">
						<input type="text" size="3" name="<?php echo AefPhotosContest::OPTION_VOTEFREQUENCYHOURS ?>" value="<?php echo $aefPC->getOption(AefPhotosContest::OPTION_VOTEFREQUENCYHOURS); ?>" />
						<span class="setting-description">
							<?php _e('How many hours between votes, when vote frequency is limited by hours.', AefPhotosContest::PLUGIN) ?>
						</span>
					</label>
					<script>
						function enableVoteFrequencyHours()
						{
							var val = jQuery('input[name=voteFrequency]:checked', '#configuration').val() ;
							if( val=='<?php echo AefPhotosContest::VOTE_FREQ_ONEPERHOURS ?>' )
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
		
		<h3><?php _e('Photos',AefPhotosContest::PLUGIN); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th align="left">
					<?php _e('Thumbnails size', AefPhotosContest::PLUGIN); ?>
				</th>
				<td>
					<?php _e('width',AefPhotosContest::PLUGIN) ?><input class="" type="text" size="4" name="thumb_w" value="<?php echo $aefPC->getOption('thumbW'); ?>" />
					<?php _e('height',AefPhotosContest::PLUGIN) ?><input class="" type="text" size="4" name="thumb_h" value="<?php echo $aefPC->getOption('thumbH'); ?>" />
					<span class="setting-description">
						<?php _e('This is photos thumbnail size.', AefPhotosContest::PLUGIN); ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Views size', AefPhotosContest::PLUGIN); ?>
				</th>
				<td>
					<?php _e('width',AefPhotosContest::PLUGIN) ?><input class="" type="text" size="4" name="view_w" value="<?php echo $aefPC->getOption('viewW'); ?>" />
					<?php _e('height',AefPhotosContest::PLUGIN) ?><input class="" type="text" size="4" name="view_h" value="<?php echo $aefPC->getOption('viewH'); ?>" />
					<span class="setting-description">
						<?php _e('This is photo view size.', AefPhotosContest::PLUGIN); ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th>
					&nbsp;
				</th>
				<td>
					<input type="button" onclick="rebuildThumbs();" value="<?php _e('Rebuild thumbs') ?>"/>
					<img id="rebuildThumbsSpin" src="<?php echo plugins_url(AefPhotosContest::PLUGIN);?>/images/wpspin-2x.gif" style="vertical-align: middle"/>
				</td>
			</tr>
		</table>

		<h3><?php _e('Advanced options',AefPhotosContest::PLUGIN); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th align="left">
					<?php _e('Facebook client id', AefPhotosContest::PLUGIN); ?>
				</th>
				<td>
					<input type="text" size="20" name="facebook_client_id" value="<?php echo $aefPC->getOption('facebookClientId'); ?>" />
					<span class="setting-description">
						<?php _e('This is the Facebook client id', AefPhotosContest::PLUGIN);?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Facebook secret key', AefPhotosContest::PLUGIN); ?>
				</th>
				<td>
					<input type="text" size="20" name="facebook_secret_key" value="<?php echo $aefPC->getOption('facebookSecretKey'); ?>" />
					<span class="setting-description">
						<?php _e('This is the Facebook secret key', AefPhotosContest::PLUGIN);?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Photos folder', AefPhotosContest::PLUGIN); ?>
				</th>
				<td>
					<input type="text" size="35" name="photoFolder" value="<?php echo $aefPC->getOption('photoFolder'); ?>" />
					<span class="setting-description"><?php _e('This is the folder path for all contest photos, relative to the wordpress content folder (wp-content).', AefPhotosContest::PLUGIN); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th align="left">
					<?php _e('Date format', AefPhotosContest::PLUGIN); ?>
				</th>
				<td>
					<select name="dateFormat">
						<?php foreach( AefPhotosContestAdmin::$dateFormats as $k => $v ){ ?>
						<option value="<?php echo $k ?>"
										<?php if($aefPC->getOption('dateFormat') == $k ) echo 'selected="selected"' ?>
										><?php echo $v['label']?></option>
						<?php } ?>
					</select>
					<span class="setting-description"><?php _e('This is the date format for vote date.', AefPhotosContest::PLUGIN); ?></span>
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
			buttonImage: "<?php echo AefPhotosContestAdmin::$images_url. 'vcalendar.png';?>",
			buttonImageOnly: true,
			dateFormat : "<?php echo AefPhotosContestAdmin::$dateFormats[$aefPC->getOption('dateFormat')]['format']; ?>"
		});
	});
</script>
