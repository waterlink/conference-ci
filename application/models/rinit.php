<?php

class Rinit {
	public function __construct(){
		R::setup($this->config->item('redbean_connection_string'));
	}
}
