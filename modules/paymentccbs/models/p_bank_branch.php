<?php
/**
 * d_hotel
 * class model for table bds_d_hotel 
 *
 * @since 23-10-2012 12:07:20
 * @author wiliamdecosta@gmail.com
 */
class p_bank_branch extends AbstractTable{
    /* Table name */
    public $table = 'p_bank_branch';
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
    public $selectClause = "SELECT bank_branch.p_bank_branch_id, bank_branch.code, bank_branch.p_bank_id, bank_branch.address, 
	                                bank_branch.loket_no, bank_branch.loket_type, bank_branch.max_user, bank_branch.status as branch_status,
	                                bank_branch.p_area_id, bank_branch.description, 
	                                to_char(bank_branch.create_date, 'yyyy-mm-dd') as create_date, 
                                    to_char(bank_branch.update_date, 'yyyy-mm-dd') as update_date, 
	                                bank_branch.create_by, bank_branch.update_by,
	                                bank.code as bank_code, bank_area.code as bank_area_code";

    public $fromClause = "FROM p_bank_branch as bank_branch
                                    LEFT JOIN p_bank as bank ON bank_branch.p_bank_id = bank.p_bank_id
                                    LEFT JOIN p_area as bank_area ON bank_branch.p_area_id = bank_area.p_area_id";
    
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
    
}
?>