<?php

class Rinit extends CI_Model {
	public function __construct(){
		global $argv;
		if ($argv[1] != 'test'){
			if ($this->config->item('redbean_auth')){
				R::setup($this->config->item('redbean_connection_string'),
					$this->config->item('redbean_user'),
					$this->config->item('redbean_password'));
			} else {
				R::setup($this->config->item('redbean_connection_string'));
			}
			// R::freeze();
		}
	}
}

