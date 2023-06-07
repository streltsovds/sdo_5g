<?php
class HM_Acl_Feedback extends HM_Acl
{
    public function __construct(Zend_Acl $acl)
    {
        foreach (['list', 'new', 'edit', 'delete'] as $action) {

            $resource = sprintf('mca:%s:%s:%s', 'subject', 'feedback', $action);
            if (!$acl->has($resource)) {
                $acl->addResource(new Zend_Acl_Resource($resource));
            }

            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        }
    }
}