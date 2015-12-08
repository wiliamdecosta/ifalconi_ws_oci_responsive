<?php
/**
 * inquiry
 * class controller for table bds_inquiry
 *
 * @since 23-10-2012 12:07:20
 * @author wiliamdecosta@gmail.com
 */
class payment_controller extends wbController{

    public static function read($args = array()){

        extract($args);

        $page = wbRequest::getVarClean('current', 'int', 1);
        $limit = wbRequest::getVarClean('rowCount', 'int', 10);

        $sort = wbRequest::getVarClean('sortby', 'str', '');
        $dir = wbRequest::getVarClean('sortdir', 'str', 'ASC');
        $searchPhrase = wbRequest::getVarClean('searchPhrase', 'str', '');

        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        $start = ($page-1) * $limit;

        try{
            $table =& wbModule::getModel('paymentccbs', 'payment');
            if(!empty($searchPhrase)) {
                $table->setCriteria("UPPER(CODE) LIKE ?", array('%'.strtoupper($searchPhrase).'%'));
            }

            $items = $table->getAll($start, $limit, $sort, $dir);
            $total = $table->countAll();


            $data['items'] = $items;
            $data['total'] = $total;
            $data['current'] = $page;
            $data['rowCount'] = $limit;

            $data['message'] = '';
            $data['success'] = true;

        }catch(Exception $e){
            $data['message'] = $e->getMessage();
            $data['success'] = false;
        }


        return $data;
    }


