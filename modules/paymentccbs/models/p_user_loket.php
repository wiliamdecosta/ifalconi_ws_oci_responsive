<?php
/**
 * d_hotel
 * class model for table bds_d_hotel
 *
 * @since 23-10-2012 12:07:20
 * @author wiliamdecosta@gmail.com
 */
class p_user_loket extends AbstractTable{
    /* Table name */
    public $table = 'p_user_loket';
    /* Alias for table */
    public $alias = '';
    /* List of table fields */
    public $fields = array();


    /* Display fields */
    public $displayFields = array();
    /* Details table */
    public $details = array();
    /* Primary key */
    public $pkey = 'p_user_loket_id';
    /* References */
    public $refs = array();

    /* select from clause for getAll and countAll */
    public $selectClause = "SELECT *";

    public $fromClause = "FROM p_user_loket";

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

    public function valid_login($user_name, $password) {

	    if(empty($user_name) or empty($password)) return "";

	    /*$sql = "SELECT p_bank_branch_id FROM p_user_loket WHERE user_name = ? AND decrypt(user_pwd) = ?";
	    $p_bank_branch_id = $this->dbconn->GetOne($sql, array($user_name, $password));

		if(empty($p_bank_branch_id)) {
		    return "";
		}*/

		$sql = "select f_login_counter('N', -999, '".$user_name."', '".$password."', '') as hasil";
		$p_user_loket_id = $this->dbconn->GetOne($sql);
        
		return $p_user_loket_id;
	}
}
?>