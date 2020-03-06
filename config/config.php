<?php
/**
 * polygon
 * @author paperen<paperen@gmail.com>
 */

return array(
	'develop' => array(
		'db' => array(
			'dbtype' => 'mysqli',
			'dbhost' => '127.0.0.1',
			'pconnect' => FALSE,
			'dbuser' => 'root',
			'dbpwd' => '',
			'dbname' => 'test',
			'charset' => 'utf8',
			'prefix' => ''
		),
		'checkplugin' => FALSE,
		'app_name' => 'polygon',
	),
	'test' => array(
		'db' => array(
			'dbtype' => 'mysqli',
			'dbhost' => '',
			'pconnect' => FALSE,
			'dbuser' => '',
			'dbpwd' => '',
			'dbname' => '',
			'charset' => 'utf8',
			'prefix' => 'test_'
		),
		'checkplugin' => FALSE,
		'app_name' => 'polygon',
	),
	'production' => array(
		'db' => array(
			'dbtype' => 'mysqli',
			'dbhost' => '',
			'pconnect' => FALSE,
			'dbuser' => '',
			'dbpwd' => '',
			'dbname' => '',
			'charset' => 'utf8',
			'prefix' => 'test_'
		),
		'checkplugin' => FALSE,
		'app_name' => 'polygon',
	),
);

