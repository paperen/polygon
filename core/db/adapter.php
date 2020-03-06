<?php
/**
 * 数据库适配器抽象类
 * User: paperen@gmail.com
 * Date: 2020/3/4 0004
 * Time: 12:20
 */

namespace db;

abstract class adapter
{
	protected $_ins;
	protected $_fields = array();
	protected $_conditions = array();
	protected $_model = 'select';
	protected $_table = '';
	protected $_offset = 0;
	protected $_limit = 10;
	protected $_is_count_result = FALSE;
	protected $_join = array();
	protected $_data = array();
	protected $_order = array();

	public function init() {
		$this->_fields = array('*');
		$this->_conditions = array();
	}
	public function set_model($model) {
		$this->_model = $model;
	}
	public function set_from($table) {
		$this->_table = $table;
	}
	public function set_limit($offset=0, $limit=10) {
		$this->_offset = $offset;
		$this->_limit = $limit;
	}
	public function set_conditions($conditions=array()) {
		$this->_conditions = $conditions;
	}
	public function set_fields($fields=array()){
		$this->_fields = $fields;
	}
	public function set_count_result() {
		$this->_is_count_result = TRUE;
	}
	public function set_join($join=array()) {
		$this->_join = $join;
	}
	public function set_data($data=array()) {
		$this->_data = $data;
	}
	public function set_order($order=array()) {
		$this->_order = $order;
	}

	protected function _get_conditions() {
		$str = '';
		foreach( $this->_conditions as $k => $v ) {
			if ( $v === NULL ) {
				$str .= "{$k}";
			} else {
				$str .= " and {$k}='{$v}'";
			}
		}
		return $str;
	}

	abstract function sql();
}