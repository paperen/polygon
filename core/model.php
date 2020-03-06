<?php

namespace lib;

class Model{
	
	// primary key
	protected $_pk;
	// fileds
	protected $_fields = array( );
	// filed => value
	protected $_data = array( );
	// table name
	protected $_table_name;
	// rules
	protected $_rules;
	protected $_table_prefix = NULL;

	protected $_adapter;
	
	protected $db;

	function __construct() {
		$dbcfg = config_item('db');
		$this->db = new \db\database();
		$this->db->init($dbcfg);
		$this->_table_prefix = $dbcfg['prefix'];

		include_once(CORE."db/adapter/{$dbcfg['dbtype']}_adapter.php");
		$adapter_class = "{$dbcfg['dbtype']}_adapter";
		if ( !class_exists($adapter_class) ) exit("{$dbcfg['dbtype']} adapter not exists");
		$this->_adapter = new $adapter_class();
	}
	
	/**
	 * 获取表名
	 * @return string 表名
	 */
	public function _table_name($table = '', $alias='') {
		$tablename = ( $table ) ? $table : $this->_table_name;
		if ( empty( $tablename ) ) $tablename = get_called_class();
		if ( $alias ) $tablename = "{$tablename} as {$alias}";
		return "{$this->_table_prefix}{$tablename}";
	}

	/**
	 * 获得主键的名称
	 * @return string 主键
	 */
	public function _primary_key() {
		return $this->_pk;
	}

	/**
	 * 获得所有字段
	 * @return array 字段
	 */
	public function _fields($prefix='') {
		$fields = array();
		foreach( $this->_fields as $k => $v ) $fields[] = ($prefix) ? "{$prefix}.{$k}" : $k;
		return $fields;
	}

	/**
	 * 获取某个字段的描述
	 * @param string $key
	 * @return string
	 */
	public function get_field( $key = '' ) {
		return ( $key && isset( $this->_fields[$key] ) ) ? $this->_fields[$key] : $this->_fields;
	}

	/**
	 * 设置数据
	 * @param array $data 数据
	 */
	public function _set_data( $data ) {
		$this->_data = $data;
	}

	/**
	 * 根据字段过滤数据
	 * @param array $data 数据
	 * @param bool $is_pk 是否包含主键
	 * @return array 过滤后的数据
	 */
	public function _filter_data( $data, $is_pk = FALSE ) {
		$filter_data = array( );
		$fields = $this->_fields();
		foreach ( $data as $k => $v ) {
			if ( $is_pk && $this->_primary_key() == $k ) continue;
			if ( !in_array( $k, $fields ) ) continue;
			$filter_data[$k] = $v;
		}
		return $filter_data;
	}

	/**
	 * 获得数据
	 * @return array 数据
	 */
	public function _get_data() {
		return $this->_data;
	}

	/**
	 * 清空数据
	 */
	public function _clear_data() {
		$this->_data = array( );
	}

	/**
	 * 插入数据
	 * @param array $data 数据
	 * @return int 生成ID
	 */
	public function insert( $data ) {
		if ( empty( $data ) ) return FALSE;
		$this->_set_data( $this->_filter_data( $data ) );
		$insert_data = $this->_get_data();
		$table = $this->_table_name();

		$this->_adapter->init();
		$this->_adapter->set_from($this->_table_name($this->_table_name));
		$this->_adapter->set_model('insert');
		$this->_adapter->set_data($insert_data);

		$this->_clear_data();
		return $this->db->insert( $this->_adapter->sql() );
	}

	/**
	 * 插入多行数据
	 * @param array $data
	 * @return int 影响行数
	 */
	public function insert_batch( $data ) {
		if ( empty($data) ) return FALSE;

		$insert_data = array();
		foreach( $data as $v ) $insert_data[] = $this->_filter_data( $v );

		$this->_adapter->init();
		$this->_adapter->set_from($this->_table_name($this->_table_name));
		$this->_adapter->set_model('insert_batch');
		$this->_adapter->set_data($insert_data);

		return $this->db->execute($this->_adapter->sql());
	}

	/**
	 * 更新数据
	 * @param array $data 数据
	 * @return int 影响行数
	 */
	public function update( $data ) {
		$pk = $this->_primary_key();
		if ( isset( $data[$pk] ) ) {
			$this->where(array($pk=>$data[$pk]));
			unset($data[$pk]);
		}

		$this->_set_data( $this->_filter_data( $data, TRUE ) );
		$update_data = $this->_get_data();

		$this->_adapter->init();
		$this->_adapter->set_from($this->_table_name($this->_table_name, $this->_alias));
		$this->_adapter->set_model('update');
		$this->_adapter->set_conditions($this->_conditions);
		$this->_adapter->set_data($update_data);
		return $this->db->update( $this->_adapter->sql() );
	}

	/**
	 * 通过主键删除数据
	 * @param int $pk 主键ID
	 * @return int 影响行数
	 */
	public function delete( $pk='' ) {
		$pkey = $this->_primary_key();
		if ( empty($this->_conditions) && empty($pk) ) return;
		if ( $pk ) $this->_conditions[$pkey] = $pk;
		$this->_adapter->init();
		$this->_adapter->set_from($this->_table_name($this->_table_name, $this->_alias));
		$this->_adapter->set_model('delete');
		$this->_adapter->set_conditions($this->_conditions);
		return $this->db->delete( $this->_adapter->sql() );
	}

