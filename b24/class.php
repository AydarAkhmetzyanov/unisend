<?php

class uniapi{
	public $login;
	public $pass;
	public $b24;
	public function __construct($host,$l,$p){
		$this->login=$l;
		$this->pass=$p;
		$this->b24=$host;
	}
	public function send($data){
		$data['LOGIN']=$this->login;
		$data['PASSWORD']=$this->pass;
		$output = '';
		$fp = fsockopen("ssl://".$this->b24, 443, $errno, $errstr, 30);
		if ($fp) {
			$strleadData = '';
			foreach ($data as $key => $value) {
				$strleadData .= ($strleadData == '' ? '' : '&').$key.'='.urlencode($value);
			}

			$str = "POST /crm/configs/import/lead.php HTTP/1.0\r\n";
			$str .= "Host: ".$this->b24."\r\n";
			$str .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$str .= "Content-Length: ".strlen($strleadData)."\r\n";
			$str .= "Connection: close\r\n\r\n";

			$str .= $strleadData;

			fwrite($fp, $str);

			$result = '';
			while (!feof($fp)) {
				$result .= fgets($fp, 128);
			}
			fclose($fp);

			$response = explode("\r\n\r\n", $result);

			$json_i = array("{'", "'}", "':'", "','");
			$json_o = array('{"', '"}', '":"', '","');
			$json = str_replace($json_i, $json_o, $response[1]);

			$result = json_decode($json, true);
			if($result['error'] != 201) {
				return false;
			}
		}
		return true;
	}
}

?>