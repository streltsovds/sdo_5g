<?php
class HM_View_Helper_VueServerFile extends Zend_View_Helper_FormHidden
{
    public function vueServerFile($name, $value = null, array $attribs = array(), array $errors = array())
    {
        self::addScriptsAndStyles($attribs['lang']);
        $errors = ZendX_JQuery::encodeJson($errors);
        $attribs = $value ? ZendX_JQuery::encodeJson($value) : "{}";

        return <<<HTML
<hm-elfinder
    name='$name'
    :errors='$errors'
    :attribs='$attribs'
    transport="v2"
>
</hm-elfinder>
HTML;
    }

    public static function addScriptsAndStyles($lang = null)
    {
        $lang = $lang ?: '';
        $view = Zend_Registry::get('view');

        $view->headLink()->appendStylesheet("//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/themes/smoothness/jquery-ui.css");
        $view->headScript()->appendFile("//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js");
        $view->headScript()->appendFile("//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js");

        $view->headScript()->appendFile($view->serverUrl('/js/lib/elfinder-2.1/js/elfinder.full.js'));
        $view->headScript()->appendFile($view->serverUrl('/js/lib/elfinder-2.1/js/proxy/elFinderSupportVer1.js'));
        $view->headLink()->appendStylesheet($view->serverUrl('/js/lib/elfinder-2.1/css/elfinder.full.css'));

        $langFile = Zend_Registry::get('config')->url->base.'js/lib/elfinder-2.1/js/i18n/elfinder.'.$lang.'.js';
        if(!file_exists(APPLICATION_PATH . '/../public' . $langFile)) {
            $langFile = Zend_Registry::get('config')->url->base.'js/lib/elfinder-2.1/js/i18n/elfinder.ru.js';
        }

        $view->headScript()->appendFile($langFile);
    }

}