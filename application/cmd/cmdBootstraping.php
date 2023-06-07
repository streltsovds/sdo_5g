<?php
 //ini_set('display_errors', 1);
// error_reporting(E_ALL);
require __DIR__ . '/../../vendor/autoload.php';

#error_reporting(E_ERROR | E_WARNING | E_PARSE);
// Указание пути к директории приложения
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../'));
defined('PUBLIC_PATH') || define('PUBLIC_PATH', realpath(dirname(__FILE__) . '/../../public'));

// Определение текущего режима работы приложения (по умолчанию production)
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

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

$applicationModule || $applicationModule = 'ELS';

define('APPLICATION_MODULE', $applicationModule);

defined('APPLICATION_VERSION') || define('APPLICATION_VERSION', '5');
define('HM_FRONTEND_FORCE_UNMANAGED_MODE', true);

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));


require_once 'Zend/Application.php';

try {
    $application = new Zend_Application(
        APPLICATION_ENV,
        realpath(APPLICATION_PATH . '/settings/config.ini')
    );
} catch (Zend_Application_Exception $e) {
}


try {
    $application->getBootstrap()->bootstrap('db');
    $application->getBootstrap()->bootstrap('loader');
    $application->getBootstrap()->bootstrap('config');
    $application->getBootstrap()->bootstrap('cache');
    $application->getBootstrap()->bootstrap('container');
    $application->getBootstrap()->bootstrap('view');
    $application->getBootstrap()->bootstrap('log');
    $application->getBootstrap()->bootstrap('oracleAdapter');
    $application->getBootstrap()->bootstrap('modules');
    $application->getBootstrap()->bootstrap('mailer');

    $application->getBootstrap()->bootstrap('router');
    $application->getBootstrap()->frontController->getRouter()->addDefaultRoutes();
} catch (Zend_Application_Bootstrap_Exception $e) {
}

//ini_set('display_errors', 0);
//error_reporting(E_ALL);

//include APPLICATION_PATH . "/../public/unmanaged/adodb_field_names.inc.php";

if (!isset($locale)) {
    if (empty($GLOBALS['locale'])) { // не везде инитится  переменная locale, поэтому пробуем дернуть из глобалс
        $locale = isset($GLOBALS['locale']) ? $GLOBALS['locale'] : null;
        try {
            $locale = Zend_Registry::get('config')->resources->locale->default;
        } catch (Zend_Exception $e) {
        }
    }
}
try {
    $translate = new Zend_Translate(
        array(
            'adapter' => 'HM_Translate_Adapter_Gettext',
            'content' => APPLICATION_PATH . '/../data/locales/' . $locale . '/LC_MESSAGES/',
            'locale' => $locale . '_unmanaged'
        )
    );
    $translate->addTranslation(
        array(
            'adapter' => 'HM_Translate_Adapter_Gettext',
            'content' => APPLICATION_PATH . '/../data/locales/' . $locale . '/LC_MESSAGES/',
            'locale'  => $locale,
        )
    );
} catch (Zend_Translate_Exception $e) {
}

Zend_Registry::set('translate', $translate);
Zend_Registry::set('Zend_Translate', $translate);

if(!defined('HARDCODE_WITHOUT_GETTEXT') || (HARDCODE_WITHOUT_GETTEXT !== true)){
    if (!function_exists('_')){
        function _($str){
            return Zend_Registry::get('translate')->_($str);
        }
    }
    if (!function_exists('_n')){
        function _n($msgid, $str, $num){
            return Zend_Registry::get('translate')->plural($msgid, $str, $num);
        }
    }
}

//setlocale(LC_ALL, 'ru_RU.UTF8'); // чтобы работал fgetcsv в процессеимпорта из 1С
