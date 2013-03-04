<?php
/*
 * Plugin admin : Overview
 */
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32">
		<br>
	</div>

	<h2><?php _e('Photos contest - Overview',
	self::PLUGIN) ?></h2>

	<h3><?php _e('Information') ?></h3>

	<p>
		<?php
		if ($this->isVoteOpen()) {
			?>
			Le vote est ouvert depuis le <?php echo $this->formatDate($this->getVoteOpenDate()) ?> jusqu'au <?php echo $this->formatDate($this->getVoteCloseDate()) ?>.
		<?php
		}
		else if ($this->isVoteToCome()) {
			?>
			Le vote ouvrira le <?php echo $this->formatDate($this->getVoteOpenDate()) ?>.
			<?php
		}
		else if ($this->isVoteFinished()) {
			?>
			Le vote est fermé depuis le <?php echo $this->formatDate($this->getVoteCloseDate()) ?>.
			<?php
		}
		else {
			?>
			Le vote n´est pas configuré.
			<?php
		}
		?>
	</p>
	<p>
		Il y a <?php echo $this->daoVotes->count(); ?> votes pour <?php echo $this->daoVotes->getVotersCount() ?> votants.
	</p>
	<p>
		Il y a <?php echo $this->daoPhotos->count(); ?> photos.
	</p>


	<form>
		<input type="button" value="Export"
					 onclick="window.location='<?php echo admin_url('admin.php?page=' . SimplePhotosContestAdmin::PAGE_OVERVIEW . '&action=export') ?>'"
					 />
	</form>


	<h3><?php _e('Documentation') ?></h3>

	<p>Le code (shortcode) qu'il faut utiliser dans le contenu d'une page pour afficher la galerie du concours photos est:</p>
	<code><?php echo SimplePhotosContest::SHORT_CODE_PHOTOS_CONTEST ?></code>
	<p>Voir la <a href="<?php echo SimplePhotosContest::$plugin_url . 'docs/index.html' ?>" target="spc-documentation">documentation</a>.</p>
</div>
