<?php

/*
 * model / AefPhotosContestVote
 */

require_once(__DIR__ . '/AefPhotosContestDao.php');

class AefPhotosContestVotes extends AefPhotosContestModelDao {

	public static function getTableName() {
		return self::DBTABLE_PREFIX . '_votes';
	}

	/**
	 * @param string $email
	 * @return array
	 */
	public function findByEmail($email, AefQueryOptions $queryOptions = null) {
		
		return $this->findBy('voter_email', $email, $queryOptions);
	}

	public function getVotersCount()
	{
		$queryOptions = new AefQueryOptions();
		$queryOptions->groupBy('voter_email');
		return $this->count($queryOptions);
	}

	/**
	 * 
	 * @param string $email
	 * @param int $photoId
	 * @return type
	 */
	public function addVote($email, $photoId) {
		
		return $this->insert( array(
				'voter_email' => $email ,
				'photo_id' => $photoId ,
				'vote_date' => date("Y-m-d H:i:s")
			));
	}

}
