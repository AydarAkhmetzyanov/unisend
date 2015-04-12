<?php
// Как вариант. Этот класс можно связать с бд и все эти дела в бд хранить, потому что метки могут жестко отличаться, т.к. их рекламщики ставят
class uniapi_source{
	
	public $utm_source=array('yandex.direct','vk','google.adwords','ok');
	public $source=array('Яндекс.Директ','Вконтакте','Гугл Адвордс','Одноклассники');
	public function analytic($utm){
		for($i=0;$i<count($this->utm_source);$i++){
			if($this->utm_source[$i]==$utm) return $this->source[$i];
		}
		return $utm;
	}
	public function add_source($utm,$src){
		$this->utm_source[]=$utm;
		$this->source[]=$src;
	}
	
}

?>