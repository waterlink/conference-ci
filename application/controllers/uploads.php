<?php

class Uploads extends CI_Controller {
	public function __construct(){
		parent::__construct();
		// ini_set("upload_max_filesize", "30M");
		$this->load->model('User');
		$this->load->model('Auth');
	}
	public function index(){
		$this->load->library('UploadHandler');
	}
	public function info(){
		echo phpinfo();
	}

}

