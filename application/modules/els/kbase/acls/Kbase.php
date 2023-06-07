<?php
class HM_Acl_Kbase extends HM_Acl
{
    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'kbase', 'course', 'import');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource); // всем
        $acl->deny(HM_Role_Abstract_RoleModel::ROLE_GUEST, $resource);
        if(!$this->isSubjectContext())
            $acl->deny(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'kbase', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource); // всем
        $acl->deny(HM_Role_Abstract_RoleModel::ROLE_GUEST, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'kbase', 'resources', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->deny(HM_Role_Abstract_RoleModel::ROLE_GUEST, $resource);
        $acl->deny(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'kbase', 'resource', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource); // всем
        $acl->deny(HM_Role_Abstract_RoleModel::ROLE_GUEST, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'kbase', 'course', 'edit-card');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource); // всем
        $acl->deny(HM_Role_Abstract_RoleModel::ROLE_GUEST, $resource);
        $acl->deny(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);


        $acl->addModuleResources('course')
            ->addModuleResources('poll')
            ->addModuleResources('blog')
            ->addModuleResources('resource')
            ->addModuleResources('task')
            ->addModuleResources('test')
            ->addModuleResources('question')
            ->addModuleResources('exercises');
    }

}