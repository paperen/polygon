## polygon框架

* 编写始于插件化/模块化思想
* 适合快速编写简单的接口
* 半自动转换中英语言

## 如何开始

1. git clone到本地后
2. composer update
3. 修改config/config.php(按实际情况调整数据库信息，区分develop/test/production三种环境配置，关于环境常量参见index.php中的ENV)
4. 浏览器访问 本地地址+/api/index
5. clone下来自带存在两个模块`admin`与`api`，`admin`是后台（只有一个登录页，没写什么逻辑，不是重点）仅提示也可以做成后台或前台等，重点是`api`

* /api/index - API模块
* /admin/login - admin模块

具体指向哪个控制器方法需要对照分别模块中路由映射

## 示例

* 为方便入手，你可以先复制以下SQL到库里执行

    
        CREATE TABLE `test` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `username` varchar(50) DEFAULT NULL COMMENT '用户名',
          `password` char(32) DEFAULT NULL COMMENT '密码',
          `salt` char(6) DEFAULT NULL COMMENT '密码盐',
          `last_login` int(10) unsigned DEFAULT NULL COMMENT '上次登录时间戳',
          `username_en` varchar(255) DEFAULT NULL COMMENT '用户名（英文）',
          `enabled` tinyint(1) unsigned DEFAULT '1' COMMENT '是否启用（0-否 1-是）',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

        CREATE TABLE `admin` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `username` varchar(50) DEFAULT NULL COMMENT '用户名',
          `password` char(32) DEFAULT NULL COMMENT '密码',
          `salt` char(6) DEFAULT NULL COMMENT '密码盐',
          `last_login` int(10) unsigned DEFAULT NULL COMMENT '上次登录时间戳',
          `username_en` varchar(255) DEFAULT NULL COMMENT '用户名（英文）',
          `enabled` tinyint(1) unsigned DEFAULT '1' COMMENT '是否启用（0-否 1-是）',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
        
        INSERT INTO `admin` VALUES ('1', 'test', '1234', '1234', '1234', 'test_en', '1');

* 然后访问`/api/scaffold`，会自动生成出admin模型（位于api/models目录）
* 访问`/api/admin`、`/api/admin_detail?id=1`查看输出结果（查询）
* 访问`/api/admin_update`，查看admin数据表中的数据（更新）
* `/api/admin_delete`，查看admin数据表中的数据（删除）
* `/api/admin_add`，查看admin数据表中的数据（插入）
* `/api/admin_add_multi`，查看admin数据表中的数据（批量插入）

到此例子已结束，代码也比较简单可以打开`api/controllers/index.php`查看到

## 路由

约定每个`plugins`都可以拥有自己的路由规则，路由文件位于每个plugin中的config目录`config.php`,以下为api模块中的路由规则

	// 仅供示范
    'admin' => 'index/index',
    'admin_detail' => 'index/detail',
    'admin_update' => 'index/update',
    'admin_delete' => 'index/delete',
    'admin_add' => 'index/add',
    'admin_add_multi' => 'index/add_multi',

## 半自动切换中英语言

在作为api使用时，若前端请求头中带有`lang`字段的话，会按照lang的值返回不同的数据，字段结构都一样的，可以使用postman在header中加上`lang`为en，请求以下`/api/admin`接口对比以下不加`lang`请求返回的`username`字段数值

控制器中重写`$_field_map`变量
获取到每个数据单位后调用`_translate`即会自动对相关字段转换中英

比如下面的例子，对于接口输出来说只有一个字段username

例如：

	/**
	 * 需要翻译的字段
	 * @var array
	 */
	protected $_field_map = array(
		'cn' => array(
			'username' => 'username',
			'username_en' => '',
		),
		'en' => array(
			'username' => '',
			'username_en' => 'username',
		),
	);
	$admin = array(
	    'username' => '中文',
	    'username_en' => 'english',
	);
	$this->_translate($admin);
	print_r($admin)

## 链式db操作

支持链式操作方式

    // 查询
    $data = $model->fields(array(
        'id',
        'last_login',
        'username',
        'username_en',
    ))
        ->where(array('enabled'=>1))
        ->pagination($page, 10)
        ->select();
        
    // 更新
    $model->where(array('id'=>1))
    ->update(array(
        'username' => 'username',
        'username_en' => 'username_en',
        'last_login' => time(),
    ));   
    
### fields

设置查询哪些字段

> 参数类型：数组

### where

设置条件

> 参数类型：数组

### pagination

分页

> 参数类型：整数（当前页数、每页查询条数）

### alias

设置表别名

> 无参数

### join

设置join

|参数|说明|必填|
|:----     |-----  |----- |
|table|表   |是|
|on  | on    |是|
|type  | left/right/inner，默认为空|否|

### select

获取全部数据

> 无参数

### find

获取第一条数据

> 无参数

### insert

插入数据

> 参数类型：数组

### insert_batch

插入多个数据

> 参数类型：数组

### update

更新

> 参数类型：数组

### delete

删除

> 无参数

### get_by_pk

获取某个主键为x的记录

> 参数类型：int

### all

获取多条数据

|参数|说明|必填|
|:----     |-----  |----- |
|limit|每次查询条数,默认10   |否|
|offset  | 查询游标,默认0   |否|

### total

获取总条数

> 无参数

### order

设置排序规则

|参数|说明|必填|
|:----     |-----  |----- |
|field|排序字段   |是|
|order_by  | 排序规则，默认为desc   |否|

## 脚手架自动生成模型文件

若自己增加一个模块可以通过脚手架生成是所有模型，用法很简单

    use lib\scaffold;
    在任何一个方法中
    scaffold::run(模块名称);
    
若某个表不需要生成模型，run方法可以传递第二个参数过滤掉

    $exclude = array('test');
    scaffold::run('api', $exclude);
    
## 日志

可以调用全局`save_log`方法打日志

在develop环境里100%会生成日志cache目录，对于非develop环境仅在错误等级为error才会记录到日志

|参数|说明|必填|
|:----     |-----  |----- |
|$msg|日志内容   |是|
|$level  | 日志等级，默认为debug   |否|

注意：若无法生成日志，请确保根目录允许生成cache目录或手动创建cache目录并允许php写入