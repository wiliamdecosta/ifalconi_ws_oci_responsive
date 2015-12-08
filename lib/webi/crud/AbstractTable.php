<?php
class UndefinedException extends Exception {} 

abstract class AbstractTable{
    /* Table prefix */
    public $prefix = '';
    /* Table name */
    public $table = '';
    /* Alias for table */
    public $alias = '';
    /* List of table fields */
    public $fields = array();
    /* Display field */
    public $displayFields = array();
    /* Details table */
    public $details = array();    
    /* Primary key */
    public $pkey = '';
    /* References */
    public $refs = array();

    /* select from clause for getAll and countAll */
    public $selectClause = "";
    public $fromClause = "";

    public $hasAutoInc = true;
    
    public $dbconn;

    public $record = array();
    public $item = array();
    
    public $criteria = array();
    public $criteriaSQL = false;
    public $actionType = '';
    
    public $likeOperator = '';
    
    function __construct(){
        
        $dbConnParams = array(
            'name' => wbConfig::get('DB.name'),
            'user' => wbConfig::get('DB.user'),
            'password' => wbConfig::get('DB.password'),
            'host' => wbConfig::get('DB.host'),
            'type' => wbConfig::get('DB.type')
        );
        wbDB::init($dbConnParams);
        $this->dbconn = & wbDB::getConn();
        
        if (count($this->displayFields)){
            if ($this->dbconn->dataProvider == 'mysql' || $this->dbconn->dataProvider == 'oci8' ) {
                $this->selectClause .= ", CONCAT(".implode(", ' - ' ,", $this->displayFields).") AS _display_field_";
                $this->likeOperator = " LIKE ";
            }else if ($this->dbconn->dataProvider == 'postgres') {
                $this->selectClause .= ", ".implode(" || ' - ' || ", $this->displayFields)." AS _display_field_";
                $this->likeOperator = " ILIKE ";
            }
        }
        
        if ($this->dbconn->dataProvider == 'mysql' || $this->dbconn->dataProvider == 'oci8') {
            $this->likeOperator = " LIKE ";    
        }else if ($this->dbconn->dataProvider == 'postgres') {
            $this->likeOperator = " ILIKE ";   
        }
   	}

    abstract public function validate();

    public function beforeWrite(){} // <-- tobe implemented
    public function afterWrite(){}

    public function GenID($seqname = ''){
        if (empty($seqname)) $seqname = $this->table.'_'.$this->pkey.'_seq';
        return $this->dbconn->GenID($seqname);
    }

    public function &getAll($start = 0, $limit = 30, $orderby = '', $ordertype = 'ASC'){
        $whereClause = $this->getCriteriaSQL();
        
        $query = $this->selectClause." ".$this->fromClause." ".$whereClause." ORDER BY ";

        if (empty($orderby)){
            $query .= $this->pkey.' '.$ordertype;
        }else{
            $query .= $orderby.' '.$ordertype;       
        }


        $items =& $this->dbconn->GetAllAssocLimit($query,$limit,$start);

        if (!is_array($items)){
            throw new Exception($this->dbconn->ErrorMsg());
        }
        
        return $items;
    }

    public function countAll(){
        $whereClause = $this->getCriteriaSQL();

        $query = "SELECT COUNT(1) ". $this->fromClause ." ".$whereClause;
        $countitems = $this->dbconn->GetOne($query);

        if ($countitems === false){
            throw new Exception($this->dbconn->ErrorMsg());
        }
        
        return $countitems;
    }

    public function getDisplayFieldCriteria($value){
        if (empty($value)) return "";
        
        if (count($this->displayFields) == 0) return "";
        
        $fields = array();
        for($i=0; $i < count($this->displayFields);$i++){
            $fields[$i] = $this->displayFields[$i].$this->likeOperator.$this->dbconn->qstr('%'.$value.'%');
        }

        $query = implode(" OR ", $fields);

        if (count($fields) > 1) $query = "(".$query.")";

        return $query;
    }

