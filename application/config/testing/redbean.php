<?php

$config['redbean_connection_string'] = 'sqlite:/data/db/a.sqlite';
$config["redbean_auth"] = false;
// R::setup($config['redbean_connection_string']);
if(file_exists("/home/dotcloud/environment.json")) {
    /* configuration on dotCloud */
    $env =  json_decode(file_get_contents("/home/dotcloud/environment.json"));
	$user = $env->DOTCLOUD_DB_MYSQL_LOGIN;
	$password = $env->DOTCLOUD_DB_MYSQL_PASSWORD; 
	$host = $env->DOTCLOUD_DB_MYSQL_HOST;
	$port = $env->DOTCLOUD_DB_MYSQL_PORT;
	$dbname = 'test';
    
    $config["redbean_connection_string"] = "mysql:dbname=$dbname;host=$host;port=$port";
    $config["redbean_auth"] = true;
    $config["redbean_user"] = $user;
    $config["redbean_password"] = "$password";
    $password = "nope";
}
