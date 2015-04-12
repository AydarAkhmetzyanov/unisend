<?php

require_once('main_cfg.php'); 
require_once('uniapi_source.php');

require_once(uniapi_dir.'/config.php');
require_once(uniapi_dir.'/class.php');

$uniapi = new uniapi(HOST,LOGIN,PASSWORD); 
$uniapi_source=new uniapi_source;
// get lead 

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
    die("Error code: #2. (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8');
$result=$mysqli->query('select * from `leads` where state=0');

while($r=$result->fetch_assoc()){
	if(empty($managers_ids)) $manager_id=$manager_default;
	else $manager_id=$managers_ids[rand(0,count($managers_ids))];
	if(!empty($r[$namekey[0]])){
		$source_name=$uniapi_source->analytic($r[$namekey[0]]);
		for($i=0;$i<count($source_names);$i++){
			echo $source_names[$i].' - '.$source_name.'<br>';
			if($source_names[$i]==$source_name) $source_id=$source_ids[$i];
		}
		if(empty($source_id)) $source_id=$source_default;
	} else $source_id=$source_default;
	print_r($source_names);
	$data=array(
		'TITLE'=>'Заявка с формы '.$r['form_name'].' - '.$r['name'].' : '.$r['time_insert'],
		'NAME' => $r['name'],
        'PHONE_WORK' => $r['phone'],
        'EMAIL_WORK' => $r['email'],
        'ASSIGNED_BY_ID' => $manager_id,
		$form_id=>$r['form_name'],
		'COMMENTS'=>'Комментарий',
		'SOURCE_ID'=>$source_id,
	);
	print_r($data);
	for($i=0;$i<count($namekey);$i++)
		$data[$fields_ids[$i]]=$r[$namekey[$i]];
	if($uniapi->send($data)) $mysqli->query('update `leads` set state=1 where id='.$r['id']);
}

?>