<?php

/*
 * 
 */

class SPCVoterStatus {

	public $canVote;
	public $nextVoteDate;
	public $lastVotedDate;
	public $lastVotedPhotoId;

	/**
	 * @param SimplePhotosContest $spc
	 * @param string $email
	 * @return \SPCVoterStatus
	 */
	public static function getVoterStatus(SimplePhotosContest $spc, $email, $photoId = null) {

		$voteStatus = new SPCVoterStatus();

		if (empty($email)) {
			$voteStatus->canVote = true;
			return $voteStatus;
		}

		$votesDao = $spc->getDaoVotes();

		$queryOptions = new SPCQueryOptions();
		$queryOptions->orderBy('vote_date', SPCQueryOptions::ORDER_DESC);
		$votes = $votesDao->findByEmail($email, $photoId, $queryOptions);

		if (count($votes) == 0) {
			$voteStatus->canVote = true;
			return $voteStatus;
		}

		//$tz = new DateTimeZone('Europe/Paris');
		$gmt_offset = get_option('gmt_offset');

		switch ($spc->getOption(SimplePhotosContest::OPTION_VOTEFREQUENCY)) {

			case SimplePhotosContest::VOTE_FREQ_ONEPERCONTEST:

				$voteStatus->canVote = false;
				$voteStatus->lastVotedPhotoId = $votes[0]['photo_id'];

				$dt = new DateTime($votes[0]['vote_date']);
				$dt->add(new DateInterval('PT' . $gmt_offset . 'H'));
				$voteStatus->lastVotedDate = $dt->format('Y-m-d H:i:s');

				$voteStatus->nextVoteDate = null;

				break;

			case SimplePhotosContest::VOTE_FREQ_ONEPERHOURS:

				//$voteStatus->lastVotedDate = $votes[0]['vote_date'] ;
				//$voteStatus->lastVotedPhotoId = $votes[0]['photo_id'] ;

				$voteStatus->lastVotedPhotoId = $votes[0]['photo_id'];

				$dt = new DateTime($votes[0]['vote_date']);
				$dt->add(new DateInterval('PT' . $gmt_offset . 'H'));
				$voteStatus->lastVotedDate = $dt->format('Y-m-d H:i:s');

				$dt = new DateTime($votes[0]['vote_date']);
				$freqHours = $spc->getOption(SimplePhotosContest::OPTION_VOTEFREQUENCYHOURS);
				$dt->add(new DateInterval('PT' . $freqHours . 'H'));

				if (new DateTime() < $dt) {
					$voteStatus->canVote = false;
				}
				else {
					$voteStatus->canVote = true;
				}

				$dt->add(new DateInterval('PT' . $gmt_offset . 'H'));
				$voteStatus->nextVoteDate = $dt->format('Y-m-d H:i:s');

				break;
			default:
				throw new Exception('hum hum...');
		}
		return $voteStatus;
	}

}
