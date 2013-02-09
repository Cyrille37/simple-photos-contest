<?php

/*
 * Class AefQueryOptions
 * Class AefPhotosContestModelDao
 */

/**
 * 
 */
class AefQueryOptions {

	const ORDER_ASC = 'ASC';
	const ORDER_DESC = 'DESC';

	public $orderBy = array();
	public $order = array();

	/**
	 * @param string $fieldName
	 * @return \AefQueryOptions
	 */
	public function orderBy($fieldName, $order = self::ORDER_ASC) {
		$this->orderBy[] = $fieldName;
		$order = strtoupper($order);
		$this->order[] = $order == self::ORDER_ASC ? $order : $order == self::ORDER_DESC ? $order : self::ORDER_ASC ;

		return $this;
	}

}

/**
 * 
 */
abstract class AefPhotosContestModelDao {

	const DBTABLE_PREFIX = 'wp_aef_spc';

	/**
	 * @var wpdb 
	 */
	protected $wpdb;

	public abstract static function getTableName();

	public function __construct(wpdb $wpdb) {
		$this->wpdb = $wpdb;
	}

	/**
	 * 
	 * @param string $fieldName
	 * @param mixed $value
	 * @return array
	 */
	public function findBy($fieldName, $value, AefQueryOptions $queryOptions = null) {

		$values = array();
		if ($value != null) {
			$values[] = $value;
		}

		$sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE ' . $fieldName . '=%s';

		if ($queryOptions != null && ($orderCount = count($queryOptions->order)) > 0) {
			$sql .= ' ORDER BY';
			for ($i = 0; $i < $orderCount; $i++) {
				$sql .= ' ' . $this->wpdb->escape($queryOptions->orderBy[$i]) . ' ' . $queryOptions->order[$i];
			}
		}
		$rows = $this->wpdb->get_results($this->wpdb->prepare($sql, $values), ARRAY_A);
		return $rows;
	}

	public function insert(array $data) {

		_log(__METHOD__);
		$fields = array();
		$placeholders = array();
		$values = array();
		foreach ($data as $k => $v) {
			$fields[] = $k;
			$placeholders[] = '%s';
			$values[] = $v;
		}

		$sql = 'INSERT INTO ' . $this->getTableName() . '(' . implode(',', $fields) . ')'
			. ' VALUES (' . implode(',', $placeholders) . ')';

		$res = $this->wpdb->query($this->wpdb->prepare($sql, $values));
		return $res;
	}

}
