<?php
/**
 * 模型脚手架
 * User: paperen
 * Date: 2020/3/6 0006
 * Time: 15:18
 */

namespace lib;

use lib\Model;

class scaffold
{
	static protected $_model;
	static protected $_dbcfg;

	/**
	 * 生成模型
	 */
	static public function run($sub='', $exclude=array()) {
		if ( empty( $sub ) ) exit('请输入生成模型的plugins目录');

		$path = ROOT . 'plugins' . DIRECTORY_SEPARATOR . $sub . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR;
		if ( !is_dir($path) ) exit("{$path}目录不存在，请先创建");

		self::$_model = new Model();
		self::$_dbcfg = config_item('db');
		$tables = self::$_model->get_all_tables();
		foreach( $tables as $table ) {
			$save_path = $path . str_replace(self::$_dbcfg['prefix'], '', $table) . '.php';
			if ( in_array($table, $exclude) ) continue;
			if ( file_exists($save_path) ) {
				echo "{$sub}目录：{$table}模型已存在，忽略\n";
				continue;
			}
			self::_gen_model($table, $save_path);

			echo "{$sub}目录：{$table}模型已生成\n";
		}
	}

	/**
	 *
	 * @param $table
	 */
	static protected function _gen_model($table, $save_path) {
		$table_info = self::$_model->describe_table($table);

		$fields_str = '';
		foreach( $table_info['fields'] as $v ) {
			$fields_str .= "		'{$v['column_name']}' => '{$v['column_comment']}',\n";
		}

		$pk = '';
		foreach( $table_info['index'] as $v ) {
			if ( $v['is_primary'] ) {
				$pk = $v['column'];
				break;
			}
		}

		$model_tpl =<<<EOT
<?php

use Lib\Model;

class {$table} extends Model
{
	protected \$_pk = '{$pk}';
	protected \$_fields = array(
{$fields_str}
	);
}
EOT;
		file_put_contents($save_path, $model_tpl);
	}
}