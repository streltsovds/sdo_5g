<?php
class Infoblock_ScreencastController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Chart;

	public function init()
	{
		parent::init();
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
	}

	public function getScreencastAction()
	{
		$screencast = $this->_getParam('screencast', null);
        $path = Zend_Registry::get('config')->path->screencasts."{$screencast}";
        $indexPath = $path."/index.html";
		if (is_dir($path) && file_exists($indexPath)) {
		    $this->view->url = "/screencasts/{$screencast}/index.html";
		}
	}
}