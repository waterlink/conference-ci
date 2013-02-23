<?php

// Тесты
class Testcases {}

// Получить данные о пользователе с указанным id
// Если есть в базе
class test_user_getById {
	private $checks = array();
	function setup(){
		R::wipe('user');

		$user = R::dispense('user');
		$user->name = 'alex';
		$id = R::store($user);
		$this->checks[] = array($id, $user->name);

		$user = R::dispense('user');
		$user->name = 'dima';
		$id = R::store($user);
		$this->checks[] = array($id, $user->name);

		$user = R::dispense('user');
		$user->name = 'onotole';
		$id = R::store($user);
		$this->checks[] = array($id, $user->name);
	}

	function test(){
		$user = new User();
		for ($i = 0; $i < count($this->checks); ++$i){
			$id = $this->checks[$i][0];
			debug('id: '.$id);
			$got_user = $user->getById($this->checks[$i][0]);
			must($got_user->id, "test_user_getById:: User not found by id: $id");
			$name1 = $got_user->name;
			$name2 = $this->checks[$i][1];
			must(
				$got_user->name == $this->checks[$i][1],
				"test_user_getById:: got_user->name == this->checks[i][1], but $name1 != $name2"
			);
		}
		unset($user);
	}

	function teardown(){
		R::wipe('user');
	}
}

// Получить данные о пользователе с указанным id
// Если нет в базе, должен вернуть false
class test_user_getById_notFound {
	function setup(){ R::wipe('user'); }
	function test(){
		$user = new User();
		$got_user = $user->getById(235);
		must($got_user === false, "test_user_getById_notFound:: expected false, got $got_user");
		unset($user);
	}
	function teardown(){ R::wipe('user'); }
}

// Создать пользователя
class test_user_create {
	function setup(){ R::wipe('user'); }
	function test(){
		$user = new User();
		$got_user = $user->create(array('name' => 'alex', 'surname' => 'fedorov'));
		debug('got_user: '.$got_user);
		must($got_user, 'test_user_create:: create returned nothing');
		must($got_user->id, 'test_user_create:: new user not stored in db');
		must($got_user->name == 'alex', "test_user_create:: new user name is wrong: ".$got_user->name." != ".'alex');
		must($got_user->surname == 'fedorov', "test_user_create:: new user surname is wrong: ".$got_user->surname." != ".'fedorov');
		$db_user = R::load('user', $got_user->id);
		debug('db_user: '.$db_user);
		must($db_user->id, "test_user_create:: not found this user in db, id: ".$got_user->id);
		must($db_user->id == $got_user->id, "test_user_create:: stored id and db id not match: ".$got_user->id." != ".$db_user->id);
		must(
			$db_user->name == $got_user->name, 
			"test_user_create:: stored name and db name not match: ".$got_user->name." != ".$db_user->name
		);
		must(
			$db_user->surname == $got_user->surname, 
			"test_user_create:: stored surname and db surname not match: ".$got_user->surname." != ".$db_user->surname
		);
		unset($user);
	}
	function teardown(){ R::wipe('user'); }
}

// Получить список пользователей в обратном хронологическом порядке
class test_user_getList {
	private $checks = array();
	function setup(){
		R::wipe('user');

		$user = R::dispense('user');
		$user->name = 'alex';
		$id = R::store($user);
		$this->checks[] = array($id, $user);

		$user = R::dispense('user');
		$user->name = 'dima';
		$id = R::store($user);
		$this->checks[] = array($id, $user);

		$user = R::dispense('user');
		$user->name = 'onotole';
		$id = R::store($user);
		$this->checks[] = array($id, $user);

		$this->checks = array_reverse($this->checks);
	}
	function test(){
		$user = new User();
		$got_user = $user->getList();
		must(is_array($got_user), "test_user_getList:: it must be list");
		must(
			count($got_user) == count($this->checks), 
			"test_user_getList:: wrong count: ".count($got_user)." != ".count($this->checks)
		);
		for ($i = 0; $i < count($this->checks); ++$i){
			$got_user_curr = $got_user[$i];
			debug('got: '.$got_user_curr);
			$check_user = $this->checks[$i][1];
			debug('check: '.$check_user);
			must(
				$got_user_curr->id == $check_user->id,
				"test_user_getList:: must be equal: ".$got_user_curr." != ".$check_user
			);
			must(
				$got_user_curr->name == $check_user->name,
				"test_user_getList:: must be equal: ".$got_user_curr." != ".$check_user
			);
		}
		unset($user);
	}
	function teardown(){ R::wipe('user'); }
}

