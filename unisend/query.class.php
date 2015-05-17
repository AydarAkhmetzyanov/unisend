<?php

require_once('JTransliteration.class.php');

class Query
{

    function __construct($dbname = false) {
		$this->dbname = $dbname;
        return $this;
    }

    public static function clear_array($array,$assoc)
	{
		$result=array();
        foreach($array as $key=>$value){
            if(isset($assoc[$value])){
                $result[$value]=$assoc[$value];
            }
        }
        return $result;
	}

    public $table;
    public $result=false;
    public $type;
    public $data;
    protected $query;
    protected $query_data=array();

	protected $dbname = false;

    protected $where_assoc = array();
    protected $limit = false;
    protected $offset = false;
    protected $join_table = false;
    protected $join_condition = false;
    protected $order_by = false;
    protected $order_direction = 'ASC';
    protected $additional_select=false;
    protected $additional_where=false;
    protected $group_by=false;
    protected $group_data=array();
    protected $select_what=false;

    public function select($table,$select_what=false) {
        $this->type = 'select';
        $this->table = '`'.$table.'`';
        $this->select_what = $select_what;
        return $this;
    }

    public function where($where_assoc){
        $this->where_assoc = array_merge($where_assoc,$this->where_assoc);
        return $this;
    }

    public function limit($limit){
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset){
        $this->offset = $offset;
        return $this;
    }

    public function order_by($order_by, $order_direction = 'ASC'){
        $this->order_by = $order_by;
        $this->order_direction = $order_direction;
        return $this;
    }

    public function additional_select($additional_select){
        $this->additional_select .= ', '.$additional_select;
        return $this;
    }

    public function join($join_table,$join_condition){
        $this->join_table = $join_table;
        $this->join_condition = $join_condition;
        return $this;
    }

    public function additional_where($additional_where){
        $this->additional_where .= ' AND '.$additional_where;
        return $this;
    }

    public function group($group_by, $group_data){
        $this->group_by = $group_by;
        $this->group_data = $group_data;
        return $this;
    }

    public function update($table, $data) {
        $this->type = 'update';
        $this->data = $data;
        $this->table = '`'.$table.'`';
        return $this;
    }

    public function insert($table, $data) {
        $this->type = 'insert';
        $this->data = $data;
        $this->table = '`'.$table.'`';
        return $this;
    }

    public function delete($table) {
        $this->type = 'delete';
        $this->table = '`'.$table.'`';
        return $this;
    }

    public function get_query(){
        switch ($this->type) {
            case 'select':
                $this->generate_select();
            break;
            case 'update':
                $this->generate_update();
            break;
            case 'insert':
                $this->generate_insert();
            break;
            case 'delete':
                $this->generate_delete();
            break;
        }
        return $this->query;
    }

    public function execute() {
        try{
            $this->get_query();
			//echo $this->query;
			if($this->dbname===false){
				$statement = Database::getInstance()->prepare($this->query);
			} else {
				$statement = Privatedatabase::getInstance($this->dbname)->prepare($this->query);
			}
            $statement->execute($this->query_data);
        } catch(PDOException $e) {
            exit($e->getMessage().' SQL query: '.$this->query);
        }
        if($this->type=='insert'){
			if($this->dbname===false){
				$this->result = Database::getInstance()->lastInsertId();
			} else {
				$this->result = Privatedatabase::getInstance($this->dbname)->lastInsertId();
			}


        } elseif($this->type=='select'){
            $this->result = $statement->fetchAll(PDO::FETCH_ASSOC);
        } else  {
            $this->result = true;
        }
        return $this->result;
    }

    protected function generate_select(){
        $this->query='';
        $this->query.='SELECT ';


        if($this->group_by === false){ //maybe add group without data...
            if($this->select_what === false){
                $this->query.='*';
            } else {
                $this->query.=$this->select_what;
            }
        } else {
            foreach($this->group_data as $function=>$column){
                switch ($function) {
                    case 'sum':
                        $this->query.='sum('.$column.') AS '.$column.'_sum'.',';
                    break;
                    case 'count':
                        $this->query.='count('.$column.') AS '.$column.'_count'.',';
                    break;
                }
            }
            $this->query=substr($this->query, 0, -1); //delete last ',' maybe better solution
        }
        if($this->additional_select !== false){
            $this->query.=$this->additional_select;
        }
        $this->query.=' FROM '.$this->table;

        if($this->join_table !== false){
            $this->query.=' JOIN '.$this->join_table.' ON ( '.$this->join_condition.') ';
        }

        $this->generate_where_part();
        if($this->group_by !== false){
            $this->query.=' GROUP BY '.$this->group_by;
        }
        if($this->order_by === false){
            //$this->query.=' ORDER BY ID ';
            //if($this->limit !== false){
            //    $this->query.=' ORDER BY [ID] '.$this->order_direction;
            //}
        } else {
            $this->query.=' ORDER BY '.$this->order_by.' '.$this->order_direction;
        }

        if(!(($this->limit !== false) && ($this->offset === false) && ($this->group_by === false))){
            if($this->limit !== false){
                if($this->offset !== false){
                    //$this->query.=' OFFSET '.$this->offset.' ROW ';
                } else {
                    //$this->query.=' OFFSET 0 ROW ';
                }
                $this->query.=' FETCH NEXT '.$this->limit.' ROW ONLY ';
            } else {
                if($this->offset !== false){
                    //$this->query.=' OFFSET '.$this->offset.' ROW ';
                } else {
                    //$this->query.=' OFFSET 0 ROW ';
                }
            }
        }

		if(($this->limit !== false) && ($this->offset === false) && ($this->group_by === false)){
            $this->query.=' limit '.$this->limit.' ';
        }
		//echo $this->query;

    }

