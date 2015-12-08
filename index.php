<?php
/**
 * Standard Web Interface Entry Point
 *
 * @copyright (C) 2010
 * @package webi
 * @author Agung Harry Purnama
 */

/* Load bootstrap so we can get started */
$GLOBALS["Webi_PageTime"] = microtime(true);
include 'lib/bootstrap.php';

/* Load webi core */
sys::import('webi.core');

function wbMain(){
    wbCore::init();
    
    list($module, $class, $method) = wbRequest::getController();

    // theme override
    $theme = wbRequest::getVarClean('theme');
    if (!empty($theme)) wbPage::setTheme($theme);

    $page = wbRequest::getVarClean('page');        
    if (!empty($page)) wbPage::setPage($page);   

    ob_start();
    $modView = wbModule::getView($module, $class, $method);
    if (ob_get_length() > 0) {
        $rawOutput = ob_get_contents();
        $modView = 'The following lines were printed in raw mode by module, however this
                      should not happen. The module is probably directly calling functions
                      like echo, print, or printf. Please modify the module to exclude direct output.
                      The module is violating Webi architecture principles.<br /><br />'.
                      $rawOutput.
                      '<br /><br />This is the real module output:<br /><br />'.
                      $modView;
    }
    ob_end_clean();
    wbPage::render($modView);
}
wbMain();