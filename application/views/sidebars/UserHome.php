<?php
class HM_View_Sidebar_UserHome extends HM_View_Sidebar_Abstract
{
    public function getIcon()
    {
        return 'user';
    }

    public function getContent()
    {
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        /** @var HM_User_UserModel $currentUser */
        $currentUser = $userService->getCurrentUser();
        $userRoles = [];
        $defaultSession = new Zend_Session_Namespace('default');
        $restoreUser = $defaultSession->userRestore;

        foreach ($currentUser->roles as $role) {
            $userRoles[$role] = HM_Role_Abstract_RoleModel::getRoleTitle($role);
        }

        $data = [
            'roles' => $userRoles,
            'currentRole' => $currentUser->role,
            'basicRoles' => HM_Role_Abstract_RoleModel::getBasicRoles(),
            'userId' => $currentUser->MID,
            'uploadPhotoUrl' => $this->view->url([
                'baseUrl' => '',
                'module' => 'user',
                'controller' => 'index',
                'action' => 'upload-photo',
                'user_id' => $currentUser->MID,
            ]),
            'switchUrl' => $this->view->url([
                'baseUrl' => '',
                'module' => 'default',
                'controller' => 'index',
                'action' => 'switch',
            ]),
            'editCardUrl' => $this->view->url([
                'baseUrl' => '',
                'module' => 'user',
                'controller' => 'edit',
                'action' => 'card',
                'user_id' => $currentUser->MID,
            ]),
            'isRestore' => !empty($restoreUser),
            'restoreUserFullName' => !empty($restoreUser) ? implode(' ', [$restoreUser->FirstName, $restoreUser->Patronymic]) : '',
            'currentUser' => $currentUser,
            'userName' => $currentUser->getNameCyr(),
            'userImage' => $this->view->baseUrl($currentUser->getPhoto()),
            'myRecord' => true,
        ];

        $defaultSession = new Zend_Session_Namespace('default');

        if (isset($defaultSession->userRestore)) {
            $data['restoreUser'] = $defaultSession->userRestore;
        }

        if ($currentUser) {
            return $this->view->partial('userhome.tpl', ['data' => HM_Json::encodeErrorSkip($data)]);
        }

        return '';
    }

    public function getTitle()
    {
        return _('Профиль');
    }

    public function getToggle()
    {
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        $user = $userService->getCurrentUser();
        $userPhoto = $user->getPhoto();
        $sidebarName = $this->getName();
//        if ($userPhoto) {
//            return '<hm-sidebar-toggle has-avatar sidebar-name="'.$sidebarName.'"><v-avatar size="36"><v-img :aspect-ratio="36/36" height="36" width="36" src="/'.$userPhoto.'"></v-img></v-avatar></hm-sidebar-toggle>';
//        } else {
//            return '<hm-sidebar-toggle sidebar-name="'.$sidebarName.'">'.$this->getIcon().'</hm-sidebar-toggle>';
//        }

        HM_Role_Abstract_RoleModel::getRoleTitle($userService->getCurrentUserRole());

        $availableRolesLinks = [];

        $availableRoles = $user->roles;

        // enduser - в конец
        array_push($availableRoles, array_shift($availableRoles));

        foreach ($availableRoles as $role) {
            if ( $role != $user->role
                && in_array($role, array_keys(HM_Role_Abstract_RoleModel::getBasicRoles()))
            ) {
                $roleName = HM_Role_Abstract_RoleModel::getRoleTitle($role);
                $availableRolesLinks[$roleName] = $this->view->url(array(
                    'baseUrl' => '',
                    'module' => 'default',
                    'controller' => 'index',
                    'action' => 'switch',
                    'role' => $role,
                ));
            }
        }

        $userStore = '';

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        $default = new Zend_Session_Namespace('default');
        if (isset($default->userRestore)) {
            $userStore = $default->userRestore->LastName . ' ' . $default->userRestore->FirstName;
        }

        $this->view->roleSwitcher = [
            'userName' => $user->LastName . ' ' . $user->FirstName,
            'userNameStore' => $userStore,
            'profileLink' => $this->view->url(array(
                'baseUrl' => '',
                'module' => 'user',
                'controller' => 'edit',
                'action' => 'card',
                'user_id' => $user->MID,
            )),
            'currentRoleName' => HM_Role_Abstract_RoleModel::getRoleTitle($user->role),
            'availableRolesLinks' => $availableRolesLinks,
            'avatar' => $userPhoto ? $userPhoto : null,
        ];
        // TODO userPhoto support!
        return
            '<hm-role-switcher
                :user-store="view.roleSwitcher.userNameStore"              
                :user-name="view.roleSwitcher.userName"
                :profile-link="view.roleSwitcher.profileLink"
                :current-role-name="view.roleSwitcher.currentRoleName"
                :available-roles-links="view.roleSwitcher.availableRolesLinks"
                :avatar="view.roleSwitcher.avatar"
            ></hm-role-switcher>';

        // '<hm-sidebar-toggle sidebar-name="'.$sidebarName.'">'.$this->getIcon().'</hm-sidebar-toggle>';
    }
}