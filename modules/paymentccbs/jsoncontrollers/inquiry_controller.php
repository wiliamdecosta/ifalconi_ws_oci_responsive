<?php
/**
 * inquiry
 * class controller for table bds_inquiry 
 *
 * @since 23-10-2012 12:07:20
 * @author wiliamdecosta@gmail.com
 */
class inquiry_controller extends wbController{    
    /**
     * read
     * controler for get all items
     */
    public static function read($args = array()){
        // Security check
        //if (!wbSecurity::check('DHotel')) return;
        if (!wbSecurity::check('Inquiry')) return;

        // Get arguments from argument array
        extract($args);
    
        $start = wbRequest::getVarClean('start', 'int', 0);
        $limit = wbRequest::getVarClean('limit', 'int', 50);
        
        $sort = wbRequest::getVarClean('sort', 'str', 'listing_no');
        $dir = wbRequest::getVarClean('dir', 'str', 'ASC');
        $query = wbRequest::getVarClean('query', 'str', '');
        
        $no_registration = wbRequest::getVarClean('no_registration', 'int', 0);

        
        $getAll = wbRequest::getVarClean('getAll', 'str', '');
        
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        try{
            $db = NewADOConnection("oci8");
            $ora_tns = wbConfig::get('DB.ora_tns');
            $ora_user = wbConfig::get('DB.ora_user');
            $ora_pass = wbConfig::get('DB.ora_pass');
            $db->Connect($ora_tns, $ora_user, $ora_pass);
            $stmt = $db->PrepareSP("BEGIN
                                   p_inq_websrvc(
                                   :i_no_layanan,
                                   :i_kode_bank,
                                   :os_jastel ,
                                   :os_id_pelanggan ,
                                   :os_nama ,
                                   :on_jml_bill  ,
                                   :on_jml_adm   ,
                                   :on_return_code ,
                                   :os_inquery_info
                                   );
                                END;
                                ");
            $i_no_layanan = wbRequest::getVarClean('i_no_layanan', 'str', '');
            $i_kode_bank = wbSession::getVar('bank_name');
            $db->InParameter($stmt,$i_no_layanan,'i_no_layanan',4000);
            $db->InParameter($stmt,$i_kode_bank,'i_kode_bank',4000);
            $db->OutParameter($stmt,$os_jastel,'os_jastel',4000);
            $db->OutParameter($stmt,$os_id_pelanggan,'os_id_pelanggan',4000);
            $db->OutParameter($stmt,$os_nama,'os_nama',4000);
            $db->OutParameter($stmt,$on_jml_bill,'on_jml_bill',4000);
            $db->OutParameter($stmt,$on_jml_adm,'on_jml_adm',4000);
            $db->OutParameter($stmt,$on_return_code,'on_return_code',4000);
            $db->OutParameter($stmt,$os_inquery_info,'os_inquery_info',4000);
            $ok = $db->Execute($stmt);
        }catch(UserLoginFailedException $e){
            $data['message'] = $e->getMessage();
        }
        $data['items'] = array(
                            'i_no_layanan' => $i_no_layanan,
                            'i_kode_bank' => $i_kode_bank,
                            'os_jastel' => $os_jastel,
                            'os_id_pelanggan' => $os_id_pelanggan,
                            'os_nama' => $os_nama,
                            'on_jml_bill' => $on_jml_bill,
                            'on_jml_adm' => $on_jml_adm,
                            'on_return_code' => $on_return_code,
                            'os_inquery_info' => $os_inquery_info,
                         );
        $data['total'] = 1;
        $data['success'] = true;
        return $data;    
    }

    /**
     * create
     * controler for create new item
     */    
    public static function create($args = array()){
        // Security check
        //if (!wbSecurity::check('DHotel')) return;

        // Get arguments from argument array
        extract($args);
        
        $data = array('items' => array(), 'success' => false, 'message' => '');
        
        $jsonItems = wbRequest::getVarClean('items', 'str', '');
        
        $items =& wbUtil::jsonDecode($jsonItems);
        if (!is_array($items)){
            $data['message'] = 'Invalid items parameter';
            return $data;
        }
        
        
        $table =& wbModule::getModel('bds', 'inquiry');
        $table->actionType = 'CREATE';
        
        if (isset($items[0])){
        	$errors = array();
        	$numSaved = 0;
        	$numItems = count($items);
        	$savedItems = array();
        	for($i=0; $i < $numItems; $i++){
        		try{
        		    $table->dbconn->BeginTrans();
            		    $items[$i][$table->pkey] = $table->GenID();
            			$table->setRecord($items[$i]);
            			$table->create();
            			$numSaved++;
        			$table->dbconn->CommitTrans();
        		}catch(Exception $e){
        		    $table->dbconn->RollbackTrans();
        			$errors[] = $e->getMessage();
        		}
        		$items[$i] = array_merge($items[$i], $table->record);
        	}
        	$numErrors = count($errors);
        	if (count($errors)){
        		$data['message'] = $numErrors." dari ".$numItems." record gagal disimpan.<br/><br/><b>System Response:</b><br/>- ".implode("<br/>- ", $errors)."";
        	}else{
        		$data['success'] = true;
        		$data['message'] = 'Data berhasil disimpan';
        	}
        	$data['items'] =$items;
        }else{
	        try{
	            // begin transaction block
	            $table->dbconn->BeginTrans();
	                // insert master
    	            $items[$table->pkey] = $table->GenID();
    	            $table->setRecord($items);
    	            $table->create();
    	            // insert detail
                    
    	            
    	            $data['success'] = true;
    	            $data['message'] = 'Data berhasil disimpan';
    	            $data['items'] = $table->get($items[$table->pkey]);
	            // all ok, commit transaction
	            $table->dbconn->CommitTrans();
	        }catch (Exception $e) {
	            // something happen, rollback transaction
	            $table->dbconn->RollbackTrans();
	            $data['message'] = $e->getMessage();
	            $data['items'] = $items;
	        }
	        
        }
    
        return $data;    
    }

    /**
     * update
     * controler for update item
     */        
    public static function update($args = array()){
        // Security check
        //if (!wbSecurity::check('DHotel')) return;
        
        // Get arguments from argument array
        extract($args);
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
            
        $jsonItems = wbRequest::getVarClean('items', 'str', '');
        $items =& wbUtil::jsonDecode($jsonItems);
        if (!is_array($items)){
            $data['message'] = 'Invalid items parameter';
            return $data;
        }
        
        $table =& wbModule::getModel('bds', 'inquiry');
        
        $table->actionType = 'UPDATE';
        
        if (isset($items[0])){
        	$errors = array();
        	$numSaved = 0;
        	$numItems = count($items);
        	$savedItems = array();
        	for($i=0; $i < $numItems; $i++){
        		try{
        			$table->setRecord($items[$i]);
        			$table->update();
        			$numSaved++;
        		}catch(Exception $e){
        			$errors[] = $e->getMessage();
        		}
        		$items[$i] = array_merge($items[$i], $table->record);
        	}
        	$numErrors = count($errors);
        	if (count($errors)){
        		$data['message'] = $numErrors." dari ".$numItems." record gagal di-update.<br/><br/><b>System Response:</b><br/>- ".implode("<br/>- ", $errors)."";
        	}else{
        		$data['success'] = true;
        		$data['message'] = 'Data berhasil di-update';
        	}
        	$data['items'] =$items;
        }else{
	        try{
	            $table->setRecord($items);
	            $table->update();

	            $data['success'] = true;
	            $data['message'] = 'Data berhasil di-update';
	            
	        }catch (Exception $e) {
	            $data['message'] = $e->getMessage();
	        }
	        $data['items'] = array_merge($items, $table->record);
        }
    
        return $data;    
    }

    /**
     * update
     * controler for remove item
     */            
    public static function destroy($args = array()){
        // Security check
        //if (!wbSecurity::check('DHotel')) return;
        
        // Get arguments from argument array
        extract($args);
    
        $jsonItems = wbRequest::getVarClean('items', 'str', '');
        $items =& wbUtil::jsonDecode($jsonItems);
        
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
    
        $table =& wbModule::getModel('bds', 'inquiry');
        
        try{
            $table->dbconn->BeginTrans();
                if (is_array($items)){
                    foreach ($items as $key => $value){
                        if (empty($value)) throw new Exception('Empty parameter');
                        
                        $table->remove($value);
                        $data['items'][] = array($table->pkey => $value);
                        $data['total']++;
                    }
                }else{
                    $items = (int) $items;
                    if (empty($items)){
                        throw new Exception('Empty parameter');
                    }
        
                    $table->remove($items);
                    $data['items'][] = array($table->pkey => $items);
                    $data['total'] = 1;            
                }
    
                $data['success'] = true;
                $data['message'] = $data['total'].' Data berhasil dihapus';
            $table->dbconn->CommitTrans();
        }catch (Exception $e) {
            $table->dbconn->RollbackTrans();
            $data['message'] = $e->getMessage();
            $data['items'] = array();
            $data['total'] = 0;            
        }
    
        return $data;    
    }
    
    /*
    * IMPORT WALKIN
    */
    public static function upload_excel($args = array()){
        
        $jsonItems = wbRequest::getVarClean('jsonItems', 'str', '');        
        $item = wbUtil::jsonDecode($jsonItems);
        
        $table =& wbModule::getModel('bds', 'inquiry');
        $table->actionType = 'CREATE';
        
        $items = $item['items'];
        $data = array('items' => array(), 'total' => 0, 'success' => true, 'message' => '');
        try {
            foreach($items as $rec) {
                $rec[$table->pkey] = $table->GenID();
                $table->setRecord($rec);
                $table->create();
            }
            return $data;
        }catch(Exception $e) {
            $data['success'] = false;
            $data['message'] = $e->getMessage();
            return $data;   
        }
       
    } 
    public static function execPembayaran($args = array()){
        // Security check
        if (!wbSecurity::check('Inquiry')) return;

        // Get arguments from argument array
        extract($args);
    
        $start = wbRequest::getVarClean('start', 'int', 0);
        $limit = wbRequest::getVarClean('limit', 'int', 50);
        
        $sort = wbRequest::getVarClean('sort', 'str', 'listing_no');
        $dir = wbRequest::getVarClean('dir', 'str', 'ASC');
        $query = wbRequest::getVarClean('query', 'str', '');
        
        $no_registration = wbRequest::getVarClean('no_registration', 'int', 0);

        
        $getAll = wbRequest::getVarClean('getAll', 'str', '');
        
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        try{
            $db = NewADOConnection("oci8");
            $ora_tns = wbConfig::get('DB.ora_tns');
            $ora_user = wbConfig::get('DB.ora_user');
            $ora_pass = wbConfig::get('DB.ora_pass');
            $db->Connect($ora_tns, $ora_user, $ora_pass);
            $stmt = $db->PrepareSP("BEGIN
                            p_pay_websrvc(
                               :i_no_layanan,
                               :i_kode_bank,
                               :branch_id,
                               :user_id, 
                               :user_name,
                               :i_id_pelanggan,
                               :i_nama,
                               :i_jml_bill,
                               :i_jml_adm,
                               :on_return_code,
                               :os_receipt_no ,
                               :os_payment_info
                               );
                                END;
                                ");
            $i_no_layanan = wbRequest::getVarClean('no_layanan','str', '');;
            $i_kode_bank = wbSession::getVar('bank_name');
            $branch_id = wbSession::getVar('branch_id');
            $user_id = wbSession::getVar('user_id');
            $user_name = wbSession::getVar('user_name');
            $i_id_pelanggan = wbRequest::getVarClean('id_pelanggan', 'int', 0);
            $i_nama =  wbRequest::getVarClean('os_nama', 'str', '');
            $i_jml_bill = wbRequest::getVarClean('jml_bill', 'float', 0);
            $i_jml_adm = wbRequest::getVarClean('jml_adm', 'float', 0);
 
            $db->InParameter($stmt,$i_no_layanan,'i_no_layanan',4000);
            $db->InParameter($stmt,$i_kode_bank,'i_kode_bank',4000);     
            $db->InParameter($stmt,$branch_id ,'branch_id',4000);    
            $db->InParameter($stmt,$user_id,'user_id',4000);      
            $db->InParameter($stmt,$user_name,'user_name',4000);    
            $db->InParameter($stmt,$i_id_pelanggan,'i_id_pelanggan',4000);
            $db->InParameter($stmt,$i_nama,'i_nama',4000);      
            $db->InParameter($stmt,$i_jml_bill,'i_jml_bill',4000);   
            $db->InParameter($stmt,$i_jml_adm,'i_jml_adm',4000);    
            $db->OutParameter($stmt,$on_return_code,'on_return_code',4000);
            $db->OutParameter($stmt,$os_receipt_no,'os_receipt_no',4000);
            $db->OutParameter($stmt,$os_payment_info,'os_payment_info',4000);
            $ok = $db->Execute($stmt);
        }catch(UserLoginFailedException $e){
            $data['message'] = $e->getMessage();
        }
        $data['items'] = $os_payment_info;
        $data['total'] = 1;
        $data['success'] = true;
        return $data;    
    }
    public static function cancelPembayaran($args = array()){
        // Security check
        if (!wbSecurity::check('Inquiry')) return;

        // Get arguments from argument array
        extract($args);
    
        $start = wbRequest::getVarClean('start', 'int', 0);
        $limit = wbRequest::getVarClean('limit', 'int', 50);
        
        $sort = wbRequest::getVarClean('sort', 'str', 'listing_no');
        $dir = wbRequest::getVarClean('dir', 'str', 'ASC');
        $query = wbRequest::getVarClean('query', 'str', '');
        
        $no_registration = wbRequest::getVarClean('no_registration', 'int', 0);

        
        $getAll = wbRequest::getVarClean('getAll', 'str', '');
        
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        try{
            $db = NewADOConnection("oci8");
            $ora_tns = wbConfig::get('DB.ora_tns');
            $ora_user = wbConfig::get('DB.ora_user');
            $ora_pass = wbConfig::get('DB.ora_pass');
        $db->Connect($ora_tns, $ora_user, $ora_pass);
            $stmt = $db->PrepareSP("BEGIN
                            p_inq_cancel_websrvc(
                               :i_no_kuitansi, 
                               :i_kode_bank,
                               :bank_branch_id,
                               :user_id, 
                               :os_jastel ,
                               :os_id_pelanggan ,
                               :os_nama  ,
                               :os_loket_no ,
                               :os_branch_code ,
                               :on_pay_branch_id ,
                               :on_pay_user_id ,
                               :os_pay_username ,
                               :os_paydate ,
                               :on_jml_bill  ,
                               :on_jml_adm   ,
                               :on_payment_receipt_id ,
                               :on_return_code ,
                               :os_payment_info 
                               ) ;
                                END;
                                ");
            $i_no_kuitansi = wbRequest::getVarClean('i_no_kuitansi','str', '');;
            $i_kode_bank = wbSession::getVar('bank_name');
            $branch_id = wbSession::getVar('branch_id');
            $user_id = wbSession::getVar('user_id');
            
            $db->InParameter($stmt,$i_no_kuitansi,'i_no_kuitansi',4000);
            $db->InParameter($stmt,$i_kode_bank,'i_kode_bank',4000);
            $db->InParameter($stmt,$branch_id,'bank_branch_id',4000);
            $db->InParameter($stmt,$user_id,'user_id',4000);
            
            $db->OutParameter($stmt,$os_jastel,'os_jastel',4000);
            $db->OutParameter($stmt,$os_id_pelanggan,'os_id_pelanggan',4000);     
            $db->OutParameter($stmt,$os_nama ,'os_nama',4000);    
            $db->OutParameter($stmt,$os_loket_no,'os_loket_no',4000);      
            $db->OutParameter($stmt,$os_branch_code,'os_branch_code',4000);    
            $db->OutParameter($stmt,$on_pay_branch_id,'on_pay_branch_id',4000);
            $db->OutParameter($stmt,$on_pay_user_id,'on_pay_user_id',4000);      
            $db->OutParameter($stmt,$os_pay_username,'os_pay_username',4000);   
            $db->OutParameter($stmt,$os_paydate,'os_paydate',4000);    
            $db->OutParameter($stmt,$on_jml_bill,'on_jml_bill',4000);
            $db->OutParameter($stmt,$on_jml_adm,'on_jml_adm',4000);
            $db->OutParameter($stmt,$on_payment_receipt_id,'on_payment_receipt_id',4000);
            $db->OutParameter($stmt,$on_return_code,'on_return_code',4000);
            $db->OutParameter($stmt,$os_payment_info,'os_payment_info',4000);
            $ok = $db->Execute($stmt);
        }catch(UserLoginFailedException $e){
            $data['message'] = $e->getMessage();
        }
        $data['items'] = array('os_jastel' => $os_jastel,
                               'os_id_pelanggan' => $os_id_pelanggan,
                               'os_nama' => $os_nama,
                               'os_loket_no' => $os_loket_no,
                               'os_branch_code' => $os_branch_code,
                               'on_pay_branch_id' => $on_pay_branch_id,
                               'on_pay_user_id' => $on_pay_user_id,
                               'os_pay_username' => $os_pay_username,
                               'os_paydate' => $os_paydate,
                               'on_jml_bill' => $on_jml_bill,
                               'on_jml_adm' => $on_jml_adm,
                               'on_payment_receipt_id' => $on_payment_receipt_id,
                               'on_return_code' => $on_return_code,
                               'os_payment_info' => $os_payment_info,
                               );
        $data['total'] = 1;
        $data['success'] = true;
        return $data;    
    }
    public static function execCancelPembayaran($args = array()){
        // Security check
        if (!wbSecurity::check('Inquiry')) return;

        // Get arguments from argument array
        extract($args);
    
        $start = wbRequest::getVarClean('start', 'int', 0);
        $limit = wbRequest::getVarClean('limit', 'int', 50);
        
        $sort = wbRequest::getVarClean('sort', 'str', 'listing_no');
        $dir = wbRequest::getVarClean('dir', 'str', 'ASC');
        $query = wbRequest::getVarClean('query', 'str', '');
        
        $no_registration = wbRequest::getVarClean('no_registration', 'int', 0);

        
        $getAll = wbRequest::getVarClean('getAll', 'str', '');
        
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        try{
            $db = NewADOConnection("oci8");
            $ora_tns = wbConfig::get('DB.ora_tns');
            $ora_user = wbConfig::get('DB.ora_user');
            $ora_pass = wbConfig::get('DB.ora_pass');
        $db->Connect($ora_tns, $ora_user, $ora_pass);
            $stmt = $db->PrepareSP("BEGIN
                            p_req_cancel_websrvc(
                               :i_no_kuitansi,
                               :i_kode_bank,
                               :bank_branch_id,
                               :user_id,
                               :user_name,  
                               :is_jastel ,
                               :is_id_pelanggan ,
                               :is_nama  ,
                               :is_loket_no ,
                               :is_branch_code ,
                               :in_pay_branch_id ,
                               :in_pay_user_id ,
                               :is_pay_username ,
                               :is_paydate ,
                               :in_jml_bill  ,
                               :in_jml_adm   ,
                               :in_payment_receipt_id ,
                               :is_reason,
                               :on_return_code ,
                               :os_return_msg 
                               ) ;
                                END;
                                ");
            $i_kode_bank = wbSession::getVar('bank_name');
            $branch_id = wbSession::getVar('branch_id');
            $user_id = wbSession::getVar('user_id');
            $user_name = wbSession::getVar('user_name');
            
            $i_no_kuitansi = wbRequest::getVarClean('i_no_kuitansi', 'str', '');
            $is_jastel = wbRequest::getVarClean('is_jastel', 'str', '');
            $is_id_pelanggan = wbRequest::getVarClean('is_id_pelanggan', 'str', '');
            $is_nama = wbRequest::getVarClean('is_nama', 'str', 'listing_no');
            $is_loket_no = wbRequest::getVarClean('is_loket_no', 'str', '');
            $is_branch_code = wbRequest::getVarClean('is_branch_code', 'str', '');
            $in_pay_branch_id = wbRequest::getVarClean('in_pay_branch_id', 'int', 0);
            $in_pay_user_id = wbRequest::getVarClean('in_pay_user_id', 'int',0);
            $is_pay_username = wbRequest::getVarClean('is_pay_username', 'str', '');
            $is_paydate = wbRequest::getVarClean('is_paydate', 'str', '');
            $in_jml_bill = wbRequest::getVarClean('in_jml_bill', 'float', 0);
            $in_jml_adm = wbRequest::getVarClean('in_jml_adm', 'float', 0);
            $in_payment_receipt_id = wbRequest::getVarClean('in_payment_receipt_id', 'int', 0);
            $is_reason = wbRequest::getVarClean('is_reason', 'str', '');
            
            $db->InParameter($stmt,$i_kode_bank,'i_kode_bank',4000);
            $db->InParameter($stmt,$branch_id,'bank_branch_id',4000);
            $db->InParameter($stmt,$user_id,'user_id',4000);
            $db->InParameter($stmt,$user_name,'user_name',4000);
            
            $db->InParameter($stmt,$i_no_kuitansi,'i_no_kuitansi',4000);
            $db->InParameter($stmt,$is_jastel,'is_jastel',4000);
            $db->InParameter($stmt,$is_id_pelanggan,'is_id_pelanggan',4000);     
            $db->InParameter($stmt,$is_nama ,'is_nama',4000);    
            $db->InParameter($stmt,$is_loket_no,'is_loket_no',4000);      
            $db->InParameter($stmt,$is_branch_code,'is_branch_code',4000);    
            $db->InParameter($stmt,$in_pay_branch_id,'in_pay_branch_id',4000);
            $db->InParameter($stmt,$in_pay_user_id,'in_pay_user_id',4000);      
            $db->InParameter($stmt,$is_pay_username,'is_pay_username',4000);   
            $db->InParameter($stmt,$is_paydate,'is_paydate',4000);    
            $db->InParameter($stmt,$in_jml_bill,'in_jml_bill',4000);
            $db->InParameter($stmt,$in_jml_adm,'in_jml_adm',4000);
            $db->InParameter($stmt,$in_payment_receipt_id,'in_payment_receipt_id',4000);
            $db->InParameter($stmt,$is_reason,'is_reason',4000);
            $db->OutParameter($stmt,$on_return_code,'on_return_code',4000);
            $db->OutParameter($stmt,$os_return_msg,'os_return_msg',4000);
            //echo '<pre>';
            //print_r(array($i_kode_bank,$branch_id,$user_id,$i_no_kuitansi,$is_jastel,$is_id_pelanggan,$is_nama,$is_loket_no,$is_branch_code,$in_pay_branch_id,$in_pay_user_id,$is_pay_username,$is_paydate,$in_jml_bill,$in_jml_adm,$in_payment_receipt_id,$is_reason));
            //exit;
            $ok = $db->Execute($stmt);
            //exit;
        }catch(Exception $e){
            $data['message'] = $e->getMessage();
            echo 'tes';
            exit;
        }
        $data['items'] = array(
                               'on_return_code' => $on_return_code,
                               'os_return_msg' => $os_return_msg,
                               );
        $data['total'] = 1;
        $data['success'] = true;
        return $data;    
    }
    public static function reqCancelInquiry($args = array()){
        // Security check
        if (!wbSecurity::check('Inquiry')) return;

        // Get arguments from argument array
        extract($args);
    
        $start = wbRequest::getVarClean('start', 'int', 0);
        $limit = wbRequest::getVarClean('limit', 'int', 50);
        
        $sort = wbRequest::getVarClean('sort', 'str', 'p_web_user_id');
        $dir = wbRequest::getVarClean('dir', 'str', 'ASC');
        $query = wbRequest::getVarClean('query', 'str', '');
        
        $p_web_user_id = wbSession::getVar('user_id');

        
        $getAll = wbRequest::getVarClean('getAll', 'str', '');
        
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        try{
            $db = NewADOConnection("oci8");
            $ora_tns = wbConfig::get('DB.ora_tns');
            $ora_user = wbConfig::get('DB.ora_user');
            $ora_pass = wbConfig::get('DB.ora_pass');
            $db->Connect($ora_tns, $ora_user, $ora_pass);
            $sql = 'select a.*
                    From T_CANCEL_PAYMENT_REQ a
                    where a.T_CANCEL_RECEIPT_ID is null
                    and a.REJECTED_BY_USER_ID is null
                    and REQUEST_BRANCH_ID in (select P_BANK_BRANCH_ID 
                               from P_BRANCH_SUPERVISOR 
                               where P_WEB_USER_ID = :p_web_user_id
                               )
                    order by a.REQUEST_DATE DESC';
            $sql_count = 'select count(*)
                    From T_CANCEL_PAYMENT_REQ a
                    where a.T_CANCEL_RECEIPT_ID is null
                    and a.REJECTED_BY_USER_ID is null
                    and REQUEST_BRANCH_ID in (select P_BANK_BRANCH_ID 
                               from P_BRANCH_SUPERVISOR 
                               where P_WEB_USER_ID = :p_web_user_id
                               )';
            $items = $db->GetAllAssocLimit($sql,$limit,$start, array('p_web_user_id' => $p_web_user_id));
            $total = $db->GetOne($sql_count, array('p_web_user_id' => $p_web_user_id));
        }catch(UserLoginFailedException $e){
            $data['message'] = $e->getMessage();
        }
        $data['items'] = $items;
        $data['total'] = $total;
        $data['message'] = 'Success';
        $data['success'] = true;
        return $data;    
    }
    public static function reqCancelStatus($args = array()){
        // Security check
        if (!wbSecurity::check('Inquiry')) return;

        // Get arguments from argument array
        extract($args);
    
        $start = wbRequest::getVarClean('start', 'int', 0);
        $limit = wbRequest::getVarClean('limit', 'int', 50);
        
        $sort = wbRequest::getVarClean('sort', 'str', 'p_web_user_id');
        $dir = wbRequest::getVarClean('dir', 'str', 'ASC');
        $query = wbRequest::getVarClean('query', 'str', '');
        
        $p_web_user_id = wbSession::getVar('user_id');

        
        $getAll = wbRequest::getVarClean('getAll', 'str', '');
        
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        try{
            $db = NewADOConnection("oci8");
            $ora_tns = wbConfig::get('DB.ora_tns');
            $ora_user = wbConfig::get('DB.ora_user');
            $ora_pass = wbConfig::get('DB.ora_pass');
            $db->Connect($ora_tns, $ora_user, $ora_pass);
            $sql = "select a.*,
                           decode(a.T_CANCEL_RECEIPT_ID, null, decode(a.REJECTED_BY_USER_ID, null, 'Permohonan', 'Rejected'), 'Approved') as Status
                    From T_CANCEL_PAYMENT_REQ a
                    where a.REQUEST_USER_ID = :p_web_user_id
                    order by a.REQUEST_DATE DESC";
            $sql_count = 'select count(*)
                    From T_CANCEL_PAYMENT_REQ a
                    where a.REQUEST_USER_ID = :p_web_user_id';
            $items = $db->GetAllAssocLimit($sql,$limit,$start, array('p_web_user_id' => $p_web_user_id));
            $total = $db->GetOne($sql_count, array('p_web_user_id' => $p_web_user_id));
        }catch(UserLoginFailedException $e){
            $data['message'] = $e->getMessage();
        }
        $data['items'] = $items;
        $data['total'] = $total;
        $data['message'] = 'Success';
        $data['success'] = true;
        return $data;    
    }
    public static function report($args = array()){
       if (!wbSecurity::check('Inquiry')) return;

        // Get arguments from argument array
        extract($args);
    
        $start = wbRequest::getVarClean('start', 'int', 0);
        $limit = wbRequest::getVarClean('limit', 'int', 50);
        
        $sort = wbRequest::getVarClean('sort', 'str', '');
        $dir = wbRequest::getVarClean('dir', 'str', 'ASC');
        $query = wbRequest::getVarClean('query', 'str', '');
        
        $lap_date = strtoupper(wbRequest::getVarClean('lap_date', 'str', ''));
        $i_report_type = wbRequest::getVarClean('jenis_laporan', 'int', 0);
        $branch_id = wbSession::getVar('branch_id');
        $user_id = wbSession::getVar('user_id');
        
        $getAll = wbRequest::getVarClean('getAll', 'str', '');
        
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        try{
            $db = NewADOConnection("oci8");
            $ora_tns = wbConfig::get('DB.ora_tns');
            $ora_user = wbConfig::get('DB.ora_user');
            $ora_pass = wbConfig::get('DB.ora_pass');
        $db->Connect($ora_tns, $ora_user, $ora_pass);
            $sql = "select s01 periode,
                           s05 no_kuitansi,
                           s02 dn,
                           s06 nama,
                           n01 tagihan,
                           n02 ppn,
                           n03 denda,
                           n04 meterai,
                           n05 deposit,
                           n06 total,
                           N07 bea_admin,
                           n08 total_pay,
                           s03 loket,
                           s04 username 
                    from table(f_lap_tel75_websrvc (:lap_date,:branch_id,:user_id,:i_report_type))";
            $items = $db->GetAllAssocLimit($sql,$limit,$start, array('lap_date' => $lap_date,'branch_id' => $branch_id,'user_id' => $user_id,'i_report_type' => $i_report_type));
        }catch(UserLoginFailedException $e){
            $data['message'] = $e->getMessage();
        }
        $data['items'] = $items;
        $data['total'] = $total;
        $data['message'] = 'Success';
        $data['success'] = true;
        return $data;    
    }
    public static function submitCancel($args = array()){
        // Security check
        if (!wbSecurity::check('Inquiry')) return;

        // Get arguments from argument array
        extract($args);
    
        $start = wbRequest::getVarClean('start', 'int', 0);
        $limit = wbRequest::getVarClean('limit', 'int', 50);
        
        $sort = wbRequest::getVarClean('sort', 'str', 'listing_no');
        $dir = wbRequest::getVarClean('dir', 'str', 'ASC');
        $query = wbRequest::getVarClean('query', 'str', '');
        
        $no_registration = wbRequest::getVarClean('no_registration', 'int', 0);

        
        $getAll = wbRequest::getVarClean('getAll', 'str', '');
        
        $jsonItems = wbRequest::getVarClean('items', 'str', '');
        
        $arrItems = (array)json_decode($jsonItems);

        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        try{
            $db = NewADOConnection("oci8");
            $ora_tns = wbConfig::get('DB.ora_tns');
            $ora_user = wbConfig::get('DB.ora_user');
            $ora_pass = wbConfig::get('DB.ora_pass');
        $db->Connect($ora_tns, $ora_user, $ora_pass);
            $stmt = $db->PrepareSP("BEGIN
                            p_exec_cancel_websrvc(:i_receipt_no,
                               :in_payment_receipt_id,
                               :i_kode_bank,
                               :i_branch_id,
                               :i_user_id,
                               :i_user_name,
                               :i_id_pelanggan,
                               :i_nama,
                               :i_jml_bill,
                               :i_jml_adm,
                               :on_t_cancel_receipt_id,
                               :on_return_code,
                               :os_cancel_info
                               );
                                END;
                                ");
            $i_kode_bank = wbSession::getVar('bank_name');
            $branch_id = wbSession::getVar('branch_id');
            $user_id = wbSession::getVar('user_id');
            $user_name = wbSession::getVar('user_name');

            $in_payment_receipt_id = $arrItems['T_RECEIPT_ID'];
            $i_receipt_no = $arrItems['RECEIPT_NO'];
            $i_id_pelanggan = $arrItems['ID_PELANGGAN'];
            $i_nama = $arrItems['ACC_LAST_NAME'];
            $i_jml_bill = $arrItems['BILL_AMOUNT'];
            $i_jml_adm = $arrItems['ADMIN_AMOUNT'];
            
            
            $db->InParameter($stmt,$i_kode_bank,'i_kode_bank',4000);
            $db->InParameter($stmt,$branch_id,'i_branch_id',4000);
            $db->InParameter($stmt,$user_id,'i_user_id',4000);
            $db->InParameter($stmt,$user_name,'i_user_name',4000);
            
            $db->InParameter($stmt,$in_payment_receipt_id,'in_payment_receipt_id',4000);
            $db->InParameter($stmt,$i_receipt_no,'i_receipt_no',4000);
            $db->InParameter($stmt,$i_id_pelanggan,'i_id_pelanggan',4000);     
            $db->InParameter($stmt,$i_nama ,'i_nama',4000);    
            $db->InParameter($stmt,$i_jml_adm,'i_jml_adm',4000);      
            $db->InParameter($stmt,$i_jml_bill,'i_jml_bill',4000);
            $db->OutParameter($stmt,$on_t_cancel_receipt_id,'on_t_cancel_receipt_id',4000);    
            $db->OutParameter($stmt,$on_return_code,'on_return_code',4000);
            $db->OutParameter($stmt,$os_return_msg,'os_cancel_info',4000);
            $ok = $db->Execute($stmt);
            //exit;
        }catch(Exception $e){
            $data['message'] = $e->getMessage();
            $data['success'] = false;
        }
        $data['items'] = array(
                               'on_return_code' => $on_return_code,
                               'os_cancel_info' => $os_return_msg,
                               'on_t_cancel_receipt_id'=> $on_t_cancel_receipt_id
                               );
        $data['total'] = 1;
        $data['success'] = true;
        $data['message'] = 'Success';
        return $data;    
    }
    public static function reportSupervisor($args = array()){
       if (!wbSecurity::check('Inquiry')) return;
        // Get arguments from argument array
        extract($args);
    
        $start = wbRequest::getVarClean('start', 'int', 0);
        $limit = wbRequest::getVarClean('limit', 'int', 50);
        
        $sort = wbRequest::getVarClean('sort', 'str', '');
        $dir = wbRequest::getVarClean('dir', 'str', 'ASC');
        $query = wbRequest::getVarClean('query', 'str', '');
        
        $start_date = strtoupper(wbRequest::getVarClean('start_date', 'str', ''));
        $end_date = strtoupper(wbRequest::getVarClean('end_date', 'str', ''));
        $p_bank_branch_id = wbRequest::getVarClean('p_bank_branch_id', 'int', 0);
        $all_store = wbRequest::getVarClean('all_store', 'str', '');
        $branch_id = wbSession::getVar('branch_id');
        $user_id = wbSession::getVar('user_id');
        $i_report_type = wbRequest::getVarClean('jenis_laporan', 'int', 0);
        if($all_store=='true'){
            $i_loket_id = null;
        }else{
            if(!empty($p_bank_branch_id)){
                $i_loket_id = $p_bank_branch_id;
            }else{
                $i_loket_id = null;
            }
        }
        
        $getAll = wbRequest::getVarClean('getAll', 'str', '');
        
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        try{
            $user_level = wbSession::getVar('user_level');
            $db = NewADOConnection("oci8");
            $ora_tns = wbConfig::get('DB.ora_tns');
            $ora_user = wbConfig::get('DB.ora_user');
            $ora_pass = wbConfig::get('DB.ora_pass');
            $db->Connect($ora_tns, $ora_user, $ora_pass);
            if($user_level=='S' || $user_level=='H'){
                $sql = "select 
                      s07 payment_type,
                      s08 trans_date,
                      s09 store_code,
                      s01 periode,
                      s05 no_kuitansi,
                      s02 dn,
                      s06 nama,
                      n01 tagihan,
                      n02 ppn,
                      n03 denda,
                      n04 meterai,
                      n05 deposit,
                      n06 total,
                      N07 bea_admin,
                      n08 total_pay,
                      s03 loket,
                      s04 username 
                from table(f_lap_tel75_suv_websrvc (:start_date, :end_date, :i_loket_id, :i_user_id,:i_report_type))
                order by s04,s07,s08";
            }else if($user_level=='A'){
                $sql ="select 
                           S03 as payment_type,
                           S09 as STORE_CODE,
                           S01 as tgl_transaksi ,
                           N01 as branch_id ,
                           S02 as branch_code,
                           N02 as jml_transaksi,
                           N03 as tagihan,
                           N04 as biaya_adm,
                           N05 as total_pay
                    from table(f_lap_tel75_HO_websrvc (:start_date, :end_date, :i_loket_id, :i_user_id,:i_report_type))
                    order by S01,S09,payment_type";

            }
            $items = $db->GetAllAssocLimit($sql,$limit,$start,array('start_date' => $start_date,'end_date' => $end_date,'i_loket_id' => $i_loket_id,'i_user_id' => $user_id,'i_report_type' => null));
        }catch(UserLoginFailedException $e){
            $data['message'] = $e->getMessage();
            $data['success'] = false;
        }
        $data['items'] = array('items' => $items,'user_level' => $user_level);
        $data['total'] = $total;
        $data['message'] = 'Success';
        $data['success'] = true;
        return $data;    
    }
    public static function reportHoDetil($args = array()){
       if (!wbSecurity::check('Inquiry')) return;
        // Get arguments from argument array
        extract($args);
    
        $start = wbRequest::getVarClean('start', 'int', 0);
        $limit = wbRequest::getVarClean('limit', 'int', 50);
        
        $sort = wbRequest::getVarClean('sort', 'str', '');
        $dir = wbRequest::getVarClean('dir', 'str', 'ASC');
        $query = wbRequest::getVarClean('query', 'str', '');
        
        $start_date = strtoupper(wbRequest::getVarClean('start_date', 'str', ''));
        $end_date = strtoupper(wbRequest::getVarClean('end_date', 'str', ''));
        $p_bank_branch_id = wbRequest::getVarClean('p_bank_branch_id', 'int', 0);
        $all_store = wbRequest::getVarClean('all_store', 'str', '');
        $branch_id = wbSession::getVar('branch_id');
        $user_id = wbSession::getVar('user_id');
        $i_report_type = wbRequest::getVarClean('jenis_laporan', 'int', 0);
        if($all_store=='true'){
            $i_loket_id = null;
        }else{
            if(!empty($p_bank_branch_id)){
                $i_loket_id = $p_bank_branch_id;
            }else{
                $i_loket_id = null;
            }
        }
        
        $getAll = wbRequest::getVarClean('getAll', 'str', '');
        
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        try{
            $user_level = wbSession::getVar('user_level');
            $db = NewADOConnection("oci8");
            $ora_tns = wbConfig::get('DB.ora_tns');
            $ora_user = wbConfig::get('DB.ora_user');
            $ora_pass = wbConfig::get('DB.ora_pass');
            $db->Connect($ora_tns, $ora_user, $ora_pass);
            if($user_level=='A'){
                $sql = "select 
                      s07 payment_type,
                      s08 trans_date,
                      s09 store_code,
                      s01 periode,
                      s05 no_kuitansi,
                      s02 dn,
                      s06 nama,
                      n01 tagihan,
                      n02 ppn,
                      n03 denda,
                      n04 meterai,
                      n05 deposit,
                      n06 total,
                      N07 bea_admin,
                      n08 total_pay,
                      s03 loket,
                      s04 username 
                from table(f_lap_tel75_det_ho_websrvc (:start_date, :end_date, :i_loket_id, :i_user_id,:i_report_type))
                order by s04,s07,s08";
            }
            $items = $db->GetAllAssocLimit($sql,$limit,$start,array('start_date' => $start_date,'end_date' => $end_date,'i_loket_id' => $i_loket_id,'i_user_id' => $user_id,'i_report_type' => null));
        }catch(UserLoginFailedException $e){
            $data['message'] = $e->getMessage();
            $data['success'] = false;
        }
        $data['items'] = array('items' => $items,'user_level' => $user_level);
        $data['total'] = $total;
        $data['message'] = 'Success';
        $data['success'] = true;
        return $data;    
    }
    public static function duplicateInquery($args = array()){
        // Security check
        //if (!wbSecurity::check('DHotel')) return;
        if (!wbSecurity::check('Inquiry')) return;

        // Get arguments from argument array
        extract($args);
    
        $start = wbRequest::getVarClean('start', 'int', 0);
        $limit = wbRequest::getVarClean('limit', 'int', 50);
        
        $sort = wbRequest::getVarClean('sort', 'str', 'listing_no');
        $dir = wbRequest::getVarClean('dir', 'str', 'ASC');
        $query = wbRequest::getVarClean('query', 'str', '');
        
        $no_registration = wbRequest::getVarClean('no_registration', 'int', 0);

        
        $getAll = wbRequest::getVarClean('getAll', 'str', '');
        
        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        try{
            $db = NewADOConnection("oci8");
            $ora_tns = wbConfig::get('DB.ora_tns');
            $ora_user = wbConfig::get('DB.ora_user');
            $ora_pass = wbConfig::get('DB.ora_pass');
            $db->Connect($ora_tns, $ora_user, $ora_pass);
            $stmt = $db->PrepareSP("BEGIN
                                   p_copy_receipt_websrvc(
                                       :i_no_layanan, 
                                       null,
                                       null,
                                       null,
                                       null,
                                       :on_return_code ,
                                       :os_receipt_no ,
                                       :os_payment_info 
                                       );
                                END;
                                ");
            $i_no_layanan = wbRequest::getVarClean('i_no_layanan', 'str', '');
            $i_kode_bank = wbSession::getVar('bank_name');
            $db->InParameter($stmt,$i_no_layanan,'i_no_layanan',4000);
            $db->OutParameter($stmt,$on_return_code,'on_return_code',4000);
            $db->OutParameter($stmt,$os_receipt_no,'os_receipt_no',4000);
            $db->OutParameter($stmt,$os_payment_info,'os_payment_info',4000);
            $ok = $db->Execute($stmt);
        }catch(UserLoginFailedException $e){
            $data['message'] = $e->getMessage();
            $data['success'] = false;
        }
        $data['items'] = array(
                            'on_return_code' => $on_return_code,
                            'os_receipt_no' => $os_receipt_no,
                            'os_payment_info' => $os_payment_info,
                         );
        $data['total'] = 1;
        $data['success'] = true;
        return $data;    
    }
    public static function execReject($args = array()){
        // Security check
        if (!wbSecurity::check('Inquiry')) return;

        // Get arguments from argument array
        extract($args);
    
        $start = wbRequest::getVarClean('start', 'int', 0);
        $limit = wbRequest::getVarClean('limit', 'int', 50);
        
        $sort = wbRequest::getVarClean('sort', 'str', 'listing_no');
        $dir = wbRequest::getVarClean('dir', 'str', 'ASC');
        $query = wbRequest::getVarClean('query', 'str', '');
        
        $no_registration = wbRequest::getVarClean('no_registration', 'int', 0);

        
        $getAll = wbRequest::getVarClean('getAll', 'str', '');
        
        $jsonItems = wbRequest::getVarClean('items', 'str', '');
        
        $arrItems = (array)json_decode($jsonItems);

        $data = array('items' => array(), 'total' => 0, 'success' => false, 'message' => '');
        
        try{
            $db = NewADOConnection("oci8");
            $ora_tns = wbConfig::get('DB.ora_tns');
            $ora_user = wbConfig::get('DB.ora_user');
            $ora_pass = wbConfig::get('DB.ora_pass');
        $db->Connect($ora_tns, $ora_user, $ora_pass);
            $stmt = $db->PrepareSP("BEGIN
                            p_exec_reject_websrvc(:i_receipt_no,
                               :in_payment_receipt_id,
                               :i_kode_bank,
                               :i_branch_id,
                               :i_user_id,
                               :i_user_name,
                               :i_id_pelanggan,
                               :i_nama,
                               :i_jml_bill,
                               :i_jml_adm,
                               :on_t_cancel_receipt_id,
                               :on_return_code,
                               :os_cancel_info
                               );
                                END;
                                ");
            $i_kode_bank = wbSession::getVar('bank_name');
            $branch_id = wbSession::getVar('branch_id');
            $user_id = wbSession::getVar('user_id');
            $user_name = wbSession::getVar('user_name');

            $in_payment_receipt_id = $arrItems['T_RECEIPT_ID'];
            $i_receipt_no = $arrItems['RECEIPT_NO'];
            $i_id_pelanggan = $arrItems['ID_PELANGGAN'];
            $i_nama = $arrItems['ACC_LAST_NAME'];
            $i_jml_bill = $arrItems['BILL_AMOUNT'];
            $i_jml_adm = $arrItems['ADMIN_AMOUNT'];
            
            
            $db->InParameter($stmt,$i_kode_bank,'i_kode_bank',4000);
            $db->InParameter($stmt,$branch_id,'i_branch_id',4000);
            $db->InParameter($stmt,$user_id,'i_user_id',4000);
            $db->InParameter($stmt,$user_name,'i_user_name',4000);
            
            $db->InParameter($stmt,$in_payment_receipt_id,'in_payment_receipt_id',4000);
            $db->InParameter($stmt,$i_receipt_no,'i_receipt_no',4000);
            $db->InParameter($stmt,$i_id_pelanggan,'i_id_pelanggan',4000);     
            $db->InParameter($stmt,$i_nama ,'i_nama',4000);    
            $db->InParameter($stmt,$i_jml_adm,'i_jml_adm',4000);      
            $db->InParameter($stmt,$i_jml_bill,'i_jml_bill',4000);
            $db->OutParameter($stmt,$on_t_cancel_receipt_id,'on_t_cancel_receipt_id',4000);    
            $db->OutParameter($stmt,$on_return_code,'on_return_code',4000);
            $db->OutParameter($stmt,$os_return_msg,'os_cancel_info',4000);
            $ok = $db->Execute($stmt);
            //exit;
        }catch(Exception $e){
            $data['message'] = $e->getMessage();
            $data['success'] = false;
        }
        $data['items'] = array(
                               'on_return_code' => $on_return_code,
                               'os_cancel_info' => $os_return_msg,
                               'on_t_cancel_receipt_id'=> $on_t_cancel_receipt_id
                               );
        $data['total'] = 1;
        $data['success'] = true;
        $data['message'] = $os_return_msg;
        return $data;    
    }
}
?>