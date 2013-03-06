<?php

/*
 * model / SPCVotesDao
 */

require_once(__DIR__ . '/SPCModelDao.php');

class SPCVotesDao extends SPCModelDao {

	public static function getTableName() {
		return self::DBTABLE_PREFIX . '_votes';
	}

	/**
	 * @param string $email
	 * @param int $photoId (optional)
	 * @param SPCQueryOptions $queryOptions (optional)
	 * @return array
	 */
	public function findByEmail($email, $photoId = null, SPCQueryOptions $queryOptions = null) {

		//return $this->findBy('voter_email', $email, $queryOptions);

		$values = array($email);
		$sql = 'SELECT * FROM ' . self::getTableName() . ' WHERE voter_email=%s';

		if (!empty($photoId)) {
			$sql.=' AND photo_id=%d';
			$values[] = $photoId;
		}
		$this->applyQueryOptions($sql, $queryOptions);

		$rows = $this->wpdb->get_results($this->wpdb->prepare($sql, $values), ARRAY_A);
		return $rows;
	}

	public function getVotersCount() {

		$sql = 'select count( distinct voter_email ) from ' . self::getTableName();
		$count = $this->wpdb->get_var($sql);
		return $count;
	}

	public function getVotesCountByVoters() {

		$sql = 'select voter_email, count(id) as votes from ' . self::getTableName();
		$sql.=' group by voter_email';

		$rows = $this->wpdb->get_results($sql, ARRAY_A);
		return $rows;
	}

	public function getVotesCountByPhotos($photo_ids = null) {

		$sql = 'select photo_id, count(id) from ' . self::getTableName();
		if (is_array($photo_ids)) {
			$sql.= ' where photo_id IN (';
			$sql.= implode(',', array_fill(0, count($photo_ids), '%s'));
			$sql.=')';
		}

		$queryOptions = new SPCQueryOptions();
		$queryOptions->groupBy('photo_id');
		$this->applyQueryOptions($sql, $queryOptions);

		$rows = $this->wpdb->get_results($this->wpdb->prepare($sql, $photo_ids), ARRAY_A);
		return $rows;
	}

	public function getVotesCountByPhoto($photo_id) {

		$sql = 'select count(id) from ' . self::getTableName();
		$sql.= ' where photo_id = %s';

		$count = $this->wpdb->get_var($this->wpdb->prepare($sql, $photo_id));

		return $count;
	}

	/**
	 * Get all vote plus columns wich contains some photo data.
	 * @param SPCQueryOptions $queryOptions
	 * @return array Array of votes plus some photo's columns
	 */
	public function getAllWithPhotoData(SPCQueryOptions $queryOptions = null) {

		$sql = 'select v.*, p.photo_name, p.photographer_name, p.photo_mime_type ';
		$sql.=' from ' . self::getTableName() . ' v';
		$sql.=' left join ' . SPCPhotosDao::getTableName() . ' p on (p.id=v.photo_id)';
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

		$nowDT = new DateTime();
		return $this->insert(array(
				'voter_email' => $email,
				'voter_ip' => (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'null'),
				'photo_id' => $photoId,
				'vote_date' => $nowDT->format('Y-m-d H:i:s')
			));
	}

}