// Получить список пользователей в обратном хронологическом порядке
// Используя skip и limit
class test_user_getList_skipAndLimit {
	function setup(){
		R::wipe('user');

		$user = R::dispense('user');
		$user->name = 'alex';
		$id = R::store($user);
		$this->checks[] = array($id, $user);

		$user = R::dispense('user');
		$user->name = 'dima';
		$id = R::store($user);
		$this->checks[] = array($id, $user);

		$user = R::dispense('user');
		$user->name = 'onotole';
		$id = R::store($user);
		$this->checks[] = array($id, $user);

		$user = R::dispense('user');
		$user->name = 'alexander';
		$id = R::store($user);
		$this->checks[] = array($id, $user);

		$user = R::dispense('user');
		$user->name = 'wolf';
		$id = R::store($user);
		$this->checks[] = array($id, $user);

		$user = R::dispense('user');
		$user->name = 'kracken';
		$id = R::store($user);
		$this->checks[] = array($id, $user);

		$this->checks = array_reverse($this->checks);
		$this->provider = array(
			array(':skip' => 0, ':limit' => 1),
			array(':skip' => 0, ':limit' => 2),
			array(':skip' => 0, ':limit' => 5),
			array(':skip' => 1, ':limit' => 4),
			array(':skip' => 2, ':limit' => 4),
			array(':skip' => 4, ':limit' => 1),
			array(':skip' => 4, ':limit' => 2),
		);
	}
	function test(){
		$user = new User();
		foreach ($this->provider as $data){
			debug("");
			$skip = $data[':skip'];
			$limit = $data[':limit'];
			$got_user = $user->getList($skip, $limit);
			must(is_array($got_user), 
				"test_user_getList_skipAndLimit:: it must be list");
			$cnt = count($got_user);
			$cnt2 = $limit;
			must($cnt == $cnt2,
				"test_user_getList_skipAndLimit:: must be exactly $cnt2 users, but $cnt");
			for ($i = $skip; $i < $limit + $skip; ++$i){
				$got_user_curr = $got_user[$i - $skip];
				debug('got: '.$got_user_curr);
				$check_user = $this->checks[$i][1];
				debug('check: '.$check_user);
				must(
					$got_user_curr->id == $check_user->id,
					"test_user_getList:: must be equal: ".$got_user_curr." != ".$check_user
				);
				must(
					$got_user_curr->name == $check_user->name,
					"test_user_getList:: must be equal: ".$got_user_curr." != ".$check_user
				);
			}
		}
		unset($user);
	}
	function teardown(){ R::wipe('user'); }
}

