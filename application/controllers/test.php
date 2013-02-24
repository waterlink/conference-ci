<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test extends CI_Controller {

	public function index($from = null, $to = null){
		// test environment init
		$this->load->model('Tests');
		$this->load->model('testcases');
		// load models for testing
		$this->load->model('user');
		$this->load->model('auth');

		// configuring tests
		// debug_turn_on();
		// run tests
		runTests();
	}

	public function scheme($from = null, $to = null){
		// test environment init
		$this->load->model('Tests');
		$this->load->model('testcases');
		// load models for testing
		$this->load->model('user');
		$this->load->model('auth');

		// configuring tests
		// debug_turn_on();
		// run tests
		runTests($this->config->item('redbean_connection_string'));
	}

	public function freeze($from = null, $to = null){
		// test environment init
		$this->load->model('Tests');
		$this->load->model('testcases');
		// load models for testing
		$this->load->model('user');
		$this->load->model('auth');

		// configuring tests
		// debug_turn_on();
		// run tests
		runTests($this->config->item('redbean_connection_string'), true);
	}

	public function coverage($from = null, $to = null){
		// test environment init
		$this->load->model('Tests');
		$this->load->model('testcases');
		// load models for testing
		$this->load->model('user');
		$this->load->model('auth');

		// configuring tests
		// debug_turn_on();
		coverage_turn_on();
		// run tests
		runTests();
	}

}