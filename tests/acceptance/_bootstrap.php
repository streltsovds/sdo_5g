<?php
define('TEST_ROOT', dirname(__FILE__));

try {
    require_once TEST_ROOT . '/../../application/cmd/cmdBootstraping.php';
    
    require_once TEST_ROOT . '/../../library/Codeception/Controller/Action.php';    
    require_once TEST_ROOT . '/../../library/Codeception/Test/Abstract.php';    
    require_once TEST_ROOT . '/../../library/Codeception/Registry.php';    

} catch (Exception $e) {
    echo $e->getMessage() . "\r\n";
    echo $e->getFile() . ": ";
    echo $e->getLine() . "\r\n";
}

$config = new stdClass();
$config->global = new Zend_Config_Ini(TEST_ROOT . '/application/settings/global.ini');
$config->pages = new Zend_Config_Ini(TEST_ROOT . '/application/settings/pages.ini');
$config->data = new Zend_Config_Ini(TEST_ROOT . '/application/settings/data.ini');
$config->users = new Zend_Config_Ini(TEST_ROOT . '/application/settings/users.ini');

Codeception_Registry::set('config', $config);

$requisites = array();
Codeception_Registry::set('requisites', $requisites);

if (!function_exists('cd')) {
    function cd($var) {return codecept_debug($var);}
}


function shut() {
    $err = error_get_last();
    cd($err);
}
//register_shutdown_function("shut");

