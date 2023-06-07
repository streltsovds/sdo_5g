<?php

class HM_Role_Abstract_RoleModel extends HM_Model_Abstract
{
    const ROLE_GUEST              = 'guest';
    const ROLE_ENDUSER            = 'enduser';
    const ROLE_SUPERVISOR         = 'supervisor';
    const ROLE_DEVELOPER          = 'developer';
    const ROLE_MANAGER            = 'manager';
    const ROLE_ADMIN              = 'admin';
    const ROLE_SIMPLE_ADMIN       = 'simple_admin';
    const ROLE_ATMANAGER          = 'atmanager';
    const ROLE_ATMANAGER_LOCAL    = 'atmanager_local';
    const ROLE_DEAN               = 'dean';
    const ROLE_DEAN_LOCAL         = 'dean_local';
    const ROLE_HR                 = 'hr';
    const ROLE_HR_LOCAL           = 'hr_local';
    const ROLE_LABOR_SAFETY       = 'labor_safety';
    const ROLE_LABOR_SAFETY_LOCAL = 'labor_safety_local';
    const ROLE_CURATOR            = 'curator';
    const ROLE_MODERATOR          = 'moderator';
    const ROLE_TEACHER            = 'teacher';

    /** @deprecated */
    const ROLE_USER               = 'user';
    /** @deprecated */
    const ROLE_STUDENT            = 'student';
    /** @deprecated */
    const ROLE_EMPLOYEE           = 'employee';
    /** @deprecated */
    const ROLE_CHIEF              = 'chief';
    /** @deprecated */
    const ROLE_PARTICIPANT = 'participant';

    const ROLE_GROUP_ALL = 'all';

    /**
     * Возвращает самую главную роль из переданного массива
     *
     * @param array $roles
     * @return string
     */
    static public function getMaxRole($roles)
    {
        global $profiles_basic_ids;

        $result = self::ROLE_ENDUSER;
        $max = 0;

        foreach ($roles as $role) {

            $roleValue = isset($profiles_basic_ids[$role]) ? (float) $profiles_basic_ids[$role] : 0;

            if ($roleValue > $max) {
                $max = $roleValue;
                $result = $role;
            }
        }

        return $result;
    }

    static public function getBasicRoles($all = true, $withRoleUnion = false)
    {
        if ($all == true) {
            $roles = array(
                self::ROLE_GUEST     => _('Гость'),
                self::ROLE_ENDUSER      => _('Пользователь'),
                self::ROLE_SUPERVISOR => _('Супервайзер'),
                self::ROLE_PARTICIPANT   => _('Участник конкурсов'),
                self::ROLE_STUDENT   => _('Слушатель'),
                self::ROLE_TEACHER   => _('Тьютор'),
                self::ROLE_DEAN      => _('Менеджер по обучению'),
                self::ROLE_DEAN_LOCAL => _('Специалист по обучению'),
                self::ROLE_MODERATOR   => _('Модератор конкурсов'),
                self::ROLE_CURATOR   => _('Менеджер конкурсов'),
                self::ROLE_LABOR_SAFETY   => _('Менеджер по охране труда'),
                self::ROLE_LABOR_SAFETY_LOCAL   => _('Специалист по охране труда'),
//                self::ROLE_DEVELOPER => _('Разработчик ресурсов'),
                self::ROLE_ATMANAGER => _('Менеджер по оценке'),
                self::ROLE_ATMANAGER_LOCAL  => _('Специалист по оценке'),
//                self::ROLE_MANAGER   => _('Менеджер базы знаний'),
                self::ROLE_HR => _('Менеджер по персоналу'),
                self::ROLE_HR_LOCAL => _('Специалист по персоналу'),
                self::ROLE_ADMIN     => _('Администратор'),
//                self::ROLE_SIMPLE_ADMIN     => _('Администратор (ограниченные права)'),
            );
        } else {
            $roles = array(
                self::ROLE_ENDUSER => _('Пользователь'),
                self::ROLE_SUPERVISOR => _('Супервайзер'),
                self::ROLE_PARTICIPANT   => _('Участник конкурсов'),
                self::ROLE_STUDENT   => _('Слушатель'),
                self::ROLE_TEACHER   => _('Тьютор'),
                self::ROLE_DEAN => _('Менеджер по обучению'),
                self::ROLE_CURATOR   => _('Менеджер конкурсов'),
                self::ROLE_LABOR_SAFETY   => _('Менеджер по охране труда'),
//                self::ROLE_DEVELOPER => _('Разработчик ресурсов'),
//                self::ROLE_MANAGER   => _('Менеджер базы знаний'),
                self::ROLE_ATMANAGER => _('Менеджер по оценке'),
                self::ROLE_HR => _('Менеджер по персоналу'),
                self::ROLE_ADMIN     => _('Администратор'),
//                self::ROLE_SIMPLE_ADMIN     => _('Администратор (ограниченные права)'),
            );
        }

        if ($withRoleUnion == true) {
            unset($roles[self::ROLE_USER], $roles[self::ROLE_CHIEF], $roles[self::ROLE_STUDENT], $roles[self::ROLE_PARTICIPANT]);
            $guest = $all ? array(self::ROLE_GUEST => _('Гость')) : array();
            $enduser = array(self::ROLE_ENDUSER => _('Пользователь'));
            $roles = $guest + $enduser + $roles;
        }


        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_BASIC_ROLES);
        Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->filter($event, $roles);
        $roles = $event->getReturnValue();

        return $roles;
    }

    static public function getRoleTitle($role)
    {
        $roles = self::getBasicRoles(true, true);
        return isset($roles[$role]) ? $roles[$role] : '';
    }

    static public function getParentRole($role)
    {
        switch ($role) {
            case HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL:
                return HM_Role_Abstract_RoleModel::ROLE_ATMANAGER;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL:
                return HM_Role_Abstract_RoleModel::ROLE_HR;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL:
                return HM_Role_Abstract_RoleModel::ROLE_DEAN;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL:
                return HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY;
                break;
            case HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN:
                return HM_Role_Abstract_RoleModel::ROLE_ADMIN;
                break;
        }

        return $role;
    }
}
