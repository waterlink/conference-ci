<?php

class Model_User extends RedBean_SimpleModel {
	public function update($self){
		$state_allowed_values = array("new", "emailsent", "paid", null);
		$arr = objectToArray($self);
		if (!isset($arr['state'])){
			$self->state = null;
		}
		must(in_array($self->state, $state_allowed_values),
			"Model_User validator:: state must be in ".json_encode($state_allowed_values));
	}
}

R::model('user', 'Model_User');

class Mbuser {}

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
	function getList($skip = false, $limit = false, $query = array('order_by' => array()), $opts = array()){
		if (isset($query['order_by'])){
			$query['order_by']['_id'] = -1;
		}
		if ($limit){
			// $query .= ' limit :limit ';
			$query['limit'] = $limit;
		}
		if ($skip){
			// $query .= ' offset :skip ';
			$query['skip'] = $skip;
		}
		$res = R::find('user', $query, $opts);
		$result = array();
		foreach ($res as $key => $value){
			$result[] = $value;
		}
		return $result;
	}
	function getListFiltered($participant = null, $state = null, $skip = false, $limit = false){
		$query = array('order_by' => array());
		$opts = array();
		if ($participant !== null){
			// $query .= ' and participant = :participant ';
			if ($participant){
				$opts['participant'] = true;
			} else {
				$opts['participant'] = false;
			}
		}
		if ($state !== null){
			// $query .= ' and state = :state ';
			$opts['state'] = $state;
		}
		return $this->getList($skip, $limit, $query, $opts);
	}
}
