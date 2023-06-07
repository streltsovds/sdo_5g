<?php
function shut() {
    $err = error_get_last();
    var_dump($err);
}
//register_shutdown_function("shut");

function index_php_log() {
    try {
        if (!class_exists('Zend_Registry')) {
            throw new Exception('Zend_Registry not available');
        }
        Zend_Registry::get('log_system')->log(
            implode("\n", func_get_args()),
            Zend_Log::ERR
        );
    }
    catch (Exception $e) {
        // see /var/log/apache2/error.log
        error_log('[HM log_system not available] '. implode("\n", func_get_args()));
    }
}

require __DIR__ . '/../vendor/autoload.php';

if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED', 8192);
}
if (!defined('E_USER_DEPRECATED')) {
    define('E_USER_DEPRECATED', 16384);
}

//error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);// & ~E_NOTICE

// Указание пути к директории приложения
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
defined('PUBLIC_PATH') || define('PUBLIC_PATH', dirname(__FILE__));

/**
 * Фича для тестовых серверов
 */

$applicationEnv = '';

if (false !== strstr($_SERVER['HTTP_HOST'], '-mssql')) {
	$applicationEnv || $applicationEnv = 'mssql';
}

if (false !== strstr($_SERVER['HTTP_HOST'], '-oracle')) {
	$applicationEnv || $applicationEnv = 'oracle';
}

if (false !== strstr($_SERVER['HTTP_HOST'], '-mysql')) {
	$applicationEnv || $applicationEnv = 'mysql';
}

// Определение текущего режима работы приложения (по умолчанию production)
$applicationEnv || $applicationEnv = getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production';
//$applicationEnv || $applicationEnv = getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development';

define('APPLICATION_ENV', $applicationEnv);

if (!isset($_SERVER['REQUEST_URI'])) // IIS HACK
{
	$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
	if (isset($_SERVER['QUERY_STRING'])) { $_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING']; }
	//this is same as HTTP_X_ORIGINAL_URL
}

$applicationModule = '';

if (preg_match("/^\/cms\/.*/i", $_SERVER['REQUEST_URI'])) {
    $applicationModule || $applicationModule = 'CMS';
}

if (preg_match("/^\/at\/.*/i", $_SERVER['REQUEST_URI'])) {
    $applicationModule || $applicationModule = 'AT';
}

if (preg_match("/^\/tc\/.*/i", $_SERVER['REQUEST_URI'])) {
    $applicationModule || $applicationModule = 'TC';
}

if (preg_match("/^\/recruit\/.*/i", $_SERVER['REQUEST_URI'])) {
    $applicationModule || $applicationModule = 'RECRUIT';
}

if (preg_match("/^\/hr\/.*/i", $_SERVER['REQUEST_URI'])) {
    $applicationModule || $applicationModule = 'HR';
}

if (preg_match("/^\/wrapper\/.*/i", $_SERVER['REQUEST_URI'])) {
    $applicationModule || $applicationModule = 'WRAPPER';
}

if (preg_match("/^\/api\/.*/i", $_SERVER['REQUEST_URI'])) {
    $applicationModule || $applicationModule = 'API';
}

if (preg_match("/^\/mobile\/.*/i", $_SERVER['REQUEST_URI'])) {
    defined('APPLICATION_MODULE') || define('APPLICATION_MODULE', 'MOBILE');
}

//$applicationModule || $applicationModule = 'ELS';

if($applicationModule){
    define('APPLICATION_MODULE', $applicationModule);
}
defined('APPLICATION_VERSION') || define('APPLICATION_VERSION', '5');

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

function pr($array) {
   echo "<xmp>";
   if (!isset($array)) echo "pr(): value not set";
   elseif (is_object($array))  print_r($array);
   elseif (!is_array($array)) echo "pr() string: $array";
   elseif (!count($array)) echo "pr(): array empty";
   else print_r($array);
   echo "</xmp>";
}

/** Zend_Application */
require_once 'Zend/Application.php';

try {
    $application = new Zend_Application(
        APPLICATION_ENV,
        APPLICATION_PATH . '/settings/config.ini'
    );
} catch (Zend_Application_Exception $e) {
}

$scheme = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : '';
$scheme = (($scheme) && ($scheme != 'off')) ? 'https' : 'http';
$scheme .= "://".$_SERVER['HTTP_HOST'];
Zend_Registry::set('baseUrl', $scheme);

/** Enjoy without unmanaged! */
$application->bootstrap()->run();

