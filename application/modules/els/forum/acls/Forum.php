<?php
class HM_Acl_Forum extends HM_Acl
{

    public function __construct(Zend_Acl $acl)
    {
        // Создание/изменение/удаление Раздела форума
        $resource = sprintf('mca:%s:%s:%s', 'forum', 'sections', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'forum', 'sections', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'forum', 'sections', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
    }
}