// Filtered list for users (participant/listener; new/email/paid)
class test_user_getListFiltered {
	function setup(){
		R::wipe('user');

		$user = R::dispense('user');
		$user->name = 'alex';
		$user->participant = true;
		$user->state = 'paid';
		$id = R::store($user);
		$this->checks[] = array($id, $user);

		$user = R::dispense('user');
		$user->name = 'dima';
		$user->participant = false;
		$user->state = 'emailsent';
		$id = R::store($user);
		$this->checks[] = array($id, $user);

		$user = R::dispense('user');
		$user->name = 'onotole';
		$user->participant = false;
		$user->state = 'new';
		$id = R::store($user);
		$this->checks[] = array($id, $user);

		$user = R::dispense('user');
		$user->name = 'alexander';
		$user->participant = false;
		$user->state = 'new';
		$id = R::store($user);
		$this->checks[] = array($id, $user);

		$user = R::dispense('user');
		$user->name = 'wolf';
		$user->participant = true;
		$user->state = 'new';
		$id = R::store($user);
		$this->checks[] = array($id, $user);

		$user = R::dispense('user');
		$user->name = 'kracken';
		$user->participant = false;
		$user->state = 'new';
		$id = R::store($user);
		$this->checks[] = array($id, $user);

		$this->checks = array_reverse($this->checks);
		$this->provider = array(
			array(':skip' => 0, ':limit' => 5, ':participant' => null, ':state' => null),
			array(':skip' => 0, ':limit' => 5, ':participant' => null, ':state' => 'new'),
			array(':skip' => 0, ':limit' => 1, ':participant' => true, ':state' => 'new'),
			array(':skip' => 0, ':limit' => 2, ':participant' => false, ':state' => 'new'),
			array(':skip' => 0, ':limit' => 5, ':participant' => false, ':state' => 'emailsent'),
			array(':skip' => 1, ':limit' => 4, ':participant' => true, ':state' => 'emailsent'),
			array(':skip' => 2, ':limit' => 4, ':participant' => false, ':state' => 'paid'),
			array(':skip' => 4, ':limit' => 1, ':participant' => true, ':state' => 'paid'),
			array(':skip' => 4, ':limit' => 2, ':participant' => false, ':state' => 'new'),
		);
	}
	function test(){
		$user = new User();
		foreach ($this->provider as $data){
			debug("");
			$skip = $data[':skip'];
			$limit = $data[':limit'];
			$participant = $data[':participant'];
			$state = $data[':state'];
			$got_user = $user->getListFiltered($participant, $state, $skip, $limit);
			$checks_filtered = array();
			for ($i = 0; $i < count($this->checks); ++$i){
				if (($this->checks[$i][1]->participant == $participant || $participant === null) &&
					($this->checks[$i][1]->state == $state || $state === null)){
					$checks_filtered[] = $this->checks[$i];
				}
			}
			for ($i = $skip; $i < $limit + $skip && $i < count($checks_filtered); ++$i){
				$got_user_curr = $got_user[$i - $skip];
				debug('got: '.$got_user_curr);
				$check_user = $checks_filtered[$i][1];
				debug('check: '.$check_user);
				must(
					$got_user_curr->id == $check_user->id,
					"test_user_getListFiltered:: must be equal: ".$got_user_curr." != ".$check_user
				);
				must(
					$got_user_curr->name == $check_user->name,
					"test_user_getListFiltered:: name must be equal: ".$got_user_curr." != ".$check_user
				);
				must(
					$got_user_curr->participant == $check_user->participant,
					"test_user_getListFiltered:: participant must be equal: ".$got_user_curr." != ".$check_user
				);
				must(
					$got_user_curr->state == $check_user->state,
					"test_user_getListFiltered:: state must be equal: ".$got_user_curr." != ".$check_user
				);
			}
		}
		unset($user);
	}
	function teardown(){ R::wipe('user'); }
}

class test_user_create_wrongState {
	function setup(){ R::wipe('user'); }
	function test(){
		$user = new User();
		function createUserWithWrongState($args){
			$user = $args['user'];
			$user->create(array(
				'name' => 'alex',
				'state' => 'wrong_state'
			));
		}
		must_throw('createUserWithWrongState', array('user' => $user),
			"test_user_create_wrongState:: wrong state must throw exception");
		unset($user);
	}
	function teardown(){ R::wipe('user'); }
}

class test_user_update {
	function setup(){
		R::wipe('user');

		$bean = R::dispense('user');
		$bean->name = 'alex';
		$id = R::store($bean);

		$this->checks = array($id, $bean);
	}
	function test(){
		$user = new User();
		$bean = $this->checks[1];
		$bean->name = 'Alexey';
		$bean->surname = 'Fedorov';
		$user->update($bean);
		$bean_db = R::load('user', $this->checks[0]);
		must($bean->name == $bean_db->name,
			"test_user_update:: update must be done successfully");
		must($bean->surname == $bean_db->surname,
			"test_user_update:: update must be done successfully");
		unset($user);
	}
	function teardown(){ R::wipe('user'); }
}

class test_cookie {
	function setup(){
		R::wipe('testcookie');
	}
	function test(){
		$cookie = new Cookie();
		unset($cookie);
	}
	function teardown(){
		R::wipe('testcookie');
	}
}

