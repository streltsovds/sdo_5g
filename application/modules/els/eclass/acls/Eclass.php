<?php
class HM_Acl_Eclass extends HM_Acl
{

    public function __construct(Zend_Acl $acl)
    {

        // Вебинар
        $resource = sprintf('mca:%s:%s:%s', 'eclass', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);

        // Просмотр видеозаписей
        $resource = sprintf('mca:%s:%s:%s', 'eclass', 'video', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
    }
}