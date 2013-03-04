<?php

/*
 * Class SPCQueryOptions
 * Class SPCModelDao
 */

/**
 * 
 */
class SPCQueryOptions {

	const ORDER_ASC = 'ASC';
	const ORDER_DESC = 'DESC';

	public $orderBy = array();
	public $order = array();
	public $limit;
	public $limit_offset;
	public $groupBy;

	/**
	 * @param string $fieldName
	 * @return \SPCQueryOptions fluent interface
	 */
	public function orderBy($fieldName, $order = self::ORDER_ASC) {
		$this->orderBy[] = $fieldName;
		$order = strtoupper($order);
		$this->order[] = $order == self::ORDER_ASC ? $order : $order == self::ORDER_DESC ? $order : self::ORDER_ASC ;
		return $this;
	}

	/**
	 * @param int $limit
	 * @param int $offset
	 * @return \SPCQueryOptions fluent interface
	 */
	public function limit($limit, $offset = 0) {
		$this->limit = intval($limit);
		$this->limit_offset = intval($offset);
		return $this;
	}

	/**
	 * @param string $fieldName
	 * @return \SPCQueryOptions fluent interface
	 */
	public function groupBy($fieldName) {
		$this->groupBy = $fieldName;
		return $this;
	}

}

/**
 * 
 */
abstract class SPCModelDao {

	const DBTABLE_PREFIX = 'wp_spc';

	/**
	 * @var wpdb 
	 */
	protected $wpdb;

	/**
	 * @var int
	 */
	protected $lastInsertId;

	public abstract static function getTableName();

	public function __construct(wpdb $wpdb) {
		$this->wpdb = $wpdb;
	}

	/**
	 * @return int
	 */
	public function count(SPCQueryOptions $queryOptions = null) {

		$sql = 'SELECT COUNT(*) FROM ' . $this->getTableName();

		$this->applyQueryOptions($sql, $queryOptions);

		$count = $this->wpdb->get_var($sql);
		return $count;
	}

	/**
	 * 
	 * @param string $fieldName
	 * @param mixed $value
	 * @return array
	 */
	public function countBy($fieldName, $value) {

		$sql = 'SELECT COUNT(*) FROM ' . $this->getTableName() . ' WHERE ' . $fieldName . '=%s';

		$count = $this->wpdb->get_var($this->wpdb->prepare($sql, $value), ARRAY_A);
		return $count;
	}

	/**
	 * @param int $id
	 * @return array
	 */
	public function getById($id) {

		$row = $this->wpdb->get_row('SELECT * FROM ' . $this->getTableName() . ' WHERE id = ' . intval($id), ARRAY_A);
		return $row;
	}

	public function getVar($id, $fieldName) {
		$var = $this->wpdb->get_var('SELECT ' . $this->wpdb->escape($fieldName) . ' FROM ' . $this->getTableName() . ' WHERE id = ' . intval($id));
		return $var;
	}

	protected function applyQueryOptions(&$sql, SPCQueryOptions $queryOptions = null) {

		if ($queryOptions == null)
			return;

		if (isset($queryOptions->groupBy)) {
			$sql.=' GROUP BY ' . $this->wpdb->escape($queryOptions->groupBy);
		}

		if (($orderCount = count($queryOptions->order)) > 0) {
			$sql .= ' ORDER BY';
			for ($i = 0; $i < $orderCount; $i++) {
				$sql .= ' ' . $this->wpdb->escape($queryOptions->orderBy[$i]) . ' ' . $queryOptions->order[$i];
			}
		}

		if (!empty($queryOptions->limit)) {
			$sql.=' LIMIT ' . intval($queryOptions->limit);
			if (!empty($queryOptions->limit_offset)) {
				$sql.=' OFFSET ' . intval($queryOptions->limit_offset);
			}
		}
	}

	/**
	 * @param SPCQueryOptions $queryOptions
	 * @return array
	 */
	public function getAll(SPCQueryOptions $queryOptions = null) {

		$sql = 'SELECT * FROM ' . $this->getTableName();

		$this->applyQueryOptions($sql, $queryOptions);

		$rows = $this->wpdb->get_results($sql, ARRAY_A);
		return $rows;
	}

	/**
	 * 
	 * @param string $fieldName
	 * @param mixed $value
	 * @return array
	 */
	public function findBy($fieldName, $value, SPCQueryOptions $queryOptions = null) {

		$values = array();
		if ($value != null) {
			$values[] = $value;
		}

		$sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE ' . $fieldName . '=%s';

		$this->applyQueryOptions($sql, $queryOptions);

		$rows = $this->wpdb->get_results($this->wpdb->prepare($sql, $values), ARRAY_A);
		return $rows;
	}

	public function getLastInsertId() {
		return $this->lastInsertId;
	}

	public function insert(array $data) {

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
		if ($res !== false) {
			$this->lastInsertId = $this->wpdb->insert_id;
		}
		return $res;
	}

	public function updateById($id, array $data) {

		$fields = array();
		$values = array();
		foreach ($data as $k => $v) {
			if ($k == 'id')
				continue;
			$fields[] = $this->wpdb->escape($k) . '=%s';
			$values[] = $v;
		}
		$values[] = $id;

		$sql = 'UPDATE ' . $this->getTableName() . ' SET ' . implode(',', $fields) . ' WHERE id=%d';
		$res = $this->wpdb->query($this->wpdb->prepare($sql, $values));

		return $res;
	}

	/**
	 * 
	 * @param int $id
	 * @return type
	 */
	public function delete($id) {

		$res = $this->wpdb->query('DELETE FROM ' . $this->getTableName() . ' WHERE id = ' . intval($id));
		return $res;
	}

}
