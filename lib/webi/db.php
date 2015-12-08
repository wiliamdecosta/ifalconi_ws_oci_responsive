<?php
include('lib/adodb/adodb-exceptions.inc.php');
include('lib/adodb/adodb.inc.php');


class wbDB extends Object{
    private static $dbConn;
    private static $dbConnParams;
    
    public static function &getConn(){
        return self::$dbConn;
    }

    public static function init($params){
        self::$dbConnParams = $params;
        
        self::$dbConn =& ADONewConnection(self::$dbConnParams['type']);
        self::$dbConn->Connect(self::$dbConnParams['host'], self::$dbConnParams['user'], self::$dbConnParams['password'], self::$dbConnParams['name']);
        
        return true;
    }   
}
?>
