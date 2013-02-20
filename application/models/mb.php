<?php

// this is mongobean < redbean implementation for mongo (not full)

// but for mongodb
class RedBean_SimpleModel {}
class CI_Model {}
$R_instance = null;
function objectToArray($d) {
  if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function
		$d = get_object_vars($d);
	}

	if (is_array($d)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return array_map(__FUNCTION__, $d);
	}
	else {
		// Return array
		return $d;
	}
}
function arrayToObject($d) {
	if (is_array($d)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return (object) array_map(__FUNCTION__, $d);
	}
	else {
		// Return object
		return $d;
	}
}
class R {
	public $scheme = array();
	public $models = array();
	static function instance(){
		global $R_instance;
		if ($R_instance === null){
			$R_instance = new R();
		}
		return $R_instance;
	}
	static function setup($constr = null){
		if ($constr === null){
			R::instance()->mongo = new Mongo();
			R::instance()->mongo = R::instance()->mongo->mb_generic;
		} else {
			R::instance()->mongo = new Mongo($constr);
		}
	}
	static function wipe($type){
		R::instance()->mongo->$type->drop();
	}
	static function dispense($type){
		$bean = R::instance()->scheme;
		$bean['id'] = 0;
		$bean['type'] = $type;
		$res = arrayToObject($bean);
		return $res;
	}
	static function store(&$bean){
		$arr = objectToArray($bean);
		$type = $bean->type;
		if (isset(R::instance()->models[$type])){
			R::instance()->models[$type]->update($bean);
		}
		foreach ($arr as $key => $value){
			R::instance()->scheme[$key] = null;
		}
		R::instance()->mongo->$type->save($bean);
		$bean->id = $bean->_id->{'$id'};
		return $bean->id;
	}
	static function load($type, $id){
		$bean = R::instance()->mongo->$type->findOne(array('_id' => new MongoId($id)));
		if (!$bean){
			return R::dispense($type);
		}
		$bean = arrayToObject($bean);
		$bean->id = $bean->_id->{'$id'};
		return $bean;
	}
	static function find($type, $query, $opts){
		$beans = R::instance()->mongo->$type->find($opts);
		if (isset($query['order_by'])){
			$beans = $beans->sort($query['order_by']);
		}
		if (isset($query['limit'])){
			$beans = $beans->limit($query['limit']);
		}
		if (isset($query['skip'])){
			$beans = $beans->skip($query['skip']);
		}
		$res = array();
		foreach ($beans as $bean){
			$bean = arrayToObject($bean);
			$bean->id = $bean->_id->{'$id'};
			$res[] = $bean;
		}
		return $res;
		// return array_reverse($res);
	}
	static function model($type, $model){
		R::instance()->models[$type] = new $model();
	}
}