	/**
	 * 通过主键获得单个数据
	 * @param int $pk 主键ID
	 * @return array 数据
	 */
	public function get_by_pk( $pk ) {
		$this->_adapter->init();
		$this->_adapter->set_from($this->_table_name($this->_table_name, $this->_alias));
		$this->_adapter->set_model('select');
		$this->_adapter->set_fields($this->_fields_select);
		$this->_adapter->set_conditions(array(
			$this->_primary_key() => $pk
		));
		return $this->db->find($this->_adapter->sql());
	}

	/**
	 * 获取所有数据
	 * @param int $limit 每页显示条数
	 * @param int $offset 游标
	 * @return array 数据
	 */
	public function all( $limit = 10, $offset = 0 ) {
		$this->_adapter->init();
		$this->_adapter->set_from($this->_table_name($this->_table_name, $this->_alias));
		$this->_adapter->set_model('select');
		$this->_adapter->set_join($this->_join);
		$this->_adapter->set_fields($this->_fields_select);
		$this->_adapter->set_conditions($this->_conditions);
		$this->_adapter->set_order($this->_order);
		$this->_adapter->set_limit($offset, $limit);
		return $this->db->select($this->_adapter->sql());
	}

	/**
	 * 获取第一条数据
	 * @return mixed
	 */
	public function find() {
		$this->_adapter->init();
		$this->_adapter->set_from($this->_table_name($this->_table_name, $this->_alias));
		$this->_adapter->set_model('select');
		$this->_adapter->set_join($this->_join);
		$this->_adapter->set_fields($this->_fields_select);
		$this->_adapter->set_conditions($this->_conditions);
		return $this->db->find($this->_adapter->sql());
	}

	/**
	 * 获得总数
	 * @return int 总数
	 */
	public function total() {
		$this->_adapter->init();
		$this->_adapter->set_from($this->_table_name($this->_table_name, $this->_alias));
		$this->_adapter->set_model('select');
		$this->_adapter->set_conditions($this->_conditions);
		$this->_adapter->set_count_result();
		return $this->db->count($this->_adapter->sql());
	}

	protected $_fields_select = array('*');

	/**
	 * 设置查询的字段
	 * @param $fields
	 * @return $this
	 */
	public function fields($fields) {
		$tmp = array();
		foreach( $fields as $v ) {
			if ( in_array($v, $this->_fields) ) $tmp[] = $v;
		}
		if ( $tmp ) $this->_fields_select = $tmp;
		return $this;
	}

	protected $_conditions = array();

	/**
	 * 设置查询的条件
	 * @param array $condition
	 * @return $this
	 */
	public function where($condition=array()) {
		foreach( $condition as $k => $v ) $this->_conditions[$k] = $v;
		return $this;
	}

	protected $_limit=10;
	protected $_offset=0;

	/**
	 * 分页
	 * @param int $page
	 * @param int $limit
	 * @return $this
	 */
	public function pagination($page=1, $limit=10) {
		if ( $page < 1 ) $page = 1;
		if ( $limit <= 0 ) $limit = 10;
		$this->_limit = $limit;
		$this->_offset = ($page-1)*$this->_limit;
		return $this;
	}

	/**
	 * 查询
	 * @return mixed
	 */
	public function select() {
		$this->_adapter->init();
		$this->_adapter->set_from($this->_table_name($this->_table_name, $this->_alias));
		$this->_adapter->set_model('select');
		$this->_adapter->set_join($this->_join);
		$this->_adapter->set_fields($this->_fields_select);
		$this->_adapter->set_conditions($this->_conditions);
		$this->_adapter->set_order($this->_order);
		$this->_adapter->set_limit($this->_offset, $this->_limit);
		return $this->db->select($this->_adapter->sql());
	}

	protected $_alias='';

	/**
	 * 设置别名
	 * @param $alias
	 * @return $this
	 */
	public function alias($alias) {
		$this->_alias = $alias;
		return $this;
	}

	protected $_join = array();

	/**
	 * join查询
	 * @param $table
	 * @param $on
	 * @param string $type
	 * @return $this
	 */
	public function join($table, $on, $type='') {
		$tmp = explode(' ', $table);
		$table = $this->_table_name(array_shift($tmp), NULL) . ' ' . implode(' ', $tmp);
		$this->_join[] = " {$type} join {$table} on {$on}";
		return $this;
	}

	/**
	 * 获取库里所有表
	 * @return array
	 */
	public function get_all_tables() {
		$this->_adapter->init();
		$this->_adapter->set_model('show');
		$this->_adapter->set_from('tables');
		return $this->db->show_tables($this->_adapter->sql());
	}

	/**
	 * 获取某个表的字段说明
	 * @param string $table
	 * @return array
	 */
	public function describe_table($table) {
		$dbcfg = config_item('db');
		return $this->db->describe_tables($table, $dbcfg['dbname']);
	}

	protected $_order = array();
	/**
	 * 设置排序规则
	 * @param $field
	 * @param string $order_by
	 * @return $this
	 */
	public function order($field, $order_by='desc') {
		$this->_order[$field] = $order_by;
		return $this;
	}
}
?>
