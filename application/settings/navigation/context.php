<?php
/*
 * Описание возможных параметров
 * * label - название пункта меню
 * * uri - используется только для групп, у которых нет своего URL
 * * pages - массив вложенных элементов навигации, рекурсия
 * * allow - массив ролей кому можно; работает только в пределах родительского allow; всем остальным - нельзя.
 * * * Используется для pages, mods, actions
 * * deny - массив ролей кому нельзя (для организации исключений внутри родительского allow)
 * * * Используется для pages, mods, actions
 * * icon - иконка, см. https://material.io/tools/icons/?style=baseline
 * * application - формация верхнего уровня [at|recruit|tc|...]; дефолтный els - можно не писать
 * * module - module.)
 * * controller - controller, если не задан - index
 * * action - action, если не задан - index
 * * params - массив параметров для подстановки; если содержит %% - заменяется на параметр из GET
 * * actions - массив страниц действий (то, что слева над гридом)
 * * modes - массив переключателей режимоа представлений (раньше назывался HeadSwitcher)
 * * aliases - массив строго ['module', 'controller', 'action'], которые с точки зрения isActive() считаются той же страницей
 * * hidden - скрывать эту страницу в меню, в остальном - это обычная страница. Значения: true или false
 * * delimiter - [before|after] для логического отделения пунктов меню
 * *
 */

/** @var HM_Acl $acl */
$acl = $this->getService('Acl');

