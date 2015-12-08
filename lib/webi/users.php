<?php
class UserLoginFailedException extends Exception{}

class wbUser extends Object{
    public static $userSessions = array();

    public static function init(){
        $user_id = wbSession::getVar('user_id');
        if ($user_id === null){
            self::setSession(WB_USER_UNREGISTERED_ID, WB_USER_UNREGISTERED_NAME);
            // Generate a random number, used for
            // some authentication
            srand((double) microtime() * 1000000);
            wbSession::setVar('rand', array(time() . '-' . rand()));
        }
    }

    public static function isLoggedIn()
    {
        $currUid = wbSession::getVar('user_id');
        if ($currUid == WB_USER_UNREGISTERED_ID || $currUid === null) return false;

        return true;
    }

    public static function logIn($userName, $password, $rememberMe = 0)
    {
        if (self::isLoggedIn()) return true;

        if (empty($userName)) throw new UserLoginFailedException('username tidak boleh kosong');
        if (empty($password)) throw new UserLoginFailedException('password tidak boleh kosong');

        $dbconn = wbDB::getConn();
        //$table = wbConfig::get('DB.prefix').'_user';
        //$query = "SELECT user_id, user_name, user_password, user_email, user_realname, user_status FROM $table WHERE user_name = ?";
        
	 //$ip_client = $_SERVER['REMOTE_ADDR'];
	 $ip_client = $_POST['REMOTE_ADDR'];
        $sess = $_COOKIE['WEBISID'];
        $db = NewADOConnection("oci8");
        $ora_tns = wbConfig::get('DB.ora_tns');
            $ora_user = wbConfig::get('DB.ora_user');
            $ora_pass = wbConfig::get('DB.ora_pass');
            $db->Connect($ora_tns, $ora_user, $ora_pass);        
	 $result = $db->GetOne("select f_websrvc_login ('$userName' ,'$password', '$ip_client', '$sess') from dual");
        $jsonItems = json_decode($result);
        if (!$result) return;
        /*if ($result->EOF) {
            $result->Close();
            throw new UserLoginFailedException('User '.$userName.' belum terdaftar');
        }*/
        $items_user = (array)$jsonItems->items[0];
        
        if ($jsonItems->success == false) {
            $db->Close();
            return $jsonItems;
        }
        //list($user_id, $user_name, $user_password, $user_email, $user_realname, $user_status) = $result->fields;
        
        $arr_user_status = array( 0 => 'i',1 => 'a');
        $user_name = $items_user['user_name'];
        $user_id = $items_user['user_id'];
        $user_email = $items_user['user_email'];
        $user_realname = $items_user['user_full_name'];
        $user_level = $items_user['user_level'];
        $user_status = $arr_user_status[$items_user['user_status']];
        
        wbSession::setVar('branch_id', $items_user['branch_id']);
        wbSession::setVar('loket_code', $items_user['loket_code']);
        wbSession::setVar('branch_code', $items_user['branch_code']);
        wbSession::setVar('branch_desc', $items_user['branch_desc']);
        wbSession::setVar('bank_name', $items_user['bank_name']);
        wbSession::setVar('role_id', $items_user['role_id']);
        wbSession::setVar('role_name', $items_user['role_name']);
        wbSession::setVar('user_level', $items_user['user_level']);
        $db->Close();
        if ($user_status != WB_STATUS_ACTIVE) throw new UserLoginFailedException('Account user anda tidak aktif. Silahkan hubungi administrator untuk info lebih lanjut');
        
        // Confirm that passwords match
        $query = "select * from core_user_role where user_id = ?";

        $result =& $dbconn->Execute($query,array($user_id));
        $core_user_role = $result->fields;
        if(empty($core_user_role['role_id'])){
        
            $query = "INSERT INTO public.core_user_role (user_id, role_id, main_role) VALUES (?, (select role_id from core_role where role_name = ?), '1')";
    
            $result =& $dbconn->Execute($query,array($user_id,$user_level));
        }
        
        $roles = self::getUserRoles($user_id);

        self::setSession($user_id, $user_name, $user_email, $user_realname, $roles);
        
        return $jsonItems;
    }

    public static function logOut()
    {
        if (!self::isLoggedIn()) return true;

        self::delSession();
        // clear all previously set authkeys, then generate a new value
        srand((double) microtime() * 1000000);
        wbSession::setVar('rand', array(time() . '-' . rand()));


        return true;
    }

    public static function setSession($user_id, $user_name, $user_email = '', $user_realname = '', $roles = array()){
        wbSession::setVar('user_id', $user_id);
        wbSession::setVar('user_name', $user_name);
        wbSession::setVar('user_email', $user_email);
        wbSession::setVar('user_realname', $user_realname);
        wbSession::setVar('roles', serialize($roles));

        return true;
    }

    public static function delSession(){
        wbSession::setVar('user_id', WB_USER_UNREGISTERED_ID);
        wbSession::setVar('user_name', WB_USER_UNREGISTERED_NAME);
        wbSession::setVar('user_email', '');
        wbSession::setVar('user_realname', '');
        wbSession::setVar('roles', serialize(array()));
        return true;
    }

    public static function &getSession(){
        $info = array();
        $info['user_id'] = wbSession::getVar('user_id');
        $info['user_name'] = wbSession::getVar('user_name');
        $info['user_email'] = wbSession::getVar('user_email');
        $info['user_realname'] = wbSession::getVar('user_realname');
        $info['roles'] = unserialize(wbSession::getVar('roles'));
        $info['user_level'] = wbSession::getVar('user_level');
        return $info;
    }

    public static function &getUserRoles($uid){
        $dbconn = wbDB::getConn();

        $prefix = wbConfig::get('DB.prefix');
        $query = "SELECT a.role_id, b.role_name "
                ."FROM ". $prefix ."_user_role as a, ". $prefix ."_role as b "
                ."WHERE a.role_id = b.role_id AND a.user_id = ?";

        $result =& $dbconn->Execute($query, array($uid));
        if (!$result) return;

        $roles = array();
        while (!$result->EOF) {
            list($role_id, $role_name) = $result->fields;
            $roles[] = array('role_id' => $role_id, 'role_name' => $role_name);
            $result->MoveNext();
        }
        $result->Close();
        return $roles;
    }


}