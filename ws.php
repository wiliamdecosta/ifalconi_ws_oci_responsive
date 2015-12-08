<?php
/**
 * Web Services Entry Point
 *
 * @copyright (C) 2010
 * @package webi
 * @author Agung Harry Purnama
 */

/* Load bootstrap so we can get started */
$GLOBALS["Webi_PageTime"] = microtime(true);
include 'lib/bootstrap.php';

/* Load Webi Core */
sys::import('webi.core');

function wbWSMain()
{
    // TODO: don't load the whole core
    wbCore::init();

    /*
     determine the server type, then
     create an instance of an that server and 
     serve the request according the ther servers protocol
    */
    $type = wbRequest::getVarClean('type');
    switch($type) {
        case 'json' :
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

            if ($result || is_array($result)){
                if (empty($callback)){
                    header('Content-Type: application/json');
                    echo json_encode($result);
                }else{
                    header('Content-Type: text/javascript');
                    echo $callback.'('.json_encode($result).')';
                }
            }else{
                header("HTTP/1.1 500 Internal Server Error");
            }
            break;
        default:
            // nothing todo for now
    }
}
wbWSMain();