<?php
define('ACCESS_NONE', 0);
define('ACCESS_OVERVIEW', 1);
define('ACCESS_READ', 2);
define('ACCESS_EDIT', 3);
define('ACCESS_ADD', 4);
define('ACCESS_DELETE', 5);
define('ACCESS_ADMIN', 6);

class wbSecurity extends Object{
    private static $accessList = array('ACCESS_NONE', 'ACCESS_OVERVIEW', 'ACCESS_READ', 'ACCESS_EDIT', 
                                       'ACCESS_ADD', 'ACCESS_DELETE', 'ACCESS_ADMIN');
                                      
    public static function check($name, $action = 1, $module = ""){
        if (empty($module)) $module = wbCache::getCached('current', 'module');

        if (empty($module)) throw new Exception("Unknown Module");

        if (!self::isPermissionExist($name)) throw new Exception('Unknown Permission Name '.$name.' on module '.$module);
        
        $sessionInfo = wbUser::getSession();

        $dbconn = wbDB::getConn();

        $prefix = wbConfig::get('DB.prefix');

        $query = "SELECT role_id FROM ".$prefix."_user_role 
                    WHERE role_id IN (select role_id FROM ".$prefix."_role_permission) AND user_id = ?";

        $result =& $dbconn->Execute($query, array($sessionInfo['user_id']));

        if (!$result) throw new Exception($dbconn->ErrorMsg());

        while (!$result->EOF) {
            list($role_id) = $result->fields;

            // check ACCESS
            $query = "SELECT COUNT(1) FROM ".$prefix."_role_permission as a, ".$prefix."_permission as b
                        WHERE a.role_id = ? AND 
                              a.permission_level >= ? AND 
                              a.permission_id = b.permission_id AND
                              b.permission_name = ? AND 
                              b.permission_module = ?";

            $count = $dbconn->GetOne($query, array($role_id, $action, $name, $module));
    
            if ($count === false){
                throw new Exception($dbconn->ErrorMsg());
            }

            if ($count) return true; // this user has ACCESS

            $result->MoveNext();
        }
        $result->Close();
        
        // this user does not access
        throw new Exception(json_encode(array('error'=>'sess_error','msg' => "Anda tidak memiliki hak akses untuk melakukan operasi ini atau sessi login anda sudah berakhir<br/><br/>Silahkan untuk melakukan login kembali")));
        throw new Exception("Anda tidak memiliki hak akses untuk melakukan operasi ini atau sessi login anda sudah berakhir<br/><br/>Nama Akses : ".self::$accessList[$action]." on ".$module.'.'.$name."<br/>Silahkan hubungi Administrator untuk mendapatkan akses tersebut");
    }
    
    public static function isPermissionExist($name){
        $dbconn = wbDB::getConn();
        
        $prefix = wbConfig::get('DB.prefix');
        $query = "SELECT COUNT(1) FROM ".$prefix."_permission WHERE permission_name = ?";

        $count = $dbconn->GetOne($query, array($name));

        if ($count === false){
            throw new Exception($dbconn->ErrorMsg());
        }

        if ($count) return true;
        
        return false;
    }

    /**
     * Generate an authorisation key
     *
     * The authorisation key is used to confirm that actions requested by a
     * particular user have followed the correct path.  Any stage that an
     * action could be made (e.g. a form or a 'delete' button) this function
     * must be called and the resultant string passed to the client as either
     * a GET or POST variable.  When the action then takes place it first calls
     * xarSecConfirmAuthKey() to ensure that the operation has
     * indeed been manually requested by the user and that the key is valid
     *
     * @access public
     * @param string modName the module this authorisation key is for (optional)
     * @return string an encrypted key for use in authorisation of operations
     * @todo bring back possibility of extra security by using date (See code)
     */
    function genAuthKey($modName = NULL)
    {
        if (empty($modName)) {
            list($modName) = wbRequest::getController();
        }

        $rands = wbSession::getVar('rand');
        $now = time();

        // convert single rand to array
        if(!is_array($rands)){
            $rands = array();
        }

        // always make a new rand value
        // format is timestamp-rand
        srand((double)microtime()*1000000);
        $rnd = rand();
        $rands[] = $now . '-' . $rnd;

        // session integrity: only keep most recent 64 values
        $rands = array_slice($rands, -64);

        wbSession::setVar('rand', $rands);

        // Date gives extra security but leave it out for now
        // $key = xarSessionGetVar('rand') . $modName . date ('YmdGi');
        $key = $rnd . strtolower($modName);

        // Encrypt key
        $authid = md5($key);

        // Return encrypted key
        return $authid;
    }

    /**
     * Confirm an authorisation key is valid
     *
     * See description of xarSecGenAuthKey for information on
     * this function
     *
     * @access public
     * @param string authIdVarName
     * @return bool true if the key is valid, false if it is not
     * @throws FORBIDDEN_OPERATION
     * @todo bring back possibility of time authorized keys
     */
    function confirmAuthKey($modName = NULL, $authIdVarName = 'authid')
    {
        if(!isset($modName)) list($modName) = wbRequest::getController();
        $authid = wbRequest::getVar($authIdVarName);

        $rands = wbSession::getVar('rand');
        $now = time();

        srand((double)microtime()*1000000);

        // convert single rand to array of "timestamp-rand()" strings
        if(!is_array($rands)){
            $rands = array();

            // session integrity: only keep most recent 64 values
            $rands = array_slice($rands, -64);

            wbSession::setVar('rand', $rands);
        }

        // needed in foreach to expire old rand values
        $age = wbConfig::get('Session.InactivityTimeout') * 60; // convert minutes to seconds

        // loop through the rands array to find a match
        foreach($rands as $r => $rnd){
            list($timestamp, $rndval) = explode('-', $rnd, 2);

            // ignore and get rid of random values older than session activity timeout
            if($now - $age > $timestamp){
                unset($rands[$r]);
                continue;
            }

            // Regenerate static part of key
            $partkey = $rndval . strtolower($modName);

            if ((md5($partkey)) == $authid) {
                // Match - get rid of it and leave happy
                unset($rands[$r]);

                // session integrity: only keep most recent 64 values
                $rands = array_slice($rands, -64);

                wbSession::setVar('rand', $rands);

                return true;
            }
        }

        throw new Exception("<p>Operasi yang anda coba lakukan tidak diperkenankan dalam kondisi ini.</p>Anda mungkin telah menekan tombol Back atau Reload pada browser dan mencoba kembali operasi yang tidak boleh diulang, atau cookie tidak diaktifkan pada browser anda");
        
        return false;
    }
}