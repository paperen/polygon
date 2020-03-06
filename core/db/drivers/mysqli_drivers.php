<?php

use db\drivers;

class mysqli_drivers extends drivers
{
    private $connid;
	private $dbname;
	private $querynum = 0;
	private $query = array();
	private $debug = 1;

    function __construct($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $charset = '') {
        $func = $pconnect == 1 ? 'mysqli_pconnect' : 'mysqli_connect';
        if(!$this->connid = $func($dbhost, $dbuser, $dbpw, $dbname)) {
            exit('Can not connect to MySQL server');
            return false;
        }
        mysqli_query($this->connid, "SET NAMES {$charset}");
        $this->dbname = $dbname;
    }

    function select($sql) {
    	$query = $this->_query($sql);
		$result = array();
		while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			$result[] = $row;
		}
		return $result;
	}

	function get_one($sql) {
		$data = $this->select($sql);
		return isset($data[0])?$data[0]:NULL;
	}

	function update($sql) {
		$this->_query($sql);
		return mysqli_affected_rows($this->connid);
	}

	function insert($sql) {
		$this->_query($sql);
		return mysqli_insert_id($this->connid);
	}

	function delete($sql) {
		$this->_query($sql);
		return mysqli_affected_rows($this->connid);
	}

    protected function _query($sql , $type = '') {
    	if ( empty($sql) ) return NULL;
		if (!($query = @mysqli_query($this->connid, $sql))) {
			save_log($sql, Constance::LOG_ERR);
			return false;
		}
		$this->query[] = $sql;
		$this->querynum++;
		return $query;
	}

	function affected_rows() {
		return mysqli_affected_rows($this->connid);
	}

	function count($sql) {
		$data = $this->get_one($sql);
		return isset($data['count(*)']) ? intval($data['count(*)']) : 0;
	}

	function execute($sql) {
		$this->_query($sql);
		return mysqli_affected_rows($this->connid);
	}

	function show_tables($sql)
	{
		$res = $this->select($sql);
		$tables = array();
		$key = "Tables_in_{$this->dbname}";
		foreach( $res as $v ) {
			$tables[] = isset($v[$key]) ? $v[$key] : NULL;
		}
		return $tables;
	}

	function describe_tables($table, $database) {
    	// 获取表所有字段
    	$sql = "select COLUMN_NAME as 'column_name', DATA_TYPE as 'data_type', COLUMN_COMMENT as 'column_comment'
from INFORMATION_SCHEMA.COLUMNS Where table_name = '$table' AND table_schema = '{$database}'";
    	$fields = $this->select($sql);

    	// 获取表索引
    	$sql = "show index from {$table}";
    	$res = $this->select($sql);
    	$index = array();
    	foreach( $res as $v ) {
			$index[] = array(
				'column' => $v['Column_name'],
				'key' => $v['Key_name'],
				'is_primary' => (strtolower($v['Key_name']) == 'primary'),
			);
		}
    	return array(
    		'fields' => $fields,
    		'index' => $index,
		);
	}

	function __destruct()
	{
		foreach( $this->query as $sql ) save_log($sql, Constance::LOG_DEBUG);
	}
}