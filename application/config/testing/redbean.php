<?php

$config['redbean_connection_string'] = 'sqlite:/data/db/a.sqlite';
$config["redbean_auth"] = false;
// R::setup($config['redbean_connection_string']);
if(file_exists("/home/dotcloud/environment.json")) {
    /* configuration on dotCloud */
    require('env.php');
    
    $config["redbean_connection_string"] = "mysql:dbname=$dbname;host=$host;port=$port";
    $config["redbean_auth"] = true;
    $config["redbean_user"] = $user;
    $config["redbean_password"] = "$password";
    $password = "nope";
}
