<?php
/*
 * Описание возможных параметров
 * * label - название пункта меню
 * * uri - используется только для групп, у которых нет своего URL
 * * pages - массив вложенных элементов навигации, рекурсия
 * * allow - массив ролей кому можно; работает только в пределах родительского allow; всем остальным - нельзя
 * * deny - массив ролей кому нельзя (для организации исключений внутри родительского allow)
 * * icon - иконка, см. https://material.io/tools/icons/?style=baseline
 * * application - формация верхнего уровня [at|recruit|tc|...]; дефолтный els - можно не писать
 * * module - module.)
 * * controller - controller, если не задан - index
 * * action - action, если не задан - index
 * * params - массив параметров для подстановки; если содержит %% - заменяется на параметр из GET
 * * actions - массив страниц действий (то, что слева над гридом)
 * * modes - массив переключателей режимоа представлений (раньше назывался HeadSwitcher)
 * * aliases - массив строго ['module', 'controller', 'action'], которые с точки зрения isActive() считаются той же страницей
 * *
 */


if (!function_exists('getTasksPages')) {
    function getTasksPages()
    {
        $services = Zend_Registry::get('serviceContainer');

        /** @var HM_Acl $acl */
        $acl = $services->getService('Acl');

        if($acl->checkRoles(HM_Role_Abstract_RoleModel::ROLE_TEACHER) && !$acl->isSubjectContext()) {
            $deny = [HM_Role_Abstract_RoleModel::ROLE_TEACHER];
        } else {
            $deny = [];
        }

        return [
            [
                'label' => _('Варианты задания'),
                'module' => 'task',
                'controller' => 'variant',
                'action' => 'list',
                'actions' => [
                    [
                        'deny' => $deny,
                        'icon' => 'add',
                        'label' => _('Создать вариант'),
                        'module' => 'task',
                        'controller' => 'variant',
                        'action' => 'new',
                        'params' => [
                            'task_id' => '%',
                            'subject_id' => '%',
                            'lesson_id' => '%',
                        ]
                    ]
                ],
            ],
        ];
    }
}

return [
    [
        'label' => _('Блок вопросов'),
        'module' => 'quest',
        'controller' => 'cluster',
        'action' => 'list',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_CURATOR,
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
            HM_Role_Abstract_RoleModel::ROLE_DEVELOPER,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
        ],
        'params' => [
            'subject_id' => '%subject_id%',
            'quest_id' => '%quest_id%',
        ],
        'actions' => [
            [
                'icon' => 'add',
                'label' => _('Создать блок вопросов'),
                'module' => 'quest',
                'controller' => 'cluster',
                'action' => 'new',
                'params' => [
                    'subject_id' => '%subject_id%',
                    'quest_id' => '%quest_id%',
                ],
            ]
        ],
    ],
    [
        'label' => _('Обобщенные трудовые функции'),
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_HR,
        ],
        'deny' => [
            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
        ],
        'application' => 'at',
        'module' => 'standard',
        'controller' => 'functions',
        'actions' => [
            [
                'icon' => 'add',
                'label' => _('Создать трудовую функцию'),
                'application' => 'at',
                'module' => 'standard',
                'controller' => 'functions',
                'action' => 'new',
            ],
        ],
    ],
    [
        'label' => _('Видеоролики'),
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
        ],
        'deny' => [
            HM_Role_Abstract_RoleModel::ROLE_GROUP_ALL,
        ],
        'module' => 'video',
        'controller' => 'list',
        'action' => 'index',
        'actions' => [
            [
                'icon' => 'add',
                'label' => _('Создать видеоролик'),
                'module' => 'video',
                'controller' => 'list',
                'action' => 'new',
            ],
        ],
    ],
    [
        'label' => _('Интересные факты'),
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
        ],
        'deny' => [
            HM_Role_Abstract_RoleModel::ROLE_GROUP_ALL,
        ],
        'module' => 'infoblock',
        'controller' => 'interesting-fact',
        'action' => 'index',
        'actions' => [
            [
                'icon' => 'add',
                'label' => _('Создать факт'),
                'module' => 'infoblock',
                'controller' => 'interesting-fact',
                'action' => 'new'
            ],
        ],
    ],
    [
        'label' => _('Рубрики классификатора'),
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
        ],
        'deny' => [
            HM_Role_Abstract_RoleModel::ROLE_GROUP_ALL,
        ],
        'module' => 'classifier',
        'controller' => 'list',
        'action' => 'index',
        'actions' => [
            [
                'icon' => 'add',
                'label' => _('Создать рубрику'),
                'module' => 'classifier',
                'controller' => 'list',
                'action' => 'new',
                'params' => [
                    'key' => '%key%',
                    'keyType' => '%keyType%',
                ],
            ],
            [
                'icon' => 'download',
                'label' => _('Импортировать рубрики из csv'),
                'module' => 'classifier',
                'controller' => 'import',
                'action' => 'index',
                'params' => [
                    'source' => 'csv',
                    'type' => '%key%',
                ],
            ],
        ],
    ],
    [
        'label' => _('Часто задаваемые вопросы'),
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_GROUP_ALL,
        ],
        'module' => 'faq',
        'controller' => 'list',
        'action' => 'index',
        'actions' => [
            [
                'icon' => 'add',
                'label' => _('Создать вопрос'),
                'module' => 'faq',
                'controller' => 'list',
                'action' => 'new',
                'allow' => [
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                ],
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_GROUP_ALL,
                ],
            ],
        ],
        'modes' => [
            [
                'label' => _('список'),
                'module' => 'faq',
                'controller' => 'list',
                'action' => 'index',
                'icon' => 'mode-list',
                'params' => [
                    'viewType' => 'default'
                ],
            ],
            [
                'label' => _('таблица'),
                'module' => 'faq',
                'controller' => 'list',
                'action' => 'index',
                'icon' => 'mode-table',
                'params' => [
                    'viewType' => 'table'
                ],
            ],
        ],
    ],
    [
        'label' => _('Индикаторы'),
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
        ],
        'deny' => [
            HM_Role_Abstract_RoleModel::ROLE_GROUP_ALL,
        ],
        'module' => 'criterion',
        'controller' => 'indicator',
        'action' => 'index',
        'actions' => [
            [
                'icon' => 'add',
                'label' => _('Создать индикатор'),
                'application' => 'at',
                'module' => 'criterion',
                'controller' => 'indicator',
                'action' => 'new',
                'params' => [
                    'criterionId' => '%criterionId%',
                ],
            ]
        ],
    ],
    [
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
        ],
        'module' => 'scale',
        'controller' => 'value',
        'action' => 'index',
    ],
    // Задания
    [
        'label' => _('Задания'),
        'uri' => '',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_DEVELOPER,
            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
        ],

        'pages' => getTasksPages()
    ],
];