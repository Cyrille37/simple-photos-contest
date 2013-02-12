<?php
/*
 * 
 */

class AefPhotosContestPhotos extends AefPhotosContestModelDao {

	public static function getTableName() {
		return self::DBTABLE_PREFIX . '_photos';
	}
	
	/**
	 * Get all photos plus a column 'votes' wich contains the votes count by photo.
	 * @param AefQueryOptions $queryOptions
	 * @return array Array of photos and a column votes
	 */
	public function getAllWithVotesCount(AefQueryOptions $queryOptions=null)
	{
		$sql='select count(v.id) as votes, p.* ';
		$sql.=' from wp_aef_spc_photos p';
		$sql.=' left join wp_aef_spc_votes v on (v.photo_id=p.id)';
		$sql.=' group by p.id';

		$this->applyQueryOptions($sql, $queryOptions);

		$rows = $this->wpdb->get_results($sql, ARRAY_A);
		return $rows;


	}
	
}