class test_cookie_implements_icookie {
	function setup(){
		R::wipe('testcookie');
	}
	function test(){
		$cookie = new Cookie();
		must(is_a($cookie, 'ICookie'),
			"test_cookie_implements_icookie:: cookie must implement ICookie");
		unset($cookie);
	}
	function teardown(){
		R::wipe('testcookie');
	}
}

class test_cookie_getCookie {
	function setup(){
		R::wipe('testcookie');

		$bean = R::dispense('testcookie');
		$bean->name = 'hello';
		$bean->value = 'world';
		R::store($bean);
	}
	function test(){
		$cookie = new Cookie();
		$value = $cookie->getCookie('hello');
		must($value == 'world',
			"test_cookie_getCookie:: value mismatch: ".$value." != world");
		unset($cookie);
	}
	function teardown(){
		R::wipe('testcookie');
	}
}

class test_cookie_getCookie_notExists {
	function setup(){
		R::wipe('testcookie');

		$bean = R::dispense('testcookie');
		$bean->name = 'hello';
		$bean->value = 'world';
		R::store($bean);
	}
	function test(){
		$cookie = new Cookie();
		$value = $cookie->getCookie('hellyeah');
		must($value === null,
			"test_cookie_getCookie_notExists:: non existant must be null, but: ".$value);
		$value = $cookie->getCookie('helloworld', false);
		must($value === false,
			"test_cookie_getCookie_notExists:: non existant must be false, but: ".$value);
		unset($cookie);
	}
	function teardown(){
		R::wipe('testcookie');
	}
}

class test_cookie_setCookie {
	function setup(){
		R::wipe('testcookie');
	}
	function test(){
		$cookie = new Cookie();
		$extra = array('secure' => true,
			'domain' => 'localhost');
		$cookie->setCookie('hello', 
			'world', 
			$extra);
		$bean = R::findOne('testcookie', 
			' name = :name ',
			array(':name' => 'hello'));
		must($bean,
			"test_cookie_setCookie:: unable to find cookie");
		must($bean->name == 'hello',
			"test_cookie_setCookie:: cookie name mismatch: hello !=".$bean->name);
		must($bean->value == 'world',
			"test_cookie_setCookie:: cookie value mismatch: world != ".$bean->value);
		must($bean->extra == json_encode($extra),
			"test_cookie_setCookie:: cookie extra mismatch: ".json_encode($extra)." != ".$bean->extra);
		unset($cookie);
	}
	function teardown(){
		R::wipe('testcookie');
	}
}

class test_cookie_setCookie_uniqueName {
	function setup(){
		R::wipe('testcookie');
	}
	function test(){
		$cookie = new Cookie();
		$cookie->setCookie('hello', 'world');
		$cookie->setCookie('hello', 'alex');
		$count = R::count('testcookie');
		must($count == 1,
			"test_cookie_setCookie_uniqueName:: name is not unique");
		unset($cookie);
	}
	function teardown(){
		R::wipe('testcookie');
	}
}

class test_cookie_setCookie_getCookie {
	function setup(){
		R::wipe('testcookie');
	}
	function test(){
		$cookie = new Cookie();
		$name = 'hello';
		$value = 'world';
		$cookie->setCookie($name, $value);
		$got_value = $cookie->getCookie($name);
		must($got_value == $value,
			"test_cookie_setCookie_getCookie:: values must match:".$value." != ".$got_value);
		unset($cookie);
	}
	function teardown(){
		R::wipe('testcookie');
	}
}

class test_auth_setupCookie {
	function setup(){
		R::wipe('auth');
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie(new Cookie());
		must(is_a($auth->cookie, 'ICookie'),
			"test_auth_setupCookie:: auth->cookie must implement ICookie");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
	}
}

class __test_auth_setupCookie_wrongCookieClass_Dummy {}
class test_auth_setupCookie_wrongCookieClass {
	function setup(){
		R::wipe('auth');
	}
	function test(){
		$auth = new Auth();
		must_throw(function($args){
			$args[':auth']->setupCookie(new __test_auth_setupCookie_wrongCookieClass_Dummy());
		}, array(':auth' => $auth),
			"test_auth_setupCookie_wrongCookieClass:: auth->setupCookie must throw exception when wrong cookie class passed");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
	}
}

