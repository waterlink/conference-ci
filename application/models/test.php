<?php
require_once('rb.php');

R::setup('sqlite:/data/db/a.sqlite');

// $bean = R::dispense('leaflet');
// $bean->title = 'Hello World';

//Store the bean
// $id = R::store($bean);

//Reload the bean
// $leaflet = R::load('leaflet',$id);

$leaflet = R::findOne('leaflet');

//Display the title
echo $leaflet->title;
