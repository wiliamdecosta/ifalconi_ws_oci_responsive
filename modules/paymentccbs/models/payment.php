<?php
/**
 * d_hotel
 * class model for table bds_d_hotel 
 *
 * @since 23-10-2012 12:07:20
 * @author wiliamdecosta@gmail.com
 */
class payment extends AbstractTable{
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
    
    
    public function executeStpPayAcc($service_no, $action, $start, $limit, $i_id) {
                
        $stmt = $this->dbconn->PrepareSP("BEGIN
                          PAYTV.STP_PAY_ACC (:i_service_no,
                           :i_action,
                           :i_start,
                           :i_limit,
                           :i_id,
                           :o_data,
                           :o_rec_num,
                           :o_msg,
                           :o_err
                           );
                            END;
                            ");
        
        $this->dbconn->InParameter($stmt,$service_no,'i_service_no',4000);
        $this->dbconn->InParameter($stmt,$action,'i_action',4000);
        $this->dbconn->InParameter($stmt,$start,'i_start',4000);
        $this->dbconn->InParameter($stmt,$limit,'i_limit',4000);
        $this->dbconn->InParameter($stmt,$i_id,'i_id',4000);

        $o_data = array();
        $this->dbconn->OutParameter($stmt, $o_data, 'o_data', -1, OCI_B_CURSOR);
        $this->dbconn->OutParameter($stmt, $total,'o_rec_num',4000);
        $this->dbconn->OutParameter($stmt, $o_msg,'o_msg',4000);
        $this->dbconn->OutParameter($stmt, $o_err,'o_err',4000);
        
        $this->dbconn->fetchMode = OCI_NUM;
        $result = $this->dbconn->Execute($stmt) or die($this->dbconn->ErrorMsg());
        $cnt = 0;
        
        while (!$result->EOF ) {
        	$rows[$cnt++] = $result->fields;
        	$result->MoveNext();
        }

        return array('rows' => $rows,
                     'total' => $total,
                     'o_msg' => $o_msg,
                     'o_err' => $o_err
                    );
        	
    }
}
?>