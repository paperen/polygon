<?php
class Load{

	public function view($name,array $vars = null, $is_layout=FALSE){
		$file = ($is_layout) ? $name : PLUGIN_ACTIVE.'views/'.$name.'.php';
		if(is_readable($file)){
			if ( isset( $vars['file'] ) ) unset($vars['file']);
			extract($vars);
			require($file);
			return true;
		}
		throw new Exception("View({$name}) missing");
	}

	private $_load_models = array();
	public function model($name){
		if ( isset( $this->_load_models[$name] ) ) return $this->_load_models[$name];
		$model = $name;
		$modelPath = PLUGIN_ACTIVE.'models/'.$model.'.php';
		if(is_readable($modelPath)){
			require_once($modelPath);
			if(class_exists($model)) $m = new $model();
			$this->_load_models[$model] = $m;
			return $this->_load_models[$model];
		}
		throw new Exception('Model missing');
	}

	/**
	 * 加载其他函数库
	 * @param string $helper 函数库文件(不含后缀)
	 */
	public function helper($helper) {
		$helper_name = trim($helper, '.php');
		if ( file_exists( PLUGIN_ACTIVE . "helper/{$helper_name}.php" ) ) include(PLUGIN_ACTIVE . "helper/{$helper_name}.php");
	}

	private $_layout = 'default';
	public function set_layout($layout) {
		$this->_layout = $layout;
	}

	public function layout($view, array $vars = null) {
		ob_start();
		$this->view($view, $vars);
		$html = ob_get_contents();
		ob_end_clean();
		$data = array(
			'content' => $html,
			'page_title'=>isset($vars['page_title'])?$vars['page_title']:NULL,
			'active'=>isset($vars['active'])?$vars['active']:NULL,
		);
		$this->view(PLUGIN_ACTIVE."views/layout/{$this->_layout}.php", $data, TRUE);
	}

	/**
	 * 加载class
	 * @param string $class 类包名
	 */
	protected $_load_class = array();
	public function library($class, $args=NULL) {
		if ( isset( $this->_load_class[$class] ) ) return $this->_load_class[$class];
		$file = PLUGIN_ACTIVE . 'library' . DIRECTORY_SEPARATOR . $class . '.php';
		if ( !file_exists( $file ) ) $file = CORE . 'library' . DIRECTORY_SEPARATOR . $class . '.php';
		if ( !@include($file) ) return;
		if ( class_exists($class) ) $this->_load_class[$class] = ($args) ? new $class($args) : new $class();
		return isset($this->_load_class[$class]) ? $this->_load_class[$class] : NULL;
	}
}
