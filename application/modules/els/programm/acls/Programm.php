<?php
class HM_Acl_Programm extends HM_Acl
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'programm', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'programm', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'programm', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'programm', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'programm', 'list', 'delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'programm', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'programm', 'index', 'unassign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

    }

}