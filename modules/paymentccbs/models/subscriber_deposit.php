<?php
/**
 * d_hotel
 * class model for table bds_d_hotel 
 *
 * @since 23-10-2012 12:07:20
 * @author wiliamdecosta@gmail.com
 */
class subscriber_deposit extends AbstractTable{
    /* Table name */
    public $table = '';
    /* Alias for table */
    public $alias = '';
    /* List of table fields */
    public $fields = array();

                           
    /* Display fields */
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
    
    public $dbconn = null;
    
    function __construct(){
        try {
            parent::__construct();
        }catch(ADODB_Exception  $e){
            throw new Exception("Database Connection Error");
        }
   	}
    
    /**
     * validate
     * input record validator
     */
    public function validate(){
        
        if ($this->actionType == 'CREATE'){
                        
        }else if ($this->actionType == 'UPDATE'){
            
        }
        return true;
    }
    
    
    public function get_deposit_amount($subscriber_id) {
        $sql = "SELECT SUM(deposit_amount) FROM subscriber_deposit WHERE subscriber_id = ?";
        $amount = $this->dbconn->GetOne($sql, array($subscriber_id));

        return $amount;
    }
}
?>