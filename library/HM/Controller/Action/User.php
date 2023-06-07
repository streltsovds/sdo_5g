<?php
class HM_Controller_Action_User extends HM_Controller_Action implements HM_Controller_Action_Interface_Context
{
    use HM_Controller_Action_Trait_Context;
    use HM_Controller_Action_Trait_Grid;

    protected $_userId = null; // ???
    protected $_user = null;

    public function init()
    {
        $this->_userId = $userId = $this->_getParam('user_id', 0);
        $this->_user = $this->getOne($this->getService('User')->findDependence('Candidate', $userId));

        $currentUser = $this->getService('User')->getCurrentUser();
        $isEnduser = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER);
        $isSelf = $currentUser && ($currentUser->MID == $this->_userId);

        if ($this->_user) {

            $this->view->setHeader($this->_user->getName());

            if (!empty(Zend_Registry::get('session_namespace_default')->userCard['returnUrl'])) {
                $this->view->setBackUrl(Zend_Registry::get('session_namespace_default')->userCard['returnUrl']);
            }

            if ($isSelf || !$isEnduser) {
                $this->initContext($this->_user);
            }

            if (!$isSelf){
                $this->view->addSidebar('user', [
                    'model' => $this->_user,
                ]);
            }
        }

        parent::init();
    }

    public function getContextNavigationModifiers()
    {
        // юзер, которого смотрим
        $viewUserId = $this->_userId;

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');

        // юзер, кто смотрит
        $currentUserId      = $userService->getCurrentUserId();
        $currentUserRole    = $userService->getCurrentUserRole();
        $currentUserIsAdmin = $acl->inheritsRole($currentUserRole, HM_Role_Abstract_RoleModel::ROLE_ADMIN);

        $currentUserNoAnyPermissions = $acl->inheritsRole($currentUserRole, array(
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
            HM_Role_Abstract_RoleModel::ROLE_DEVELOPER,
            HM_Role_Abstract_RoleModel::ROLE_CURATOR,
            HM_Role_Abstract_RoleModel::ROLE_GUEST,
            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
            HM_Role_Abstract_RoleModel::ROLE_MODERATOR,
            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
        ));

        if ($currentUserNoAnyPermissions && ($viewUserId != $currentUserId)) {
            return array(new HM_Navigation_Modifier_Remove_Navigation());
        }

        $resources = array();
        $modifiers = array();

        if ($viewUserId == $currentUserId) {
            // для текущего пользователя скрываем пункты:
            $resources[] = 'cm:user:page2_1'; // Войти от имени пользователя
        } else {

            $resources[] = 'cm:user:page8'; // Настройка ленты активности

            if (!$currentUserIsAdmin) {
                // если пользователь не админ и смотрит не свою карточку
                $resources[] = 'cm:user:page1'; // Группа с пунктом "Редактирование учетной записи"
            }
        }

        if ($currentUserIsAdmin) {

            $viewUserRoles = $userService->getUserRoles($viewUserId);

            if (!array_intersect($viewUserRoles, HM_Responsibility_ResponsibilityModel::getResponsibilityRoles())) {
                $resources[] = 'cm:user:page5_1'; // Назначение области ответственности
            }
        }

        foreach ($resources as $resource) {
            $modifiers[] = new HM_Navigation_Modifier_Remove_Page('resource', $resource);
        }

        return $modifiers;
    }
}