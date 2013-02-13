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

	public function getVotersCount() {
		$queryOptions = new AefQueryOptions();
		$queryOptions->groupBy('voter_email');
		return $this->count($queryOptions);
	}

	public function getVotesCountByPhotos(array $photo_ids) {

		$sql = 'select photo_id, count(id) from ' . $this->getTableName();
		$sql.= ' where photo_id IN (';
		$sql.= implode(',', array_fill ( 0 , count($photo_ids) , '%s'));
		$sql.=') group by photo_id';

		$rows = $this->wpdb->get_results($this->wpdb->prepare($sql, $photo_ids), ARRAY_A);

		return $rows;
	}
	
	/**
	 * Get all vote plus columns wich contains some photo data.
	 * @param AefQueryOptions $queryOptions
	 * @return array Array of votes plus some photo's columns
	 */
	public function getAllWithPhotoData(AefQueryOptions $queryOptions=null)
	{
		$sql='select v.*, p.photo_name, p.photographer_name, p.photo_mime_type ';
		$sql.=' from wp_aef_spc_votes v';
		$sql.=' left join wp_aef_spc_photos p on (p.id=v.photo_id)';
		//$sql.=' group by p.id';

		$this->applyQueryOptions($sql, $queryOptions);

		$rows = $this->wpdb->get_results($sql, ARRAY_A);
		return $rows;


	}

	/**
	 * 
	 * @param string $email
	 * @param int $photoId
	 * @return type
	 */
	public function addVote($email, $photoId) {

		return $this->insert(array(
				'voter_email' => $email,
				'photo_id' => $photoId,
				'vote_date' => date("Y-m-d H:i:s")
			));
	}

}
