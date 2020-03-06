<?php

use db\adapter;

class mysqli_adapter extends adapter
{

	function sql()
	{
		if ( $this->_model == 'select' ) {
			// 查询
			$sql = $this->_select();
		} else if( $this->_model == 'update' ) {
			// 更新
			$sql = $this->_update();
		} else if( $this->_model == 'insert' ) {
			// 插入
			$sql = $this->_insert();
		} else if( $this->_model == 'insert_batch' ) {
			// 插入（批量）
			$sql = $this->_insert_batch();
		} else if( $this->_model == 'delete' ) {
			// 删除
			$sql = $this->_delete();
		} else if( $this->_model == 'show' ) {
			// 获取数据库信息
			$sql = "show {$this->_table}";
		}
		return $sql;
	}

	/**
	 * 组合查询SQL
	 * @return string
	 */
	protected function _select() {
		if ( $this->_is_count_result ) {
			$sql = "select count(*) from {$this->_table}";
		} else {
			$sql = "select ".implode(',', $this->_fields) . " from {$this->_table}";
		}
		foreach( $this->_join as $v ) $sql .= " {$v}";
		$sql .= " where 1" . $this->_get_conditions();
		if ( $this->_order ) {
			$sql .= " order by ";
			$tmp = array();
			foreach( $this->_order as $k => $ord ) {
				$tmp[] = "{$k} {$ord}";
			}
			$sql .= implode(',', $tmp);
		}
		if ( $this->_limit && !$this->_is_count_result ) $sql .= " limit {$this->_offset},{$this->_limit}";
		return $sql;
	}

	/**
	 * 组合更新SQL
	 * @return string
	 */
	protected function _update() {
		$sql = "update {$this->_table} set ";
		$tmp = array();
		foreach( $this->_data as $k => $v ) $tmp[] = "{$k}='{$v}'";
		$sql .= implode(',', $tmp);
		$sql .= " where 1".$this->_get_conditions();
		return $sql;
	}

	/**
	 * 组合插入SQL
	 * @return string
	 */
	protected function _insert() {
		$fields = array();
		$data = array();
		foreach ($this->_data as $k => $v) {
			$fields[] = $k;
			$data[] = $v;
		}
		$sql = "insert into {$this->_table} (`" . implode('`,`', $fields) . "`) values ('" . implode('\',\'', $data) . "')";
		return $sql;
	}

	/**
	 * 组合插入SQL
	 * @return string
	 */
	protected function _insert_batch() {
		$fields = array();
		$data = array();
		foreach ($this->_data as $k => $vs) {
			$tmp = array();
			foreach( $vs as $k1 => $v1 ) {
				if ( $k == 0 ) $fields[] = $k1;
				$tmp[] = $v1;
			}
			$data[] = "('" . implode('\',\'', $tmp) . "')";
		}
		$sql = "insert into {$this->_table} (`" . implode('`,`', $fields) . "`) values " . implode(',', $data);
		return $sql;
	}

	/**
	 * 组合删除SQL
	 * @return string
	 */
	protected function _delete() {
		$sql = "delete from {$this->_table} where 1" . $this->_get_conditions();
	}
}