    protected function generate_update(){
        $this->query='';
        $this->query.='UPDATE ';
        $this->query.=$this->table.' SET ';//SCHEMA notice when add mysql support
        foreach($this->data as $column=>$value){
			$columntrans = JTransliteration::transliterate($column);
            $this->query_data['data_'.$columntrans]=$value;
            $this->query.=$column.'=:data_'.$columntrans.',';
        }
        $this->query=substr($this->query, 0, -1); //delete last ',' maybe better solution
        $this->generate_where_part();
    }

    protected function generate_insert(){
        $this->query='';
        $this->query.='INSERT INTO ';
        $this->query.=$this->table.' (';//SCHEMA notice when add mysql support
        foreach($this->data as $column=>$value){
            $this->query.=$column.',';
        }
        $this->query=substr($this->query, 0, -1); //delete last ',' maybe better solution
        $this->query.=') VALUES (';
        foreach($this->data as $column=>$value){
            $this->query_data['data_'.$column]=$value;
            $this->query.=':data_'.$column.',';
        }
        $this->query=substr($this->query, 0, -1); //delete last ',' maybe better solution
        $this->query.=');';
    }

    protected function generate_delete(){
        $this->query='';
        $this->query.='DELETE FROM ';
        $this->query.=$this->table.' ';//SCHEMA notice when add mysql support
        $this->generate_where_part();

		        if($this->order_by === false){
            //$this->query.=' ORDER BY ID ';
            //if($this->limit !== false){
            //    $this->query.=' ORDER BY [ID] '.$this->order_direction;
            //}
        } else {
            $this->query.=' ORDER BY '.$this->order_by.' '.$this->order_direction;
        }

				if(($this->limit !== false) && ($this->offset === false) && ($this->group_by === false)){
            $this->query.=' limit '.$this->limit.' ';
        }
    }

    protected function generate_where_part(){
        $this->query.=' WHERE 1=1 ';//SCHEMA notice when add mysql support
        foreach($this->where_assoc as $column=>$value){
            if(substr($column, -1)=='='){
                $columFiltered=substr($column,0,strlen($column)-2);
                $this->query.=' AND '.$column.':where_'.$columFiltered;
            }elseif(substr($column, -1)=='>'){
                $columFiltered=substr($column,0,strlen($column)-2);
                $this->query.=' AND '.$column.':where_'.$columFiltered;
            }elseif(substr($column, -1)=='<'){
                $columFiltered=substr($column,0,strlen($column)-2);
                $this->query.=' AND '.$column.':where_'.$columFiltered;
            }elseif(substr($column, -2)=='>='){
                $columFiltered=substr($column,0,strlen($column)-3);
                $this->query.=' AND '.$column.':where_'.$columFiltered;
            }elseif(substr($column, -2)=='>='){
                $columFiltered=substr($column,0,strlen($column)-3);
                $this->query.=' AND '.$column.':where_'.$columFiltered;
            }elseif(substr($column, -2)=='<>'){
                $columFiltered=substr($column,0,strlen($column)-3);
                $this->query.=' AND '.$column.':where_'.$columFiltered;
            }elseif(substr($column, -4)=='LIKE'){
                $columFiltered=substr($column,0,strlen($column)-5);
                $this->query.=' AND '.$column.' :where_'.$columFiltered;//problem here space after like and before
            } else {
                $columFiltered=$column;
                $this->query.=' AND '.$column.'=:where_'.$columFiltered;
            }
            $this->query_data['where_'.$columFiltered]=$value;
        }
        if($this->additional_where != false){
            $this->query.=$this->additional_where;
        }
    }

}
