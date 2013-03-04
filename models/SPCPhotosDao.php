<?php

/*
 * 
 */

class SPCPhotosDao extends SPCModelDao {

	public static function getTableName() {
		return self::DBTABLE_PREFIX . '_photos';
	}

	/**
	 * Get all photos plus a column 'votes' wich contains the votes count by photo.
	 * Note: add a group by to $queryOptions.
	 * @param SPCQueryOptions $queryOptions
	 * @return array Array of photos and a column votes
	 */
	public function getAllWithVotesCount(SPCQueryOptions $queryOptions = null) {

		$sql = 'select count(v.id) as votes, p.* ';
		$sql.=' from '.self::getTableName().' p';
		$sql.=' left join '.SPCVotesDao::getTableName().' v on (v.photo_id=p.id)';

		//$sql.=' group by p.id';
		$queryOptions->groupBy('p.id');

		$this->applyQueryOptions($sql, $queryOptions);

		$rows = $this->wpdb->get_results($sql, ARRAY_A);
		return $rows;
	}

	/**
	 * @param int $id
	 * @return array ['votes'=>n, 'voters'=>n]
	 */
	public function getVotesAndVotersCounts($id) {

		$sql = 'SELECT count(id) as votes, count(distinct voter_email) as voters';
		$sql.=' FROM '.SPCVotesDao::getTableName();
		$sql.= ' WHERE photo_id=' . intval($id);

		$row = $this->wpdb->get_row($sql, ARRAY_A);

		return array($row['votes'], $row['voters']);
	}

	public function orderInsertBefore($srcId, $destId) {

		$destPhotoOrder = $this->getVar($destId, 'photo_order');

		$sql = 'UPDATE ' . $this->getTableName()
			. ' SET photo_order = photo_order + 1'
			. ' WHERE photo_order >= (SELECT * FROM (SELECT photo_order FROM '.self::getTableName().' WHERE id = %s) p1 )'
			. ' AND photo_order < (SELECT * FROM (SELECT photo_order FROM '.self::getTableName().' WHERE id = %s) p2 )';

		$this->wpdb->query($this->wpdb->prepare($sql, array($destId, $srcId)));

		$this->updateById($srcId, array('photo_order' => $destPhotoOrder));
	}

	public function orderInsertAfter($srcId, $destId) {

		$destPhotoOrder = $this->getVar($destId, 'photo_order');

		$sql = 'UPDATE ' . $this->getTableName()
			. ' SET photo_order = photo_order - 1'
			. ' WHERE photo_order > (SELECT * FROM (SELECT photo_order FROM '.self::getTableName().' WHERE id = %s) p1 )'
			. ' AND photo_order <= (SELECT * FROM (SELECT photo_order FROM '.self::getTableName().' WHERE id = %s) p2 )';
		$this->wpdb->query($this->wpdb->prepare($sql, array($srcId, $destId)));

		$this->updateById($srcId, array('photo_order' => $destPhotoOrder));
	}

	public function getPhotoOrderMax()
	{
		return $this->wpdb->get_var('SELECT max(photo_order) from '.self::getTableName() );
	}
}
