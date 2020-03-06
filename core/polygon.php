<?php
/**
 * polygon
 * @author paperen<paperen@gmail.com>
 */
session_start();
define('CORE', ROOT.'core'.DIRECTORY_SEPARATOR);
define('PLUGINS', ROOT.'plugins'.DIRECTORY_SEPARATOR);
define('CONFIG', ROOT.'config'.DIRECTORY_SEPARATOR);

class Polygon
{
	static $_self;
	public function run() {
		if ( $_SERVER['REQUEST_METHOD'] == 'OPTIONS' ) {
			header('Access-Control-Allow-Methods:*');
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Credentials: true');
			header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, token, crossdomain, lang, private");
			exit;
		}
		if ( empty( $this->_c ) && empty( $this->_m ) ) exit('hello polygon!');
		require(CORE.'constance.php');
		require(CORE.'controller.php');
		if ( !@include( PLUGINS.$this->_p.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR."{$this->_c}.php" ) ) exit("{$this->_p} plugin missing controller({$this->_c})");

		define('PLUGIN_ACTIVE', PLUGINS . $this->_p . DIRECTORY_SEPARATOR);

		$controller = "{$this->_c}Controller";
		$c = new $controller();
		$this->_m = empty($this->_m) ? 'index' : $this->_m;
		if ( !method_exists($c, $this->_m) ) exit("{$this->_p} plugin {$this->_c} controller missing method {$this->_m}");
		try {
			call_user_func_array(array($c, $this->_m), $this->_args);
		} catch( Exception $e ) {
			exit($e->getMessage());
		}
	}

	static function init() {
		if ( self::$_self !== NULL ) return self::$_self;
		require(CORE.'common.php');

		// init self
		!defined('ENV') && define('ENV', 'production');
		$config = @include(CONFIG.'config.php');
		self::$_self = new self(isset($config[ENV]) ? $config[ENV] : NULL);

		// init model
		require(CORE.'model.php');

		// init plugin
		self::$_self->_plugins();

		// init route
		self::$_self->_route();

		// check plugin
		self::$_self->_check_plugin();

		require(CORE.'load.php');
	}

	static function instance() {
		if ( empty( self::$_self ) ) self::init();
		return self::$_self;
	}

	public $config;
	function __construct($config) {
		$this->config = $config;
	}

	/**
	 * config
	 * @param string $k key
	 * @param mixed $v data
	 * @return mixed
	 */
	public function config($k=NULL, $v=NULL) {
		if ( empty( $k ) ) return $this->config;
		if ( $v ) $this->config[$k] = $v;
		$c = isset($this->config[$k]) ? $this->config[$k] : NULL;
		if ( $c ) return $c;
		if ( $this->_p ) return isset($this->config["{$this->_p}.{$k}"]) ? $this->config["{$this->_p}.{$k}"] : NULL;
	}

	private function _get_plugin_table() {
		$db_config = config_item('db');
		return $db_config['prefix'] . 'plugins';
	}

	private $_plugins = array();
	/**
	 * 初始化插件
	 */
	private function _plugins() {
		$active_plugin = array();
		// check plugin
		$check = config_item('checkplugin');
		$need_schema = array();
		if ( $check ) {
			// init plugins
			$db_config = config_item('db');
			$db = database($db_config);
			$table = $this->_get_plugin_table();
			$data = $db->select("select * from {$table} where `status`=1");
			// no active plugin
			if ( empty( $data ) ) return;
			foreach( $data as $k => $v ) {
				$plugins_data[] = $v['plugin'];
				if ( $v['schema'] ) $need_schema[] = $v['plugin'];
			}
			// IO
			$fp = opendir(PLUGINS);
			while (FALSE !== ($plugin = readdir($fp))) {
				if ($plugin == '.' || $plugin == '..') continue;
				if (is_dir(PLUGINS . $plugin) && in_array($plugin, $plugins_data)) $active_plugin[] = $plugin;
			}
			closedir($fp);
		} else {
			$fp = opendir(PLUGINS);
			while (FALSE !== ($plugin = readdir($fp))) {
				if ($plugin == '.' || $plugin == '..') continue;
				if (is_dir(PLUGINS . $plugin)) $plugins_data[] = $plugin;
			}
			closedir($fp);
			$active_plugin = $plugins_data;
		}
		$this->_plugins = $active_plugin;

		foreach( $active_plugin as $p ) {
			$c = @include(PLUGINS."{$p}/config/config.php");
			if ( empty( $c ) ) continue;
			if ( in_array($p, $need_schema) ) $this->_schema($p);
			foreach( $c as $k => $v ) {
				$this->config("{$p}.{$k}", $v);
			}
			// helper
			if ( isset( $c['helper'] ) ) {
				foreach( $c['helper'] as $v ) {
					@include(PLUGINS."{$p}/helper/{$v}.php");
				}
			}
		}
	}

