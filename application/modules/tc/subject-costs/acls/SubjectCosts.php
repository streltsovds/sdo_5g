<?php
class HM_Acl_SubjectCosts extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {
        $this->actualCosts($acl);
    }
    
    public function actualCosts($acl) {
        $resource = sprintf('mca:%s:%s:%s', 'subject-costs', 'actual-costs', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'subject-costs', 'actual-costs', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'subject-costs', 'actual-costs', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'subject-costs', 'actual-costs', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'subject-costs', 'actual-costs', 'delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
    }
}
?>