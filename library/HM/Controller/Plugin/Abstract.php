<?php
class HM_Controller_Plugin_Abstract extends Zend_Controller_Plugin_Abstract
{
    /**
     * @param $name
     * @return HM_Service_Abstract
     */
    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    /**
     * @return HM_View_Extended
     */
    public function getView()
    {
        return Zend_Registry::get('view');
    }
}
