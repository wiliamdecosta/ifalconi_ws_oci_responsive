<?php

set_error_handler('wbPHPErrorHandler');
set_exception_handler('wbExceptionErrorHandler');

class wbExceptionHandler extends Object
{
	private static $levels = array(
        E_ERROR				=>	'Error',
		E_WARNING			=>	'Warning',
		E_PARSE				=>	'Parsing Error',
		E_NOTICE			=>	'Notice',
		E_CORE_ERROR		=>	'Core Error',
		E_CORE_WARNING		=>	'Core Warning',
		E_COMPILE_ERROR		=>	'Compile Error',
		E_COMPILE_WARNING	=>	'Compile Warning',
		E_USER_ERROR		=>	'User Error',
		E_USER_WARNING		=>	'User Warning',
		E_USER_NOTICE		=>	'User Notice',
		E_STRICT			=>	'Runtime Notice'
	);

    public static function stripFilePath($filepath){
		$filepath = str_replace("\\", "/", $filepath);
		
		// For safety reasons we do not show the full file path
		if (FALSE !== strpos($filepath, '/'))
		{
			$x = explode('/', $filepath);
			$filepath = $x[count($x)-2].'/'.end($x);
		}
        return $filepath;        
    }

    public static function showPHPError($severity, $message, $filepath, $line)
    {
		$severity = (!isset(self::$levels[$severity])) ? $severity : self::$levels[$severity];
	    
	    $filepath = self::stripFilePath($filepath);
		
		if (ob_get_level() > 0) ob_end_clean();	

		ob_start();
		include('themes/'.wbPage::getThemeName().'/templates/php-error.php');
		$errorMsg = ob_get_contents();
		ob_end_clean();
		wbPage::render($errorMsg);       
    }
    
    public static function showException($e)
    {
        if (ob_get_level() > 0) ob_end_clean();	
        
		$file = self::stripFilePath($e->getFile());
        
        $error = $e->getCode()." (".get_class($e).")";
        $message = $e->getMessage();
        $line = $e->getLine();
        $backtrace = "<p>".str_replace("\n","<br/>",$e->getTraceAsString())."</p>";
        
		ob_start();
		include('themes/'.wbPage::getThemeName().'/templates/exception-error.php');
		$errorMsg = ob_get_contents();
		ob_end_clean();

		wbPage::render($errorMsg);
    }
}

function wbPHPErrorHandler($severity, $message, $filepath, $line)
{	
    /*
        We don't bother with "strict" notices since they will fill up
	    the log file with information that isn't normally very
	    helpful.  For example, if you are running PHP 5 and you
	    use version 4 style class functions (without prefixes
	    like "public", "private", etc.) you'll get notices telling
	    you that these have been deprecated.
    */
	if ($severity == E_STRICT) return;
    
    /*
        Should we display the error?
	    We'll get the current error_reporting level and add its bits
	    with the severity bits to find out.	
    */
	if (($severity & error_reporting()) == $severity)
	{
		wbExceptionHandler::showPHPError($severity, $message, $filepath, $line);
		exit;
	}
    
    // todo : add log error here based on config    
}

function wbExceptionErrorHandler($exception){
    wbExceptionHandler::showException($exception);
}