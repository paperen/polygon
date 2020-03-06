<?php
abstract class Controller
{
	protected $load;
	protected $_lang;
	protected $_field_map;

	public function __construct(){
		$this->load = new Load();
		$this->_lang = isset($_SERVER['HTTP_LANG']) ? $_SERVER['HTTP_LANG'] : 'cn';
		if ( !in_array($this->_lang, array('en','cn')) ) $this->_lang = 'cn';
	}

	public function model($model) {
		return $this->load->model($model);
	}

	public function response($data=NULL, $msg='success', $err=0) {
		header("Access-Control-Allow-Origin: *");
		header('Content-type: application/json');
		echo json_encode(array(
			'err' => $err,
			'msg' => $msg,
			'data' => $data,
		));
		exit;
	}

	protected function lang() {
		return $this->_lang;
	}

	protected function _translate(&$v, $type=NULL) {
		if ( $type ) {
			$fieldMap = isset( $this->_field_map[$type] ) ? $this->_field_map[$type] : NULL;
		} else {
			$fieldMap = $this->_field_map;
		}
		if ( empty($fieldMap) ) return;
		$langField = $fieldMap[$this->_lang];
		foreach( $langField as $k => $k1 ) {
			if ( !isset($v[$k]) ) continue;
			if ( $k1 ) {
				$v[$k1] = $v[$k];
				if ( $k1 != $k ) unset($v[$k]);
			}
			if (empty($k1)) unset($v[$k]);
		}
	}

	protected $_type = array();
	protected function _get_type() {
		if ( $this->_type ) return $this->_type;
		$model = $this->model('type');
		$type = $model->all();
		foreach ($type as $v) {
			$this->_type[$v['id']] = $v;
		}
		return $this->_type;
	}

	protected function _init_project($project) {
		$id = $project['id'];
		$parea_model = $this->model('project_area');
		$project_area = $parea_model->get_by_project_id($id);
		foreach( $project_area as $k => $v ) $this->_translate($project_area[$k], 'area');
		$project['area'] = $project_area;

		$pclient_model = $this->model('project_client');
		$project_client = $pclient_model->get_by_project_id($id);
		foreach( $project_client as $k => $v ) $this->_translate($project_client[$k], 'client');
		$project['client'] = $project_client;

		$project_client_type = $pclient_model->get_type_by_project_id($id);
		foreach( $project_client_type as $k => $v ) $this->_translate($project_client_type[$k], 'client_type');
		$project['client_type'] = $project_client_type;

//		$pawards_model = $this->model('project_awards');
//		$project_awards = $pawards_model->get_by_project_id($id);
//		foreach( $project_awards as $k => $v ) $this->_translate($project_awards[$k], 'awards');
//		$project['awards'] = $project_awards;

		$pimages_model = $this->model('project_images');
		$project_imgs_cover = $pimages_model->get_by_project_id($id, TRUE);
		$project_imgs = $pimages_model->get_by_project_id($id);
		$imgs = array();
		foreach( $project_imgs as $k => $v ) {
			$imgs[] = array(
				'url' => $v['img_url'],
				'thumb' => $v['img_url'],
			);
		}
		$project['images'] = $imgs;

		$type_ids = explode(',', $project['type_id']);
		$project['type_id'] = $type_ids;
		$type_data = $this->_get_type();
		$type = array();
		foreach( $type_ids as $tid ) {
			if ( !isset($type_data[$tid]) ) continue;
			if ( $this->_lang == 'cn' ) {
				$type[] = $type_data[$tid]['type'];
			} else {
				$type[] = $type_data[$tid]['type_en'];
			}
		}
		$project['type'] = $type;

		return $project;
	}
}