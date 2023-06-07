<?php
class HM_Acl_Meeting
{

    public function __construct(Zend_Acl $acl)
    {
        // Просмотр списка занятий
        $resource = sprintf('mca:%s:%s:%s', 'meeting', 'list', 'index');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);

        // Просмотр списка занятий
        $resource = sprintf('mca:%s:%s:%s', 'meeting', 'list', 'my');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);

        // Создание занятия
        $resource = sprintf('mca:%s:%s:%s', 'meeting', 'list', 'new');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'meeting', 'ajax', 'modules-list');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'meeting', 'ajax', 'participants-list');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);

        // Редактирование занятия
        $resource = sprintf('mca:%s:%s:%s', 'meeting', 'list', 'edit');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);

        // Удаление занятия
        $resource = sprintf('mca:%s:%s:%s', 'meeting', 'list', 'delete');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);

        // Генерация занятия
        $resource = sprintf('mca:%s:%s:%s', 'meeting', 'list', 'generate');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);

        // подробные рез-ты
        $resource = sprintf('mca:%s:%s:%s', 'meeting', 'result', 'index');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);

        // удаление попыток
        $resource = sprintf('mca:%s:%s:%s', 'meeting', 'result', 'delete-attempt');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);

        // запуск занятия
        $resource = sprintf('mca:%s:%s:%s', 'meeting', 'execute', 'index');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource); // ENDUSER'у нельзя запускать занятия на оценку, но эта проверка на уровне контроллера
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);

        // просмотр результата
        $resource = sprintf('mca:%s:%s:%s', 'meeting', 'result', 'test-mini');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_CURATOR, $resource);
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);
        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'meeting', 'list', 'edit-section');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
    }
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'meeting', 'list', 'order-section');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
//        $acl->allow(HM_Role_Abstract_RoleModel::ROLE_MODERATOR, $resource);

    }
}