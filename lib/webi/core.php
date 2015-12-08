<?php
/**
 * The Core
 *
 * @package core
 */

// Before we do anything make sure we can except out of code in a predictable matter
sys::import('webi.exceptions');

define('WB_USER_UNREGISTERED_ID', 0);
define('WB_USER_UNREGISTERED_NAME', 'Guest');

define('WB_STATUS_ACTIVE', 'a');
define('WB_STATUS_INACTIVE', 'i');

//define('WB_STATUS_ACTIVE', '1');
//define('WB_STATUS_INACTIVE', '4');

class wbCore extends Object{
    /**
     * Core version informations
     *
     * should be upgraded on each release for
     * better control on config settings
     */
    const VERSION = '1.0.0';

	public static function init($config = array()){
        //-- todo : load log handler here    

        // load system config
        sys::import('webi.config');
        wbConfig::init();

        // load variables handler, server/request/response utilities
        sys::import('webi.server');

        // load template, page handler
        sys::import('webi.template');
        sys::import('webi.htmlElementWidget');
        //wbPage::init();

        // load database
        sys::import('webi.db');
        /*$dbConnParams = array(
            'name' => wbConfig::get('DB.name'),
            'user' => wbConfig::get('DB.user'),
            'password' => wbConfig::get('DB.password'),
            'host' => wbConfig::get('DB.host'),
            'type' => wbConfig::get('DB.type'),
        );
        wbDB::init($dbConnParams);*/
        
        // load session handler
        /*sys::import('webi.sessions');
        wbSession::init();*/
        
        //-- todo : load language system
        
        // load utilities function
        sys::import('webi.utils');

        // load module handler
        sys::import('webi.modules');

        sys::import('webi.crud.AbstractTable');

        //-- todo : load users and security system
        /*sys::import('webi.users');
        wbUser::init();
        
        sys::import('webi.security');*/

        return true;
    }
    
	/**
     * Checks if a certain function was disabled in php.ini
     *
     * xarCore.php function
     * @access public
     * @param string The function name; case-sensitive
     * @return bool true or false
     */
    function isFuncDisabled($funcName)
    {
        static $disabled;

        if (!isset($disabled)) {
            // Fetch the disabled functions as an array.
            // White space is trimmed here too.
            $functions = preg_split('/[\s,]+/', trim(ini_get('disable_functions')));

            if ($functions[0] != '') {
                // Make the function names the keys.
                // Values will be 0, 1, 2 etc.
                $disabled = array_flip($functions);
            } else {
                $disabled = array();
            }
        }

        return (isset($disabled[$funcName]) ? true : false);
    }
    
}

/**
 * Convenience class for keeping track of core cached stuff
 *
 * @package core
 * @todo this is closer to the caching subsystem than here
 * @todo i dont like the array shuffling
 * @todo separate file
 * @todo this is not xarCore, this is xarCoreCache
**/
class wbCache extends Object
{
    private static $cacheCollection = array();

    /**
     * Check if a variable value is cached
     *
     * @param key string the key identifying the particular cache you want to access
     * @param name string the name of the variable in that particular cache
     * @return mixed value of the variable, or false if variable isn't cached
     * @todo make sure we can make this protected
    **/
    public static function isCached($cacheKey, $name)
    {
        if (!isset(self::$cacheCollection[$cacheKey])) {
            self::$cacheCollection[$cacheKey] = array();
            return false;
        }
        return isset(self::$cacheCollection[$cacheKey][$name]);
    }

    /**
     * Get the value of a cached variable
     *
     * @param string $key  the key identifying the particular cache you want to access
     * @param string $name the name of the variable in that particular cache
     * @return mixed value of the variable, or null if variable isn't cached
     * @todo make sure we can make this protected
    **/
    public static function getCached($cacheKey, $name)
    {
        if (!isset(self::$cacheCollection[$cacheKey][$name])) {
            return;
        }
        return self::$cacheCollection[$cacheKey][$name];
    }

    /**
     * Set the value of a cached variable
     *
     * @param key string the key identifying the particular cache you want to access
     * @param name string the name of the variable in that particular cache
     * @param value string the new value for that variable
     * @return void
     * @todo make sure we can make this protected
    **/
    public static function setCached($cacheKey, $name, $value)
    {
        if (!isset(self::$cacheCollection[$cacheKey])) {
            self::$cacheCollection[$cacheKey] = array();
        }
        self::$cacheCollection[$cacheKey][$name] = $value;
    }

    /**
     * Delete a cached variable
     *
     * @param key the key identifying the particular cache you want to access
     * @param name the name of the variable in that particular cache
     * @return null
     * @todo remove the double whammy
     * @todo make sure we can make this protected
    **/
    public static function delCached($cacheKey, $name)
    {
        if (isset(self::$cacheCollection[$cacheKey][$name])) {
            unset(self::$cacheCollection[$cacheKey][$name]);
        }
        //This unsets the key that said that collection had already been retrieved

        //Seems to have caused a problem because of the expected behaviour of the old code
        //FIXME: Change how this works for a mainstream function, stop the hacks
        if (isset(self::$cacheCollection[$cacheKey][0])) {
            unset(self::$cacheCollection[$cacheKey][0]);
        }
    }

    /**
     * Flush a particular cache (e.g. for session initialization)
     *
     * @param  cacheKey the key identifying the particular cache you want to wipe out
     * @return null
     * @todo make sure we can make this protected
    **/
    public static function flushCached($cacheKey)
    {
        if(isset(self::$cacheCollection[$cacheKey])){
            unset(self::$cacheCollection[$cacheKey]);
        }
    }
}

class wbController extends Object
{
    public static $loggedInFirst = false;
}
?>
