<?php

use Lib\Model;

class admin extends Model
{
	protected $_pk = 'id';
	protected $_fields = array(
		'id' => '',
		'username' => '用户名',
		'password' => '密码',
		'salt' => '密码盐',
		'last_login' => '上次登录时间戳',
		'username_en' => '用户名（英文）',
		'enabled' => '是否启用（0-否 1-是）',

	);
}