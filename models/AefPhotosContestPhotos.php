<?php
/*
 * 
 */

class AefPhotosContestPhotos extends AefPhotosContestModelDao {

	public static function getTableName() {
		return self::DBTABLE_PREFIX . '_photos';
	}
}
