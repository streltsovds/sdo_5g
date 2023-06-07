<?php
class HM_Acl_Vacancy extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'vacancy', 'list', 'create-from-structure');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'application', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'vacancy', 'report', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);
        // не просто разрешить всем пользователям, а убедиться что он именно руководитель данного vacancy'а
        $this->allowForVacancy($acl, HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);        
        
        $resource = sprintf('mca:%s:%s:%s', 'vacancy', 'index', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'vacancy', 'index', 'programm');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'vacancy', 'list', 'create-from-application');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);

        // Просмотр вакантных должностей
        $resource = sprintf('mca:%s:%s:%s', 'vacancy', 'vacancy', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);
    }
}
?>