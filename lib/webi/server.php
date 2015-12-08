<?php
class wbServer extends Object
{
    /**
     * Gets a server variable
     *
     * Returns the value of $name server variable.
     * Accepted values for $name are exactly the ones described by the
     * {@link http://www.php.net/manual/en/reserved.variables.html PHP manual}.
     * If the server variable doesn't exist void is returned.
     *
     * @access public
     * @param name string the name of the variable
     * @return mixed value of the variable
     */
    static function getVar($name)
    {
        assert('version_compare("5.0",phpversion()) <= 0; /* The minimum PHP version supported by Webi is 5.0 */');
        if (isset($_SERVER[$name])) return $_SERVER[$name];
        if($name == 'PATH_INFO')    return;
        if (isset($_ENV[$name]))    return $_ENV[$name];
        if ($val = getenv($name))   return $val;
        return; // we found nothing here
    }

    /**
     * Get base URI for 
     *
     * @access public
     * @return string base URI for Webi
     * @todo remove whatever may come after the PHP script - TO BE CHECKED !
     * @todo See code comments.
     */
    static function getBaseURI()
    {
        // Get the name of this URI
        $path = self::getVar('REQUEST_URI');

        if (empty($path)) {
            // REQUEST_URI was empty or pointed to a path
            // adapted patch from Chris van de Steeg for IIS
            // Try SCRIPT_NAME
            $path = self::getVar('SCRIPT_NAME');
            if (empty($path)) {
                // No luck there either
                // Try looking at PATH_INFO
                $path = self::getVar('PATH_INFO');
            }
        }

        $path = preg_replace('/[#\?].*/', '', $path);

        $path = preg_replace('/\.php\/.*$/', '', $path);
        if (substr($path, -1, 1) == '/') {
            $path .= 'dummy';
        }
        $path = dirname($path);

        //FIXME: This is VERY slow!!
        if (preg_match('!^[/\\\]*$!', $path)) {
            $path = '';
        }
        return $path;
    }

    /**
     * Gets the host name
     *
     * Returns the server host name fetched from HTTP headers when possible.
     * The host name is in the canonical form (host + : + port) when the port is different than 80.
     *
     * @access public
     * @return string HTTP host name
     */
    static function getHost()
    {
        $server = self::getVar('HTTP_HOST');
        if (empty($server)) {
            // HTTP_HOST is reliable only for HTTP 1.1
            $server = self::getVar('SERVER_NAME');
            $port   = self::getVar('SERVER_PORT');
            if ($port != '80') $server .= ":$port";
        }
        return $server;
    }

    /**
     * Gets the current protocol
     *
     * Returns the HTTP protocol used by current connection, it could be 'http' or 'https'.
     *
     * @access public
     * @return string current HTTP protocol
     */
    static function getProtocol()
    {
        if (preg_match('/^http:/', self::getVar('REQUEST_URI'))) {
            return 'http';
        }
        $HTTPS = self::getVar('HTTPS');
        // IIS seems to set HTTPS = off for some reason
        return (!empty($HTTPS) && $HTTPS != 'off') ? 'https' : 'http';
    }

    /**
     * get base URL for Webi
     *
     * @access public
     * @return string base URL for Webi
     */
    static function getBaseURL()
    {
        static $baseurl = null;
        if (isset($baseurl))  return $baseurl;

        $server   = self::getHost();
        $protocol = self::getProtocol();
        $path     = self::getBaseURI();

        $baseurl = "$protocol://$server$path/";
        return $baseurl;
    }

    /**
     * get the elapsed time since this page started
     *
     * @access public
     * @return seconds and microseconds elapsed since the page started
     */
    static function getPageTime()
    {
        return microtime(true) - $GLOBALS["Webi_PageTime"];
    }

