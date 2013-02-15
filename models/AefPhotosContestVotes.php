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
	 * @param int $photoId (optional)
	 * @param AefQueryOptions $queryOptions (optional)
	 * @return array
	 */
	public function findByEmail($email, $photoId = null, AefQueryOptions $queryOptions = null) {

		//return $this->findBy('voter_email', $email, $queryOptions);

		$values = array($email);
		$sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE voter_email=%s';

		if (!empty($photoId)) {
			$sql.=' AND photo_id=%d';
			$values[] = $photoId;
		}
		$this->applyQueryOptions($sql, $queryOptions);

		$rows = $this->wpdb->get_results($this->wpdb->prepare($sql, $values), ARRAY_A);
		return $rows;
	}

	public function getVotersCount() {

		$queryOptions = new AefQueryOptions();
		$queryOptions->groupBy('voter_email');
		return $this->count($queryOptions);
	}

	public function getVotesCountByVoters() {

		$sql = 'select voter_email, count(id) as votes from ' . $this->getTableName();
		$sql.=' group by voter_email';

		$rows = $this->wpdb->get_results($sql, ARRAY_A);
		return $rows;
	}

	public function getVotesCountByPhotos($photo_ids = null) {

		$sql = 'select photo_id, count(id) from ' . $this->getTableName();
		if (is_array($photo_ids)) {
			$sql.= ' where photo_id IN (';
			$sql.= implode(',', array_fill(0, count($photo_ids), '%s'));
			$sql.=')';
		}

		$queryOptions = new AefQueryOptions();
		$queryOptions->groupBy('photo_id');
		$this->applyQueryOptions($sql, $queryOptions);

		$rows = $this->wpdb->get_results($this->wpdb->prepare($sql, $photo_ids), ARRAY_A);
		return $rows;
	}

	public function getVotesCountByPhoto($photo_id) {
		$sql = 'select count(id) from ' . $this->getTableName();
		$sql.= ' where photo_id = %s';

		$count = $this->wpdb->get_var($this->wpdb->prepare($sql, $photo_id));

		return $count;
	}

	/**
	 * Get all vote plus columns wich contains some photo data.
	 * @param AefQueryOptions $queryOptions
	 * @return array Array of votes plus some photo's columns
	 */
	public function getAllWithPhotoData(AefQueryOptions $queryOptions = null) {
		$sql = 'select v.*, p.photo_name, p.photographer_name, p.photo_mime_type ';
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
