<?php
class wbModule extends Object
{
    const MODULES_DIR = 'modules';
    const CONTROLLERS_DIR = 'controllers';
    const MODELS_DIR = 'models';

    public static function getRealClassName($class){
        if (strpos($class, '.') === false) return $class;
        else return substr(strrchr($class, "."), 1);    
    }

    public static function &getData($module, $class, $method, $params = array(), $type = ''){
        try{
            wbTemplate::setModTemplate($module, str_replace('.','/',$class).'-'.strtolower($method));

            $data = self::call($module, $class, $method, $params, $type);
        }catch(Exception $e){
            throw $e;
        }

        return $data;
    }

    public static function &getView($module, $class, $method, $params = array(), $type = ''){
        try{
            $data = self::getData($module, $class, $method, $params, $type);

            $output = wbTemplate::getModView($data);
        }catch(Exception $e){
            $output = $e->getMessage();
        }
        
        return $output;
    }

    /**
     * call module controller
     */
    public static function call($module, $class, $method, $params = array(), $type = ''){
        $className = self::loadController($module, $class, $type);
        
        if (!method_exists($className, $method)) throw new ClassMethodNotExistException('Method '.$method.' does not exist in Class '.$className);

        $vars = get_class_vars ($className);
        if (isset($vars['loggedInFirst']) && $vars['loggedInFirst'] === true){
            if (!wbUser::isLoggedIn()){
                $redirecturl = wbModule::url($module, $module, 'main');
                $url = wbModule::url('base', 'base', 'loginform', array('redirecturl' => $redirecturl));
                throw new Exception('Session login anda telah expire atau belum login. Silahkan <a href="'.$url.'" title="Login">Login</a> kembali.<br/><br/><b>Security Info</b><br/>Hal ini dilakukan untuk menjaga keamanan aplikasi, jika anda telah idle dalam jangka waktu yg cukup lama maka anda diharuskan untuk melakukan autentifikasi kembali');
            }
        }

        return call_user_func($className.'::'.$method, $params);
    }

    public static function loadController($module, $class, $type = ''){
         
        $className = self::getRealClassName($class.'_controller');
        
        if (class_exists($className)) return $className;
        
        try{
            sys::import(self::MODULES_DIR.'.'.$module.'.'.$type.self::CONTROLLERS_DIR.'.'.$class.'_controller');
        }catch (Exception $e){
            //throw new Exception('Controller does not exist. Trying to load <b>'. $class . '</b> in '.$module);
            throw new Exception('<h1>'.ucfirst($class).' Module</h1><p>Mohon maaf fitur ini masih dalam pengembangan. Silahkan kunjungi beberapa waktu lagi</p>');
        }

        if (!class_exists($className)) throw new ClassNotExistException('Class '.$class.' does not exist in Module '.$module);        

        return $className;
    }

    public static function loadModel($module, $class){
        $className = self::getRealClassName($class);
        
        if (class_exists($className)) return $className;
        
        try{
            sys::import(self::MODULES_DIR.'.'.$module.'.'.self::MODELS_DIR.'.'.$class);
        }catch (Exception $e){
            throw new Exception('Model does not exist. Trying to load <b>'. $class . '</b> in '.$module);
        }

        if (!class_exists($className)) throw new ClassNotExistException('Class '.$class.' does not exist in Module '.$module);

        return $className;
    }

    public static function &getModel($module, $class, $params = NULL){
        if (empty($module) || empty($class)){
            throw new Exception('module and class must not empty');
        }
        
        $className = self::loadModel($module, $class);

        if ($params !== NULL)
            $tclass = new $className($params);
        else
            $tclass = new $className();
        return $tclass;
    }

    public static function url($module = NULL, $class = '', $method = 'main', $args = array(), $generateXMLURL = false, $fragment = NULL)
    {
        // Parameter separator and initiator.
        $psep = '&';
        $pini = '?';
        $pathsep = '/';
    
        // Initialise the path.
        $path = '';
    
        $BaseModURL = 'index.php';
    
        // No module specified - just jump to the home page.
        if (empty($module)) return wbServer::getBaseURL() . $BaseModURL;
    
        // If we have an empty argument (ie null => null) then set a flag and
        // remove that element.
        // FIXME: this is way too hacky, NULL as a key for an array sooner or later will fail. (php 4.2.2 ?)
        if (is_array($args) && @array_key_exists(NULL, $args) && $args[NULL] === NULL) {
            // This flag means that the GET part of the URL must be opened.
            $open_get_flag = true;
            unset($args[NULL]);
        }
    
        // If the path is still empty, then there is either no short URL support
        // at all, or no short URL encoding was available for these arguments.
        if (empty($path)) {
            $baseargs = array();
            $baseargs = array('module' => $module);
            $baseargs['class'] = $class;
            $baseargs['method'] = $method;
    
            // Standard entry point - index.php or BaseModURL if provided in config.system.php
            $args = $baseargs + $args;
    
            // Add GET parameters to the path, ensuring each value is encoded correctly.
            $path = self::_URLaddParametersToPath($args, $BaseModURL, $pini, $psep);
    
            // We have the long form of the URL here.
            // Again, some form of hook may be useful.
        }
    
        // Add the fragment if required.
        if (isset($fragment)) $path .= '#' . urlencode($fragment);
    
        // Encode the URL if an XML-compatible format is required.
        if ($generateXMLURL) $path = htmlspecialchars($path);
    
        // Return the URL.
        return wbServer::getBaseURL() . $path;
    }
    public static function getInfo($module, $name = ''){}
    public static function getConfig($module, $name = ''){}
    
