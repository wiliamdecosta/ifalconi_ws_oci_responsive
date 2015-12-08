<?php
/**
 * Web Services Entry Point
 *
 * @copyright (C) 2010
 */

/* Load bootstrap so we can get started */
// nothing todo for now
define ( 'MUST_FROM_INDEX', 'SAMPLE_WS2011.2' );

//load nusoap library
require 'lib/webi/nusoap.php';
//run ws server
if (! defined ( 'MUST_FROM_INDEX' )) exit ( 'Cannot access file directly' );

//WS Configuration
define ( 'WS_SIKP', 'BDS_SECURITY' );
define ( 'WS_NAMA_WSDL', 'WSDL_' . WS_SIKP . '.wsdl' );

//Create WS Service Instance with WSDL
$ws_svr = new nusoap_server ();
$ws_svr->soap_defencoding = 'UTF-8';
$ws_svr->configureWSDL ( WS_SIKP, 'urn:' . WS_NAMA_WSDL );


function ws_proccess($search,
				$getParams,
				$controller,
				$postParams,
				$jsonItems,
				$start,
				$limit){
	    
    $GLOBALS["Webi_PageTime"] = microtime(true);
    include 'lib/bootstrap.php';
    /* Load Webi Core */
    sys::import('webi.core');
    wbCore::init();
    $_GET['jsonItems']=$jsonItems;
    if(!empty($getParams)){
        $getParams =& wbUtil::jsonDecode($getParams);
    }else{
        $getParams = array();
    }
    
    if(json_decode($postParams) > 0){
        $postParams =json_decode($postParams);
    }else{
        $postParams=array();
    }
    $controller =& wbUtil::jsonDecode($controller);
    $type = $controller['type'];
    if(!empty($getParams)){
        foreach($getParams as $key => $value){
            $_GET[$key]=$value;
        }
    }
    if(!empty($postParams)){
        foreach($postParams as $key => $value){
            $_POST[$key]=$value;
        }
    }
        
    $_GET['module']=$controller['module'];
    $_GET['class']=$controller['class'];
    $_GET['method']=$controller['method'];
    list($module, $class, $method) = wbRequest::getController();
    
    $callback = wbRequest::getVarClean('callback');
    if (!wbModule::isAvailable($module, $class, $type)){
        header("HTTP/1.1 400 Bad Request");
        return;        
    }
    
    try{
        $result = wbModule::call($module, $class, $method, array(), $type);
    }catch(Exception $e){
        $result = array('items' => array(), 'total' => 0, 'success' => false, 'message' => $e->getMessage());
    }
    
    $return = array();
	$return ['success'] = $result['success'];
	$return ['message'] = $result['message'];
	$return ['total'] = (int)$result['total'];
	$return ['data'] = $result['items'];
	$return ['current'] = (int)$result['current'];
	$return ['rowCount'] = (int)$result['rowCount'];
	
	$return = base64_encode ( serialize ( $return ) );
	
	return $return;
}

$ws_svr->register ( 'ws_proccess', 
		array('search' => 'xsd:string',
			  'getParams' => 'xsd:string',
			  'controller' => 'xsd:string',
			  'postParams' => 'xsd:string',
			  'jsonItems' => 'xsd:string',
			  'start' => 'xsd:integer',
			  'limit' => 'xsd:integer'),  
		array ('return' => 'xsd:string' ), 
		'urn:' . WS_NAMA_WSDL, 
		'urn:' . WS_NAMA_WSDL . '#ws_proccess', 
		'rpc', 
		'encoded', 
		'Deskripsi fungsi ws_proccess' );
		
$HTTP_RAW_POST_DATA = isset ( $HTTP_RAW_POST_DATA ) ? $HTTP_RAW_POST_DATA : '';
$ws_svr->service ( $HTTP_RAW_POST_DATA );
exit ();
