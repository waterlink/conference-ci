<?php

function endsWith( $str, $sub ) {
   return ( substr( $str, strlen( $str ) - strlen( $sub ) ) === $sub );
}

function must($cond, $desc){
	if (!$cond){
		throw new Exception($desc);
	}
}

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

class REST_Controller extends CI_Controller {
	protected $request;
	public function __construct(){
		parent::__construct();
		$this->setupRequest(new Request_CI($this));
	}
	public function setupRequest($request){
		$this->request = $request;
	}
	public function getRequestObject(){
		return $this->request;
	}
	public function __call($name, $args){
		$method = strtolower($this->request->method());
		if (method_exists($this, $name.'_'.$method)){
			$res = call_user_func(array($this, $name.'_'.$method));
			echo json_encode($res);
		}
	}
	function _remap($name, $args){
		$method = strtolower($this->request->method());
		if (!$method){
			$method = 'get';
		}
		if (method_exists($this, $name.'_'.$method)){
			try {
				$res = call_user_func_array(array($this, $name.'_'.$method), $args);
			} catch (exception $e) {
				$res = array(
					'error' => $e->getMessage()
				);
			}
			echo json_encode($res);
		} else {
			// return parent::_remap($name, $args);
			return false;
		}
	}
	public function get($name, $default = null){
		return $this->request->get($name, $default);
	}
	public function post($name = null){
		if (!$name){
			return objectToArray(json_decode($this->request->post(null)));
		} else {
			$data = objectToArray(json_decode($this->request->post(null)));
			return $data[$name];
		}
	}
	public function put(){
		if (!$name){
			return objectToArray(json_decode($this->request->post(null)));
		} else {
			$data = objectToArray(json_decode($this->request->post(null)));
			return $data[$name];
		}
	}
	public function delete(){
		if (!$name){
			return objectToArray(json_decode($this->request->post(null)));
		} else {
			$data = objectToArray(json_decode($this->request->post(null)));
			return $data[$name];
		}
	}
}

class rest_test extends REST_Controller {
	public function __construct(){
		parent::__construct();
	}
	public function hello_get(){
		$hello = $this->get('hello');
		return array(
			'result' => 'get '.$hello
		);
	}
	public function hello_put(){
		$put = $this->put();
		$hello = $put['hello'];
		return array(
			'result' => 'put '.$hello
		);
	}
	public function hello_delete(){
		$put = $this->delete();
		$hello = $put['hello'];
		return array(
			'result' => 'delete '.$hello
		);
	}
	public function hello_post(){
		$put = $this->post();
		$hello = $put['hello'];
		return array(
			'result' => 'post '.$hello
		);
	}
}

interface IRequest {
	// returns request method
	public function method();
	// returns request get
	public function get($name, $default = null);
	// returns request post
	public function post($name, $default = null);
}

class Request implements IRequest {
	public $data = array(
		'method' => 'get',
		'get' => array(),
		'post' => array()
	);
	// returns request method
	public function method(){
		return $this->data['method'];
	}
	// returns request get
	public function get($name, $default = null){
		if (isset($this->data['get'][$name])){
			return $this->data['get'][$name];
		}
		return $default;
	}
	// returns request post
	public function post($name, $default = null){
		if (is_array($this->data['post'])){
			if (isset($this->data['post'][$name])){
				return $this->data['post'][$name];
			}
			return $default;
		} else {
			return $this->data['post'];
		}
	}
}

class Request_CI implements IRequest {
	public function __construct($ci){
		$this->ci = $ci;
	}
	// returns request method
	public function method(){
		return $this->ci->input->server('REQUEST_METHOD');
	}
	// returns request get
	public function get($name, $default = null){
		$res = $this->ci->input->get($name, true);
		if ($res === false){
			return $default;
		}
		return $res;
	}
	// returns request post
	public function post($name, $default = null){
		if (!$this->ci->input->post()){
			$HTTP_RAW_POST_DATA = file_get_contents("php://input");
			return $HTTP_RAW_POST_DATA;
		}
		$res = $this->ci->input->post($name, true);
		if ($res === false){
			return $default;
		}
		return $res;
	}
}
