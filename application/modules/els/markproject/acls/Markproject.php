<?php
class HM_Acl_Markproject
{
    public function __construct (Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'markproject', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'markproject', 'index', 'set-score');
        $acl->addResource(new Zend_Acl_Resource($resource));
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'markproject', 'index', 'set-comment');
        $acl->addResource(new Zend_Acl_Resource($resource));
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'markproject', 'index', 'graduate-participants');
        $acl->addResource(new Zend_Acl_Resource($resource));
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'markproject', 'index', 'graduate-participants-grid');
        $acl->addResource(new Zend_Acl_Resource($resource));
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'markproject', 'index', 'clear-schedule');
        $acl->addResource(new Zend_Acl_Resource($resource));
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'markproject', 'index', 'word');
        $acl->addResource(new Zend_Acl_Resource($resource));
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'markproject', 'index', 'excel');
        $acl->addResource(new Zend_Acl_Resource($resource));
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'markproject', 'index', 'print');
        $acl->addResource(new Zend_Acl_Resource($resource));
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
    }
}