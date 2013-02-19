<?php

class Rinit extends CI_Model {
	public function __construct(){
		R::setup($this->config->item('redbean_connection_string'));
	}
}
