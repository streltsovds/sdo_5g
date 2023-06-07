<?php
class HM_Acl_Report extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'report', 'planned-actual-costs', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
    }
}
?>