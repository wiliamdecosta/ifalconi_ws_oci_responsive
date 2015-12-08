<?php
/**
 * inquiry
 * class controller for table deposit_controller
 *
 * @since 04/08/2015 13:01:43
 * @author wiliamdecosta@gmail.com
 */
class subscriber_deposit_controller extends wbController{


    public static function get_deposit_amount($args = array()){

        extract($args);

        $subscriber_id = wbRequest::getVarClean('subscriber_id', 'int', 0);

        try{
            $items = array();
            $table =& wbModule::getModel('paymentccbs', 'subscriber_deposit');

            $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
            
            $data['items'] = empty($subscriber_id) ? 0 : $table->get_deposit_amount($subscriber_id);
            $data['success'] = true;
            $data['total'] = 1;
            $data['message'] = 'success return value';
        }catch(Exception $e){
            $data['message'] = $e->getMessage();
            $data['success'] = false;
        }

        return $data;
    }
}
?>