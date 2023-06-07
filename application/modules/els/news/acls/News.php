<?php
class HM_Acl_News extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'news', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource); // всем

        $resource = sprintf('mca:%s:%s:%s', 'news', 'index', 'grid');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->deny(null, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN, $resource);

        //$acl->deny(HM_Role_Abstract_RoleModel::ROLE_GUEST,$resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'news', 'index', 'view');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource); // всем
        //$acl->deny(HM_Role_Abstract_RoleModel::ROLE_GUEST,$resource);
        
		$resource = sprintf('mca:%s:%s:%s', 'news', 'index', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->deny(null, $resource); 
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN,$resource);

        $resource = sprintf('mca:%s:%s:%s', 'news', 'index', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->deny(null, $resource); 
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN,$resource);
    }
}