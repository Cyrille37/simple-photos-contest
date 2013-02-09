<?php

/*
 * 
 */

class AefPhotosContestVoterStatus {

	public $canVote;
	public $nextVoteDate;
	public $lastVotedDate;
	public $lastVotedPhotoId;

	/**
	 * @param AefPhotosContest $aefPC
	 * @param string $email
	 * @return \AefPhotosContestVoterStatus
	 */
	public static function getVoterStatus(AefPhotosContest $aefPC, $email) {

		$voteStatus = new AefPhotosContestVoterStatus();

		if( empty($email) )
		{
			$voteStatus->canVote = true;
			return $voteStatus ;
		}

		$votesDao = $aefPC->getDaoVotes();

		$queryOptions = new AefQueryOptions();
		$queryOptions->orderBy('vote_date', AefQueryOptions::ORDER_DESC);
		$votes = $votesDao->findByEmail($email, $queryOptions);
_log('$votes[0][vote_date]: '.$votes[0]['vote_date']);
		if (count($votes) == 0) {
			$voteStatus->canVote = true;
			return $voteStatus;
		}

		switch ($aefPC->getOption(AefPhotosContest::OPTION_VOTEFREQUENCY)) {

			case AefPhotosContest::VOTE_FREQ_ONEPERCONTEST:
				
				$voteStatus->canVote = false;
				$voteStatus->lastVotedDate = $votes[0]['vote_date'] ;
				$voteStatus->lastVotedPhotoId = $votes[0]['photo_id'] ;
				break;

			case AefPhotosContest::VOTE_FREQ_ONEPERHOURS:

				$voteStatus->lastVotedDate = $votes[0]['vote_date'] ;
				$voteStatus->lastVotedPhotoId = $votes[0]['photo_id'] ;

				$freqHours = $aefPC->getOption(AefPhotosContest::OPTION_VOTEFREQUENCYHOURS);

				$nowTime = time();
				$lastVotedTime = new DateTime($voteStatus->lastVotedDate);
				// $dt->getTimestamp(); //PHP 5.3
				// $dt->format('U'); // PHP < 5.3
				$lastVotedTime = $lastVotedTime->format('U');
				$hoursDiff = $nowTime/60/60 - $lastVotedTime/60/60 ;
				if( $hoursDiff < $freqHours )
				{
					$voteStatus->canVote = false;
				}
				else
				{
					$voteStatus->canVote = true;
				}

				$nextVoteTime = $lastVotedTime + $freqHours*60*60 ;
				$voteStatus->nextVoteDate = date('Y-m-d H:i:s', $nextVoteTime) ;

				break;
			default:
				throw new Exception('hum hum...');
		}
		return $voteStatus;
	}

}
