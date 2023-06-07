<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/30/18
 * Time: 3:31 PM
 */

class HM_View_Sidebar_Session extends HM_View_Sidebar_Abstract
{
    public function getIcon()
    {
        return 'session'; // @todo
    }

    public function getTitle()
    {
        return 'Оценочная сессия';
    }

    public function getContent()
    {
        $userService = Zend_Registry::get('serviceContainer')->getService('User');
        $sessionUserService = Zend_Registry::get('serviceContainer')->getService('AtSessionUser');

        $isSessionUser = $sessionUserService->isSessionUser(
            $this->_options['model']->session_id,
            $userService->getCurrentUserId()
        );

        $data = [
            'isSessionUser' => $isSessionUser,
            'model' => $this->getModel(),
        ];

        if ($userService->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_ENDUSER) {
            return $this->view->partial('session-enduser.tpl', $data);
        } else {
            if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole($userService->getCurrentUserRole(), array(
                HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
            ))) {
                return $this->view->partial('session-supervisor.tpl', $data);
            }
            return $this->view->partial('session-manager.tpl', $data);
        }
    }
}