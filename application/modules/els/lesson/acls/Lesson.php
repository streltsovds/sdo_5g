<?php
class HM_Acl_Lesson
{

    public function __construct(Zend_Acl $acl)
    {
        // Просмотр списка занятий
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'index');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        //$acl->allow(HM_Role_Abstract_RoleModel::ROLE_METODIST, $resource);

        // Просмотр списка занятий
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'my');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);

        // Прокторинг
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'proctored');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);

        // Создание занятия
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'new');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'ajax', 'modules-list');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);


        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'ajax', 'students-list');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'ajax', 'groups-list');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);


        // Редактирование занятия
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'edit');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);

        // Удаление занятия
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'delete');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);

        // Генерация занятия
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'generate');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);

        // подробные рез-ты
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'result', 'index');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);

        // удаление попыток
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'result', 'delete-attempt');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);

        // запуск занятия
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'execute', 'index');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource); // ENDUSER'у нельзя запускать занятия на оценку, но эта проверка на уровне контроллера
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, $resource);

        // просмотр результата
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'result', 'test-mini');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);

        // просмотр результатов задания преподом
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'result', 'extended');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'edit-section');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
    }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);


        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'order-section');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_TEACHER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'import', 'csv');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }

        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, $resource);
        $acl->deny(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, $resource);
        $acl->deny(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL, $resource);

    }
}
