<?php
/**
 * inquiry
 * class controller for table bds_inquiry
 *
 * @since 23-10-2012 12:07:20
 * @author wiliamdecosta@gmail.com
 */
class p_bank_branch_controller extends wbController{

    public static function read($args = array()){
        
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        extract($args);

        $page = wbRequest::getVarClean('current', 'int', 1);
        $limit = wbRequest::getVarClean('rowCount', 'int', 10);

        $sort = wbRequest::getVarClean('sortby', 'str', 'p_bank_branch_id');
        $dir = wbRequest::getVarClean('sortdir', 'str', 'DESC');
        $searchPhrase = wbRequest::getVarClean('searchPhrase', 'str', '');
        
        $p_bank_branch_id = wbRequest::getVarClean('p_bank_branch_id', 'int', 0);
        $p_bank_id = wbRequest::getVarClean('p_bank_id', 'int', 0);
        
        $start = ($page-1) * $limit;

        try{
            $table =& wbModule::getModel('paymentccbs', 'p_bank_branch');
            //Set default criteria. You can override this if you want
            foreach ($table->fields as $key => $field){
                if (!empty($$key)){ // <-- Perhatikan simbol $$
                    if ($field['type'] == 'str'){
                        $table->setCriteria($table->getAlias().$key.$table->likeOperator.'?', array($$key));
                    }else{
                        $table->setCriteria($table->getAlias().$key.' = ?', array($$key));
                    }
                }
            }
            
            if(!empty($searchPhrase)) {
                $table->setCriteria("(UPPER(bank_branch.code) ILIKE ? OR UPPER(bank.code) ILIKE ?)", array('%'.$searchPhrase.'%', '%'.$searchPhrase.'%'));
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
    
}
?>