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
            if (array_key_exists('HTTPS', $_SERVER)) {
                $protocol = ($_SERVER['HTTPS'] != "off") ? "https" : "http";
            } else {
                $protocol = "http";
            }
			redirect($protocol."://".$_SERVER["HTTP_HOST"]."/templates/valid.html");
            #echo "redirecting..";
            #echo http_redirect("/templates/valid.html", array(), false, HTTP_REDIRECT_FOUND);
		} else {
			echo "Go to hell, mr Hacker ! :)";
		}
	}
}
