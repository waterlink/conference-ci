<?php

class Auth {
	function group() {
		return array("operator");
	}
}

class Validate extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('User');
		// $this->load->model('Auth');
		$this->load->helper('url');
	}
	function index() {
		$code = $this->input->get("code", true);
		$user = R::findOne('user', 
			" validationcode = :code ",
			array(":code" => $code));
		if ($user) {
			$user->validation = true;
			R::store($user);
			redirect("../templates/valid.html", "refresh");
		} else {
			echo "Go to hell, mr Hacker ! :)";
		}
	}
}
