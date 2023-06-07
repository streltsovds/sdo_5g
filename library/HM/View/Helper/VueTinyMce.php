<?php

class HM_View_Helper_VueTinyMce extends Zend_View_Helper_HtmlElement
{
    public function vueTinyMce($id, $value = null, array $attribs = array(), array $errors = array())
    {
        $configRoot = Zend_Registry::get('config');

        if (isset($configRoot->wysiwyg->params)) {
            $config = $configRoot->wysiwyg->params;

            $scriptDebugMode = $config->script_debug_mode;

            $tinyMceScriptUrl = $scriptDebugMode ? $config->script_url_debug : $config->script_url;

            if ($tinyMceScriptUrl) {
                $this->view->headScript()->appendFile($tinyMceScriptUrl);
            }
        }

        //scripts for elFinder
        $this->view->headLink()->appendStylesheet("//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/themes/smoothness/jquery-ui.css");

        $this->view->headScript()->appendFile("//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js");

        $this->view->headScript()->appendFile("//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js");

        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/elfinder-2.1/js/elfinder.full.js'));
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/elfinder-2.1/js/proxy/elFinderSupportVer1.js'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/elfinder-2.1/css/elfinder.full.css'));

        if (!isset($attribs['lang'])){
            $attribs['lang'] = 'ru';
        }

        $serviceContainer = Zend_Registry::get('serviceContainer');
        /** @var HM_Storage_StorageService $storageService */
        $storageService = $serviceContainer->getService('Storage');
        $currentUserId = $serviceContainer->getService('User')->getCurrentUserId();
        $elFinderDir = $storageService->createUserDirIfNotExists($currentUserId);

        $attribs['target_hash'] = $elFinderDir->hash;

        $langFile = Zend_Registry::get('config')->url->base.'js/lib/elfinder-2.1/js/i18n/elfinder.'.$attribs['lang'].'.js';
        if(!file_exists(APPLICATION_PATH . '/../public' . $langFile)) {
            $langFile = Zend_Registry::get('config')->url->base.'js/lib/elfinder-2.1/js/i18n/elfinder.ru.js';
        }

        $this->view->headScript()->appendFile($langFile);

        $stylePath = ZendX_JQuery::encodeJson($this->view->VueScript()->getRealPath('app.css'));


        $attribsJson = HM_Json::encodeErrorThrow($attribs);
        $errorsJson = HM_Json::encodeErrorThrow($errors);
        $valueHtmlEncoded = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5);

        /** см. frontend/app/src/components/forms/hm-tiny-mce/index.vue */

        return <<<HTML
<hm-tiny-mce
    name='$id'
    :attribs='$attribsJson'
    value='$valueHtmlEncoded'
    :errors='$errorsJson'
    :style-path='$stylePath'
>
</hm-tiny-mce>
HTML;

    }
}
