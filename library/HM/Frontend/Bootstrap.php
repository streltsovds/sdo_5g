<?php

class HM_Frontend_Bootstrap extends HM_View_Helper_Abstract
{
    const COOKIE_DEBUG_NAME = 'hm-dev-debug-enabled';

    protected static $_needInit = true;

    public function __construct()
    {
        self::_initParams();
    }

    protected static $debug = false;
    protected static $frontDebug = false;
    protected static $baseUrl = '/hm';
    protected static $build = false;
    protected static $buildDate = false;

    protected static function _loadConfig()
    {
        $config = file_get_contents(APPLICATION_PATH.'/settings/frontend.json');

        return json_decode($config, true);
    }

    protected static function _initParams()
    {
        self::$debug = self::_getBuildServer();

        if (self::$debug) {
            self::$frontDebug = isset($_COOKIE[self::COOKIE_DEBUG_NAME]) && $_COOKIE[self::COOKIE_DEBUG_NAME] === '1';
        }

        $versionInfo = HM_Frontend_Version::getVersionInfo();

        self::$build     = self::$frontDebug ? time() : $versionInfo['build'];
        self::$buildDate = (self::$frontDebug ? time() : $versionInfo['timestamp']) * 1000;

    }

    protected static function _injectStyleSheet($type = 'screen')
    {
        if (self::$frontDebug) {

            $config = json_encode(self::_loadConfig());

            $buildUrl = self::_getBuildServer().'/sass/get?v='.self::$build.'&media='.$type.'&theme=default&config='.urlencode($config).'&serverUrl='.urlencode('http://'.$_SERVER['SERVER_NAME'].'/');

            $url = '/dev_tools/proxy?url='.urlencode($buildUrl);

        } else {
            $url = self::$baseUrl.'/css/themes/default/'.$type.'.css?v='.self::$build;
        }

        $id = 'hm-core';

        if ($type !== 'screen') {
            $id .= '-'.$type;
        }

        $id .= '-stylesheet';

        switch ($type) {
            case 'print':
                $media = 'print';
                break;
            default:
                $media = 'all';
        }

        return '<link href="'.$url.'" rel="stylesheet" id="'.$id.'" media="'.$media.'">';
    }

    public function getCss()
    {
        $result  = self::_injectStyleSheet('screen');
        $result .= '<!--[if IE]>';
        $result .= self::_injectStyleSheet('ie');
        $result .= '<![endif]-->';
        $result .= self::_injectStyleSheet('print');

        return $result;

    }

    public function getJS()
    {
        $build     = self::$build;
        $buildDate = self::$buildDate;

        $baseUrl = self::$baseUrl;

        $result = '<script>'.
                        'window.hm = window.hm || {}; '.
                        'hm.dict = hm.dict || {}; '.
                        'hm.isDebug = '.json_encode(self::$frontDebug).'; '.
                        'hm.debugAllowed = '.json_encode(self::$debug).'; '.
                        'hm.basePath = '.json_encode($baseUrl).'; '.
                        'hm.build = '.$build.'; '.
                        'hm.buildDate = new Date('.$buildDate.');'.
                        'hm.appClass = "hm.core.Application";'.
                        'hm.serverInitTime = '.time().' * 1000;'.
                        'hm.clientInitTime = Date.now();'.
                   '</script>';

        if (self::$frontDebug) {

            $config = json_encode(self::_loadConfig());

            // дебаг одним файлом конкурса
            $result .= self::_injectScript(self::_getBuildServer().'/js/get/full?v='.$build.'&config='.urlencode($config));

        } else {
            $result .= self::_injectScript($baseUrl.'/js/hm.min.js?v='.$build);
        }

        $result .= '<script>'.
                         'HM.init();'.
                   '</script>';

        return $result;

    }

    protected static function _getBuildServer()
    {
        return getenv('HM_FRONTEND_BUILD_SERVER');
    }

    protected static function _injectScript($fileName)
    {
        return '<script src="'.$fileName.'"></script>';
    }

}