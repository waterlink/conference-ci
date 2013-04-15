<?php

class Api extends REST_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->model('User');
		$this->load->model('Auth');
	}
	public function user_get($id = null){
		if ($id){
			$res = $this->User->getById($id);
			if ($res){
				$res = $res->export();
			}
			return $res;
		}
		$words = $this->get('words');
		if (!$words){
			$words = array();
		}
		return beansToList($this->User->getListFiltered(
			$this->get('participant'),
			$this->get('status'),
			$this->get('skip'),
			$this->get('limit'),
			$words
		));
	}
	public function user_put($id){
		$put = $this->put();
		// $put["id"] = $id;
		// $bean = R::dispense("user");
		// $bean->import($put);
		$this->User->update($put, $id);
	}
	public function user_post(){
		$data = $this->post();
		$data['id'] = 0;
		$this->User->create($data);
	}
	public function user_delete($id = null){
		if (!$id){
			R::wipe('user');
		} else {
			R::trash($this->User->getById($id));
		}
	}
	public function index_get(){
		return array(
			'whois' => $this->Auth->whoIs(),
			'group' => $this->Auth->group()
		);
	}
	public function index_put($operator = null){
		if (!$operator){
			$this->Auth->changePassword(
				$this->put('old_password'),
				$this->put('new_password')
			);
		} else {
			$this->Auth->resetPassword(
				$operator,
				$this->put('new_password')
			);
		}
	}
	public function index_post($operator = null){
		if (!$operator){
			return $this->Auth->register(
				$this->post('login'),
				$this->post('password'),
				$this->post("email")
			);
		} else {
			return $this->Auth->login(
				$operator,
				$this->post('password')
			);
		}
	}
	public function index_delete(){
		$this->Auth->logoff();
	}
	public function operator_get(){
		if (!in_array("admin", $this->Auth->group())){
			return array("error" => "Access denied");
		}
		return R::exportAll(R::findAll('operator'));
	}
	public function operator_put($login = null){
		if (!in_array("admin", $this->Auth->group())){
			return array("error" => "Access denied");
		}
		$newEmail = $this->put("email");
		if (!$newEmail){
			return array("error" => "Email must be specified");
		}
		if ($login){
			$bean = R::findOne("operator", 
				" login = :login ",
				array(":login" => $login));
			if ($bean){
				$bean->email = $newEmail;
				R::store($bean);
				return true;
			}
		}
	}
	public function operator_delete($id = null){
		if (!in_array("admin", $this->Auth->group())){
			return array("error" => "Access denied");
		}
		if ($id){
			$bean = R::load('operator', $id);
			if ($bean && !$bean->admin){
				R::trash($bean);
			}
		} else {
			// R::wipe('operator');
		}
	}
	// public function auth_get(){
	// 	return $this->input->cookie('auth');
	// }
	public function settings_get(){
		$bean = R::findOne("settings");
		if ($bean){
			return $bean->export();
		}
		return false;
	}
	public function settings_put(){
		if (!in_array("admin", $this->Auth->group())){
			return array("error" => "Access denied");
		}
		$bean = R::findOne("settings");
		if (!$bean){
			$bean = R::dispense("settings");
		}
		$bean->import($this->put());
		R::store($bean);
	}
}
