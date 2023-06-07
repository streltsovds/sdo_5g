<?php
class HM_Responsibility_ResponsibilityModel extends HM_Model_Abstract
{
    const TYPE_STRUCTURE  = 1;
    const TYPE_SUBJECT    = 2;
    const TYPE_PROGRAMM   = 3;
    const TYPE_GROUP      = 4;
    const TYPE_SUBJECT_OT = 5;
    
    const DEFAULT_ACCESS_NONE = 0;
    const DEFAULT_ACCESS_ALL = 1;

    public static function getResponsibilityRoles()
    {
        return array(
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        );
    }

    public static function getMarksheetResponsibilityRoles()
    {
        return array(
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_TEACHER
        );
    }

    public static function getResponsibilityDefaultAccess($role = false)
    {
        if (!$role) {
            $role = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole();
        }
        $access = array(
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR => self::DEFAULT_ACCESS_NONE,
            HM_Role_Abstract_RoleModel::ROLE_DEAN => self::DEFAULT_ACCESS_ALL
        );
        return isset($access[$role]) ? $access[$role] : self::DEFAULT_ACCESS_ALL;
    }
}
