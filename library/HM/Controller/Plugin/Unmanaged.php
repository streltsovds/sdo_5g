<?php

/**
 * Плагин решает все функции, связанные с layout'ом
 * (проксирует вывод старого контроллера).
 * Todo: писать полноценный layout-контроллер, который сам управляет меню, ролями етс.
 *
 */
class HM_Controller_Plugin_Unmanaged extends Zend_Controller_Plugin_Abstract
{

	private $_placeholders = array(
		'content' => '',
	);

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	$this->_authenticate();
    }

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $locale = Zend_Registry::get('Zend_Locale');
        if ($locale != 'ru_RU') {
            if (strpos($locale, 'unmanaged') === false) {
                if (Zend_Registry::isRegistered('translate')) {
                    Zend_Registry::get('translate')->setLocale($locale . '_unmanaged'); // turn back unmanaged locale
                }
            }
        }
        Controller::finish_gettext_debug_mode();
    	$this->_set_layout();
    }

    private function _set_layout()
    {
        $response = $this->getResponse();
        ob_start();
		Zend_Registry::get('unmanaged_controller')->terminate();
        $unmanaged_layout = ob_get_clean();

        $this->_placeholders['content']      = $response->getBody();
        $this->_placeholders['breadcrumbs']  = Zend_Registry::get('view')->concatBreadCrumbs();
        $this->_placeholders['esNotifies']  = Zend_Registry::get('view')->getEsNotifies();
        $this->_placeholders['jQuery']       = Zend_Registry::get('view')->jQuery();
        $this->_placeholders['headScript']   = Zend_Registry::get('view')->headScript();
        $this->_placeholders['inlineScript'] = Zend_Registry::get('view')->inlineScript();
        $this->_placeholders['headLink']     = Zend_Registry::get('view')->headLink();
        $this->_placeholders['headStyle']    = Zend_Registry::get('view')->headStyle();
        $this->_placeholders['jQueryHeadScript']   = Zend_Registry::get('view')->jQuery()->headScript();
        $this->_placeholders['jQueryInlineScript'] = Zend_Registry::get('view')->jQuery()->inlineScript();
        $this->_placeholders['jQueryHeadLink']     = Zend_Registry::get('view')->jQuery()->headLink();

        $frontendBootstrap = new HM_Frontend_Bootstrap();

        $this->_placeholders['hmJsBootstrap']      = $frontendBootstrap->getJS();
        $this->_placeholders['hmCssBootstrap']     = $frontendBootstrap->getCss();

        $this->_setPlaceholders($unmanaged_layout);
        $response->prepend('unmanaged_layout', $unmanaged_layout);
    }

    private function _setPlaceholders(&$str)
    {
		foreach ($this->_placeholders as $name => $value) {
			$str = str_replace("<!--placeholder:{$name}-->", $value, $str);
		}
    }
}

