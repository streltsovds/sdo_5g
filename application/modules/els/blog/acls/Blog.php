<?php
class HM_Acl_Blog extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'blog', 'index', 'view');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource); // разрешить всем, кто прошел проверку isActivityUser()? т.е. точно имеет отношение к данному курсу (dean|teacher|student|graduated)
		
		$resource = sprintf('mca:%s:%s:%s', 'blog', 'index', 'comment');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->deny(null, $resource); 
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN,$resource);

		$resource = sprintf('mca:%s:%s:%s', 'blog', 'index', 'index-grid');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->deny(null, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ADMIN,$resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN,$resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER,$resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,$resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR,$resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR,$resource);


//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR,$resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN,$resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR,$resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER,$resource);
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR,$resource);
        
        // не просто разрешить всем студентам, а убедиться что он именно студент на этом курсе
        $this->allowForSubject($acl, HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        $this->allowForProject($acl, HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
    }
}