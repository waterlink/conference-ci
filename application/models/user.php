<?php

$validating = false;

class Model_User extends RedBean_SimpleModel {
//    private $validating = false;
	public function update(){
        global $validating;
        if ($validating){ return; }
        $validating = true;
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
        $validating = false;
	}
	public function open(){
        global $validating;
        if ($validating){ return; }
        $validating = true;
		$auth = new Auth();
		$group = $auth->group();
		must(in_array('operator', $group),
			"You are not allowed to view users");
		unset($auth);
        $validating = false;
	}
	public function delete(){
        global $validating;
        if ($validating){ return; }
        $validating = true;
		$auth = new Auth();
		$group = $auth->group();
		must(in_array('admin', $group),
			"You are not allowed to remove users");
		unset($auth);
	    $validating = false;
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
		$needEmailValidation = !$user->id;
		$user = User::_insensitiveFields($user);
		R::store($user);
		if ($needEmailValidation) {
			$this->config->load("mailgun");
			$this->load->library("Mailgun", $this->config->item("mailgun"));
			$user->validation = false;
			$user->validationcode = hash('sha256', time() + rand());
			R::store($user);
			if (array_key_exists('HTTPS', $_SERVER)) {
				$protocol = ($_SERVER['HTTPS'] != "off") ? "https" : "http";
			} else {
				$protocol = "http";
			}
			// $protocol = (in_array('HTTPS', $_SERVER) && $_SERVER['HTTPS'] != "off") ? "https" : "http";
  			$base = $protocol . "://" . $_SERVER['HTTP_HOST'];
  			$link = $base . "/validate?code=".$user->validationcode;
			$subject = $this->load->view("registration_title", "", true);
			$text = $this->load->view("registration_text", array("user" => $user, "link" => $link), true);
			$html = $this->load->view("registration_message", array("user" => $user, "link" => $link), true);
			$this->mailgun->send_complex_message($user->email, false, $subject, $text, $html);
		}
		return $user;
	}
	function getList($skip = false, $limit = false, $query = ' 1 = 1 ', $opts = array()){
		$query .= ' and validation order by id desc ';
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
	function update($bean, $id = false){
		if (!$id){
			R::store($bean);
		} else {
			$obj = R::load("user", $id);
			$obj->import($bean);
			R::store($obj);
		}
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

	function oldIsValidated(){
		foreach (R::findAll("user") as $user){
			$user->validation = true;
			$user->validationcode = hash('sha256', time() + rand());
			R::store($user);
			echo ".";
		}
	}

}
