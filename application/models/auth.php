<?php

class Auth extends CI_Model {
	public function __construct(){
		parent::__construct();
		$this->setupCookie(new Cookie_CI());
	}
	public function setupCookie($cookie){
		must(is_a($cookie, 'ICookie'),
			"Auth::setupCookie:: cookie must implement ICookie");
		$this->cookie = $cookie;
	}
	public function isGuest(){
		return $this->cookie->getCookie('auth') === null;
	}
	public function isLoggedIn(){
		if ($this->isGuest()){
			return false;
		}
		$session = $this->cookie->getCookie('auth');
		$auth = R::findOne('auth',
			' session = :session ',
			array(':session' => $session));
		if (!$auth){
			$this->cookie->setCookie('auth', null);
			return false;
		}
		return true;
	}
	public function operator(){
		$session = $this->cookie->getCookie('auth');
		$auth = R::findOne('auth',
			' session = :session ',
			array(':session' => $session));
		return $auth->operator;
	}
	public function whoIs(){
		if (!$this->isLoggedIn()){
			return false;
		}
		$operator = $this->operator();
		return $operator->login;
	}
	public function group(){
		if (!$this->isLoggedIn()){
			return array('guest');
		}
		$res = array('operator');
		$operator = $this->operator();
		if ($operator->admin){
			$res[] = 'admin';
		}
		return $res;
	}
	public function generateSession($login){
		$timestamp = time();
		$data = rand().$login.$timestamp;
		$hashed_data = Model_Operator::hashPassword($data);
		return $hashed_data;
	}
	public function login($login, $password){
		$password = Model_Operator::hashPassword($password);
		$operator = R::findOne('operator',
			' login = :login and password = :password',
			array(':login' => $login,
				':password' => $password));
		if (!$operator){
			$session = $this->cookie->getCookie('auth');
			$auth = R::findOne('auth',
				' session = :session ',
				array(':session' => $session));
			if ($auth){
				R::trash($auth);
			}
			return false;
		}
		$session = $this->generateSession($login);
		$auth = R::dispense('auth');
		$auth->session = $session;
		$auth->operator = $operator;
		R::store($auth);
		$this->cookie->setCookie('auth', $session);
		return true;
	}
	public function register($login, $password, $email){
		$operator = R::findOne('operator',
			' login = :login ',
			array(':login' => $login));
		if ($operator){
			return false;
		}
		$count = R::count('operator', ' admin ');
		if ($count > 0){
			$group = $this->group();
			if (!in_array('admin', $group)){
				return false;
			}
		}
		$operator = R::dispense('operator');
		$operator->login = $login;
		$operator->password = $password;
		$operator->hashed = false;
		$operator->email = $email;
		if ($count == 0){
			$operator->admin = true;
		}
		R::store($operator);
		return true;
	}
	public function changePassword($old, $new){
		$operator = $this->operator();
		if (!$this->login($operator->login, $old)){
			return false;
		}
		$operator->password = $new;
		$operator->hashed = false;
		R::store($operator);
		return true;
	}
	public function logoff(){
		$session = $this->cookie->getCookie('auth');
		$auth = R::findOne('auth',
			' session = :session ',
			array(':session' => $session));
		if ($auth){
			R::trash($auth);
		}
		$this->cookie->setCookie('auth', null);
	}
	public function resetPassword($login, $new){
		$group = $this->group();
		if (!in_array('admin', $group)){
			return false;
		}
		$operator = R::findOne('operator',
			' login = :login ',
			array(':login' => $login));
		if (!$operator){
			return false;
		}
		$operator->password = $new;
		$operator->hashed = false;
		R::store($operator);
		return true;
	}
}


interface ICookie {
	// returns cookie with name
	public function getCookie($name, $default = null);
	// sets cookie
	public function setCookie($name, $value, $extra = array('secure' => true));
}


class Cookie implements ICookie {
	// returns cookie with name
	public function getCookie($name, $default = null){
		$bean = R::findOne('testcookie', 
			' name = :name ', 
			array(':name' => $name));
		if (!$bean){
			return $default;
		}
		return $bean->value;
	}
	// sets cookie
	public function setCookie($name, $value, $extra = array('secure' => true)){
		$bean = R::findOne('testcookie',
			' name = :name ', 
			array(':name' => $name));
		if (!$bean){
			$bean = R::dispense('testcookie');
			$bean->name = $name;
		}
		$bean->value = $value;
		$bean->extra = json_encode($extra);
		R::store($bean);
	}
}

class Model_Operator extends RedBean_SimpleModel {
	static function hashPassword($password){
		return hash('sha256', $password);
	}
	function update(){
		if (!$this->hashed){
			$this->password = Model_Operator::hashPassword($this->password);
			$this->hashed = true;
		}
	}
	function delete(){
		$auth = new Auth();
		$group = $auth->group();
		must(in_array('admin', $group),
			"You are not allowed to delete operators");
		unset($auth);
	}
}

class Cookie_CI extends CI_Model implements ICookie {
	public function __construct(){
		parent::__construct();
	}
	// returns cookie with name
	public function getCookie($name, $default = null){
		$value = $this->input->cookie($name, TRUE);
		if ($value === false){
			return $default;
		}
		return $value;
	}
	// sets cookie
	public function setCookie($name, $value, $extra = array('secure' => false, 'expire' => 86500)){
		$data = $extra;
		$data['name'] = $name;
		$data['value'] = $value;
		$this->input->set_cookie($data);
	}
}
