<?php

class HM_Acl_Report
{

    public function __construct(Zend_Acl $acl)
    {
        foreach ([
                     sprintf('mca:%s:%s:%s', 'report', 'index', 'view'),
                 ] as $resource) {
            $this->denyForGuest($acl, $resource);
        }

        foreach ($resources = [
            sprintf('mca:%s:%s:%s', 'report', 'list', 'edit'),
            sprintf('mca:%s:%s:%s', 'report', 'list', 'delete'),
            sprintf('mca:%s:%s:%s', 'report', 'generator', 'construct'),
            sprintf('mca:%s:%s:%s', 'report', 'generator', 'grid'),
            sprintf('mca:%s:%s:%s', 'report', 'generator', 'save')
        ] as $resource) {

            if (!$acl->has($resource))
                $acl->addResource(new Zend_Acl_Resource($resource));
        }

        // Оказывается, в allow/deny можно передавать [$roles] и [$resources]
        $acl->allow([
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_HR
        ], $resources);

        $resource = sprintf('mca:%s:%s:%s', 'report', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'report', 'list', 'tree');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);

        // report/index/view
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $reportId = (int) $request->getParam('report_id', 0);

        $services = Zend_Registry::get('serviceContainer');

        /** @var HM_Collection $reportRolesRaw */
        $reportRolesRaw = $services->getService('ReportRole')->fetchAll(['report_id = ?' => $reportId]);

        if ($reportRolesRaw->count()) {
            $reportRoles = $reportRolesRaw->asArrayOfArrays();

            $roles = array_column($reportRoles, 'role');
            $resource = sprintf('mca:%s:%s:%s', 'report', 'index', 'view');

            if(!$acl->has($resource))
                $acl->addResource(new Zend_Acl_Resource($resource));

            $acl->allow($roles, $resource);
            // Запрещаем всем, кроме allow
            $acl->deny(null, $resource);
        }
    }

    private function denyForGuest(Zend_Acl $acl, string $resource): void
    {
        if (!$acl->has($resource))
            $acl->addResource(new Zend_Acl_Resource($resource));

        $acl->allow(null, $resource);
        $acl->deny(HM_Role_Abstract_RoleModel::ROLE_GUEST, $resource);
    }

}