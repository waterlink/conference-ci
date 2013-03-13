<?php

class Model_User extends RedBean_SimpleModel {
	public function update(){
		$status_allowed_values = array("new", "emailsent", "paid", null);
		must(in_array($this->status, $status_allowed_values),
			"Model_User validator:: status must be in ".json_encode($status_allowed_values));
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
	function create($vars){
		$user = R::dispense('user');
		foreach ($vars as $key => $value){
			$user->$key = $value;
		}
		R::store($user);
		return $user;
	}
	function getList($skip = false, $limit = false, $query = ' 1 = 1 ', $opts = array()){
		$query .= ' order by id desc ';
		if ($limit){
			$query .= ' limit :limit ';
			$opts[':limit'] = $limit;
		}
		if ($skip){
			$query .= ' offset :skip ';
			$opts[':skip'] = $skip;
		}
		$res = R::find('user', $query, $opts);
		$result = array();
		foreach ($res as $key => $value){
			$result[] = $value;
		}
		return $result;
	}
	function getListFiltered($participant = null, $status = null, $skip = false, $limit = false){
		$query = ' 1 = 1 ';
		$opts = array();
		if ($participant !== null){
			$query .= ' and participant = :participant ';
			if ($participant){
				$opts[':participant'] = "1";
			} else {
				$opts[':participant'] = "0";
			}
		}
		if ($status !== null){
			$query .= ' and status = :status ';
			$opts[':status'] = $status;
		}
		return $this->getList($skip, $limit, $query, $opts);
	}
	function update($bean){
		R::store($bean);
	}
}