class test_auth_isGuest_notAuthenticated {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie(new Cookie());
		must($auth->isGuest(),
			"test_auth_isGuest_notAuthenticated:: isGuest must return true when not authenticated");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
	}
}

class test_auth_isGuest_authenticated {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');

		$this->cookie = new Cookie();
		$this->cookie->setCookie('auth', '4436754abba474f3244f23f423fe');
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		must(!$auth->isGuest(),
			"test_auth_isGuest_authenticated:: isGuest must return false when authenticated");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
	}
}

class test_auth_isLoggedIn {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();
		$this->cookie->setCookie('auth', '4436754abba474f3244f23f423fe');

		$operator = R::dispense('operator');
		$operator->login = 'alex';
		R::store($operator);

		$bean = R::dispense('auth');
		$bean->session = '4436754abba474f3244f23f423fe';
		$bean->operator = $operator;
		R::store($bean);
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		must($auth->isLoggedIn(),
			"test_auth_isLoggedIn:: auth->isLoggedIn must return true");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_isLoggedIn_whenGuest {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		must(!$auth->isLoggedIn(),
			"test_auth_isLoggedIn_whenGuest:: auth->isLoggedIn must return false");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_isLoggedIn_whenLoggedOff {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();
		$this->cookie->setCookie('auth', '4436754abba474f3244f23f423fe');
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		must(!$auth->isLoggedIn(),
			"test_auth_isLoggedIn_whenLoggedOff:: auth->isLoggedIn must return false");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_isLoggedIn_whenLoggedOff_mustUnsetCookie {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();
		$this->cookie->setCookie('auth', '4436754abba474f3244f23f423fe');
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		must(!$auth->isLoggedIn(),
			"test_auth_isLoggedIn_whenLoggedOff:: auth->isLoggedIn must return false");
		must($this->cookie->getCookie('auth') === null,
			"test_auth_isLoggedIn_whenLoggedOff_mustUnsetCookie:: auth cookie is still set");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_whoIs {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();
		$this->cookie->setCookie('auth', '4436754abba474f3244f23f423fe');

		$operator = R::dispense('operator');
		$operator->login = 'alex';
		R::store($operator);

		$bean = R::dispense('auth');
		$bean->session = '4436754abba474f3244f23f423fe';
		$bean->operator = $operator;
		R::store($bean);
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		$login = $auth->whoIs();
		must($login == 'alex',
			"test_auth_whoIs:: login mismatch: alex != ".$login);
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_whoIs_whenGuest {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();
		// $this->cookie->setCookie('auth', '4436754abba474f3244f23f423fe');

		// $operator = R::dispense('operator');
		// $operator->login = 'alex';
		// R::store($operator);

		// $bean = R::dispense('auth');
		// $bean->session = '4436754abba474f3244f23f423fe';
		// $bean->operator = $operator;
		// R::store($bean);
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		$login = $auth->whoIs();
		must(!$login,
			"test_auth_whoIs_whenGuest:: login must be false when guest");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_whoIs_whenLoggedOff {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();
		$this->cookie->setCookie('auth', '4436754abba474f3244f23f423fe');

		// $operator = R::dispense('operator');
		// $operator->login = 'alex';
		// R::store($operator);

		// $bean = R::dispense('auth');
		// $bean->session = '4436754abba474f3244f23f423fe';
		// $bean->operator = $operator;
		// R::store($bean);
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		$login = $auth->whoIs();
		must(!$login,
			"test_auth_whoIs_whenLoggedOff:: login must be false when logged off");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_group_whenGuest {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();
		// $this->cookie->setCookie('auth', '4436754abba474f3244f23f423fe');

		// $operator = R::dispense('operator');
		// $operator->login = 'alex';
		// R::store($operator);

		// $bean = R::dispense('auth');
		// $bean->session = '4436754abba474f3244f23f423fe';
		// $bean->operator = $operator;
		// R::store($bean);
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		$group = $auth->group();
		must($group == array('guest'),
			"test_auth_group_whenGuest:: group must be guest");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_group_whenLoggedOff {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();
		$this->cookie->setCookie('auth', '4436754abba474f3244f23f423fe');

		// $operator = R::dispense('operator');
		// $operator->login = 'alex';
		// R::store($operator);

		// $bean = R::dispense('auth');
		// $bean->session = '4436754abba474f3244f23f423fe';
		// $bean->operator = $operator;
		// R::store($bean);
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		$group = $auth->group();
		must($group == array('guest'),
			"test_auth_group_whenLoggedOff:: group must be guest");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_group_whenOperator {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();
		$this->cookie->setCookie('auth', '4436754abba474f3244f23f423fe');

		$operator = R::dispense('operator');
		$operator->login = 'alex';
		R::store($operator);

		$bean = R::dispense('auth');
		$bean->session = '4436754abba474f3244f23f423fe';
		$bean->operator = $operator;
		R::store($bean);
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		$group = $auth->group();
		must($group == array('operator'),
			"test_auth_group_whenOperator:: group must be operator");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_group_whenAdmin {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();
		$this->cookie->setCookie('auth', '4436754abba474f3244f23f423fe');

		$operator = R::dispense('operator');
		$operator->login = 'alex';
		$operator->admin = true;
		R::store($operator);

		$bean = R::dispense('auth');
		$bean->session = '4436754abba474f3244f23f423fe';
		$bean->operator = $operator;
		R::store($bean);
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		$group = $auth->group();
		must($group == array('operator', 'admin'),
			"test_auth_group_whenAdmin:: group must be operator, admin");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_operatorModel_passwordMustStoreAsHash {
	function setup(){
		R::wipe('operator');
	}
	function test(){
		$operator = R::dispense('operator');
		$operator->login = 'alex';
		$operator->password = 'helloworld';
		$operator->admin = true;
		$id = R::store($operator);
		$operator = R::load('operator', $id);
		must($operator->password != 'helloworld',
			"test_operatorModel_passwordMustStoreAsHash:: password must now match its hashed version, maybe you forgot do some hashing in your model?");
	}
	function teardown(){
		R::wipe('operator');
	}
}

class test_auth_login {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();

		$operator = R::dispense('operator');
		$operator->login = 'alex';
		$operator->password = 'helloworld';
		$operator->admin = true;
		R::store($operator);
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		must($auth->login('alex', 'helloworld'),
			"test_auth_login:: login must be successfull");
		must($auth->whoIs() == 'alex',
			"test_auth_login:: cookie must be set after login");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_login_invalidLogin {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();

		$operator = R::dispense('operator');
		$operator->login = 'alex';
		$operator->password = 'helloworld';
		$operator->admin = true;
		R::store($operator);
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		must(!$auth->login('alex', 'helliworld'),
			"test_auth_login_invalidLogin:: login must not be successfull");
		must($auth->whoIs() == false,
			"test_auth_login_invalidLogin:: whois must return false for invalid logins");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_register_whenNoOperatorsExists {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		must($auth->register('alex', 'helloworld'),
			"test_auth_register_whenNoOperatorsExists:: register must be successfull");
		must($auth->login('alex', 'helloworld'),
			"test_auth_register_whenNoOperatorsExists:: login must be successfull");
		must($auth->whoIs() == 'alex',
			"test_auth_register_whenNoOperatorsExists:: cookie must be set after login");
		$operator = R::findOne('operator',
			' login = :login ',
			array(':login' => 'alex'));
		must($operator->admin,
			"test_auth_register_whenNoOperatorsExists:: first operator must be admin");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_register_whenThereAreOperators_NotLoggedIn {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();

		$operator = R::dispense('operator');
		$operator->login = 'alex';
		$operator->password = 'helloworld';
		$operator->admin = true;
		R::store($operator);

		$operator = R::dispense('operator');
		$operator->login = 'waterlink';
		$operator->password = 'bgatest';
		$operator->admin = false;
		R::store($operator);
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		must(!$auth->register('tester', 'test'),
			"test_auth_register_whenThereAreOperators_NotLoggedIn:: register must not be successfull");
		must(!$auth->login('tester', 'test'),
			"test_auth_register_whenThereAreOperators_NotLoggedIn:: login must not be successfull");
		must($auth->whoIs() == false,
			"test_auth_register_whenThereAreOperators_NotLoggedIn:: whoIs must return false");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_register_whenThereAreOperators_whenAdmin {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();

		$operator = R::dispense('operator');
		$operator->login = 'alex';
		$operator->password = 'helloworld';
		$operator->admin = true;
		R::store($operator);

		$operator = R::dispense('operator');
		$operator->login = 'waterlink';
		$operator->password = 'bgatest';
		$operator->admin = false;
		R::store($operator);
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		$auth->login('alex', 'helloworld');
		must($auth->register('tester', 'test'),
			"test_auth_register_whenThereAreOperators_whenAdmin:: register must be successfull");
		must($auth->login('tester', 'test'),
			"test_auth_register_whenThereAreOperators_whenAdmin:: login must be successfull");
		must($auth->whoIs() == 'tester',
			"test_auth_register_whenThereAreOperators_whenAdmin:: whoIs must return tester");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_register_whenThereAreOperators_whenOperator {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();

		$operator = R::dispense('operator');
		$operator->login = 'alex';
		$operator->password = 'helloworld';
		$operator->admin = true;
		R::store($operator);

		$operator = R::dispense('operator');
		$operator->login = 'waterlink';
		$operator->password = 'bgatest';
		$operator->admin = false;
		R::store($operator);
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		$auth->login('waterlink', 'bgatest');
		must(!$auth->register('tester', 'test'),
			"test_auth_register_whenThereAreOperators_whenOperator:: register must not be successfull");
		must(!$auth->login('tester', 'test'),
			"test_auth_register_whenThereAreOperators_whenOperator:: login must not be successfull");
		must($auth->whoIs() == false,
			"test_auth_register_whenThereAreOperators_whenOperator:: whoIs must return false");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_register_loginIsUnique {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();

		$operator = R::dispense('operator');
		$operator->login = 'alex';
		$operator->password = 'helloworld';
		$operator->admin = true;
		R::store($operator);

		$operator = R::dispense('operator');
		$operator->login = 'waterlink';
		$operator->password = 'bgatest';
		$operator->admin = false;
		R::store($operator);
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		$auth->login('alex', 'helloworld');
		$auth->register('someoperator', 'bgashecka');
		$auth->register('someoperator', 'whatthefuck');
		$count = R::count('operator');
		must($count > 2,
			"test_auth_register_loginIsUnique:: no registers where done");
		must($count == 3,
			"test_auth_register_loginIsUnique:: login should be unique");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_register_whenThereAreOperators_whenAdmin_createOperator {
	function setup(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');

		$this->cookie = new Cookie();

		$operator = R::dispense('operator');
		$operator->login = 'alex';
		$operator->password = 'helloworld';
		$operator->admin = true;
		R::store($operator);

		$operator = R::dispense('operator');
		$operator->login = 'waterlink';
		$operator->password = 'bgatest';
		$operator->admin = false;
		R::store($operator);
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		$auth->login('alex', 'helloworld');
		must($auth->register('tester', 'test'),
			"test_auth_register_whenThereAreOperators_whenAdmin_createOperator:: register must be successfull");
		must($auth->login('tester', 'test'),
			"test_auth_register_whenThereAreOperators_whenAdmin_createOperator:: login must be successfull");
		must($auth->whoIs() == 'tester',
			"test_auth_register_whenThereAreOperators_whenAdmin_createOperator:: whoIs must return tester");
		must($auth->group() == array('operator'),
			"test_auth_register_whenThereAreOperators_whenAdmin_createOperator:: all consequent registers must be operators");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('testcookie');
		R::wipe('operator');
	}
}

class test_auth_changePassword {
	function setup(){
		R::wipe('auth');
		R::wipe('operator');
		R::wipe('testcookie');

		$this->cookie = new Cookie();

		$auth = new Auth();
		$auth->setupCookie($this->cookie);

		$auth->register('alex', 'helloworld');
		$auth->login('alex', 'helloworld');
		$auth->register('bga', 'test');
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		$auth->login('bga', 'test');
		$auth->changePassword('test', 'testing');

		must(!$auth->login('bga', 'test'),
			"test_auth_changePassword:: login with old password must fail");
		must($auth->login('bga', 'testing'),
			"test_auth_changePassword:: login with new password must success");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('operator');
		R::wipe('testcookie');
	}
}

class test_auth_changePassword_wrongOldPassword {
	function setup(){
		R::wipe('auth');
		R::wipe('operator');
		R::wipe('testcookie');

		$this->cookie = new Cookie();

		$auth = new Auth();
		$auth->setupCookie($this->cookie);

		$auth->register('alex', 'helloworld');
		$auth->login('alex', 'helloworld');
		$auth->register('bga', 'test');
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);
		$auth->login('bga', 'test');

		must(!$auth->changePassword('tesdsfat', 'testing'),
			"test_auth_changePassword_wrongOldPassword:: changePassword must fail if old password is wrong");
		must($auth->login('bga', 'test'),
			"test_auth_changePassword_wrongOldPassword:: login with old password must success");
		must(!$auth->login('bga', 'testing'),
			"test_auth_changePassword_wrongOldPassword:: login with new password must fail");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('operator');
		R::wipe('testcookie');
	}
}

class test_auth_logoff {
	function setup(){
		R::wipe('auth');
		R::wipe('operator');
		R::wipe('testcookie');

		$this->cookie = new Cookie();
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);

		$auth->register('alex', 'helloworld');
		$auth->login('alex', 'helloworld');
		$auth->logoff();

		must($auth->isGuest(),
			"test_auth_logoff:: after logoff must be as guest");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('operator');
		R::wipe('testcookie');
	}
}

class test_auth_resetPassword_whenAdmin {
	function setup(){
		R::wipe('auth');
		R::wipe('operator');
		R::wipe('testcookie');

		$this->cookie = new Cookie();

		$auth = new Auth();
		$auth->setupCookie($this->cookie);

		$auth->register('alex', 'helloworld');
		$auth->login('alex', 'helloworld');

		$auth->register('opuser', 'opuser');
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);

		must($auth->resetPassword('opuser', 'testing'),
			"test_auth_resetPassword_whenAdmin:: admin must be able to reset any password");
		must(!$auth->login('opuser', 'opuser'),
			"test_auth_resetPassword_whenAdmin:: old password must not login");
		must($auth->login('opuser', 'testing'),
			"test_auth_resetPassword_whenAdmin:: new password must login");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('operator');
		R::wipe('testcookie');
	}
}

class test_auth_resetPassword_whenNotAdmin {
	function setup(){
		R::wipe('auth');
		R::wipe('operator');
		R::wipe('testcookie');

		$this->cookie = new Cookie();

		$auth = new Auth();
		$auth->setupCookie($this->cookie);

		$auth->register('alex', 'helloworld');
		$auth->login('alex', 'helloworld');

		$auth->register('opuser', 'opuser');
		$auth->register('operator', 'bgatest');
	}
	function test(){
		$auth = new Auth();
		$auth->setupCookie($this->cookie);

		$auth->login('operator', 'bgatest');

		must(!$auth->resetPassword('opuser', 'testing'),
			"test_auth_resetPassword_whenNotAdmin:: operator must not be able to reset any password");
		must($auth->login('opuser', 'opuser'),
			"test_auth_resetPassword_whenNotAdmin:: old password must login");
		must(!$auth->login('opuser', 'testing'),
			"test_auth_resetPassword_whenNotAdmin:: new password must not login");
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
		R::wipe('operator');
		R::wipe('testcookie');
	}
}

class test_auth_defaultCookieIsCI {
	function setup(){

	}
	function test(){
		$auth = new Auth();
		must(is_a($auth->cookie, 'Cookie_CI'),
			"test_auth_defaultCookieIsCI:: auth->cookie by default must be set to new Cookie_CI in its constructor; use this->setupCookie(new Cookie_CI());");
		unset($auth);
	}
	function teardown(){

	}
}

class test_auth {
	function setup(){
		R::wipe('auth');
	}
	function test(){
		$auth = new Auth();
		unset($auth);
	}
	function teardown(){
		R::wipe('auth');
	}
}

class test_test_runTests {
	function setup(){}
	function test(){}
	function teardown(){}
}

