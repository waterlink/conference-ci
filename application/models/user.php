<?php

class Model_User extends RedBean_SimpleModel {
	public function update(){
		$status_allowed_values = array("new", "emailsent", "paid", null);
		must(in_array($this->status, $status_allowed_values),
			"Model_User validator:: status must be in ".json_encode($status_allowed_values));
		// check if we have already that email in db
		$got = R::findOne("user", 
			" email = :email and id != :id ", 
			array(
				":email" => $this->email,
				":id" => $this->id
			));
		must(!$got,
			"Model_User validator:: duplicate email");
	}
	public function open(){
		$auth = new Auth();
		$group = $auth->group();
		must(in_array('operator', $group),
			"You are not allowed to view users");
		unset($auth);
	}
	public function delete(){
		$auth = new Auth();
		$group = $auth->group();
		must(in_array('admin', $group),
			"You are not allowed to remove users");
		unset($auth);
	}
}

class User extends CI_Model {
	function getById($id){
		$res = R::load('user', $id);
		if (!$res->id){
			return false;
		}
		return $res;
	}
	function _insensitiveFields($user){
		$user->lowerName = mb_strtolower($user->name);
		$user->lowerSurname = mb_strtolower($user->surname);
		$user->lowerPatronymic = mb_strtolower($user->patronymic);
		$user->email = mb_strtolower($user->email);
		return $user;
	}
	function create($vars){
		$user = R::dispense('user');
		foreach ($vars as $key => $value){
			$user->$key = $value;
		}
		$user = User::_insensitiveFields($user);
		R::store($user);
		return $user;
	}
	function getList($skip = false, $limit = false, $query = ' 1 = 1 ', $opts = array()){
		$query .= ' order by id desc ';
		if ($limit){
			$limit = preg_replace("[^0-9]", "", $limit);
			$query .= " limit $limit ";
			// $opts[':limit'] = $limit;
		}
		if ($skip){
			$skip = preg_replace("[^0-9]", "", $skip);
			$query .= " offset $skip ";
			// $opts[':skip'] = $skip;
		}
		$res = R::find('user', $query, $opts);
		$result = array();
		foreach ($res as $key => $value){
			$result[] = $value;
		}
		return $result;
	}
	function getListFiltered($participant = null, $status = null, $skip = false, $limit = false, $words = array()){
		$query = ' 1 = 1 ';
		$opts = array();
		$wordsCount = min(count($words), 3);
		if ($participant !== null){
			$query .= ' and participant = :participant ';
			if ($participant){
				$opts[':participant'] = "1";
			} else {
				$opts[':participant'] = "0";
			}
		}
		if ($wordsCount > 0){
			for ($i = 0; $i < $wordsCount; ++$i){
				$query .= ' and ( 0 = 1 ';
					foreach (array('lowerName', 'lowerSurname', 'lowerPatronymic') as $field){
						$query .= " or $field like :word$i ";
					}
				$query .= ' ) ';
				$opts[":word$i"] = $words[$i].'%';
			}
		}
		if ($status !== null){
			$query .= ' and status = :status ';
			$opts[':status'] = $status;
		}
		// if ($wordsCount) var_dump(array(':query' => $query, ':opts' => $opts));
		return $this->getList($skip, $limit, $query, $opts);
	}
	function update($bean){
		R::store($bean);
	}
}

class Migrations {

	function removeDuplicateEmails(){
		$flag = false;
		while (!$flag){
			$flag = true;
			foreach (R::findAll("user") as $user){
				$got = R::find("user", 
					" email = :email and id != :id ", 
					array(
						":email" => $user->email,
						":id" => $user->id
					));
				if ($got) foreach ($got as $duplicate){
					$flag = false;
					R::trash($duplicate);
				}
				if (!$flag) break;
				echo ".";
			}
		}
	}

	function lowerCaseNameSurnamePatronymic(){
		foreach (R::findAll("user") as $user){
			$user = User::_insensitiveFields($user);
			R::store($user);
			echo ".";
		}
	}

}