$return = [
    // Пользователь
    'user' => [
        'label' => _('Пользователь'),
        'uri' => '',
        'allow' => [
            // запрещаем вручную в HM_Controller_Action_User
//            HM_Role_Abstract_RoleModel::ROLE_GROUP_ALL,
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
        ],

        'pages' => [
            [
                'label' => _('Карточка'),
                'module' => 'user',
                'controller' => 'report',
                'action' => 'index',
                'params' => [
                    'user_id' => '%user_id%'
                ],
            ],
            [
                'label' => _('История'),
                'module' => 'user',
                'controller' => 'index',
                'action' => 'study-history',
                'pages' => [
                    [
                        'label' => _('История обучения'),
                        'module' => 'user',
                        'controller' => 'index',
                        'action' => 'study-history',
                        'params' => [
                            'user_id' => '%user_id%'
                        ],
                    ],
                    [
                        'label' => _('История оценки'),
                        'module' => 'user',
                        'controller' => 'index',
                        'action' => 'sessions',
                        'params' => [
                            'user_id' => '%user_id%'
                        ],
                    ],
                ],
            ],
            [
                'label' => _('Назначения'),
                'module' => 'user',
                'controller' => 'student',
                'action' => 'assign',
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                    HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                ],
                'pages' => [
                    [
                        'label' => _('Назначение на курсы как слушатель'),
                        'module' => 'user',
                        'controller' => 'student',
                        'action' => 'assign',
                        'params' => [
                            'user_id' => '%user_id%'
                        ],
                    ],
                    [
                        'label' => _('Назначение на курсы как тьютор'),
                        'module' => 'user',
                        'controller' => 'teacher',
                        'action' => 'assign',
                        'params' => [
                            'user_id' => '%user_id%'
                        ],
                    ],
                ],
            ],
        ],
    ],
    // Тест
    'quest' => [
        'label' => _('Тест'), // @todo: здесь может быть и опрос и оценочная форма и т.п.
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
        /**
         * Часть страниц с условиями скрывается в @see HM_Controller_Action_Quest::getContextNavigationModifiers
         */
        'pages' => [
            [
                'id' => 'mca:quest:question:list',
                'label' => _('Вопросы'),
                'module' => 'quest',
                'controller' => 'question',
                'action' => 'list',
                'params' => [
                    'quest_id' => '%quest_id%',
                    'subject_id' => '%subject_id%',
                    'lesson_id' => '%lesson_id%'
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать вопрос'),
                        'module' => 'quest',
                        'controller' => 'question',
                        'action' => 'new',
                        'params' => [
                            'quest_id' => '%quest_id%',
                            'subject_id' => '%subject_id%',
                            'lesson_id' => '%lesson_id%'
                        ]
                    ],
                    [
                        'icon' => 'download',
                        'label' => _('Импортировать вопросы теста'),
                        'module' => 'quest',
                        'controller' => 'question',
                        'action' => 'import',
                        'params' => [
                            'quest_id' => '%quest_id%',
                            'subject_id' => '%subject_id%',
                        ],
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                        ],
                    ],
                ],
            ],
            [
                'id' => 'mca:quest:cluster:list',
                'label' => _('Блоки'),
                'module' => 'quest',
                'controller' => 'cluster',
                'action' => 'list',
                'params' => [
                    'quest_id' => '%quest_id%',
                    'subject_id' => '%subject_id%',
                    'lesson_id' => '%lesson_id%'
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать блок'),
                        'module' => 'quest',
                        'controller' => 'cluster',
                        'action' => 'new',
                        'params' => [
                            'quest_id' => '%quest_id%',
                        ]
                    ],
                ],
            ],
            [
                'label' => _('Показатели'),
                'module' => 'quest',
                'controller' => 'category',
                'action' => 'list',
                'params' => [
                    'quest_id' => '%quest_id%',
                    'subject_id' => '%subject_id%',
                    'lesson_id' => '%lesson_id%'
                ],
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать показатель'),
                        'module' => 'quest',
                        'controller' => 'category',
                        'action' => 'new',
                        'params' => [
                            'quest_id' => '%quest_id%',
                            'subject_id' => '%subject_id%',
                            'lesson_id' => '%lesson_id%'
                        ]
                    ],
                ],
                'aliases' => [
                    [
                        'module' => 'quest',
                        'controller' => 'category',
                        'action' => 'new',
                    ],
                    [
                        'module' => 'quest',
                        'controller' => 'category',
                        'action' => 'edit',
                    ],
                ]
            ],
            [
                'label' => _('Статистика ответов'),
                'module' => 'quest',
                'controller' => 'report',
                'action' => 'index',
                'params' => [
                    'quest_id' => '%quest_id%',
                    'subject_id' => '%subject_id%',
                    'lesson_id' => '%lesson_id%'
                ],
                'modes' => [
                    [
                        'id' => 'mca:quest:report:diagram',
                        'label' => _('диаграммы распределения'),
                        'icon' => 'pie-chart',
                        'module' => 'quest',
                        'controller' => 'report',
                        'action' => 'diagram',
                        'params' => [
                            'quest_id' => '%quest_id%'
                        ]
                    ],
                    [
                        'id' => 'mca:quest:report:questions',
                        'label' => _('сводный отчёт'),
                        'icon' => 'mode-list',
                        'module' => 'quest',
                        'controller' => 'report',
                        'action' => 'questions',
                        'params' => [
                            'quest_id' => '%quest_id%'
                        ]
                    ],
                    [
                        'id' => 'mca:quest:report:poll',
                        'label' => _('подробный отчёт'),
                        'icon' => 'mode-table',
                        'module' => 'quest',
                        'controller' => 'report',
                        'action' => 'poll',
                        'params' => [
                            'quest_id' => '%quest_id%'
                        ]
                    ],
                ],
            ],
        ]
    ],
    // Учебный курс
    'subject' => [
        'label' => _('Учебный курс'),
        'uri' => '',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_TEACHER
        ],
        'pages' => [
            [
                'id' => 'mca:subject:index:description',
                'label' => _('О курсе'),
                'module' => 'subject',
                'controller' => 'index',
                'action' => 'description',
                'params' => [
                    'subject_id' => '%subject_id%'
                ],
                'pages' => [
                    [
                        'label' => _('Новости'),
                        'module' => 'news',
                        'controller' => 'index',
                        'action' => 'grid',
                        'hidden' => true,
                        'actions' => [
                            [
                                'icon' => 'add',
                                'label' => _('Создать новость'),
                                'module' => 'news',
                                'controller' => 'index',
                                'action' => 'new',
                                'params' => [
                                    'subject_id' => '%subject_id%',
                                    'subject' => 'subject',
                                ],
                                'deny' => [
                                    HM_Role_Abstract_RoleModel::ROLE_ENDUSER
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // для менеджеров
            [
                'id' => 'mca:subject:lessons:edit',
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                ],
                'label' => _('План занятий'),
                'module' => 'subject',
                'controller' => 'lessons',
                'action' => 'edit',
                'params' => [
                    'subject_id' => '%subject_id%'
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать занятие'),
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                        ],
                        'module' => 'subject',
                        'controller' => 'lesson',
                        'action' => 'create',
                        'params' => [
                            'subject_id' => '%subject_id%'
                        ],
                    ],
                    [
                        'icon' => 'add',
                        'label' => _('Создать раздел'),
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                        ],
                        'module' => 'subject',
                        'controller' => 'section',
                        'action' => 'create',
                        'params' => [
                            'subject_id' => '%subject_id%'
                        ],
                    ],
                    [
                        'icon' => 'download',
                        'label' => _('Импортировать план'),
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                        ],
                        'module' => 'subject',
                        'controller' => 'lessons',
                        'action' => 'import',
                        'params' => [
                            'subject_id' => '%subject_id%'
                        ],
                    ],
                    [
                        'icon' => 'list-items',
                        'label' => _('Сгенерировать'),
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                        ],
                        'module' => 'subject',
                        'controller' => 'lessons',
                        'action' => 'generate',
                        'params' => [
                            'subject_id' => '%subject_id%'
                        ],
                    ],
                ],
                'modes' => [
                    [
                        'label' => _('Исходные материалы'), // icon?
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                        ],
                        'module' => 'subject',
                        'controller' => 'materials',
                        'action' => 'index',
                        'params' => [
                            'subject_id' => '%subject_id%',
                        ],
                        'icon' => 'settings',
                    ],
                    [
                        'label' => _('Таблица'), // icon?
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                        ],
                        'module' => 'subject',
                        'controller' => 'lessons',
                        'action' => 'grid',
                        'params' => [
                            'subject_id' => '%subject_id%',
                        ],
                        'icon' => 'mode-table',
                    ],
                    [
                        'label' => _('Список'), // icon?
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                        ],
                        'module' => 'subject',
                        'controller' => 'lessons',
                        'action' => 'edit',
                        'params' => [
                            'subject_id' => '%subject_id%',
                        ],
                        'icon' => 'mode-list',
                    ],
                ],
                'pages' => [
                    [
                        'label' => _('Результаты занятия'),
                        'hidden' => true,
                        'module' => 'subject',
                        'controller' => 'results',
                        'action' => 'index',
                        'params' => [
                            'subject_id' => '%subject_id%'
                        ],
// этот переключатель нужен только для одного вида ресурсов - уч.модуль SCORM
// желательно добавлять на лету
//                        'modes' => [
//                            [
//                                'icon' => 'mode-table',
//                                'label' => _('Таблица'),
//                                'module' => 'subject',
//                                'controller' => 'results',
//                                'action' => 'index',
//                                'params' => [
//                                    'subject_id' => '%subject_id%',
//                                    'lesson_id' => '%lesson_id%'
//                                ],
//                            ],
//                            [
//                                'icon' => 'mode-list',
//                                'label' => _('Результаты по SCO'),
//                                'module' => 'subject',
//                                'controller' => 'results',
//                                'action' => 'sco',
//                                'params' => [
//                                    'subject_id' => '%subject_id%',
//                                    'lesson_id' => '%lesson_id%'
//                                ],
//                            ],
//                        ]
                    ],
                ],
                'aliases' => [
                    [
                        'module' => 'subject',
                        'controller' => 'lessons',
                        'action' => 'import',
                    ],
                ],
            ],

            // для слушателя
            [
                'id' => 'mca:subject:lessons:index',
                'label' => _('План занятий'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER
                ],
                'module' => 'subject',
                'controller' => 'lessons',
                'action' => 'index',
                'params' => [
                    'subject_id' => '%subject_id%'
                ],
                'aliases' => [
                    [
                        'module' => 'lesson',
                        'controller' => 'result',
                        'action' => 'index',
                    ],
                ],
            ],

            [
                'id' => 'mca:quest:question:list',
                'hidden' => true,
                'module' => 'quest',
                'controller' => 'question',
                'action' => 'list',
                'params' => [
                    'quest_id' => '%quest_id%'
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать вопрос'),
                        'module' => 'quest',
                        'controller' => 'question',
                        'action' => 'new',
                        'params' => [
                            'quest_id' => '%quest_id%'
                        ]
                    ],
                ],
            ],

            // для менеджеров
            [
                'label' => _('Назначения'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                ],
                'module' => 'subject',
                'params' => [
                    'subject_id' => '%subject_id%'
                ],
                'pages' => [
                    [
                        'label' => _('Слушатели'),
                        'module' => 'assign',
                        'controller' => 'student',
                        'params' => [
                            'subject_id' => '%subject_id%'
                        ],
                    ],
                    [
                        'label' => _('Прошедшие обучение'),
                        'module' => 'assign',
                        'controller' => 'graduated',
                        'params' => [
                            'subject_id' => '%subject_id%'
                        ],
                    ],
                    [
                        'label' => _('Заявки на обучение'),
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                        ],
                        'module' => 'order',
                        'controller' => 'list',
                        'params' => [
                            'subject_id' => '%subject_id%'
                        ],
                    ],
                    [
                        'delimiter' => 'before',
                        'label' => _('Тьюторы'),
                        'module' => 'assign',
                        'controller' => 'teacher',
                        'params' => [
                            'subject_id' => '%subject_id%'
                        ],
                    ],
                    [
                        'delimiter' => 'before',
                        'label' => _('Группы'),
                        'allow' => [
                            HM_Role_Abstract_RoleModel::ROLE_DEAN,
                            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                            HM_Role_Abstract_RoleModel::ROLE_TEACHER
                        ],
                        'module' => 'study-groups',
                        'controller' => 'list',
                        'action' => 'subject',
                        'params' => [
                            'subject_id' => '%subject_id%'
                        ],
                    ],
                    [
                        'label' => _('Подгруппы на курсе'),
                        'module' => 'group',
                        'params' => [
                            'subject_id' => '%subject_id%'
                        ],
                        'actions' => [
                            [
                                'icon' => 'add',
                                'label' => _('Создать подгруппу'),
                                'module' => 'group',
                                'controller' => 'index',
                                'action' => 'new',
                                'params' => [
                                    'subject_id' => '%subject_id%',
                                ],
                            ],
                        ],
                    ],
                ],
                'aliases' => [
                    [
                        'module' => 'study-groups',
                        'controller' => 'users',
                        'action' => 'index',
                    ],
                ],
            ],
            [
                'label' => _('Результаты'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                ],
                'module' => 'marksheet',
                'params' => [
                    'subject_id' => '%subject_id%'
                ],
            ],
            [
                'label' => _('Взаимодействие'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                ],
                'pages' => [
                    [
                        'id' => 'mca:subject:contacts:index-manager',
                        'label' => _('Сообщения'),
                        'module' => 'subject',
                        'controller' => 'contacts',
                        'action' => 'index-manager', // то же самое, только для для navigation
                        'params' => [
                            'subject_id' => '%subject_id%',
                        ],
                    ],
                    // ФОРУМ КУРСА
                    [
                        'label' => _('Форум'),
                        'module' => 'forum',
                        'controller' => 'index',
                        'action' => 'index',
                        'params' => [
                            'subject_id' => '%subject_id%',
                        ],
                        'aliases' => [
                            [
                                'module' => 'forum',
                                'controller' => 'sections',
                                'action' => 'index',
                            ],
                            [
                                'module' => 'forum',
                                'controller' => 'messages',
                                'action' => 'index',
                            ],
                        ],
                    ],
                    [
                        'label' => _('Обратная связь'),
                        'delimiter' => 'before',
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                        ],
                        'module' => 'feedback',
                        'controller' => 'list',
                        'action' => 'index',
                        'params' => [
                            'subject_id' => '%subject_id%',
                        ],
                        'actions' => [
                            [
                                'icon' => 'add',
                                'label' => _('Создать мероприятие'),
                                'module' => 'feedback',
                                'controller' => 'list',
                                'action' => 'new',
                                'params' => [
                                    'subject_id' => '%subject_id%',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // для слушателя
            [
                'label' => _('Взаимодействие'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER
                ],
                'pages' => [
                    [
                        'id' => 'mca:subject:contacts:index',
                        'label' => _('Сообщения'),
                        'module' => 'subject',
                        'controller' => 'contacts',
                        'params' => [
                            'subject_id' => '%subject_id%',
                        ],
                    ],
                    // ФОРУМ КУРСА
                    [
                        'label' => _('Форум'),
                        'allow' => [
                            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                        ],
                        'module' => 'forum',
                        'controller' => 'index',
                        'action' => 'index',
                        'params' => [
                            'subject_id' => '%subject_id%',
                        ],
                        'aliases' => [
                            [
                                'module' => 'forum',
                                'controller' => 'sections',
                                'action' => 'index',
                            ],
                            [
                                'module' => 'forum',
                                'controller' => 'messages',
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'session' => [
        'label' => _('Оценочная сессия'),
        'uri' => '',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
        ],
        'pages' => [
//            [
//                'label' => _('Описание'),
//                'application' => 'at',
//                'module' => 'session',
//                'controller' => 'report',
//                'action' => 'card',
//                'params' => [
//                    'session_id' => '%session_id%'
//                ],
//            ],
            [
                'label' => _('Участники'),
                'application' => 'at',
                'module' => 'session',
                'controller' => 'user',
                'action' => 'list',
                'params' => [
                    'session_id' => '%session_id%'
                ],
            ],
            [
                'label' => _('Респонденты'),
                'application' => 'at',
                'module' => 'session',
                'controller' => 'respondent',
                'action' => 'list',
                'params' => [
                    'session_id' => '%session_id%'
                ],
            ],
            [
                'label' => _('Оценочные формы'),
                'application' => 'at',
                'module' => 'session',
                'controller' => 'event',
                'action' => 'list',
                'params' => [
                    'session_id' => '%session_id%'
                ],
            ],
            [
                'label' => _('Результаты'),
                'application' => 'at',
                'module' => 'session',
                'controller' => 'monitoring',
                'action' => 'index',
                'params' => [
                    'session_id' => '%session_id%'
                ],
            ],
        ],
    ],
    // Вакансия
    'vacancy' => [
        'label' => _('Вакансия'),
        'uri' => '',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
        ],
        'pages' => [
            [
                'id' => 'mca:vacancy:report:card',
                'label' => _('Описание'),
                'application' => 'recruit',
                'module' => 'vacancy',
                'controller' => 'report',
                'action' => 'card',
                'params' => [
                    'vacancy_id' => '%vacancy_id%'
                ],
            ],
            [
                'id' => 'mca:vacancy:report:card',
                'label' => _('Участники'),
                'application' => 'recruit',
                'module' => 'candidate',
                'controller' => 'assign',
                'action' => 'index',
                'params' => [
                    'vacancy_id' => '%vacancy_id%'
                ],
                'pages' => [
                    [
                        'label' => _('Кандидаты'),
                        'application' => 'recruit',
                        'module' => 'candidate',
                        'controller' => 'assign',
                        'action' => 'index',
                        'params' => [
                            'vacancy_id' => '%vacancy_id%'
                        ],
                        'actions' => [
                            [
                                'icon' => 'add',
                                'label' => _('Создать кандидата'),
                                'module' => 'user',
                                'controller' => 'list',
                                'action' => 'new',
                                'params' => [
                                    'vacancy_id' => '%vacancy_id%'
                                ],
                            ],
                        ],
                        'aliases' => [
                            [
                                'module' => 'candidate',
                                'controller' => 'search',
                                'action' => 'advanced-search',
                            ],
                        ],
                        'modes' => [
                            [
                                'label' => _('поиск'),
                                'application' => 'recruit',
                                'module' => 'candidate',
                                'controller' => 'search',
                                'action' => 'advanced-search',
                                'params' => [
                                    'vacancy_id' => '%vacancy_id%'
                                ],
                                'icon' => 'search',
                            ],
// чтобы не забыть, что такой функционал есть
//                            [
//                                'label' => _('рекомендации'),
//                                'application' => 'recruit',
//                                'module' => 'candidate',
//                                'controller' => 'search',
//                                'action' => 'index',
//                                'params' => [
//                                    'vacancy_id' => '%vacancy_id%'
//                                ],
//                                'icon' => 'stars', // ?
//                            ],
                            [
                                'label' => _('таблица'), // icon?
                                'application' => 'recruit',
                                'module' => 'candidate',
                                'controller' => 'assign',
                                'action' => 'index',
                                'params' => [
                                    'vacancy_id' => '%vacancy_id%'
                                ],
                                'icon' => 'mode-table',
                            ],
                        ],
                    ],
                    [
                        'label' => _('Специалисты по подбору'),
                        'application' => 'recruit',
                        'module' => 'recruiter',
                        'controller' => 'list',
                        'action' => 'index',
                        'params' => [
                            'vacancy_id' => '%vacancy_id%'
                        ],
                    ],
                ],
            ],
            [
                'label' => _('Оценочные формы'),
                'application' => 'at',
                'module' => 'session',
                'controller' => 'event',
                'action' => 'list',
                'params' => [
                    'vacancy_id' => '%vacancy_id%'
                ],
            ],
            [
                'hidden' => true,
                'label' => _('Программа подбора'),
                'application' => 'recruit',
                'module' => 'vacancy',
                'controller' => 'index',
                'action' => 'programm',
                'params' => [
                    'vacancy_id' => '%vacancy_id%'
                ],
                'modes' => [
                    [
                        'label' => _('календарь'), // icon?
                        'module' => 'programm',
                        'controller' => 'evaluation',
                        'action' => 'calendar',
                        'icon' => 'calendar',
                        'params' => [
                            'vacancy_id' => '%vacancy_id%'
                        ],
                    ],
                    [
                        'label' => _('программа'), // icon?
                        'application' => 'recruit',
                        'module' => 'vacancy',
                        'controller' => 'index',
                        'action' => 'programm',
                        'icon' => 'checklist',
                        'params' => [
                            'vacancy_id' => '%vacancy_id%'
                        ],
                        'aliases' => [
                            [
                                'module' => 'programm',
                                'controller' => 'evaluation',
                                'action' => 'index',
                            ],
                        ]
                    ],
                ],
                'aliases' => [
                    [
                        'module' => 'programm',
                        'controller' => 'evaluation',
                        'action' => 'index',
                    ],
                    [
                        'module' => 'programm',
                        'controller' => 'evaluation',
                        'action' => 'calendar',
                    ],
                ]
            ],
        ],
    ],
    // Cессия адаптации
    'newcomer' => [
        'label' => _('Сессия адаптации'),
        'uri' => '',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
        ],
        'pages' => [
            [
                'label' => _('Описание'),
                'application' => 'recruit',
                'module' => 'newcomer',
                'controller' => 'report',
                'action' => 'index',
                'params' => [
                    'newcomer_id' => '%newcomer_id%'
                ],
            ],
            [
                'label' => _('Задачи'),
                'application' => 'recruit',
                'module' => 'newcomer',
                'controller' => 'kpi',
                'action' => 'index',
                'params' => [
                    'newcomer_id' => '%newcomer_id%'
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать задачу'),
                        'application' => 'recruit',
                        'module' => 'newcomer',
                        'controller' => 'kpi',
                        'action' => 'new',
                        'params' => [
                            'newcomer_id' => '%newcomer_id%'
                        ],
                    ],
                ],
            ],
            [
                'label' => _('Оценка'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                    HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
                ],
                'application' => 'at',
                'module' => 'session',
                'controller' => 'event',
                'action' => 'list',
                'params' => [
                    'newcomer_id' => '%newcomer_id%'
                ],
            ],
            [
                'label' => _('Оценка'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                ],
                'application' => 'at',
                'module' => 'session',
                'controller' => 'event',
                'action' => 'my',
                'params' => [
                    'newcomer_id' => '%newcomer_id%'
                ],
            ],
            [
                'label' => _('Результаты'),
                'application' => 'recruit',
                'module' => 'newcomer',
                'controller' => 'report',
                'action' => 'user',
                'params' => [
                    'newcomer_id' => '%newcomer_id%'
                ],
            ],
            [
                'label' => _('Ещё'),
                'icon' => 'settings',
                'uri' => '',
                'pages' => [
                    [
                        'label' => _('Курсы начального обучения'),
                        'application' => 'recruit',
                        'module' => 'newcomer',
                        'controller' => 'subjects',
                        'action' => 'index',
                        'params' => [
                            'newcomer_id' => '%newcomer_id%'
                        ],
                    ],
                    [
                        'label' => _('Календарь адаптации'),
                        'application' => 'recruit',
                        'module' => 'newcomer',
                        'controller' => 'calendar',
                        'action' => 'index',
                        'params' => [
                            'newcomer_id' => '%newcomer_id%'
                        ],
                    ],
                ],
            ],
        ],
    ],
    // Cессия КР
    'reserve' => [
        'label' => _('Сессия кадрового резерва'),
        'uri' => '',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
        ],
        'pages' => [
            [
                'label' => _('Описание'),
                'application' => 'hr',
                'module' => 'reserve',
                'controller' => 'report',
                'action' => 'index',
                'params' => [
                    'reserve_id' => '%reserve_id%'
                ],
            ],
            [
                'label' => _('Задачи'),
                'application' => 'hr',
                'module' => 'reserve',
                'controller' => 'kpi',
                'action' => 'index',
                'params' => [
                    'reserve_id' => '%reserve_id%'
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать задачу'),
                        'application' => 'hr',
                        'module' => 'reserve',
                        'controller' => 'kpi',
                        'action' => 'new',
                        'params' => [
                            'reserve_id' => '%reserve_id%'
                        ],
                    ],
                ],
            ],
            [
                'label' => _('Оценка'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                    HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
                ],
                'application' => 'at',
                'module' => 'session',
                'controller' => 'event',
                'action' => 'list',
                'params' => [
                    'reserve_id' => '%reserve_id%'
                ],
            ],
            [
                'label' => _('Оценка'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                ],
                'application' => 'at',
                'module' => 'session',
                'controller' => 'event',
                'action' => 'my',
                'params' => [
                    'reserve_id' => '%reserve_id%'
                ],
            ],
            [
                'label' => _('Результаты'),
                'application' => 'hr',
                'module' => 'reserve',
                'controller' => 'report',
                'action' => 'user',
                'params' => [
                    'reserve_id' => '%reserve_id%'
                ],
            ],
            [
                'label' => _('Ещё'),
                'icon' => 'settings',
                'uri' => '',
                'pages' => [
                    [
                        'label' => _('Курсы в рамках программы КР'),
                        'application' => 'hr',
                        'module' => 'reserve',
                        'controller' => 'subjects',
                        'action' => 'index',
                        'params' => [
                            'reserve_id' => '%reserve_id%'
                        ],
                    ],
                ],
            ],
        ],
    ],
    'profile' => [
        'label' => _('Профиль должности'),
        'uri' => '',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
        ],
        'pages' => [
            [
                'label' => _('Описание'),
                'application' => 'at',
                'module' => 'profile',
                'controller' => 'report',
                'params' => [
                    'profile_id' => '%profile_id%'
                ],
            ],
            [
                'label' => _('Требования'),
//                'icon' => 'settings',
                'uri' => '',
                'pages' => [
                    [
                        'label' => _('Формальные требования'),
                        'application' => 'at',
                        'module' => 'profile',
                        'controller' => 'index',
                        'action' => 'requirements',
                        'params' => [
                            'profile_id' => '%profile_id%'
                        ],
                    ],
//                    [
//                        'label' => _('Требования по профстандартам'),
//                        'application' => 'at',
//                        'deny' => [
//                            HM_Role_Abstract_RoleModel::ROLE_DEAN,
//                            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
//                        ],
//                        'module' => 'profile',
//                        'controller' => 'index',
//                        'action' => 'skills',
//                        'params' => [
//                            'profile_id' => '%profile_id%'
//                        ],
//                    ],
                    [
                        'delimiter' => 'before',
                        'id' => 'mca:profile:criterion:corporate',
                        'label' => _('Компетенции'),
                        'application' => 'at',
                        'module' => 'profile',
                        'controller' => 'criterion',
                        'action' => 'corporate',
                        'params' => [
                            'profile_id' => '%profile_id%'
                        ],
                        'modes' => [
                            [
                                'label' => _('профиль успешности (диаграмма)'),
                                'application' => 'at',
                                'module' => 'profile',
                                'controller' => 'report',
                                'action' => 'competence',
                                'icon' => 'pie-chart',
                                'params' => [
                                    'profile_id' => '%profile_id%'
                                ],
                                'deny' => [
                                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                                ],
                            ],
                            [
                                'label' => _('профиль успешности'),
                                'application' => 'at',
                                'module' => 'profile',
                                'controller' => 'competence',
                                'action' => 'index',
                                'icon' => 'bar-chart', // ??
                                'params' => [
                                    'profile_id' => '%profile_id%'
                                ],
                                'deny' => [
                                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                                ],
                            ],
                            [
                                'label' => _('таблица'),
                                'application' => 'at',
                                'module' => 'profile',
                                'controller' => 'criterion',
                                'action' => 'corporate',
                                'icon' => 'mode-table',
                                'params' => [
                                    'profile_id' => '%profile_id%'
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 'mca:profile:criterion:professional',
                        'label' => _('Квалификации'),
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_DEAN,
                            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                        ],
                        'application' => 'at',
                        'module' => 'profile',
                        'controller' => 'criterion',
                        'action' => 'professional',
                        'params' => [
                            'profile_id' => '%profile_id%'
                        ],
                        'modes' => [
                            [
                                'label' => _('профиль успешности'),
                                'application' => 'at',
                                'module' => 'profile',
                                'controller' => 'competence',
                                'action' => 'professional',
                                'icon' => 'bar-chart', // ??
                                'deny' => [
                                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                                ],
                                'params' => [
                                    'profile_id' => '%profile_id%'
                                ],
                            ],
                            [
                                'label' => _('таблица'),
                                'application' => 'at',
                                'module' => 'profile',
                                'controller' => 'criterion',
                                'action' => 'professional',
                                'icon' => 'mode-table',
                                'params' => [
                                    'profile_id' => '%profile_id%'
                                ],
                            ],
                        ],
                    ],
                    [
                        'label' => _('Личностные характеристики'),
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_DEAN,
                            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                        ],
                        'application' => 'at',
                        'module' => 'profile',
                        'controller' => 'criterion',
                        'action' => 'personal',
                        'params' => [
                            'profile_id' => '%profile_id%'
                        ],
                    ],
                    [
                        'label' => _('Показатели эффективности'),
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_DEAN,
                            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                        ],
                        'application' => 'at',
                        'module' => 'kpi',
                        'controller' => 'list',
                        'action' => 'index',
                        'params' => [
                            'profile_id' => '%profile_id%'
                        ],
                        'actions' => [
                            [
                                'icon' => 'add',
                                'label' => _('Создать показатель эффективности'),
                                'application' => 'at',
                                'module' => 'kpi',
                                'controller' => 'list',
                                'action' => 'new',
                                'params' => [
                                    'profile_id' => '%profile_id%',
                                ],
                            ],
                        ],
                    ],
//                    [
//                        'label' => _('Назначение профиля должностям'),
//                        'application' => 'at',
//                        'module' => 'profile',
//                        'controller' => 'position',
//                        'action' => 'list',
//                        'params' => [
//                            'profile_id' => '%profile_id%'
//                        ],
//                    ],
                ],
            ],
            [
                'label' => _('Программы'),
//                'icon' => 'settings',
                'uri' => '',
                'pages' => [
                    [
                        // current page назначается вручную в Programm_EvaluationController
                        'id' => 'mca:profile:index:programm-recruit',
                        'label' => _('Программа начального обучения'),
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                        ],
                        'application' => 'at',
                        'module' => 'profile',
                        'controller' => 'index',
                        'action' => 'programm-elearning',
                        'params' => [
                            'profile_id' => '%profile_id%'
                        ],
                        'modes' => [
                            [
                                'label' => _('календарь'), // icon?
                                'module' => 'programm',
                                'controller' => 'subject',
                                'action' => 'calendar',
                                'icon' => 'calendar',
                                'params' => [
                                    'profile_id' => '%profile_id%',
                                    'programm_id' => '%programm_id%',
                                ],
                            ],
                            [
                                'label' => _('программа'), // icon?
                                'module' => 'programm',
                                'controller' => 'subject',
                                'action' => 'index',
                                'icon' => 'checklist',
                                'params' => [
                                    'profile_id' => '%profile_id%',
                                    'programm_id' => '%programm_id%'
                                ],
                            ],
                        ],
                        'aliases' => [
                            [
                                'module' => 'programm',
                                'controller' => 'subject',
                                'action' => 'index',
                            ],
                        ]
                    ],
                    [
                        // current page назначается вручную в Programm_EvaluationController
                        'id' => 'mca:profile:index:programm-assessment',
                        'label' => _('Программа регулярной оценки'),
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_DEAN,
                            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                            HM_Role_Abstract_RoleModel::ROLE_HR,
                            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                        ],
                        'application' => 'at',
                        'module' => 'profile',
                        'controller' => 'index',
                        'action' => 'programm-assessment',
                        'params' => [
                            'profile_id' => '%profile_id%'
                        ],
                    ],
                    [
                        // current page назначается вручную в Programm_EvaluationController
                        'id' => 'mca:profile:index:programm-reserve',
                        'label' => _('Программа оценки кадрового резерва'),
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_DEAN,
                            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                        ],
                        'application' => 'at',
                        'module' => 'profile',
                        'controller' => 'index',
                        'action' => 'programm-reserve',
                        'params' => [
                            'profile_id' => '%profile_id%'
                        ],
                        'modes' => [
                            [
                                'label' => _('календарь'), // icon?
                                'module' => 'programm',
                                'controller' => 'evaluation',
                                'action' => 'calendar',
                                'icon' => 'calendar',
                                'params' => [
                                    'profile_id' => '%profile_id%',
                                    'programm_id' => '%programm_id%',
                                ],
                            ],
                            [
                                'label' => _('программа'), // icon?
                                'application' => 'at',
                                'module' => 'profile',
                                'controller' => 'index',
                                'action' => 'programm-reserve',
                                'icon' => 'checklist',
                                'params' => [
                                    'profile_id' => '%profile_id%'
                                ],
                                'aliases' => [
                                    [
                                        'module' => 'programm',
                                        'controller' => 'evaluation',
                                        'action' => 'index',
                                    ],
                                ]
                            ],
                        ],
                    ],
                ],
            ],
            [
                'label' => _('Назначения'),
                'application' => 'at',
                'module' => 'profile',
                'controller' => 'position',
                'action' => 'list',
                'params' => [
                    'profile_id' => '%profile_id%'
                ],
            ],
        ],
    ],
];

return $return;