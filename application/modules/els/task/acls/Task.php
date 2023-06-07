<?php
class HM_Acl_Task extends HM_Acl
{

    public function __construct(Zend_Acl $acl)
    {

        $resource = sprintf('mca:%s:%s:%s', 'task', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            
        } else {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'task', 'list', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'task', 'list', 'unassign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        }

        // ----------------------------------------------------------------

        $resource = sprintf('mca:%s:%s:%s', 'task', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
            
        } else {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'task', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
            
        } else {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'task', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
            
        } else {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'task', 'list', 'delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            
        } else {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'task', 'list', 'publish');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);

        /* TASK VARIANTS */

        $resource = sprintf('mca:%s:%s:%s', 'task', 'variant', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);

        if (!$this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'task', 'variant', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));

        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);

        if (!$this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'task', 'index', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));

        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'task', 'variant', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));

        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);

        if (!$this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        }
    }
}