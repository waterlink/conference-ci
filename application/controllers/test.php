<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test extends CI_Controller {

	public function index(){
		// test environment init
		$this->load->model('Tests');
		$this->load->model('Mbtestcases');
		// load models for testing
		$this->load->model('Mbuser');

		// configuring tests
		// debug_turn_on();
		// run tests
		runTests();
	}

}