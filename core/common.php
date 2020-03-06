<?php

function config_item($k=NULL, $v=NULL) {
	return Polygon::instance()->config($k, $v);
}

function database() {
	static $_db;
	if ( $_db ) return $_db;
	$db_config = config_item('db');
	if ( !include(CORE."db/{$db_config['dbtype']}.php") ) exit("{$db_config['dbtype']} missing");
	$db_type = "db_{$db_config['dbtype']}";
	$_db = new $db_type($db_config['dbhost'], $db_config['dbuser'], $db_config['dbpwd'], $db_config['dbname'], $db_config['pconnect'], $db_config['charset']);
	return $_db;
}

/**
 * 重定向
 * @param string $url
 */
function redirect($url) {
	header("location:{$url}");
	exit;
}
/**
 * 组合站点链接
 * @param string $uri
 * @return string
 */
function base_url($uri = NULL) {
	return config_item('site_url') . $uri;
}

/**
 * 模块链接
 * @param string $uri
 * @return string
 */
function module_url($uri = NULL) {
	$p = Polygon::instance()->plugin();
	$uri = ($p) ? "{$p}/{$uri}" : $uri;
	return config_item('site_url') . $uri;
}

/**
 * 获取传输参数
 * @param $key
 * @param bool $clean
 * @param null $default
 * @return mixed
 */
function get($key, $clean=TRUE, $default=NULL) {
	return Polygon::instance()->input($key, $clean, $default);
}

/**
 * 记录日志
 * @param $msg
 * @param string $level
 */
function save_log($msg, $level=Constance::LOG_DEBUG) {
	if ( ENV == Constance::ENV_DEVELOP ) {
		$time = date('H:i:s');
		$log_file = 'log_'.date('ymd').'.log';
		$fp = fopen(ROOT . 'cache' . DIRECTORY_SEPARATOR . $log_file, 'a+');
		fwrite($fp, "[{$time}][{$level}]: {$msg}\n");
		fclose($fp);
	}
}