    /**
     * Encode parts of a URL.
     * This will encode the path parts, the and GET parameter names
     * and data. It cannot encode a complete URL yet.
     *
     * @access private
     * @param data string the data to be encoded (see todo)
     * @param type string the type of string to be encoded ('getname', 'getvalue', 'path', 'url', 'domain')
     * @return string the encoded URL parts
     * @todo this could be made public
     * @todo support arrays and encode the complete array (keys and values)
    **/
    function _URLencode($data, $type = 'getname')
    {
        // Different parts of a URL are encoded in different ways.
        // e.g. a '?' and '/' are allowed in GET parameters, but
        // '?' must be encoded when in a path, and '/' is not
        // allowed in a path at all except as the path-part
        // separators.
        // The aim is to encode as little as possible, so that URLs
        // remain as human-readable as we can allow.
    
        // We will encode everything first, then restore a select few
        // characters.
        // TODO: tackle it the other way around, i.e. have rules for
        // what to encode, rather than undoing some ecoded characters.
        $data = rawurlencode($data);
    
        $decode = array(
            'path' => array(
                array('%2C', '%24', '%21', '%2A', '%28', '%29', '%3D'),
                array(',', '$', '!', '*', '(', ')', '=')
            ),
            'getname' => array(
                array('%2C', '%24', '%21', '%2A', '%28', '%29', '%3D', '%27', '%5B', '%5D'),
                array(',', '$', '!', '*', '(', ')', '=', '\'', '[', ']')
            ),
            'getvalue' => array(
                array('%2C', '%24', '%21', '%2A', '%28', '%29', '%3D', '%27', '%5B', '%5D', '%3A', '%2F', '%3F', '%3D'),
                array(',', '$', '!', '*', '(', ')', '=', '\'', '[', ']', ':', '/', '?', '=')
            )
        );
    
        // TODO: check what automatic ML settings have on this.
        // I suspect none, as all multi-byte characters have ASCII values
        // of their parts > 127.
        if (isset($decode[$type])) {
            $data = str_replace($decode[$type][0], $decode[$type][1], $data);
        }
        return $data;
    }
    
    /**
     * Format GET parameters formed by nested arrays, to support xarModURL().
     * This function will recurse for each level to the arrays.
     *
     * @access private
     * @param args array the array to be expanded as a GET parameter
     * @param prefix string the prefix for the GET parameter
     * @return string the expanded GET parameter(s)
     **/
    function _URLnested($args, $prefix)
    {
        $path = '';
        foreach ($args as $key => $arg) {
            if (is_array($arg)) {
                $path .= URLnested($arg, $prefix . '['.self::_URLencode($key, 'getname').']');
            } else {
                $path .= $prefix . '['.self::_URLencode($key, 'getname').']' . '=' . self::_URLencode($arg, 'getvalue');
            }
        }
    
        return $path;
    }
    
    /**
     * Add further parameters to the path, ensuring each value is encoded correctly.
     *
     * @access private
     * @param args array the array to be encoded
     * @param path string the current path to append parameters to
     * @param psep string the path seperator to use
     * @return string the path with encoded parameters
     */
    function _URLaddParametersToPath($args, $path, $pini, $psep)
    {
        if (count($args) > 0)
        {
            $params = '';
    
            foreach ($args as $k=>$v) {
                if (is_array($v)) {
                    // Recursively walk the array tree to as many levels as necessary
                    // e.g. ...&foo[bar][dee][doo]=value&...
                    $params .= self::_URLnested($v, $psep . $k);
                } elseif (isset($v)) {
                    // TODO: rather than rawurlencode, use a xar function to encode
                    $params .= (!empty($params) ? $psep : '') . self::_URLencode($k, 'getname') . '=' . self::_URLencode($v, 'getvalue');
                }
            }
    
            // Join to the path with the appropriate character,
            // depending on whether there are already GET parameters.
            $path .= (strpos($path, $pini) === false ? $pini : $psep) . $params;
        }
    
        return $path;
    }    
    
    /**
     * is module/controller class available
     */
    public static function isAvailable($module, $class = '', $type = ''){
        $path = self::MODULES_DIR.'/'.$module;
        
        if (!is_dir($path)) return false;
        
        if (!empty($class)){
            $class = str_replace('.', '/', $class).'_controller.php';
            $path = $path.'/'.$type.self::CONTROLLERS_DIR.'/'.$class;

            if (!is_file($path)) return false;
        }
        
        return true;
    }
    
}
