<?php
class HM_Acl_Demo extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {
        if (APPLICATION_ENV != 'development') {

            $resource = sprintf('mca:%s:%s:%s', 'demo', 'vue', 'betterScroll');
            $acl->addResource(new Zend_Acl_Resource($resource));
            $acl->deny(null, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

            $resource = sprintf('mca:%s:%s:%s', 'demo', 'vue', 'data');
            $acl->addResource(new Zend_Acl_Resource($resource));
            $acl->deny(null, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

            $resource = sprintf('mca:%s:%s:%s', 'demo', 'vue', 'fonts');
            $acl->addResource(new Zend_Acl_Resource($resource));
            $acl->deny(null, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

            $resource = sprintf('mca:%s:%s:%s', 'demo', 'vue', 'forms');
            $acl->addResource(new Zend_Acl_Resource($resource));
            $acl->deny(null, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

            $resource = sprintf('mca:%s:%s:%s', 'demo', 'vue', 'icons');
            $acl->addResource(new Zend_Acl_Resource($resource));
            $acl->deny(null, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

            $resource = sprintf('mca:%s:%s:%s', 'demo', 'vue', 'imageMultiSelect');
            $acl->addResource(new Zend_Acl_Resource($resource));
            $acl->deny(null, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

            $resource = sprintf('mca:%s:%s:%s', 'demo', 'vue', 'modal');
            $acl->addResource(new Zend_Acl_Resource($resource));
            $acl->deny(null, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);


            $resource = sprintf('mca:%s:%s:%s', 'demo', 'vue', 'pdf');
            $acl->addResource(new Zend_Acl_Resource($resource));
            $acl->deny(null, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

            $resource = sprintf('mca:%s:%s:%s', 'demo', 'vue', 'translate');
            $acl->addResource(new Zend_Acl_Resource($resource));
            $acl->deny(null, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

            $resource = sprintf('mca:%s:%s:%s', 'demo', 'vue', 'ts');
            $acl->addResource(new Zend_Acl_Resource($resource));
            $acl->deny(null, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);
        }
    }
}
