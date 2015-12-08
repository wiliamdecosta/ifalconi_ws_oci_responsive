<?php
class wbConfig extends Object{
    const CONFIGFILE = 'var/config.system.php';
    
    private static $sysConfig;
    
    public static function init(){
        include_once self::CONFIGFILE;
        self::$sysConfig = $sysConfig;
    }
    
    public static function get($name){
        if (isset(self::$sysConfig[$name])) return self::$sysConfig[$name];
        
        return '';
    }
    
    public static function set($name, $value){
        self::$sysConfig[$name] = $value;
    }
}
?>