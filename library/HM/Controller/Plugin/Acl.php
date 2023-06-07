<?php

use HM_Role_Abstract_RoleModel as Roles;

class HM_Controller_Plugin_Acl extends HM_Controller_Plugin_Abstract
{
    protected function _loadModuleAcl(HM_Acl $acl,$moduleName)
    {
        $aclDirectory = Zend_Controller_Front::getInstance()->getModuleDirectory().'/acls';

        if (!is_dir($aclDirectory) || !is_readable($aclDirectory)) {
            return;
        }

        if (!($handle = opendir($aclDirectory))) {
            return;
        }

        while (false !== ($file = readdir($handle))) {

            if (in_array($file, array('.', '..'))) {
                continue;
            }

            if (substr($file, -4) !== '.php') {
                continue;
            }

            $acl->storeModuleName($moduleName);

            $moduleAclClassName = 'HM_Acl_'.substr($file, 0, -4);

            new $moduleAclClassName($acl);
        }
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if($_SERVER['REQUEST_METHOD']=='OPTIONS') { //Иначе приложение не смогет передавать client-security-token
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, ".HM_User_UserService::SECUTITY_TOKEN_NAME);
            die();
        }

        $client_security_token = $this->getService('User')->getSecurityToken($this->_request->getParams());
        if($client_security_token) {
            $this->getService('User')->authorizeByToken();
        }

        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        $moduleName    = $request->getModuleName();
        $isAjaxRequest = $request->isXmlHttpRequest();

        $this->_loadModuleAcl($acl, $moduleName);

        // @todo: а зачем здесь $this->session????
        $this->session = $session = new Zend_Session_Namespace('default');

        $switch = $session->switch_role;
        $session->switch_role = 0;

        $currentUserId = $userService->getCurrentUserId();

        // если должны куда-то редиректиться после авторизации - редиректимся
        if ($currentUserId && !$isAjaxRequest && isset($session->autoredirect) && strlen($session->autoredirect['url']) ) {
            $url = $session->autoredirect['url'];
            unset ($session->autoredirect);
            header('Location: '.$url);
            exit();
        }

        /** @var HM_Log_LogService $logService */
        $logService = $this->getService('Log');

        $resource = HM_ControllerAcl::getResourceName($moduleName, $request->getControllerName(), $request->getActionName());

        // Решение проблемы в ACL со действием другого регистра
        $resource = strtolower($resource);

        if ($acl->has($resource)) {

            $userRole = $userService->getCurrentUserRole();

            if (!$acl->isAllowed($userRole, $resource)) {

                $view = $this->getView();

                if ($switch == 1) {
                    header('Location: '.$view->serverUrl('/'));
                    exit();
                }

                $logService->log(
                    $currentUserId,
                    'Unauthorized access',
                    'Fail',
                    Zend_Log::WARN,
                    get_class($this)
                );

                if (($userRole === Roles::ROLE_GUEST) && !$isAjaxRequest) {
                    $session->autoredirect['url'] = $_SERVER['REQUEST_URI'];
                    header('Location: '.$view->serverUrl('/'));
                    exit();
                }

                $request->setModuleName('default');
                $request->setControllerName('index');
                $request->setActionName('index');

                throw new HM_Permission_Exception(_('Не хватает прав доступа.'));

            }
        }

        $logService->log(
            $currentUserId,
            'Access Granted',
            'Success',
            Zend_Log::NOTICE,
            $_SERVER['REQUEST_URI']
        );
    }
}