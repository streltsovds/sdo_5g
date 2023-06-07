<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 6/21/19
 * Time: 3:25 PM
 */

class HM_Acl_Chat extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {
        parent::__construct();

        $resource = sprintf('mca:%s:%s:%s', 'news', 'index', 'index-grid');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->deny(null, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
    }
}