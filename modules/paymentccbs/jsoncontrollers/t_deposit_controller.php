<?php
/**
 * inquiry
 * class controller for table bds_inquiry
 *
 * @since 23-10-2012 12:07:20
 * @author wiliamdecosta@gmail.com
 */
class t_deposit_controller extends wbController {

    public static function read($args = array()){
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        extract($args);

        $page = wbRequest::getVarClean('current', 'int', 1);
        $limit = wbRequest::getVarClean('rowCount', 'int', -1);

        $sort = wbRequest::getVarClean('sortby', 'str', 't_deposit_id');
        $dir = wbRequest::getVarClean('sortdir', 'str', 'DESC');
        $searchPhrase = wbRequest::getVarClean('searchPhrase', 'str', '');
        
        $t_deposit_id = wbRequest::getVarClean('t_deposit_id', 'int', 0);
        $subscriber_id = wbRequest::getVarClean('subscriber_id', 'int', 0);
        
        $start = ($page-1) * $limit;

        try{
            $table =& wbModule::getModel('paymentccbs', 't_deposit');
                        
            if(!empty($searchPhrase)) {
                $table->setCriteria("(deposit.trans_no ILIKE ? )", array('%'.$searchPhrase.'%'));
            }
            
            if(!empty($subscriber_id)) {
                $table->setCriteria("deposit.subscriber_id = ?", array($subscriber_id));
            }

            $items = $table->getAll($start, $limit, $sort, $dir);
            $total = $table->countAll();
            
            if(count($items) > 0) {
                $items[0]['status'] = 3;    
            }
            
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
    
    
    public static function add_deposit($args = array()){
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        $service_no = wbRequest::getVarClean('service_no', 'str', '');
        $account_no = wbRequest::getVarClean('account_no', 'str', '');
        $p_user_loket_id = wbRequest::getVarClean('p_user_loket_id', 'str', '');
        $subscriber_id = wbRequest::getVarClean('subscriber_id', 'int', 0);
        
        $deposit_amount = wbRequest::getVarClean('deposit_amount', 'int', 0);
        $is_returnable = wbRequest::getVarClean('is_returnable', 'str', '');
        $ip_address = wbRequest::getVarClean('ip_address', 'str', get_ip_address());
        
        $result = "";
        
        try{
            $table =& wbModule::getModel('paymentccbs', 't_deposit');
            $result = $table->add_deposit_amount($service_no, $account_no, $subscriber_id, $is_returnable, $deposit_amount, $p_user_loket_id, $ip_address);
        
            if($result == 'OK') {
                $data['success'] = true;
                $data['message'] = 'Add deposit amount success';    
            }else {
                $data['message'] = $result; 
            }
            $data['total'] = 1;
        }catch(Exception $e){
            $data['message'] = $e->getMessage();
            $data['success'] = false;
        }

        return $data;
    }
    
    public static function cancel_deposit($args = array()){
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        $p_user_loket_id = wbRequest::getVarClean('p_user_loket_id', 'int', 0);
        $subscriber_id = wbRequest::getVarClean('subscriber_id', 'int', 0);
        
        $t_deposit_id = wbRequest::getVarClean('t_deposit_id', 'int', 0);
        $ip_address = wbRequest::getVarClean('ip_address', 'str', get_ip_address());
        
        $result = "";
        
        try{
            $table =& wbModule::getModel('paymentccbs', 't_deposit');
            $result = $table->cancel_deposit_amount($t_deposit_id, $subscriber_id, $p_user_loket_id, $ip_address);
        
            if($result == 'OK') {
                $data['success'] = true;
                $data['message'] = 'Cancel deposit amount success';    
            }else {
                $data['message'] = $result; 
            }
            $data['total'] = 1;
        }catch(Exception $e){
            $data['message'] = $e->getMessage();
            $data['success'] = false;
        }

        return $data;
    }
}
?>