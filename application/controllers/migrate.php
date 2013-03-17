<?php

class Migrate extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->model('User');
		$this->load->model('Auth');
	}
	public function index(){
		Migrations::removeDuplicateEmails();
		Migrations::lowerCaseNameSurnamePatronymic();
	}
}

