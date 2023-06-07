<?php
class HM_Resource_View extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var HM_View_Extended
     */
    protected $_view = null;

    public function init()
    {
        if (defined('APPLICATION_MODULE') && APPLICATION_MODULE == 'MOBILE') {
            $this->_view = new Zend_View();
            Zend_Registry::set('view', $this->_view);
            return $this->_view;
        }

        if (null === $this->_view) {

            // fix обработки ошибок во view helper'ах
            Zend_Layout::startMvc(
                [
                  'pluginClass' => 'HM_Layout_Controller_Plugin_Layout',
//                  'pluginClass' => 'Zend_Layout_Controller_Plugin_Layout',
                ]
            );
            $layout = Zend_Layout::getMvcInstance();

            $this->_view = new HM_View();
            $this->_view->setEncoding(Zend_Registry::get('config')->charset);
            $this->_view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
            $this->_view->addHelperPath("HM/View/Helper", "HM_View_Helper");
            $this->_view->addHelperPath(Zend_Registry::get('config')->path->helpers->default, 'HM_View_Helper');
            $this->_view->addHelperPath(Zend_Registry::get('config')->path->infoblocks->default, 'HM_View_Infoblock');
            $this->_view->addHelperPath(Zend_Registry::get('config')->path->sidebars->default, 'HM_View_Sidebar');

            $this->_view->addScriptPath(APPLICATION_PATH.'/views/layout/');

            // здесь старые шаблоны, возможно стоит от них избавляться
            $this->_view->addScriptPath(APPLICATION_PATH.'/views/partials/');

            // init overloaded jquery helper
            require_once 'HM/View/Helper/JQuery/Container.php';
            Zend_Registry::set('ZendX_JQuery_View_Helper_JQuery', new HM_View_Helper_JQuery_Container());

            $layout->setViewSuffix('tpl');
            $layout->setView($this->_view);

            $view_renderer = new Zend_Controller_Action_Helper_ViewRenderer();
            $view_renderer
                ->setView($this->_view)
                ->setViewSuffix('tpl');

            Zend_Controller_Action_HelperBroker::addHelper($view_renderer);
            Zend_Controller_Action_HelperBroker::addPrefix('HM_Controller_Action_Helper');
            Zend_Registry::set('view', $this->_view);
        }

        return $this->_view;
    }
}
