<?php
class HM_Acl_Notice
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'notice', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'notice', 'index', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
    }
}