    public function &get($id, $raiseExceptionOnEmpty = false){
        $whereClause = "WHERE ".$this->getAlias().$this->pkey." = ?";

        $query = $this->selectClause." ".$this->fromClause." ".$whereClause;
        $item =& $this->dbconn->GetItem($query, array($id));
        
        if (!is_array($item)){
            throw new Exception($this->dbconn->ErrorMsg());
        }

        if ($raiseExceptionOnEmpty === true){
            if (empty($item[$this->pkey])){
                throw new Exception("ID (".$this->pkey.") ".$id." tidak ada dalam database. Anda atau user lain mungkin sudah menghapus data tersebut");
            }
        }
        
        return $item;
    }

    public function isUnique($field, $value, $exid = false){
        $type = gettype($value);
        
        if ($type == 'string'){
            $operator = $this->likeOperator;
        }else{
            $operator = '=';
        }
                        
        $query = "SELECT COUNT(1) FROM ".$this->table." WHERE ".$field." ".$operator." ?";
        $bind = array($value);
        
        if ($exid !== false){
            $query .= " AND ".$this->pkey." != ?";
            $bind[] = $exid;
        }

        $countitems = $this->dbconn->GetOne($query, $bind);

        if ($countitems === false){
            throw new Exception($this->dbconn->ErrorMsg());
        }
        
        if ($countitems > 0) return false;

        return true;
    }

    /* Set record */
    public function setRecord($record){
        if (empty($record[$this->pkey])) throw new Exception("Nilai ".$this->pkey." tidak boleh kosong");
        
        $this->item = $record;
        $this->record = array(); 
		
        foreach ($this->fields as $key => $attrib){
            $value = '';
            if ($attrib['nullable']){
                if (isset($record[$key])){
                    if ($record[$key] == '')
                        $value = null;
                    else
                        $value = $record[$key];
                }else{
                    continue;
                }
            }            
            
            if ($this->actionType == 'CREATE'){
                if (!$attrib['nullable']){
                    if (!isset($record[$key]) || $record[$key] == ''){
                        throw new Exception("Nilai ".$attrib['display']." tidak boleh kosong");
                    }else{
                        $value = $record[$key];
                    }
                }
            }else if ($this->actionType == 'UPDATE'){
                if (!$attrib['nullable']){
                    if (!isset($record[$key])) continue;
                    
                    if ($record[$key] == ''){
                        throw new Exception("Nilai ".$attrib['display']." tidak boleh kosong");
                    }else{
                        $value = $record[$key];
                    }
                }
            }

            if (!empty($value)){
                if ($attrib['type'] == 'date'){
        			if (strlen($value) > 10) $value = substr($value, 0, 10);
        			
        			$pieces = explode('-', $value);
        			
        			if (count($pieces) != 3) throw new Exception("Format tanggal harus dd-mm-yyyy");
        			
        			if (!checkdate($pieces[1], $pieces[2],$pieces[0])) throw new Exception("Nilai tanggal '".$value."' tidak benar");        			
                }else if ($attrib['type'] == 'int' || $attrib['type'] == 'float'){
                    if (!is_numeric($value)) throw new Exception("Nilai ".$attrib['display']." harus berupa bilangan");
                }
                
                if ($attrib['unique'] && $key != $this->pkey){
                    if (!$this->isUnique($key, $value, $record[$this->pkey])){
                        throw new Exception('Duplikasi pada '.$attrib['display'].' "'.$value.'". Mohon masukan nilai lain');
                    }
                }
            }
            $this->record[$key] = $value;
        }

        return $this->record;
    }

    public function create(&$dbconn = false){
        try{
            $this->validate();
            
            if ($dbconn === false)
                $this->dbconn->insertRecord($this->table, $this->record);
            else
                $dbconn->insertRecord($this->table, $this->record);
            
            $this->afterWrite();
        }catch(Exception $e){
            throw $e;
        }
        return true;
    }

