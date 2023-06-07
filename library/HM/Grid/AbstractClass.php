<?php

abstract class HM_Grid_AbstractClass
{
    public function getView()
    {
        return Zend_Registry::get('view');
    }

    public function getService($service)
    {
        return Zend_Registry::get('serviceContainer')->getService($service);
    }

    public function getRequest()
    {
        return Zend_Controller_Front::getInstance()->getRequest();
    }

    public function isAjaxRequest()
    {
        return $this->getRequest()->isXmlHttpRequest();
    }

    public function currentUserIs($role)
    {
        /** @var Zend_Acl $aclService */
        $aclService = $this->getService('Acl');
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        return $aclService->inheritsRole($userService->getCurrentUserRole(), $role);
    }

    protected function _urlAllowed($url)
    {
        if (!is_array($url)) {
            return true;
        }

        $request = $this->getRequest();

        $defaultUrlParams = array(
            'module'     => $request->getModuleName(),
            'controller' => $request->getControllerName(),
            'action'     => $request->getActionName()
        );

        $url = array_merge($defaultUrlParams, $url);

        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');

        $resource = HM_ControllerAcl::getResourceName($url['module'], $url['controller'], $url['action']);

        return $acl->isCurrentAllowed($resource);

    }
}