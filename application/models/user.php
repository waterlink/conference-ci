<?php

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
	function getList($skip = false, $limit = false){
		$query = ' 1=1 order by id desc ';
		$opts = array();
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
}
