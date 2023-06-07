<?php
class HM_Acl_Materials extends HM_Acl
{
    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'project', 'materials', 'edit');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        //$acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'project', 'materials', 'delete-section');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        //$acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'project', 'materials', 'edit-section');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        //$acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'project', 'materials', 'order-section');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        //$acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
    }
}