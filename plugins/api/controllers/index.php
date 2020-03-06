<?php

use lib\scaffold;

class indexController extends Controller{


	public function hello() {
		$this->response('hello');
	}

	/**
	 * 生成模型
	 */
	public function scaffold() {
		// 如果是非开发环境禁用
		if ( ENV != Constance::ENV_DEVELOP ) exit();

		// 排除某个表不需要生成模型
		$exclude = array('test');

		scaffold::run('api', $exclude);
		scaffold::run('admin', $exclude);
	}


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

	/**
	 * 查询
	 */
	public function index() {
		$page = get('page');
		$model = $this->model('admin');

		$data = $model->fields(array(
			'id',
			'last_login',
			'username',
			'username_en',
		))
			->where(array('enabled'=>1))
			->pagination($page, 10)
			->order('password')
			->select();

		$this->response(array(
			'total' => $model->total(),
			'data' => $data,
		));
	}

	/**
	 * 获取详情
	 */
	public function detail() {
		try {
			$id = intval(get('id'));
			if ( empty($id) ) throw new Exception('id不能为空', -1);

			$model = $this->model('admin');
			//$admin = $model->where(array('id'=>$id))->find(); 一样效果
			$admin = $model->get_by_pk($id);
			if ( empty($admin) ) throw new Exception('异常操作', -2);

			$this->_translate($admin);
			$this->response($admin);
		} catch (Exception $e) {
			$this->response(NULL, $e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 更新
	 */
	public function update() {
		try {
			$model = $this->model('admin');
			$res = $model->where(array('id'=>1))
				->update(array(
					'username' => 'username',
					'username_en' => 'username_en',
					'last_login' => time(),
				));

			$this->response($res);
		} catch (Exception $e) {
			$this->response(NULL, $e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 删除
	 */
	public function delete() {
		try {
			$model = $this->model('admin');
			$res = $model->where(array('enabled'=>1, 'username'=>'username'))->delete();

			$this->response($res);
		} catch (Exception $e) {
			$this->response(NULL, $e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 添加记录
	 */
	public function add() {
		try {
			$model = $this->model('admin');
			$tmp = array(
				'username' => '用户名',
				'salt' => 'salt',
				'last_login' => time(),
				'enabled' => '1',
				'username_en' => 'username_en',
			);
			$res = $model->insert($tmp);

			$this->response($res);
		} catch (Exception $e) {
			$this->response(NULL, $e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 添加多条记录
	 */
	public function add_multi() {
		try {
			$model = $this->model('admin');
			$tmp = array(
				array(
					'username' => '用户名',
					'salt' => 'salt',
					'last_login' => time(),
					'enabled' => '1',
					'username_en' => 'username_en',
				),
				array(
					'username' => '用户名2',
					'salt' => 'salt',
					'last_login' => time(),
					'enabled' => '1',
					'username_en' => 'username_en2',
				)
			);
			$res = $model->insert_batch($tmp);
			$this->response($res);
		} catch (Exception $e) {
			$this->response(NULL, $e->getMessage(), $e->getCode());
		}
	}

	public function join() {
		try {
			$model = $this->model('admin');
			$res = $model->alias('a')
				->join('admin b', 'b.enabled=a.id')
				->select();
			$this->response($res);
		} catch (Exception $e) {
			$this->response(NULL, $e->getMessage(), $e->getCode());
		}
	}
}