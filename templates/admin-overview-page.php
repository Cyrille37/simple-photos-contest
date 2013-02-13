<?php
/*
 * Plugin admin : Overview
 */
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32">
		<br>
	</div>
	
	<h2><?php _e('Concours photo - Tableau de bord')?></h2>
	
	<p>
		<?php
		if ($this->isVoteOpen()) {
			?>
			Le vote est ouvert depuis le <?php echo $this->formatDate($this->getVoteOpenDate()) ?> jusqu'au <?php echo $this->formatDate($this->getVoteCloseDate()) ?>.
		<?php }
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
</div>
