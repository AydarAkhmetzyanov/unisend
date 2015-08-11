<?php

class b24{

    //config
    private $login='administrator@creativestripe.ru';
    private $pass='';
    private $host='creativestripe.bitrix24.ru';
    private $managers=array(1); //1 is admin, userid for leads if table empty
    // source ids
    private $source_names=array('other'=>'SELF','yandex_direct'=>'other','adwords'=>'1','vk'=>'2'); //self is default source in bitrix, self always exists. if source recognized by source detector assign detected source to bitrix source
    // additional unrequired data
    private $fields_ids=array('referrer'=>'UF_CRM_1431823117'
                             ,'domain'=>'UF_CRM_1431823123'
                             ,'formname'=>'UF_CRM_1431823190'
                             ,'utm_source'=>'UF_CRM_1431823196'
                             ,'utm_campaign'=>'UF_CRM_1431823201'
                             ,'utm_content'=>'UF_CRM_1431823206'
                             ,'utm_keyword'=>'UF_CRM_1431823210'
                             ,'utm_medium'=>'UF_CRM_1431823239'
                             ,'utm_network'=>'UF_CRM_1431823245'
                             ,'utm_placement'=>'UF_CRM_1431823250'
                             ,'utm_term'=>'UF_CRM_1431823254' );

    public function send($data){
        $senddata = array();

        foreach ($this->fields_ids as $key => $value) {
            $senddata[$value] = $data[$key];
        }

        $managers = (new Query())->select('unisend_leads')->where(array('manager_ID<>'=>0,'phone'=>$data['phone']))->limit(1)->execute();
        if(empty($managers)){
            $managers = (new Query())->select('unisend_managers_forleads')->order_by('leadcount')->limit(1)->execute();
        }
        $senddata['ASSIGNED_BY_ID'] = $managers[0]['manager_ID'];

        if(isset($this->source_names[$data['detected_source']])){
            $senddata['SOURCE_ID']=$this->source_names[$data['detected_source']];
        } else {
            $senddata['SOURCE_ID']=$this->source_names['other'];
        }

        $senddata['PHONE_WORK']=$data['phone'];
        $senddata['EMAIL_WORK']=$data['email'];
        $senddata['COMMENTS']=$data['data'];
        $senddata['TITLE']='Сайт: '.$data['domain'].' Заявка с формы:'.$data['formname'].' - '.$data['name'].' : '.$data['timestamp'];
        $senddata['NAME']=$data['name'];

        if(!$this->send_core($senddata)){
            $senddata['EMAIL_WORK']='';
            if(!$this->send_core($senddata)){
                return false;
            }
        }

        $query = 'UPDATE `unisend_managers_forleads` set `leadcount`=`leadcount`+1 where `manager_ID`=:ASSIGNED_BY_ID';
        try{
            $statement = Database::getInstance()->prepare($query);
            $statement->bindParam(':ASSIGNED_BY_ID', $senddata['ASSIGNED_BY_ID']);
            $statement->execute();
        } catch(PDOException $e) {
            exit($e->getMessage().' SQL query: '.$query);
        }
        $query = 'UPDATE `unisend_leads` set manager_ID=:ASSIGNED_BY_ID where id=:id';
        try{
            $statement = Database::getInstance()->prepare($query);
            $statement->bindParam(':ASSIGNED_BY_ID', $senddata['ASSIGNED_BY_ID']);
            $statement->bindParam(':id', $data['id']);
            $statement->execute();
        } catch(PDOException $e) {
            exit($e->getMessage().' SQL query: '.$query);
        }

        return true;
    }

    //installation check
    public function __construct(){
        $query = "CREATE TABLE IF NOT EXISTS `unisend_managers_forleads` (
                      `manager_ID` int(11) NOT NULL,
                      `leadcount` int(11) DEFAULT 0
                 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        try{
            $statement = Database::getInstance()->prepare($query);
			$statement->closeCursor();
            $statement->execute();
        } catch(PDOException $e) {
            exit($e->getMessage().' SQL query: '.$query);
        }
        $query = "ALTER TABLE `unisend_leads` ADD manager_ID int(11) DEFAULT 0;";
        try{
            $statement = Database::getInstance()->prepare($query);
            $statement->execute();
        } catch(PDOException $e) {
            //just ignore existance
        }
        //if empty users add user
        $unisend_managers_forleads = (new Query())->select('unisend_managers_forleads')->execute();
        if(empty($unisend_managers_forleads)){
            foreach ($this->managers as $manager) {
                (new Query())->insert('unisend_managers_forleads',array('manager_ID'=>$manager))->execute();
            }
        }
    }

    private function send_core($data){
        $data['LOGIN']=$this->login;
        $data['PASSWORD']=$this->pass;
        $output = '';
        $fp = fsockopen("ssl://".$this->host, 443, $errno, $errstr, 30);
        if (!$fp) {
            echo "$errstr ($errno)<br />\n";
            return false;
        } else {
            $strleadData = '';
            foreach ($data as $key => $value) {
                $strleadData .= ($strleadData == '' ? '' : '&').$key.'='.urlencode($value);
            }

            $str = "POST /crm/configs/import/lead.php HTTP/1.0\r\n";
            $str .= "Host: ".$this->host."\r\n";
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