    public static function normal_payment($args = array()){
               
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        extract($args);

        $page = wbRequest::getVarClean('current', 'int', 1);
        $limit = wbRequest::getVarClean('rowCount', 'int', 10);

        $sort = wbRequest::getVarClean('sortby', 'str', '');
        $dir = wbRequest::getVarClean('sortdir', 'str', 'ASC');
        $searchPhrase = wbRequest::getVarClean('searchPhrase', 'str', '');

        /* post params */
        $service_no = wbRequest::getVarClean('service_no', 'str', '');
        $p_bank_branch_id = wbRequest::getVarClean('p_bank_branch_id', 'int', 0);
        $action = wbRequest::getVarClean('action', 'str', 'query');
        $i_id = wbRequest::getVarClean('i_id', 'array', array());
        $i_subscriberid = wbRequest::getVarClean('i_subscriberid', 'int', 0);
        $cboxdeposit = wbRequest::getVarClean('cboxdeposit', 'str', 'N');

        $client_ip_address = wbRequest::getVarClean('client_ip_address', 'str', '');
        $p_user_loket_id = wbRequest::getVarClean('p_user_loket_id', 'int', 0);
        $user_name = wbRequest::getVarClean('user_name', 'str', '');

        /* make $i_id with comma separated */
        $idList = "";
        $prefix = "";
        foreach ($i_id as $theid){
            $idList .= $prefix . "'" . substr($theid,0,strpos($theid,"_")) . "'";
            $prefix = ", ";
        }
        
        $start = ($page-1) * $limit;

        /*$data['message'] .= "action: ".$action."</br>";
        $data['message'] .= "service_no: ".$service_no."</br>";
        $data['message'] .= "id: ".$idList."</br>";
        $data['message'] .= "p_bank_branch_id: ".$p_bank_branch_id."</br>";
        return $data;*/

        try{
            
            $items = array();
            $table =& wbModule::getModel('paymentccbs', 'payment');

            $table->dbconn->fetchMode = PGSQL_NUM;
            $query = "SELECT * FROM ifp.f_pay_acc(?,?,?,?,?,?,?,?,?,?,?,?)"; /* tambahkan subscriber id, use_deposit */
            $result =& $table->dbconn->Execute($query, array($action, $service_no, $start, $limit, $idList, $p_user_loket_id, $user_name, $p_bank_branch_id, $client_ip_address, $i_subscriberid, $cboxdeposit, strtoupper(date("d-M-Y"))));

            $rows = $result->fields;

            if( isset ($rows['cnt']) ) {

                $data['total'] = $rows['cnt'];
                $data['message'] = $rows['msg'];
                $data['success'] = true;

                if( $rows['cnt'] == 0 ) {
                    $data['items'] = array();
                    if(strtoupper(substr($rows['msg'],0,5)) == 'ERROR') {
                        $data['success'] = false;
                    }
                }else {
                    $rows = array($rows);
                    for($i = 0; $i < count($rows); $i++)
                        $rows[$i]['id'] .= "_".$rows[$i]['account_no'];

                    $data['items'] = $rows;
                }

            }else {
                for($i = 0; $i < count($rows); $i++)
                    $rows[$i]['id'] .= "_".$rows[$i]['account_no'];

                $data['items'] = $rows;
                $data['total'] = $rows[0]['cnt'];
                $data['message'] = $rows[0]['msg'];
                $data['success'] = true;
            }

            $data['current'] = $page;
            $data['rowCount'] = $limit;


        }catch(Exception $e){
            $data['message'] = $e->getMessage();
            $data['success'] = false;
        }

        return $data;
    }
    
    
    public static function cancel_payment($args = array()){
               
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        extract($args);

        $page = wbRequest::getVarClean('current', 'int', 1);
        $limit = wbRequest::getVarClean('rowCount', 'int', 10);

        $sort = wbRequest::getVarClean('sortby', 'str', '');
        $dir = wbRequest::getVarClean('sortdir', 'str', 'ASC');
        $searchPhrase = wbRequest::getVarClean('searchPhrase', 'str', '');

        /* post params */
        $service_no = wbRequest::getVarClean('service_no', 'str', '');
        $p_bank_branch_id = wbRequest::getVarClean('p_bank_branch_id', 'int', 0);
        $action = wbRequest::getVarClean('action', 'str', 'queryreceipt');
        $i_id = wbRequest::getVarClean('i_id', 'array', array());
        $i_subscriberid = wbRequest::getVarClean('i_subscriberid', 'int', 0);
        $cboxdeposit = wbRequest::getVarClean('cboxdeposit', 'str', 'N');

        $client_ip_address = wbRequest::getVarClean('client_ip_address', 'str', '');
        $p_user_loket_id = wbRequest::getVarClean('p_user_loket_id', 'int', 0);
        $user_name = wbRequest::getVarClean('user_name', 'str', '');

        /* make $i_id with comma separated */
        $idList = "";
        $prefix = "";
        foreach ($i_id as $theid){
            $idList .= $prefix . "'" . substr($theid,0,strpos($theid,"_")) . "'";
            $prefix = ", ";
        }
        
        $start = ($page-1) * $limit;

        try{
            
            $items = array();
            $table =& wbModule::getModel('paymentccbs', 'payment');

            $table->dbconn->fetchMode = PGSQL_NUM;
            $query = "SELECT * FROM ifp.f_pay_acc(?,?,?,?,?,?,?,?,?,?,?,?)"; /* tambahkan subscriber id, use_deposit */
            $result =& $table->dbconn->Execute($query, array($action, $service_no, $start, $limit, $idList, $p_user_loket_id, $user_name, $p_bank_branch_id, $client_ip_address, $i_subscriberid, $cboxdeposit, strtoupper(date("d-M-Y"))));

            $rows = $result->fields;

            if( isset ($rows['cnt']) ) {

                $data['total'] = $rows['cnt'];
                $data['message'] = $rows['msg'];
                $data['success'] = true;

                if( $rows['cnt'] == 0 ) {
                    $data['items'] = array();
                    if(strtoupper(substr($rows['msg'],0,5)) == 'ERROR') {
                        $data['success'] = false;
                    }
                }else {
                    $rows = array($rows);
                    for($i = 0; $i < count($rows); $i++)
                        $rows[$i]['id'] .= "_".$rows[$i]['account_no'];

                    $data['items'] = $rows;
                }

            }else {
                for($i = 0; $i < count($rows); $i++)
                    $rows[$i]['id'] .= "_".$rows[$i]['account_no'];

                $data['items'] = $rows;
                $data['total'] = $rows[0]['cnt'];
                $data['message'] = $rows[0]['msg'];
                $data['success'] = true;
            }

            $data['current'] = $page;
            $data['rowCount'] = $limit;


        }catch(Exception $e){
            $data['message'] = $e->getMessage();
            $data['success'] = false;
        }

        return $data;
    }
}
?>