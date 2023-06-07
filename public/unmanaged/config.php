<?php

if (!defined('APPLICATION_PATH')) { // pure unmanaged
    define('APPLICATION_PATH', $_SERVER['DOCUMENT_ROOT'] . '/../../application/');
    set_include_path(implode(PATH_SEPARATOR, array(
        realpath(APPLICATION_PATH . '/../library'),
        get_include_path(),
    )));
}

if (!defined('APPLICATION_ENV')) {
    /**
     * Фича для тестовых серверов
     */
    if (false !== strstr($_SERVER['HTTP_HOST'], '-mssql')) {
        defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'mssql');
    }

    if (false !== strstr($_SERVER['HTTP_HOST'], '-oracle')) {
        defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'oracle');
    }

    if (false !== strstr($_SERVER['HTTP_HOST'], '-mysql')) {
        defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'mysql');
    }
    
    // Определение текущего режима работы приложения (по умолчанию production)
    defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
}



require_once('Zend/Config/Ini.php');
require_once('HM/Config/Ini.php');

$GLOBALS['ini'] = new HM_Config_Ini(APPLICATION_PATH . '/settings/config.ini', APPLICATION_ENV, false, APPLICATION_PATH . '/settings/config.dev.ini');
$GLOBALS['ini'] = $GLOBALS['ini']->toArray();

if (!defined("dbdriver"))   define("dbdriver",  $GLOBALS['ini']['resources']['db']['unadapter']);
if (!defined("dbhost"))     define("dbhost",    $GLOBALS['ini']['resources']['db']['params']['unhost']);
if (!defined("dbuser"))     define("dbuser",    $GLOBALS['ini']['resources']['db']['params']['username']);
if (!defined("dbpass"))     define("dbpass",    $GLOBALS['ini']['resources']['db']['params']['password']);
if (!defined("dbbase"))     define("dbbase",    $GLOBALS['ini']['resources']['db']['params']['dbname']);
if (!defined("httphost"))   define("httphost",  "http://");

?>