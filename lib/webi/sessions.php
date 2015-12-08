<?php
class wbSession extends Object{
    const  PREFIX='WBSV'; // Reserved by us for our session vars
    const  COOKIE='WBSID'; // Our cookiename

	public static function getAll(){
        $dbconn = wbDB::getConn();
        
        $prefix = wbConfig::get('DB.prefix');
        $query = "SELECT sesskey, expiry, expireref, created, modified, sessdata "
                ."FROM ". $prefix ."_sessions WHERE expiry > NOW()";
        
        $result =& $dbconn->Execute($query);
        if (!$result) return;
    
        $items = array();
        while (!$result->EOF) {
            list($sesskey, $expiry, $expireref, $created, $modified, $sessdata) = $result->fields;
            $items[] = array('sesskey' => $sesskey, 
            				 'expiry' => $expiry, 
            				 'expireref' => $expireref, 
            				 'created' => $created, 
            				 'modified' => $modified, 
            				 'sessdata' => $sessdata);
            $result->MoveNext();
        }
        $result->Close();
        return $items;		
	}

	public static function getLoggedInUsers(){
		$items = self::getAll();
       
        if (count($items) == 0)
            return array();
		
		$users = array();

        foreach ($items as $item){
            $data = adodb_unserialize(urldecode($item['sessdata']));
            if ($data[self::PREFIX . 'user_id'] > 0){
            	$users[] = array('user_id' => $data[self::PREFIX . 'user_id'],
            					 'user_name' => $data[self::PREFIX . 'user_name'],
            					 'user_email' => $data[self::PREFIX . 'user_email'],
            					 'user_realname' => $data[self::PREFIX . 'user_realname'],
            					 'user_logon' => $item['created']);
            }
        }
        
		return $users;
	}

    public static function init(){
        if (!wbCore::isFuncDisabled('ini_set')){
            // PHP configuration variables
            // Stop adding SID to URLs
            ini_set('session.use_trans_sid', 0);

            // User-defined save handler
            ini_set('session.save_handler', 'user');

            // How to store data
            ini_set('session.serialize_handler', 'php');

            // Use cookie to store the session ID
            ini_set('session.use_cookies', 1);

            // Name of our cookie
            ini_set('session.name', 'WEBISID');

            $path = wbServer::getBaseURI();
            if (empty($path)) {
                $path = '/';
            }

            // Lifetime of our cookie. Session lasts set number of days
            $lifetime = wbConfig::get('Session.Duration') * 86400;

            ini_set('session.cookie_lifetime', $lifetime);

            // Cookie path
            // this should be customized for multi-server setups wanting to share
            // sessions
            ini_set('session.cookie_path', $path);

            // Garbage collection
            ini_set('session.gc_probability', 1);

            // Inactivity timeout for user sessions
            ini_set('session.gc_maxlifetime', wbConfig::get('Session.InactivityTimeout') * 60);

            // Auto-start session
            ini_set('session.auto_start', 1);
        }

        include_once('lib/adodb/session/adodb-session2.php');
        $GLOBALS['ADODB_SESS_CONN'] =& wbDB::getConn();

        ADODB_Session::table(wbConfig::get('DB.prefix').'_sessions');
        session_start();
    }

    /**
     * Get a session variable
     *
     * @param name name of the session variable to get
     */
    public static function getVar($name)
    {
        $var = self::PREFIX . $name;

        if (isset($_SESSION[$var])) {
            return $_SESSION[$var];
        }

        return null;
    }

    /**
     * Set a session variable
     * @param name name of the session variable to set
     * @param value value to set the named session variable
     */
    public static function setVar($name, $value)
    {
        //assert('!is_null($value); /* Not allowed to set variable to NULL value */');
        // security checks : do not allow to set the id or mess with the session serialization
        //if ($name == 'role_id' || strpos($name,'|') !== false) return false;

        $var = self::PREFIX . $name;
        $_SESSION[$var] = $value;
        return true;
    }

    /**
     * Delete a session variable
     * @param name name of the session variable to delete
     */
    public static function delVar($name)
    {
        //if ($name == 'role_id') return false;

        $var = self::PREFIX . $name;

        if (!isset($_SESSION[$var])) {
            return false;
        }
        unset($_SESSION[$var]);
        // still needed here too
        // mrb: why?
        if (ini_get('register_globals')) {
            session_unregister($var);
        }
        return true;
    }    
}