	/**
	 * init plugin SQL schema
	 * @param $plugin
	 */
	private function _schema($plugin) {
		$c = @include(PLUGINS."{$plugin}/config/schema.php");
		if ( isset($c['schema']) && $c['schema'] ) {
			$db = database(config_item('db'));
			$sql_arr = array_filter(explode(';', $c['schema']));
			foreach( $sql_arr as $sql ) $db->query($sql);

			// update schema status
			$table = $this->_get_plugin_table();
			$sql = "update {$table} set `schema`=0 where plugin='{$plugin}'";
			$db->query($sql);
		}
	}

	/**
	 * plugin
	 * @var string
	 */
	private $_p = NULL;
	/**
	 * controller
	 * @var string
	 */
	private $_c = NULL;
	/**
	 * method
	 * @var string
	 */
	private $_m = NULL;
	/**
	 * args
	 * @var string
	 */
	private $_args = array();
	/**
	 * route
	 */
	private function _route() {
		$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : NULL;
		$path_info = empty($path_info) ? (isset($_SERVER['ORIG_PATH_INFO'])?$_SERVER['ORIG_PATH_INFO'] : NULL) : $path_info;
		$path_info = empty($path_info) ? (isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI'] : NULL) : $path_info;
		if ( strpos($path_info, '?') !== FALSE ) $path_info = preg_replace('/\?(.*)/', '', $path_info);
		$path_info = str_replace(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']), '', $path_info);
		$path_info = explode('/', trim($path_info,'/'));
		if ( empty( $this->_plugins ) ) return;
		$this->_p = isset($path_info[0]) ? array_shift($path_info) : NULL;
		$this->_c = isset($path_info[0]) ? array_shift($path_info) : 'index';
		//$this->_m = isset($path_info[0]) ? array_shift($path_info) : 'index';
		if ( $path_info ) $this->_args = $path_info;
		if ( empty( $this->_p ) ) {
			// when on root
			foreach( $this->_plugins as $p ) {
				$r = $this->config("{$p}.route");
				foreach ($r as $k => $v) {
					if ( $k == '/' ) {
						$this->_p = $p;
						$rule = explode('/', $v);
						$this->_c = array_shift($rule);
						$this->_m = array_shift($rule);
						break;
					}
				}
			}
		} else {
			// translate route
			$r = $this->config("{$this->_p}.route");
			if ( isset( $r[$this->_c] ) ) {
				$tmp = explode('/', $r[$this->_c]);
				$this->_c = $tmp[0];
				$this->_m = $tmp[1];
			}
		}
	}

	private function _check_plugin() {
		// 检查插件是否有定义依赖关系
		$rely_on = $this->config("{$this->_p}.relyon");
		if ( $rely_on ) {
			foreach( $rely_on as $v ) {
				if ( !in_array($v, $this->_plugins) ) exit("{$this->_p} relyon {$v}(Not available).Please enable it.");
			}
		}
	}

	/**
	 * 获取当前插件
	 * @return string
	 */
	public function plugin() {
		return $this->_p;
	}

	public function input($k, $clean = TRUE, $default=NULL) {
		$post_raw = @json_decode(file_get_contents('php://input'), TRUE);
		$_POST = $post_raw;
		$v = isset( $_GET[$k] ) ? $_GET[$k] : NULL;
		$v = empty($v) ? (isset($_POST[$k]) ? $_POST[$k] : NULL) : $v;
		if ( empty($v) && $default ) $v = $default;
		return ($clean) ? addslashes(strip_tags($v)) : $v;
	}
}