    /**
     * Get current URL (and optionally add/replace some parameters)
     *
     * @access public
     * @param args array additional parameters to be added to/replaced in the URL (e.g. theme, ...)
     * @param generateXMLURL boolean over-ride Server default setting for generating XML URLs (true/false/NULL)
     * @param target string add a 'target' component to the URL
     * @return string current URL
     * @todo cfr. BaseURI() for other possible ways, or try PHP_SELF
     */
    static function getCurrentURL($args = array(), $generateXMLURL = false, $target = NULL)
    {
        $server   = self::getHost();
        $protocol = self::getProtocol();
        $baseurl  = "$protocol://$server";

        // get current URI
        $request = self::getVar('REQUEST_URI');

        if (empty($request)) {
            // adapted patch from Chris van de Steeg for IIS
            // TODO: please test this :)
            $scriptname = self::getVar('SCRIPT_NAME');
            $pathinfo   = self::getVar('PATH_INFO');
            if ($pathinfo == $scriptname) {
                $pathinfo = '';
            }
            if (!empty($scriptname)) {
                $request = $scriptname . $pathinfo;
                $querystring = self::getVar('QUERY_STRING');
                if (!empty($querystring)) $request .= '?'.$querystring;
            } else {
                $request = '/';
            }
        }

        // Note to Dracos: please don't replace & with &amp; here just yet - give me some time to test this first :-)
        // Mike can we change these now, so we can work on validation a bit?

        // add optional parameters
        if (count($args) > 0) {
            if (strpos($request,'?') === false)
                $request .= '?';
            else
                $request .= '&';

            foreach ($args as $k=>$v) {
                if (is_array($v)) {
                    foreach($v as $l=>$w) {
                        // TODO: replace in-line here too ?
                        if (!empty($w)) $request .= $k . "[$l]=$w&";
                    }
                } else {
                    // if this parameter is already in the query string...
                    if (preg_match("/(&|\?)($k=[^&]*)/",$request,$matches)) {
                        $find = $matches[2];
                        // ... replace it in-line if it's not empty
                        if (!empty($v)) {
                            $request = preg_replace("/(&|\?)".preg_quote($find)."/","$1$k=$v",$request);

                            // ... or remove it otherwise
                        } elseif ($matches[1] == '?') {
                            $request = preg_replace("/\?".preg_quote($find)."(&|)/",'?',$request);
                        } else {
                            $request = str_replace("&$find",'',$request);
                        }
                    } elseif (!empty($v)) {
                        $request .= "$k=$v&";
                    }
                }
            }
            // Strip off last &
            $request = substr($request, 0, -1);
        }

        // Finish up
        if (isset($target)) $request .= '#' . urlencode($target);
        if ($generateXMLURL) $request = htmlspecialchars($request);
        return $baseurl . $request;
    }
}

class wbRequest extends Object
{
    public static $defaultRequest = array('base', 'base', 'main');

    static function getVarClean($name, $type = '', $defaultValue = NULL)
    {
        $var = self::getVar($name);
        $var = self::cleanVar($var);
        
        if ($var == '' && $defaultValue !== NULL){
            return $defaultValue;
        }
        
        if (!isset($type)) return $var;
        
        switch ($type) {
            case 'bool':
                if (is_bool($var)) return $var;
                break;
            case 'str':
            case 'string':
                if (is_string($var)) return $var;
                break;
            case 'object':
                if (is_object($var)) return $var;
                break;
            case 'array':
                if (is_array($var)) return $var;         
                break;   
            case 'float':
            case 'int':
            case 'numeric':
                if (is_numeric($var)) return $var;
                break;
            default:
                return $var;
        }

        if (isset($defaultValue)) return $defaultValue;

        return '';
    }

    static function cleanVar($var)
    {
        $search = array('|</?\s*SCRIPT[^>]*>|si',
                        '|</?\s*FRAME[^>]*>|si',
                        '|</?\s*OBJECT[^>]*>|si',
                        '|</?\s*META[^>]*>|si',
                        '|</?\s*APPLET[^>]*>|si',
                        '|</?\s*LINK[^>]*>|si',
                        '|</?\s*IFRAME[^>]*>|si',
                        '|STYLE\s*=\s*"[^"]*"|si');
        // short open tag <  followed by ? (we do it like this otherwise our qa tests go bonkers)
        $replace = array('');
        // Clean var
        $var = preg_replace($search, $replace, $var);
    
        return $var;
    }

