<?php

class Uniapi_source{

	public static function detect_source($data){
		$available_sources=array('other','yandex_direct','vk','adwords');
		return $available_sources[0];
	}

}

?>