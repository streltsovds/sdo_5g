<?php

require_once 'Zend/View/Helper/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class HM_View_Helper_Abstract extends Zend_View_Helper_Abstract
{
    public function setView(Zend_View_Interface $view)
    {
        $this->view = clone $view;
        $config = Zend_Registry::get('config');
        $this->view->addScriptPath($config->path->helpers->complex);
        $this->view->addScriptPath($config->path->helpers->layout);
        return $this;
    }

    /**
     * @param $name
     * @return HM_Service_Abstract
     * @throws Zend_Exception
     */
    protected function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

}