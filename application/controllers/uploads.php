<?php

class Uploads extends CI_Controller {
	public function __construct(){
		parent::__construct();
		// ini_set("upload_max_filesize", "30M");
		$this->load->model('User');
		$this->load->model('Auth');
	}
	public function index($id = false){
		if (!$id){
			$this->load->library('UploadHandler');
		} else {
			$this->load->library('UploadHandler', array(
				'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']).'/files/'.$id.'/',
			));
		}
	}
	// public function info(){
	// 	echo phpinfo();
	// }
	public function id(){
		$bean = R::dispense('upload');
		R::store($bean);
		echo json_encode(array(
			'id' => $bean->id
		));
	}

}

