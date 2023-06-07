<?php

abstract class HM_ControllerAcl
{
    protected $_module = '';
    protected $_controller = '';

    /**
     * @var HM_Acl
     */
    protected $_acl = null;

    public function __construct (HM_Acl $acl)
    {
        $this->_acl = $acl;

        $this->_init();
    }

    abstract protected function _init();

    public static function getResourceName($module, $controller, $action)
    {
        return sprintf('mca:%s:%s:%s', $module, $controller, $action);
    }

    protected function _createResource($action)
    {
        if ($this->_module === '') {
            throw new Exception(_('Не указан модуль, для которого настраиваются права'));
        }

        $resource = self::getResourceName($this->_module, $this->_controller, $action);

        $this->_acl->addResource(new Zend_Acl_Resource($resource));

        return $resource;
    }

    protected function _allow($actionOrMca, $roles)
    {
        if (!is_array($actionOrMca)) {
            $this->_acl->allow($roles, $this->_createResource($actionOrMca));
        } else {
            list($module, $controller, $action) = $actionOrMca;
            $this->_module = $module;
            $this->_controller = $controller;
            $this->_acl->allow($roles, $this->_createResource($action));
        }
    }

}