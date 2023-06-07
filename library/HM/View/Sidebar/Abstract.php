<?php

require_once 'Zend/View/Helper/Abstract.php';

abstract class HM_View_Sidebar_Abstract extends Zend_View_Helper_Abstract
{
    protected $_options;

    protected $_modal = false;

    protected $_opened = false;

    abstract function getIcon();

    abstract function getContent();

    public function setOptions($options)
    {
        $this->_options = $options;

        $this->setModal(isset($options['modal']) ? $options['modal'] : false); // NOT modal by default
        $this->setOpened(isset($options['opened']) ? $options['opened'] : false); // opened by default

        return $this;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function getModel()
    {
        return $this->_options['model'] ? : null;
    }

    /**
     * @param bool $modal
     */
    public function setModal($modal)
    {
        $this->_modal = $modal;
    }

    public function isModal()
    {
        return $this->_modal;
    }

    /**
     * @param bool $opened
     */
    public function setOpened($opened)
    {
        $this->_opened = $opened;
    }

    public function isOpened()
    {
        return $this->_opened;
    }

    public function getName()
    {
        return lcfirst(str_replace('HM_View_Sidebar_', '', get_class($this)));
    }

    public function getTitle()
    {
        return $this->getIcon();
    }

    public function getCount()
    {
        return 0;
    }

    /**
     * Получить разметку переключалки
     *
     * @return string разметка переключалки сайдбара
     */
    public function getToggle() {
//        $testings = $this->getIcon();
        return
            '<hm-sidebar-toggle 
                has-avatar 
                sidebar-name="' . $this->getName() . '"
                title="' . $this->getTitle() . '"
                data-debug-php-classname="'. get_class($this). '"
             >
             <!--<template v-slot:notification>
                <hm-notification-counter/>
             </template>-->
             <svg-icon color=" #FFFFFF" name="'. $this->getIcon() .'" count="' . $this->getCount() . '" title="" />
            </hm-sidebar-toggle>';
    }

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
        $config = Zend_Registry::get('config');
        $this->view->addScriptPath($config->path->sidebars->default . 'views/');
        parent::setView($view);

        $userService = Zend_Registry::get('serviceContainer')->getService('User');
        $view->isEndUser = $userService->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_ENDUSER;

        return $this;
    }


    /**
     * @param  $name
     * @return HM_Service_Abstract
     */
    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    /**
     * @param  HM_Collection $collection
     * @return HM_Model_Abstract
     */
    public function getOne($collection)
    {
        if (count($collection)) {
            return $collection->current();
        }
        return false;
    }

    public function __call($name, $arguments)
    {
        if ($name == $this->getName()) {
            return $this;
        }
    }

    public function isSubjectContext()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $subject = $request->getParam('subject');
        $subjectId = $request->getParam('subject_id');

        return $subject ? 'subject' == $subject : $subjectId;
    }
}