    /**
     * Get request variable
     *
     * @access public
     * @param name string
     * @param allowOnlyMethod string
     * @return mixed
     * @todo change order (POST normally overrides GET)
     * @todo have a look at raw post data options (xmlhttp postings)
     */
    static function getVar($name, $allowOnlyMethod = NULL)
    {
        if ($allowOnlyMethod == 'GET') {
            if (isset($_GET[$name])) {
                // Then check in $_GET
                $value = $_GET[$name];
            } else {
                // Nothing found, return void
                return;
            }
        } elseif ($allowOnlyMethod == 'POST') {
            if (isset($_POST[$name])) {
                // First check in $_POST
                $value = $_POST[$name];
            } else {
                // Nothing found, return void
                return;
            }
        } else {
            if (isset($_POST[$name])) {
                // Then check in $_POST
                $value = $_POST[$name];
            } elseif (isset($_GET[$name])) {
                // Then check in $_GET
                $value = $_GET[$name];
            } else {
                // Nothing found, return void
                return;
            }
        }

        if (get_magic_quotes_gpc()) {
            $value = self::__stripslashes($value);
        }
        return $value;
    }

    static function __stripslashes($value)
    {
        $value = is_array($value) ? array_map(array('self','__stripslashes'), $value) : stripslashes($value);
        return $value;
    }

    /**
     * Get controller request
     */
    static function getController(){
        $module = self::getVar('module');
        $class = self::getVar('class');
        $method = self::getVar('method');

        if (!empty($module)){
            if (!preg_match('/^[a-z][a-z_0-9]*$/', $module)){
                $module = '';
            }
        }

        if (empty($module)){
            $module = wbConfig::get('Module.defaultModule');
            $class = wbConfig::get('Module.defaultClass');
            $method = wbConfig::get('Module.defaultMethod');
        }else{
            if (!empty($class)){
                if (!preg_match('/^[a-zA-Z._\x7f-\xff][a-zA-Z.0-9_\x7f-\xff]*$/', $class)){
                    $class = '';
                }
            }

            if (empty($class)){
                if ($module == wbConfig::get('Module.defaultModule')){
                    $class =  wbConfig::get('Module.defaultClass');
                }
            }
            
            if (!empty($method)){
                if(!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $method)){
                    $method = '';
                }
            }

            if (empty($method)) {
                if ($module == wbConfig::get('Module.defaultModule') && $class ==  wbConfig::get('Module.defaultClass')){
                    $method = wbConfig::get('Module.defaultMethod');
                }
            }
        }

        if (empty($module)){
            $requestInfo = self::$defaultRequest;
        }else{
            if (empty($class)) $class = $module;
            if (empty($method)) $method = 'main';

            $requestInfo = array($module, $class, $method);
        }
        
        wbCache::setCached('current', 'module', $requestInfo[0]);
        wbCache::setCached('current', 'controller', $requestInfo[1]);
        wbCache::setCached('current', 'method', $requestInfo[2]);

        return $requestInfo;
    }

    /**
     * Check to see if this is a local referral
     *
     * @access public
     * @return bool true if locally referred, false if not
     */
    static function IsLocalReferer()
    {
        $server  = wbServer::getHost();
        $referer = wbServer::getVar('HTTP_REFERER');

        if (!empty($referer) && preg_match("!^https?://$server(:\d+|)/!", $referer)) {
            return true;
        } else {
            return false;
        }
    }
}

class wbResponse extends Object
{
    /**
     * Carry out a redirect
     *
     * @access public
     * @param redirectURL string the URL to redirect to
     */
    static function Redirect($url)
    {
        $redirectURL=urldecode($url); // this is safe if called multiple times.
        if (headers_sent() == true) return false;

        // Remove &amp; entities to prevent redirect breakage
        $redirectURL = str_replace('&amp;', '&', $redirectURL);

        if (substr($redirectURL, 0, 4) != 'http') {
            // Removing leading slashes from redirect url
            $redirectURL = preg_replace('!^/*!', '', $redirectURL);

            // Get base URL
            $baseurl = wbServer::getBaseURL();

            $redirectURL = $baseurl.$redirectURL;
        }

        if (preg_match('/IIS/', wbServer::getVar('SERVER_SOFTWARE')) && preg_match('/CGI/', wbServer::getVar('GATEWAY_INTERFACE')) ) {
            $header = "Refresh: 0; URL=$redirectURL";
        } else {
            $header = "Location: $redirectURL";
        }// if

        // Start all over again
        header($header);

        // NOTE: we *could* return for pure '1 exit point' but then we'd have to keep track of more,
        // so for now, we exit here explicitly. Besides the end of index.php this should be the only
        // exit point.
        exit();
    }
}
?>