<?php
class HM_Acl_Question extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'question', 'list', 'test');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'question', 'list', 'exercise');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'question', 'list', 'quiz');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'question', 'list', 'task');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);

        // создание вопроса в опросе
        $resource = sprintf('mca:%s:%s:%s', 'question', 'list', 'new-quiz');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            
        } else {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        }

        // создание варианта задания
        $resource = sprintf('mca:%s:%s:%s', 'question', 'list', 'new-task');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            
        } else {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'question', 'import', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            
        } else {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        }

        // ----------------------------------------------------------------
/*@D
        // создание вопроса в тесте
        $resource = sprintf('mca:%s:%s:%s', 'question', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            
        } else {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'question', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            
        } else {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        }
*/
        $resource = sprintf('mca:%s:%s:%s', 'question', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            
        } else {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'question', 'list', 'delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            
        } else {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'question', 'list', 'assign-to-test');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            
        } else {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        }


        $resource = sprintf('privileges:%s', 'gridswitcher');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            
        } else {
            //$acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            //$acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        }

    }
}