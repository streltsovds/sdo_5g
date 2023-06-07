<?php
class HM_Acl_Section extends HM_Acl
{
    public function __construct(Zend_Acl $acl)
    {
        foreach (['create', 'delete'] as $action) {

            $resource = sprintf('mca:%s:%s:%s', 'subject', 'section', $action);
            if (!$acl->has($resource)) {
                $acl->addResource(new Zend_Acl_Resource($resource));
            }

            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL, $resource);
        }
    }
}
