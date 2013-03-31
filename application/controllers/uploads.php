<?php

function Download($path, $speed = null, $multipart = true)
{
	while (ob_get_level() > 0)
	{
		ob_end_clean();
	}

	if (is_file($path = realpath($path)) === true)
	{
		$file = @fopen($path, 'rb');
		$size = sprintf('%u', filesize($path));
		$speed = (empty($speed) === true) ? 1024 : floatval($speed);

		if (is_resource($file) === true)
		{
			set_time_limit(0);

			if (strlen(session_id()) > 0)
			{
				session_write_close();
			}

			if ($multipart === true)
			{
				$range = array(0, $size - 1);

				if (array_key_exists('HTTP_RANGE', $_SERVER) === true)
				{
					$range = array_map('intval', explode('-', preg_replace('~.*=([^,]*).*~', '$1', $_SERVER['HTTP_RANGE'])));

					if (empty($range[1]) === true)
					{
						$range[1] = $size - 1;
					}

					foreach ($range as $key => $value)
					{
						$range[$key] = max(0, min($value, $size - 1));
					}

					if (($range[0] > 0) || ($range[1] < ($size - 1)))
					{
						header(sprintf('%s %03u %s', 'HTTP/1.1', 206, 'Partial Content'), true, 206);
					}
				}

				header('Accept-Ranges: bytes');
				header('Content-Range: bytes ' . sprintf('%u-%u/%u', $range[0], $range[1], $size));
			}

			else
			{
				$range = array(0, $size - 1);
			}

			header('Pragma: public');
			header('Cache-Control: public, no-cache');
			header('Content-Type: application/octet-stream');
			header('Content-Length: ' . sprintf('%u', $range[1] - $range[0] + 1));
			header('Content-Disposition: attachment; filename="' . basename($path) . '"');
			header('Content-Transfer-Encoding: binary');

			if ($range[0] > 0)
			{
				fseek($file, $range[0]);
			}

			while ((feof($file) !== true) && (connection_status() === CONNECTION_NORMAL))
			{
				echo fread($file, round($speed * 1024)); flush(); sleep(1);
			}

			fclose($file);
		}

		exit();
	}

	else
	{
		header(sprintf('%s %03u %s', 'HTTP/1.1', 404, 'Not Found'), true, 404);
	}

	return false;
}
  
class Zipper extends ZipArchive { 
	
  public function addDir($path) { 
	//print 'adding ' . $path . '<br>'; 
	$this->addEmptyDir($path); 
	$nodes = glob($path . '/*'); 
	foreach ($nodes as $node) { 
	  //print $node . '<br>'; 
	  if (is_dir($node)) { 
		$this->addDir($node); 
	  } else if (is_file($node))  { 
		$this->addFile($node); 
	  } 
	} 
  } 
	
} // class Zipper 


class Uploads extends CI_Controller {
  public function __construct(){
	parent::__construct();
	// ini_set("upload_max_filesize", "30M");
	$this->load->model('User');
	$this->load->model('Auth');
		$this->config->load('uploads');
  }
  public function index($id = false){
	if (!$id){
	  $this->load->library('UploadHandler');
	} else {
	  // sanitize id
	  $id = preg_replace("/[^0-9]/", "", $id);
	  $this->load->library('UploadHandler', array(
		'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']).'/files/'.$id.'/',
	  ));
	}
  }
  // public function info(){
  //   echo phpinfo();
  // }
  public function id(){
	$bean = R::dispense('upload');
	R::store($bean);
	echo json_encode(array(
	  'id' => $bean->id
	));
  }

  public function download($uid){
	// $id = preg_replace("/[^0-9]/", "", $id);
	if (!in_array("operator", $this->Auth->group())){
	  header(sprintf('%s %03u %s', 'HTTP/1.1', 403, 'Forbidden'), true, 403);
	}
	$user = R::load('user', $uid);
	//echo "uid=$uid;";
	if (!$user){
	  echo "No such user";
	  return;
	}
	$id = $user->uploadId;
	//echo "id=$id;";
	//chdir("./files/$id");
	$cwd = getcwd();
	//echo "cwd=$cwd;";
		if ($this->config->item("use_fio_in_download_archive_name")){
		  $zipFile = "files/".$uid."-".$user->surname.'_'.$user->name.'_'.$user->patronymic.'.zip';
		} else {
		  $zipFile = "files/".$uid."-archive.zip";
		}
	//echo "zipfile=$zipFile;";
	$dirToZip = "files/$id";
	$fail = false;
	$zipArchive = new Zipper(); //new ZipArchive();

	if (!$zipArchive->open($zipFile, ZIPARCHIVE::OVERWRITE))
		die("Failed to create archive\n");

	//echo "blob=
	//$zipArchive->addEmptyDir("files");
	$zipArchive->addDir($dirToZip);
	//echo "blob=end;";
	if (!$zipArchive->status == ZIPARCHIVE::ER_OK){
		echo "Failed to write files to zip\n";
		$fail = True;
	}
	//echo "fail=";
	// if ($fail){ echo "true;"; } else { echo "false;"; }

	//echo "closing=start;";
	$zipArchive->close();
	//echo "closing=end;";
	if ($fail) return;

	//echo "donwloading; exiting for debug;";
	//return;
	return Download($zipFile);
  }

}

