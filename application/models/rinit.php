<?php

class Rinit extends CI_Model {
	public function __construct(){
		global $argv;
		if ($argv[1] != 'test'){
			R::setup($this->config->item('redbean_connection_string'));
			// R::freeze();
		}
	}
}

