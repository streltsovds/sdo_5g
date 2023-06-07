<?php
class HM_Acl_User
{
    public function __construct (Zend_Acl $acl)
    {
        foreach ([
                     sprintf('mca:%s:%s:%s', 'user', 'list', 'view'),
                     sprintf('mca:%s:%s:%s', 'user', 'edit', 'card'),
                     sprintf('mca:%s:%s:%s', 'user', 'report', 'index')
                 ] as $resource) {

            $this->denyForGuest($acl, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);
        $acl->deny(HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'set-comment');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'block');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'unblock');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->deny(HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'generate');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'duplicate-merge');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'login-as');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        if (Zend_Registry::get('serviceContainer')->getService('Deputy')->whoseDeputyIam() !== null) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        }

        $resource = HM_Acl::RESOURCE_USER_CONTROL_PANEL;
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource, HM_Acl::PRIVILEGE_VIEW);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource, HM_Acl::PRIVILEGE_EDIT);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource, HM_Acl::PRIVILEGE_VIEW);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource, HM_Acl::PRIVILEGE_VIEW);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource, HM_Acl::PRIVILEGE_VIEW);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource, HM_Acl::PRIVILEGE_VIEW);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource, HM_Acl::PRIVILEGE_VIEW);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource, HM_Acl::PRIVILEGE_VIEW);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource, HM_Acl::PRIVILEGE_VIEW);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource, HM_Acl::PRIVILEGE_VIEW);
        if (
            Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId()
            && Zend_Registry::get('serviceContainer')->getService('User')->isManager()
        ) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource, HM_Acl::PRIVILEGE_VIEW);
        }


        $resource = sprintf('mca:%s:%s:%s', 'user', 'dean', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'teacher', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'student', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'import', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'import', 'process');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'index', 'sessions');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);

        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'index', 'study-history');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);


//        newStudyHistory
        $resource = sprintf('mca:%s:%s:%s', 'user', 'index', 'new-study-history');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);

    }

    /**
     * @param Zend_Acl $acl
     * @param string $resource
     * @throws Zend_Acl_Exception
     */
    private function denyForGuest(Zend_Acl $acl, string $resource): void
    {
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource);
        $acl->deny(HM_Role_Abstract_RoleModel::ROLE_GUEST, $resource);
    }
}