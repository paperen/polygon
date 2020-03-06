<?php
class indexController extends Controller{

	public function login() {
		$data = array();
		$admin_model = $this->model('admin');
		$admin = $admin_model->all();
		$this->load->view('login',$data);
	}

	public function logout($id) {

	}
}