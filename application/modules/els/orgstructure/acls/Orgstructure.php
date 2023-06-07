<?php
class HM_Acl_Orgstructure extends HM_Acl
{

    public function __construct(Zend_Acl $acl)
    {

        // $acl->addRole(new Zend_Acl_Role('test'));
        //ROLE_DEAN
        //ROLE_MANAGER

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'supervisor');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'admin');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'classifier');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        //$acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'archive');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'edit-respondents');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'get-tree-branch');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'card');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'import', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'check');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'import', 'process');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'login-as');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL, $resource);
        //$acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource); // некоторым заказчикам это надо разрешать
        
        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'assign-profile');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'unassign-profile');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'session', 'list', 'create-from-structure');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'vacancy', 'list', 'create-from-structure');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'newcomer', 'list', 'create-from-structure');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'rotation', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'profile', 'report', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'vacancy', 'report', 'card');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'newcomer', 'report', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
    }
}