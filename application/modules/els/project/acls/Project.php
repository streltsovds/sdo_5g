<?php
class HM_Acl_Project extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {
        $this->_indexAction($acl);
        $this->_listAction($acl);
    }

    private function _indexAction(Zend_Acl $acl)
    {
        // Просмотре курса
        $resource = sprintf('mca:%s:%s:%s', 'project', 'index', 'index');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);

        // Просмотр курсов
        $resource = sprintf('mca:%s:%s:%s', 'project', 'index', 'courses');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
        //$acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);

        // Просмотр смена режима прохождения
        $resource = sprintf('mca:%s:%s:%s', 'project', 'index', 'changemode');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);

        // Cмена состояния курса
        $resource = sprintf('mca:%s:%s:%s', 'project', 'index', 'change-state');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);

        // Просмотр смена режима прохождения
        $resource = sprintf('mca:%s:%s:%s', 'project', 'index', 'edit-services');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
        //$acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
    }

    private function _listAction(Zend_Acl $acl)
    {
        // Просмотре курсов
        $resource = sprintf('mca:%s:%s:%s', 'project', 'list', 'index');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
        //$acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);


        // Просмотр карточки
        $resource = sprintf('mca:%s:%s:%s', 'project', 'list', 'view');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
        //$acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);

        // Редактирование курса
        $resource = sprintf('mca:%s:%s:%s', 'project', 'list', 'edit');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
        //$acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);

        // Создание курса
        $resource = sprintf('mca:%s:%s:%s', 'project', 'list', 'new');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);

        // Удаление курса
        $resource = sprintf('mca:%s:%s:%s', 'project', 'list', 'delete');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'project', 'list', 'delete-by');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);


/*
        // Импорт эл.курса
        $resource = sprintf('mca:%s:%s:%s', 'course', 'import', 'project');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        */

        $resource = sprintf('mca:%s:%s:%s', 'course', 'import', 'project');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isProjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        } else {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('privileges:%s', 'gridswitcher');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isProjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            $acl->deny(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'project', 'index', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isProjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            $acl->deny(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        }


        $resource = sprintf('mca:%s:%s:%s', 'project', 'list', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'project', 'index', 'unassign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isProjectContext()) {
            $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
            $acl->deny(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        }


        // Редактирование курса
        $resource = sprintf('mca:%s:%s:%s', 'course', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);


        // Удаление курса
        $resource = sprintf('mca:%s:%s:%s', 'project', 'index', 'course-delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'project', 'index', 'course-delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);

        //Calendar
        $resource = sprintf('mca:%s:%s:%s', 'project', 'list', 'calendar');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
    }
}