<?php
class HM_Controller_Plugin_DirectAccess extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        
        $frontController = Zend_Controller_Front::getInstance();
        $request = $frontController->getRequest();
        if ($request && $request->getParam('direct-access')) {
            
            $login = $request->getParam('login');
            $domain = '';
            if(strpos($login, '@')!==false)
                list($login, $domain) = explode("@", $login);
            else
                if (false !== strstr($login, '\\')) {
                    list($domain, $login) = explode('\\', $login);
                }
            $serviceContainer->getService('User')->authorizeByLogin($login, $domain);
            $role = $request->getParam('role', HM_Role_Abstract_RoleModel::ROLE_ENDUSER);
            $serviceContainer->getService('User')->switchRole($role);
            
            list($url, $params) = explode('?', $_SERVER['REQUEST_URI']);
            header('Location: ' . $url);
            exit();
        }
        return true;
        
    }
}
