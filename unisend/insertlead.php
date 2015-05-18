<?php

require_once('main_cfg.php');
require_once('query.class.php');
require_once('uniapi_source.php');

if(empty($_POST)) die('Error code: #1. Empty POST data.');

//create table for leads
$query = 'create table if not exists `unisend_leads` (
			`id` int(11) AUTO_INCREMENT,
			`status` TINYINT DEFAULT 0,
			`timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			`formname` varchar(255),
			`name` varchar(255),
			`email` varchar(255),
			`phone` varchar(255),
			`data` varchar(255),
			`referrer` varchar(255),
			`domain` varchar(255),
			`detected_source` varchar(255),
			`utm_source` varchar(255),
			`utm_campaign` varchar(255),
			`utm_content` varchar(255),
			`utm_keyword` varchar(255),
			`utm_medium` varchar(255),
			`utm_network` varchar(255),
			`utm_placement` varchar(255),
			`utm_term` varchar(255),
			primary key(`id`)
		)';
try{
	$statement = Database::getInstance()->prepare($query);
	$statement->execute();
} catch(PDOException $e) {
	exit($e->getMessage().' SQL query: '.$query);
}

//apply source detector
$_POST['detected_source'] = Uniapi_source::detect_source($_POST);

//add lead
$insert_data = array();
$fields=array('formname','name','email','phone','data','referrer','domain','detected_source'
	,'utm_source','utm_campaign','utm_content','utm_keyword','utm_medium','utm_network','utm_placement','utm_term');
	foreach ($fields as $value) {
		if(isset($_POST[$value])){
			$insert_data[$value] = $_POST[$value];
		} else {
			$insert_data[$value] = '';
		}
	}

$insert_data['phone'] = str_replace(array('+','-','(',')',' '),array('','','','',''),$insert_data['phone']);

$leads = (new Query())->select('unisend_leads')->where(array('phone'=>$insert_data['phone'],'formname'=>$insert_data['formname'],'domain'=>$insert_data['domain']))->additional_where('`timestamp` >= DATE_SUB(NOW(),INTERVAL 1 HOUR)')->limit(1)->execute();
if(empty($leads)){
	(new Query())->insert('unisend_leads',$insert_data)->execute();
}