    public function update(){
        try {
            $this->validate();

            $this->dbconn->updateRecord($this->table, $this->record, $this->pkey);
            $this->afterWrite();
        }catch(Exception $e){
            throw $e;
        }
        
        return true;
    }

    /* Remove record by id */    
    public function remove($id){

        if ($this->isRefferenced($id)){
            throw new Exception('ID '.$id.' tidak bisa di hapus karena sudah di referensi oleh data lain');
        }

        $query = "DELETE FROM ".$this->table." WHERE ".$this->pkey." = ?";

        $result =& $this->dbconn->Execute($query, array($id));
        
        if (!$result){
            throw new Exception($this->dbconn->ErrorMsg());
        }
        
        $result->Close();
        
        return true;
    }

    public function isRefferenced($id){
        if (count($this->refs) == 0) return false;
  	
        foreach ($this->refs as $table => $field){
            $numitem = $this->dbconn->GetOne("SELECT COUNT(1) FROM ".$table." WHERE ".$field." = ?", array($id));
            
            if ($numitem === false){
                throw new Exception($this->dbconn->ErrorMsg());
            }
            
            if ($numitem > 0) return true;
        }
        return false;
    }

    public function bindValuesToSQL($sql,$inputarr=false){
        if ($inputarr) {
			if (!is_array($inputarr)) $inputarr = array($inputarr);
			
			$element0 = reset($inputarr);
			# is_object check because oci8 descriptors can be passed in
			$array_2d = is_array($element0) && !is_object(reset($element0));
			//remove extra memory copy of input -mikefedyk
			unset($element0);
			

				$sqlarr = explode('?',$sql);
					
				if (!$array_2d) $inputarr = array($inputarr);
				foreach($inputarr as $arr) {
					$sql = ''; $i = 0;
					//Use each() instead of foreach to reduce memory usage -mikefedyk
					while(list(, $v) = each($arr)) {
						$sql .= $sqlarr[$i];
						// from Ron Baldwin <ron.baldwin#sourceprose.com>
						// Only quote string types	
						$typ = gettype($v);
						if ($typ == 'string')
							//New memory copy of input created here -mikefedyk
							$sql .= $this->dbconn->qstr($v);
						else if ($typ == 'double')
							$sql .= str_replace(',','.',$v); // locales fix so 1.1 does not get converted to 1,1
						else if ($typ == 'boolean')
							$sql .= $v ? $this->dbconn->true : $this->dbconn->false;
						else if ($typ == 'object') {
							if (method_exists($v, '__toString')) $sql .= $this->dbconn->qstr($v->__toString());
							else $sql .= $this->dbconn->qstr((string) $v);
						} else if ($v === null)
							$sql .= 'NULL';
						else
							$sql .= $v;
						$i += 1;
					}
					if (isset($sqlarr[$i])) {
						$sql .= $sqlarr[$i];
						if ($i+1 != sizeof($sqlarr)) throw new Exception("Input Array does not match ?: ".htmlspecialchars($sql));
					} else if ($i != sizeof($sqlarr)){
						throw new Exception("Input Array does not match ?: ".htmlspecialchars($sql));
		            }
				}	
			
		}
        
        return $sql;
    }
    
    public function getAlias(){
        if (empty($this->alias)) return '';
        
        return $this->alias.'.';
    }

    public function resetCriteria(){
        $this->criteria = array();
        $this->criteriaSQL = false;
    }
    
    public function setCriteria($sql, $values = false){
        $this->criteria[] = $this->bindValuesToSQL($sql, $values);

        return $this->criteria;
    }
    
    public function getCriteria(){
        return $this->criteria;
    }

    public function getCriteriaSQL(){
        if ($this->criteriaSQL === false){
            $this->criteriaSQL = "";
            if (count($this->criteria)){
                $this->criteriaSQL = "WHERE ".implode(' AND ', $this->criteria);
            }
        }
        return $this->criteriaSQL;
    }
}
?>