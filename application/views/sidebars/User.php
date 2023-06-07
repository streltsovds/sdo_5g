<?php
class HM_View_Sidebar_User extends HM_View_Sidebar_Abstract
{
    public function getIcon()
    {
        return 'user';
    }

    public function getTitle()
    {
        return _('Пользователь');
    }

    public function getContent()
    {
        $model = $this->getModel();
        $viewUserId = $model->MID;
        $serviceContainer = Zend_Registry::get('serviceContainer');
        /** @var HM_User_UserService $userService */
        $userService = $serviceContainer->getService('User');
        /** @var HM_Acl $acl */
        $acl = $serviceContainer->getService('Acl');

        $currentUserId = $userService->getCurrentUserId();
        $currentUserRole = $userService->getCurrentUserRole();
        $currentUserIsAdmin = $acl->inheritsRole($currentUserRole, HM_Role_Abstract_RoleModel::ROLE_ADMIN);
        $showEditUrl = $acl->inheritsRole($currentUserRole, [HM_Role_Abstract_RoleModel::ROLE_ADMIN, HM_Role_Abstract_RoleModel::ROLE_DEAN]);

        $viewUserRoles = $userService->getUserRoles($viewUserId);
        $viewUserHasResponsibilityRoles = array_intersect($viewUserRoles, HM_Responsibility_ResponsibilityModel::getResponsibilityRoles());
        $viewUserIsCurrent = ($viewUserId == $currentUserId);

        $data = [
            'currentUserId' => $currentUserId,
            'sendMessageUrl' => $this->view->url(['module' => 'message', 'controller' => 'send', 'action' => 'simple']),
            'showLoginAs' => ($currentUserIsAdmin && !$viewUserIsCurrent),
            'loginAsUrl' => $this->view->url([
                'baseUrl' => '',
                'module' => 'user',
                'controller' => 'list',
                'action' => 'login-as',
                'user_id' => $model->MID,
            ]),
            'showEditResponsibility' => ($currentUserIsAdmin && $viewUserHasResponsibilityRoles),
            'editResponsibilityUrl' => $this->view->url([
                'baseUrl' => '',
                'module' => 'user',
                'controller' => 'responsibility',
                'action' => 'assign',
                'user_id' => $model->MID,
            ]),
            'userId' => $model->MID,
            'uploadPhotoUrl' => $this->view->url([
                'baseUrl' => '',
                'module' => 'user',
                'controller' => 'index',
                'action' => 'upload-photo',
                'user_id' => $model->MID,
            ]),
            'switchUrl' => $this->view->url([
                'baseUrl' => '',
                'module' => 'default',
                'controller' => 'index',
                'action' => 'switch',
            ]),
            'showEditUrl' => $showEditUrl,
            'editCardUrl' => $this->view->url([
                'baseUrl' => '',
                'module' => 'user',
                'controller' => 'edit',
                'action' => 'index',
                'user_id' => $model->MID,
            ]),
            'userName' => $model->getNameCyr(),
            'userImage' => $this->view->baseUrl($model->getPhoto()),
            'myRecord' => ($model->MID === $currentUserId),
        ];

        return $this->view->partial('user.tpl', ['data' => HM_Json::encodeErrorSkip($data)]);
    }
}
