<?php
/**
 * 数据库操作类
 * User: paperen@gmail.com
 * Date: 2020/3/4 0004
 * Time: 12:20
 */

namespace db;

class database
{
	protected $_db;
	public function init($db_config) {
		if ( $this->_db ) return $this->_db;
		if ( !include_once(CORE."db/drivers/{$db_config['dbtype']}_drivers.php") ) exit("{$db_config['dbtype']} missing");
		$db_type = "{$db_config['dbtype']}_drivers";
		$this->_db = new $db_type($db_config['dbhost'], $db_config['dbuser'], $db_config['dbpwd'], $db_config['dbname'], $db_config['pconnect'], $db_config['charset']);
	}

	public function find($sql) {
		return $this->_db->get_one($sql);
	}

	public function select($sql) {
		return $this->_db->select($sql);
	}

	public function insert($sql) {
		return $this->_db->insert($sql);
	}

	public function delete($sql) {
		return $this->_db->delete($sql);
	}

	public function update($sql) {
		return $this->_db->update($sql);
	}

	public function count($sql) {
		return $this->_db->count($sql);
	}

	public function execute($sql) {
		return $this->_db->execute($sql);
	}

	public function show_tables($sql) {
		return $this->_db->show_tables($sql);
	}

	public function describe_tables($table, $database) {
		return $this->_db->describe_tables($table, $database);
	}
}