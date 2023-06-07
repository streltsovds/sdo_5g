<?php
class HM_Acl_Marksheet
{
    public function __construct (Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
		$acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);		
		
        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'set-score');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);		

        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'set-comment');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);				

        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'graduate-students');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);				

        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'graduate-students-grid');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'clear-schedule');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);				

        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'word');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);				

        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'excel');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);				

        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'print');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);				
    }
}