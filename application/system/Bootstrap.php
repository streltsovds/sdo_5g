<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    private $_config = null;

    public function __construct($application)
    {
        if (!defined('HARDCODE_WITHOUT_SESSION') || (HARDCODE_WITHOUT_SESSION !== true)) {
            $this->_initSession(); // хардкор патамушта нужно инициализировать zend-сессии до require 1.php
        }
        parent::__construct($application);
    }

    protected function _initRequest()
    {
        $front = $this->bootstrap('frontController')->getResource('frontController');
        $front->setRequest(new HM_Controller_Request_Http());
    }

    protected function _initSession()
    {
        if (empty($_SESSION)) {
            if (isset($_POST['sessid']) && strlen($_POST['sessid'])) {
                session_id($_POST['sessid']);
            }

            Zend_Registry::set('session_redirector', new Zend_Session_Namespace('redirector'));

            Zend_Registry::set('session_namespace_default', new Zend_Session_Namespace('default'));
        }
    }

    protected function _initLoader()
    {
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->setFallbackAutoloader(true);
        return $loader;
    }

    /**
     * @return HM_Config_Ini
     * @throws Zend_Config_Exception
     */
    protected function _initConfig()
    {
        /**
         * @todo put dev config file instead of last null parameter after HM_Config_Ini refactoring!
         */
        $config = new HM_Config_Ini(APPLICATION_PATH . '/settings/config.ini', APPLICATION_ENV, true, null);
        $this->_config = $config;
        Zend_Registry::set('config', $config);
        return $config;
    }

    /**
     * @throws Zend_Application_Bootstrap_Exception
     * @throws Zend_Exception
     */
    protected function _initLanguage()
    {
        $locale = $this->bootstrap('locale')->getResource('locale');
        if ($locale != 'ru_RU') {
            $config = $this->bootstrap('config')->getResource('config');
            $languages = $config->languages;
            $this->translatorCustom($locale, $config, $languages);
            Zend_Registry::get('translate')->setLocale($locale); // override unmanaged locale
        }
        Zend_Registry::set('locale', $locale);
    }

    protected function _initDefaultTimeZone()
    {
        date_default_timezone_set(Zend_Registry::get('config')->timezone->default);
    }

    protected function _initLog()
    {
        $logs = array(
            'log_system' => $this->_config->path->log->system . date('Y-m') . '.txt',
            'log_mail' => $this->_config->path->log->mail . date('Y-m') . '.html',
            'log_security' => $this->_config->path->log->security . date('Y-m') . '.txt',
            'log_integration' => $this->_config->path->log->integration . date('Y-m') . '.txt',
        );

        foreach ($logs as $name => $path) {
            if (!file_exists($path)) {
                @touch($path);
                @chmod($path, 0666);
            }
            $writer = new Zend_Log_Writer_Stream($path);
            $log = new Zend_Log($writer);
            Zend_Registry::set($name, $log);
        }
    }

    protected function _initFileTransferAdapter()
    {
        $adapter = new Zend_File_Transfer_Adapter_Http();
        $adapter->setDestination($this->_config->path->upload->tmp);
        return $adapter;
    }

    protected function _initActionHelperBroker()
    {
        Zend_Controller_Action_HelperBroker::addPrefix('HM_Controller_Action_Helper');
    }

    /**
     * @throws Zend_Application_Bootstrap_Exception
     */
    protected function _initModulesDirectory()
    {
        $front = $this->bootstrap('frontController')->getResource('frontController');
        $config = $this->bootstrap('config')->getResource('config');

        if (defined('APPLICATION_MODULE')) {
            switch (APPLICATION_MODULE) {
                case 'AT':
                    $front->addModuleDirectory(APPLICATION_PATH . '/modules/at');
                    $front->setBaseUrl($config->url->base.'at/');
                    break;
                case 'MOBILE':
                    $front->addModuleDirectory(APPLICATION_PATH .'/modules/mobile');
                    $front->setBaseUrl($config->url->base.'mobile/');
                    break;
                case 'HR':
                    $front->addModuleDirectory(APPLICATION_PATH . '/modules/hr');
                    $front->setBaseUrl($config->url->base . 'hr/');
                    break;
                case 'RECRUIT':
                    $front->addModuleDirectory(APPLICATION_PATH . '/modules/recruit');
                    $front->setBaseUrl($config->url->base . 'recruit/');
                    break;
                case 'TC':
                    $front->addModuleDirectory(APPLICATION_PATH . '/modules/tc');
                    $front->setBaseUrl($config->url->base . 'tc/');
                    break;
                case 'CMS':
                    $front->addModuleDirectory(APPLICATION_PATH . '/modules/cms');
                    $front->setBaseUrl($config->url->base . 'cms/');
                    break;
                case 'WRAPPER':
                    $front->addModuleDirectory(APPLICATION_PATH . '/modules/els');
                    $front->setBaseUrl($config->url->base . 'wrapper/');
                    break;
                case 'API':
                    $front->addModuleDirectory(APPLICATION_PATH . '/modules/api');
                    $front->setBaseUrl($config->url->base . 'api/');
                    break;
                default:
                    $front->addModuleDirectory(APPLICATION_PATH . '/modules/els');
                    $front->setBaseUrl($config->url->base);
                    break;
            }
        } else {
            $front->addModuleDirectory(APPLICATION_PATH . '/modules/els');
            $front->setBaseUrl($config->url->base);
        }
    }

    protected function _initNavigation()
    {
        $container = $this->bootstrap('container')->getResource('container');
        $this->bootstrap('db');

        Zend_View_Helper_Navigation_HelperAbstract::setDefaultAcl($container->getService('Acl'));
        Zend_View_Helper_Navigation_HelperAbstract::setDefaultRole($container->getService('User')->getCurrentUserRole());
    }

    // ???
    protected function _initOracleAdapter()
    {

        $db = $this->bootstrap('db')->getResource('db');
        $this->bootstrap('toolbar');

        $adapter = strtolower(get_class($db));
        $adapter = str_replace("zend_db_adapter_", "", $adapter);
        $adapter = str_replace("hm_db_adapter_", "", $adapter);
        if (in_array($adapter, array('pdo_oci', 'oracle'))) {
            $db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
        }

        $config = Zend_Registry::get('config');
        if ($config->cache->db == true) {

            $frontendOptions = array(
                'automatic_serialization' => true
            );

            $backendOptions = array(
                'cache_dir' => APPLICATION_PATH . '/../data/cache/'
            );

            $cache = Zend_Cache::factory(
                            'Core', 'File', $frontendOptions, $backendOptions
            );

            // Next, set the cache to be used with all table objects
            Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
        }
    }

    protected function _initRouter()
    {

        $config = $this->bootstrap('config')->getResource('config');
        $router = new Zend_Controller_Router_Rewrite();
        $router->addConfig($config, 'routes');
        $front = $this->bootstrap('frontController')->getResource('frontController');

        // Не используется?
        if (defined('APPLICATION_MODULE') && (APPLICATION_MODULE == 'ELS') && (false !== strstr($_SERVER['REQUEST_URI'], '/rest/'))/*         * && ($front->getRequest()->getModuleName() == 'rest') */) {
            $router->addRoute('rest', new Zend_Rest_Route($front, array(), array('rest')));
        }

        $front->setRouter($router);
        return $router;
    }

    protected function _initMailer()
    {
        $config = $this->bootstrap('config')->getResource('config');
        $transport = $config->mailer->transport;
        $mailTransportName = 'Zend_Mail_Transport_'.ucfirst($transport);
        if (!class_exists($mailTransportName)) {
            throw new RuntimeException('Mail transport name doesn\'t define');
        }
        Zend_Mail::setDefaultTransport(
            new $mailTransportName($config->mailer->params->toArray())
        );
        Zend_Mail::setDefaultFrom($config->mailer->default->email, $config->mailer->default->name);
    }

    protected function _initCache()
    {
        $config = $this->bootstrap('config')->getResource('config');
        $cache = Zend_Cache::factory('Core', $config->cache->type, $config->cache->frontend->toArray(), $config->cache->backend->toArray());
        Zend_Registry::set('cache', $cache);

        return $cache;
    }

    protected function _initExtensions()
    {
        $container = $this->bootstrap('container')->getResource('container');
        $container->getService('Extension')->init();

        if (defined('APPLICATION_MODULE') && APPLICATION_MODULE=='MOBILE') return;

        $event = new sfEvent($this, HM_Extension_ExtensionService::EVENT_AFTER_INIT_EXTENSIONS);
        $container->getService('EventDispatcher')->notify($event);
    }

    protected function translatorCustom($locale, $config, $languages)
    {
        $hmLang = isset($_COOKIE['hmlang']) ? $_COOKIE['hmlang'] : 'ru';
        if (isset($hmLang)) {
            if (isset($languages->$hmLang)) {
                $locale = $languages->$hmLang->locale;
            }
        } elseif (!$config->resources->locale->force) {
            require_once('Zend/Locale.php');
            $l = new Zend_Locale();
            $accepted = $l->getBrowser();
            if (is_array($accepted) && count($accepted)) {
                foreach($accepted as $acceptedLocale => $weight) {
                    foreach($languages as $lang => $langLocale) {
                        if (strtolower($acceptedLocale) == strtolower($langLocale['locale'])) {
                            $locale = $langLocale['locale'];
                            break 2;
                        }
                    }
                }
            }
        }

        $translate = new Zend_Translate(
            array(
                'adapter' => 'HM_Translate_Adapter_Gettext',
                'content' => APPLICATION_PATH . '/../data/locales/' . $locale . '/LC_MESSAGES/',
                'locale'  => $locale
            )
        );

        $translate->getAdapter()->generateJsTranslate($locale);
        Zend_Registry::set('translate', $translate);
        Zend_Registry::set('Zend_Translate', $translate);

        if (!function_exists('_')) {
            function _($str) {
                return Zend_Registry::get('translate')->_($str);
            }
        }

        if (!function_exists('_n')) {
            function _n($msgid, $str, $num) {
                return Zend_Registry::get('translate')->plural($msgid, $str, $num);
            }
        }

        if (!function_exists('generateRandomColorClass')) {
            /**
             * Выдает случайный цвет из набора vuetify (который всегда можно переопределить)
             *
             * Подробнее про цвета по ссылке https://vuetifyjs.com/ru/style/colors
             * @return string цвет
             */
            function generateRandomColorClass()
            {
                $vuetifyColorsClasses = array(
                    'red darken-1', // #E53935
                    'red darken-2', // #D32F2F
                    'pink', // #E91E63
                    'purple', // #9C27B0
                    'deep-purple', // #673AB7
                    'indigo', // #3F51B5
                    'blue', // #2196F3
                    'light-blue darken-1', // #039BE5
                    'light-blue darken-2', // #0288D1
                    'cyan', // #00BCD4
                    'teal', // #009688
                    'green', // #4CAF50
                    'light-green', // #8BC34A
                    'lime', // #CDDC39
                    'yellow darken-1', // #FDD835
                    'yellow darken-2', // #FBC02D
                    'amber', // #FFC107
                    'orange', // #FF9800
                    'deep-orange', // #FF5722
                    'brown', // #795548
                    'blue-grey' // #607D8B
                );

                return $vuetifyColorsClasses[rand(0, count($vuetifyColorsClasses) - 1)];
            }
        }
    }

    /**
     * @throws Zend_Application_Bootstrap_Exception
     * @throws Zend_Locale_Exception
     * @throws Zend_Translate_Exception
     * @throws Zend_Exception
     */
    protected function _initTranslator()
    {
        require_once('Zend/Translate.php');
        require_once('Zend/Translate/Adapter/Gettext.php');
        require_once('Zend/Registry.php');

        $config = $this->bootstrap('config')->getResource('config');
        $languages = $config->languages;
        $locale = Zend_Registry::get('locale');

        return $this->translatorCustom($locale, $config, $languages);
    }
}