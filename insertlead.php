<?php

require_once('main_cfg.php'); 

if(empty($_POST)) die('Error code: #1. Empty POST data.');


// mysql

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
    die("Error code: #2. (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8');

// create database
$query='create table if not exists `leads` (
	`id` int(11) AUTO_INCREMENT,
	`form_name` varchar(255),
	`name` varchar(255),
	`email` varchar(255),
	`phone` varchar(255),
	`state` int(1),
	`time_insert` datetime,
	primary key(`id`)
)';

$mysqli->query($query);
$keyStr='';
$dataStr='';
// Доп поля из $namekey
for($i=0;$i<count($namekey);$i++){
	$mysqli->query('alter ignore table `leads` add `'.$namekey[$i].'` varchar(255);');
	$keyStr.=',`'.$namekey[$i].'`';
	$dataStr.=',"'.$_POST[$namekey[$i]].'"';
}
	

// insert lead to datebase
$mysqli->query('insert into `leads` (`form_name`,`name`,`email`,`phone`,`state`,`time_insert`'.$keyStr.') 
	values(
		"'.$_POST['formname'].'",
		"'.$_POST['name'].'",
		"'.$_POST['email'].'",
		"'.$_POST['phone'].'",
		0,"'.date('Y-m-d H:i:s').'"
		'.$dataStr.'
	);
');
file_get_contents('http://'.$_SERVER['NAME'].'/uniapi/php_worker.php');

?>