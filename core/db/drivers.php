<?php
/**
 * 数据库操作抽象类
 * User: paperen@gmail.com
 * Date: 2020/3/4 0004
 * Time: 12:20
 */

namespace db;

abstract class drivers
{
	/**
	 * 查询（多行）
	 * @param $sql
	 * @return mixed
	 */
	abstract function select($sql);

	/**
	 * 查询
	 * @param $sql
	 * @return mixed
	 */
	abstract function get_one($sql);

	/**
	 * 更新
	 * @param $sql
	 * @return mixed
	 */
	abstract function update($sql);

	/**
	 * 插入
	 * @param $sql
	 * @return mixed
	 */
	abstract function insert($sql);

	/**
	 * 删除
	 * @param $sql
	 * @return mixed
	 */
	abstract function delete($sql);

	/**
	 * 执行（内部方法）
	 * @param $sql
	 * @param string $type
	 * @return mixed
	 */
	abstract protected function _query($sql , $type = '');

	/**
	 * 影响行数
	 * @return mixed
	 */
	abstract function affected_rows();

	/**
	 * 查询总数
	 * @param $sql
	 * @return mixed
	 */
	abstract function count($sql);

	/**
	 * 执行命令
	 * @param $sql
	 * @return mixed
	 */
	abstract function execute($sql);

	/**
	 * 获取库里所有表
	 * @param $sql
	 * @return mixed
	 */
	abstract function show_tables($sql);
}