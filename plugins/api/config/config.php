<?php
/**
 * api 模块
 * @author paperen<paperen@gmail.com>
 */

return array(
	'status' => 1, // active
	'name' => 'api',

	// 路由规则
	'route' => array(
		'/' => 'index/hello',

		// 仅供示范
		'admin' => 'index/index',
		'admin_detail' => 'index/detail',
		'admin_update' => 'index/update',
		'admin_delete' => 'index/delete',
		'admin_add' => 'index/add',
		'admin_add_multi' => 'index/add_multi',
		'admin_join' => 'index/join',

		'scaffold' => 'index/scaffold',
	),


	'debug' => TRUE,
);

