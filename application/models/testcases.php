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
	}
	function test(){
		$user = new User();
		$skip = 1;
		$limit = 2;
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
		unset($user);
	}
	function teardown(){ R::wipe('user'); }
}

class test_test_runTests {
	function setup(){}
	function test(){}
	function teardown(){}
}

