<?php

class Api extends REST_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->model('User');
		$this->load->model('Auth');
	}
	public function user_get($id = null){
		if ($id){
			return $this->User->getById($id);
		}
		return $this->User->getListFiltered(
			$this->get('participant'),
			$this->get('state'),
			$this->get('skip'),
			$this->get('limit')
		);
	}
	public function user_put($id){
		$this->User->create($this->put());
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
				$this->post('password')
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
		$operators = R::findAll('operator');
		foreach($operators as &$operator){
			$operator = $operator->export();
		}
		return $operators;
	}
	public function operator_delete($id = null){
		if ($id){
			$bean = R::findOne('operator', $id);
			R::trash($bean);
		} else {
			R::wipe('operator');
		}
	}
	public function auth_get(){
		return $this->input->cookie('auth');
	}
}
