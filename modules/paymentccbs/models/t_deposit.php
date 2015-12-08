<?php
/**
 * d_hotel
 * class model for table bds_d_hotel
 *
 * @since 23-10-2012 12:07:20
 * @author wiliamdecosta@gmail.com
 */
class t_deposit extends AbstractTable{
    /* Table name */
    public $table = 't_deposit';
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
    public $selectClause = "SELECT deposit.*";

    public $fromClause = "FROM v_t_deposit as deposit";

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

    public function add_deposit_amount($i_service_no, $i_account_no, $i_subscriberid, $i_returnable, $depositamount, $p_user_loket_id, $ip_address) {
        $this->dbconn->fetchMode = PGSQL_NUM;
        $query = "SELECT ifp.f_add_deposit(?,?,?,?,?,?,?) AS hasil";
        $result =& $this->dbconn->Execute($query, array($i_service_no, $i_account_no, $i_subscriberid, $i_returnable, $depositamount, $p_user_loket_id, $ip_address));


        $rows = $result->fields;
        return $rows['hasil'];

    }

    public function cancel_deposit_amount($t_deposit_id, $subscriber_id, $p_user_loket_id, $ip_address) {

        if(empty($t_deposit_id)) {
            $query = "SELECT MAX(t_deposit_id) FROM v_t_deposit WHERE subscriber_id = ?";
            $t_deposit_id = $this->dbconn->GetOne($query, array($subscriber_id));
        }
        
        $this->dbconn->fetchMode = PGSQL_NUM;
        $query = "SELECT ifp.f_cancel_deposit(?,?,?) AS hasil";
        $result =& $this->dbconn->Execute($query, array($t_deposit_id, $p_user_loket_id, $ip_address));

        $rows = $result->fields;
        return $rows['hasil'];
    }
}
?>