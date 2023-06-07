<?php
/*
 * Описание возможных параметров
 * * label - название пункта меню
 * * label_short - краткое название пункта меню для отображеня в свернутом состоянии (если нужно)
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
 * * hidden - скрывать эту страницу в меню, в остальном - это обычная страница. Значения: true или false
 * * delimiter - [before|after] для логического отделения пунктов меню
 * *
 */

/**
 * @var HM_Acl $aclService
 * @var HM_User_UserService $userService
 * @var HM_Option_OptionService $optionService
 */
$aclService = Zend_Registry::get('serviceContainer')->getService('Acl');
$optionService = Zend_Registry::get('serviceContainer')->getService('Option');

$return = [

    /*
     *  Страницы ENDUSER
     *
     */

    [
        'label' => _('Главная'),
        'icon' => 'homeMain',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_GROUP_ALL,
        ],
        'deny' => [
            HM_Role_Abstract_RoleModel::ROLE_GUEST,
        ],
        'module' => 'index',
        'controller' => 'index',
        'action' => 'index',
    ],
    [
        'label' => _('Обучение'),
        'icon' => 'education',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
        ],
        'module' => 'subject',
        'controller' => 'my',
        'action' => 'index',
        'modes' => [
            [
                'label' => _('Курсы'),
                'module' => 'subject',
                'controller' => 'my',
                'action' => 'index',
                'params' => [
                    'switcher' => 'list',
                ],
                'icon' => 'mode-list',
            ],
            [
                'label' => _('Программы'),
                'module' => 'subject',
                'controller' => 'my',
                'action' => 'index',
                'params' => [
                    'switcher' => 'program',
                ],
                'icon' => 'planning',
            ],
        ],
    ],
    [
        'label' => _('Каталог курсов'),
        'icon' => 'coursesCatalog',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER
        ],
        'module' => 'subject',
        'controller' => 'catalog',
        'action' => 'index',
    ],
    [
        'label' => _('Оценка персонала'),
        'icon' => 'PersonelAssessment',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
        ],
        'application' => 'at',
        'module' => 'session',
        'controller' => 'list',
        'action' => 'my',
    ],

    /*
     *  Страницы SUPERVISOR
     *
     */

    [
        'label' => _('Обучение'),
        'icon' => 'education',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
        ],
        'module' => 'assign',
        'controller' => 'staff',
    ],
    [
        'label' => _('Подбор'),
        'icon' => 'StaffRecruitment',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
        ],
        'application' => 'recruit',
        'module' => 'application',
        'controller' => 'list',
    ],
    [
        'label' => _('Адаптация'),
        'icon' => 'Adaptation',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
        ],
        'application' => 'recruit',
        'module' => 'newcomer',
        'controller' => 'list',
    ],
    [
        'label' => _('Оценка персонала'),
        'icon' => 'PersonelAssessment',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
        ],
        'application' => 'at',
        'module' => 'session',
        'controller' => 'report',
        'action' => 'matrix-progress',
    ],
    [
        'label' => _('Оргструктура'),
        'icon' => 'OrgStructure',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
        ],
        'module' => 'orgstructure',
        'controller' => 'list',
        'action' => 'supervisor',
    ],
    [
        'label' => _('База знаний'),
        'label_short' => _('База зн'),
        'icon' => 'KnowledgeBase',
        'module' => 'kbase',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
        ],
        'hidden' => !$aclService->checkRoles([
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR
        ]),
        'aliases' => [
            [
                'module' => 'resource',
                'controller' => 'catalog',
            ],
        ],
    ],
    [
        'label' => _('Новости'),
        'icon' => 'News',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
        ],
        'module' => 'news',
        'hidden' => !$aclService->checkRoles([
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
        ]),
    ],

    // Администрирование
    [
        'label' => _('Администрирование'),
        'label_short' => _('Админ'),
        'icon' => 'Administration',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ADMIN
        ],
        'module' => 'user',
        'controller' => 'list',
        'pages' => [
            [
                'label' => _('Все учетные записи'),
                'delimiter' => 'after',
                'module' => 'user',
                'controller' => 'list',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать учетную запись'),
                        'module' => 'user',
                        'controller' => 'list',
                        'action' => 'new',
                    ],
                    [
                        'icon' => 'users',
                        'label' => _('Сгенерировать учетные записи'),
                        'module' => 'user',
                        'controller' => 'list',
                        'action' => 'generate',
                    ],
                    [
                        'icon' => 'download',
                        'label' => _('Импортировать учетные записи из CSV'),
                        'module' => 'user',
                        'controller' => 'import',
                        'action' => 'index',
                        'params' => [
                            'source' => 'csv',
                        ],
                    ],
                ],
            ],
            [
                'label' => _('Супервайзеры'),
                'module' => 'assign',
                'controller' => 'supervisor',
            ],
            [
                'id' => 'mca:assign:atmanager:index',
                'label' => _('Менеджеры и специалисты по оценке'),
                'module' => 'assign',
                'controller' => 'atmanager',
            ],
            [
                'id' => 'mca:assign:recruiter:index',
                'label' => _('Менеджеры и специалисты по подбору'),
                'module' => 'assign',
                'controller' => 'recruiter',
            ],
            [
                'id' => 'mca:assign:labor-safety:index',
                'label' => _('Менеджеры и специалисты по охране труда'),
                'module' => 'assign',
                'controller' => 'labor-safety',
            ],
            [
                'id' => 'mca:assign:dean:index',
                'label' => _('Менеджеры и специалисты по обучению'),
                'module' => 'assign',
                'controller' => 'dean',
            ],
            [
                'id' => 'mca:assign:curator:index',
                'label' => _('Менеджеры конкурсов'),
                'module' => 'assign',
                'controller' => 'curator',
            ],
            [
                'label' => _('Администраторы'),
                'module' => 'assign',
                'controller' => 'admin',
            ],
        ],
    ],
    // Обучение
    [
        'label' => _('Обучение'),
        'icon' => 'education',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ],
        'module' => 'subject',
        'controller' => 'list',
        'params' => [
            'position' => 1,
        ],
        'pages' => [
            [
                'label' => _('Учебные курсы'),
                'module' => 'subject',
                'controller' => 'list',
                'action' => 'index',
                'params' => [
                    'base' => 0,
                ],
                'actions' => [
                    [
                        'label' => _('Создать учебный курс'),
                        'module' => 'subject',
                        'controller' => 'list',
                        'action' => 'new',
                        'icon' => 'add',
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                        ]
                    ]
                ]
            ],
            [
                'label' => _('Учебные сессии'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                ],
                'module' => 'subject',
                'controller' => 'list',
                'action' => 'index',
                'params' => [
                    'base' => 2
                ],
            ],
            [
                'label' => _('Учебные программы'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                ],
                'module' => 'programm',
                'controller' => 'list',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать программу'),
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                        ],
                        'module' => 'programm',
                        'controller' => 'list',
                        'action' => 'new',
                    ]
                ]
            ],
            [
                'delimiter' => 'before',
                'label' => _('Внешние курсы'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                ],
                'application' => 'tc',
                'module' => 'subject',
                'controller' => 'fulltime',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать внешний курс'),
                        'application' => 'tc',
                        'module' => 'subject',
                        'controller' => 'fulltime',
                        'action' => 'new',
                    ]
                ]
            ],
            [
                'label' => _('Сессии внешних курсов'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                ],
                'application' => 'tc',
                'module' => 'subject',
                'controller' => 'fulltime',
                'params' => [
                    'base' => '2'
                ]
            ],
            [
                'delimiter' => 'before',
                'label' => _('Обратная связь'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                ],
                'module' => 'feedback',
                'controller' => 'list',
                'action' => 'index',
                'params' => [
                    'subject_id' => '0'
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать мероприятие'),
                        'module' => 'feedback',
                        'controller' => 'list',
                        'action' => 'new',
                    ]
                ]
            ],
//            [
//                'label' => _('Результаты обучения'),
//                'deny' => [
//                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
//                ],
//                'module' => 'subject',
//                'controller' => 'history',
//                'actions' => [
//                    [
//                        'icon' => 'upload',
//                        'label' => _('Импортировать историю обучения'),
//                        'module' => 'user',
//                        'controller' => 'import',
//                        'action' => 'index',
//                        'params' => [
//                            'source' => 'study-history-csv',
//                        ]
//                    ]
//                ]
//            ],
        ],
    ],
    // Планирование
    [
        'label' => _('Планирование'),
        'label_short' => _('Планирование'),
        'icon' => _('Planning'),
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ],
        'application' => 'tc',
        'module' => 'session',
        'controller' => 'list',
        'pages' => [
            [
                'label' => _('Сессии годового планирования'),
                'application' => 'tc',
                'module' => 'session',
                'controller' => 'list',
            ],
            [
                'label' => _('Сессии квартального  планирования'),
                'application' => 'tc',
                'module' => 'session-quarter',
                'controller' => 'list',
            ],
            [
                'delimiter' => 'before',
                'label' => _('Истечение сроков сертификатов'),
                'module' => 'certificates',
                'controller' => 'list',
                'action' => 'index',
            ],
            [
                'delimiter' => 'before',
                'label' => _('Фактические затраты на обучение'),
                'application' => 'tc',
                'module' => 'subject-costs',
                'controller' => 'actual-costs',
                'action' => 'index',
            ],
        ],
    ],
    // Подбор
    [
        'label' => _('Подбор'),
        'icon' => 'StaffRecruitment',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
        ],
        'application' => 'recruit',
        'pages' => [
            [
                'label' => _('Сессии подбора'),
                'application' => 'recruit',
                'module' => 'vacancy',
                'controller' => 'list',
                'actions' => [
                    // todo: ssl для проверки
                    [
                        'label' => _('Загрузить новые вакансии в базу'),
                        'application' => 'recruit',
                        'module' => 'vacancy',
                        'controller' => 'list',
                        'action' => 'load-new-vacancies',
                    ]
                ]
            ],
            [
                'label' => _('Заявки на подбор'),
                'application' => 'recruit',
                'module' => 'application',
                'controller' => 'list',
            ],
            [
                'label' => _('Вакантные должности'),
                'application' => 'recruit',
                'module' => 'vacancy',
                'controller' => 'vacancy',
            ],
            [
                'delimiter' => 'before',
                'label' => _('База резюме'),
                'application' => 'recruit',
                'module' => 'candidate',
                'controller' => 'list',
                'actions' => [
                    // todo: ssl
                    [
                        'label' => _('Загрузить новые резюме в базу'),
                        'application' => 'recruit',
                        'module' => 'candidate',
                        'controller' => 'list',
                        'action' => 'load-new-resumes'
                    ]
                ]
            ],
            [
                'label' => _('Провайдеры подбора'),
                'application' => 'recruit',
                'module' => 'provider',
                'controller' => 'list',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать нового провайдера'),
                        'application' => 'recruit',
                        'module' => 'provider',
                        'controller' => 'list',
                        'action' => 'new'
                    ]
                ]
            ],
            [
                'delimiter' => 'before',
                'label' => _('Планируемые затраты'),
                'application' => 'recruit',
                'module' => 'costs',
                'controller' => 'planned-costs',
            ],
            [
                'label' => _('Фактические затраты'),
                'application' => 'recruit',
                'module' => 'costs',
                'controller' => 'actual-costs',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать запись'),
                        'application' => 'recruit',
                        'module' => 'costs',
                        'controller' => 'planned-costs',
                        'action' => 'new'
                    ]
                ]
            ],
        ],
    ],
    // Адаптация
    [
        'label' => _('Адаптация'),
        'icon' => 'Adaptation',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
        ],
        'application' => 'recruit',
        'module' => 'newcomer',
        'controller' => 'new-assignments',
        'pages' => [
            [
                'label' => _('Сессии адаптации'),
                'application' => 'recruit',
                'module' => 'newcomer',
                'controller' => 'list',
            ],
            [
                'delimiter' => 'before',
                'label' => _('Новые назначения'),
                'application' => 'recruit',
                'module' => 'newcomer',
                'controller' => 'new-assignments',
            ],
        ],
    ],
    // Оценка персонала
    [
        'label' => _('Оценка персонала'),
        'icon' => 'PersonelAssessment',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL
        ],
        'application' => 'at',
        'module' => 'assign',
        'pages' => [
            [
                'label' => _('Оценочные сессии'),
                'application' => 'at',
                'module' => 'session',
                'controller' => 'list',
                'actions' => [
                    [
                        // todo: не выводится форма
                        'icon' => 'add',
                        'label' => _('Создать оценочную сессию'),
                        'application' => 'at',
                        'module' => 'session',
                        'controller' => 'list',
                        'action' => 'new',
                    ]
                ]
            ],
            [
                'delimiter' => 'before',
                'label' => _('Участники оценочных сессий'),
                'application' => 'at',
                'module' => 'session',
                'controller' => 'users',
                'action' => 'list',
            ],
        ]
    ],
    // Кадровый резерв
    [
        'label' => _('Кадровый резерв'),
        'icon' => 'PersonalReserve',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
        ],
        'application' => 'hr',
        'module' => 'reserve',
        'controller' => 'list',
        'pages' => [
            [
                'label' => _('Сессии кадрового резерва'),
                'application' => 'hr',
                'module' => 'reserve',
                'controller' => 'list',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать сессию КР'),
                        'application' => 'hr',
                        'module' => 'reserve',
                        'controller' => 'list',
                        'action' => 'new',
                    ]
                ]
            ],
            [
                'label' => _('Заявки на участие в сессиях кадрового резерва'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
                ],
                'application' => 'hr',
                'module' => 'reserve-request',
                'controller' => 'list',
            ],
            [
                'delimiter' => 'before',
                'label' => _('Должности кадрового резерва'),
                'application' => 'hr',
                'module' => 'reserve',
                'controller' => 'position',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать сессию КР'),
                        'application' => 'hr',
                        'module' => 'reserve',
                        'controller' => 'position',
                        'action' => 'new',
                    ]
                ]
            ],
        ]
    ],
    // Ротация
    [
        'label' => _('Ротация'),
        'icon' => 'Rotation',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
        ],
        'application' => 'hr',
        'module' => 'rotation',
        'controller' => 'list',
        'pages' => [
            [
                'label' => _('Ротация'),
                'application' => 'hr',
                'module' => 'rotation',
                'controller' => 'list',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать сессию ротации'),
                        'application' => 'hr',
                        'module' => 'rotation',
                        'controller' => 'list',
                        'action' => 'new',
                    ]
                ]
            ]
        ]
    ],
    // Пользователи
    [
        'label' => _('Назначения'),
        'label_short' => _('Назначен'),
        'icon' => 'Destination',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ],
        'module' => 'assign',
        'controller' => 'student',
        'pages' => [
            [
                'label' => _('Слушатели'),
                'module' => 'assign',
                'controller' => 'student',
                'params' => [
                    'subject_id' => '0'
                ],
            ],
            [
                'label' => _('Заявки на обучение'),
                'module' => 'order',
                'controller' => 'list',
                'params' => [
                    'subject_id' => '0'
                ],
            ],
            [
                'label' => _('Прошедшие обучение'),
                'module' => 'assign',
                'controller' => 'graduated',
                'params' => [
                    'subject_id' => '0'
                ],
            ],
            [
                'delimiter' => 'before',
                'label' => _('Тьюторы'),
                'module' => 'assign',
                'controller' => 'teacher',
                'params' => [
                    'subject_id' => '0'
                ],
                'pages' => [
                    [
                        'hidden' => true,
                        'label' => _('Календарь мероприятий'),
                        'module' => 'assign',
                        'controller' => 'teacher',
                        'action' => 'calendar',
                    ],
                ],
            ],
            [
                'delimiter' => 'before',
                'label' => _('Учебные группы'),
                'module' => 'study-groups',
                'controller' => 'list',
                'params' => [
                    'subject_id' => '0'
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать учебную группу'),
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                        ],
                        'module' => 'study-groups',
                        'controller' => 'list',
                        'action' => 'new',
                    ],
                    [
                        'icon' => 'download',
                        'label' => _('Импортировать учетные записи из CSV'),
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                        ],
                        'module' => 'user',
                        'controller' => 'import',
                        'action' => 'index-csv',
//                        'params' => [
//                            'source' => 'csv'
//                        ]
                    ]
                ]
            ],
        ],
    ],
    // Обучение ОТ
    [
        'label' => _('Обучение'),
        'icon' => 'education',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
        ],
        'module' => 'subject',
        'controller' => 'list',
        'params' => [
            'position' => 2,
        ],
        'pages' => [
            [
                'label' => _('Курсы по ОТ'),
                'module' => 'subject',
                'controller' => 'list',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать учебный курс'),
                        'module' => 'subject',
                        'controller' => 'list',
                        'action' => 'new'
                    ]
                ]
            ],
            [
                'label' => _('Учебные сессии по ОТ'),
                'module' => 'subject',
                'controller' => 'list',
                'action' => 'index',
                'params' => [
                    'base' => '2'
                ]
            ],
            [
                'label' => _('Обучение пользователей подразделения'),
                'module' => 'assign',
                'controller' => 'staff',
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY
                ]
            ],
            [
                'delimiter' => 'before',
                'label' => _('Результаты обучения по курсам ОТ'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
                ],
                'module' => 'subject',
                'controller' => 'history',
                'action' => 'index',
                'params' => [
                    'ordergrid' => 'end_DESC'
                ],
                'actions' => [
                    [
                        'icon' => 'download',
                        'label' => _('Импортировать историю обучения из CSV'),
                        'module' => 'user',
                        'controller' => 'import',
                        'action' => 'index',
                        'params' => [
                            'source' => 'study-history-csv'
                        ]
                    ]
                ]
            ],
        ]
    ],
    // Портал
    [
        'label' => _('Портал'),
        'icon' => 'Portal',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ADMIN
        ],
        'module' => 'info',
        'controller' => 'list',
        'pages' => [
            [
                'label' => _('Виджеты'),
                'module' => 'interface',
                'controller' => 'edit',
            ],
//            [
//            [
//                'label' => _('Взаимодействие'),
//                'module' => 'activity',
//                'controller' => 'edit',
//            ],
            [
                'delimiter' => 'before',
                'label' => _('Информационные блоки'),
                'module' => 'info',
                'controller' => 'list',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать информационный блок'),
                        'module' => 'info',
                        'controller' => 'list',
                        'action' => 'new',
                    ],
                ],
            ],
            [
                'label' => _('Информационные страницы'),
                'module' => 'htmlpage',
                'controller' => 'list',
                'actions' => [
                    [
                        'id' => 'mca:htmlpage:group:index:new',
                        'icon' => 'add',
                        'label' => _('Создать группу страниц'),
                        'module' => 'htmlpage',
                        'controller' => 'group',
                        'action' => 'new',
                        'params' => [
                            'key' => '%key%',
                        ],
                    ],
                    [
                        'id' => 'mca:htmlpage:list:index:new',
                        'icon' => 'add',
                        'label' => _('Создать страницу'),
                        'module' => 'htmlpage',
                        'controller' => 'list',
                        'action' => 'new',
                        'params' => [
                            'key' => '%key%',
                        ],
                    ],
                ]
            ],
            // Новости
            [
                'delimiter' => 'before',
                'label' => _('Новости'),
                'label_short' => _('Новости'),
                'icon' => 'News',
                'module' => 'news',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать новость'),
                        'module' => 'news',
                        'controller' => 'index',
                        'action' => 'new',
                        'params' => [
                            'context_type' => '%context%',
                            'context_id' => '%context_id%',
                            'subject_id' => '%subject_id%',
                        ],
                    ],
                ],
                'modes' => [
                    [
                        'label' => _('список'),
                        'module' => 'news',
                        'action' => 'index',
                        'params' => [
                            'context_type' => '%context%',
                            'context_id' => '%context_id%',
                            'subject_id' => '%subject_id%',
                        ],
                        'icon' => 'mode-list',
                    ],
                    [
                        'label' => _('таблица'),
                        'module' => 'news',
                        'controller' => 'index',
                        'action' => 'grid',
                        'params' => [
                            'context_type' => '%context%',
                            'context_id' => '%context_id%',
                            'subject_id' => '%subject_id%',
                        ],
                        'icon' => 'mode-table',
                    ],
                ],
            ],
            // Форум
            [
                'label' => _('Форум'),
                'label_short' => _('Форум'),
                'module' => 'forum',
                'controller' => 'sections',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать раздел'),
                        'module' => 'forum',
                        'controller' => 'sections',
                        'action' => 'new',
                    ],
                ],
            ],
        ],
    ],
    // Оргструктура
    [
        'label' => _('Оргструктура'),
        'label_short' => _('Оргструкт'),
        'icon' => 'OrgStructure',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
        ],
        'module' => 'orgstructure',
        'controller' => 'list',
        'pages' => [
            [
                'label' => _('Оргструктура'),
                'module' => 'orgstructure',
                'controller' => 'list',
                'actions' => [
                    [
                        'label' => _('Создать подразделение'),
                        'icon' => 'add',
                        'module' => 'orgstructure',
                        'controller' => 'list',
                        'action' => 'new',
                        'params' => [
                            'item' => 'department',
                            'parent' => '%key%',
                        ],
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_DEAN,
                            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                            HM_Role_Abstract_RoleModel::ROLE_HR,
                            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR
                        ]
                    ],
                    [
                        'label' => _('Создать должность'),
                        'icon' => 'add',
                        'module' => 'orgstructure',
                        'controller' => 'list',
                        'action' => 'new',
                        'params' => [
                            'item' => 'position',
                            'parent' => '%key%',
                        ],
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_DEAN,
                            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                            HM_Role_Abstract_RoleModel::ROLE_HR,
                            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR
                        ]
                    ],
                    [
                        'icon' => 'download',
                        'label' => _('Импортировать оргструктуру из CSV'),
                        'module' => 'orgstructure',
                        'controller' => 'import',
                        'action' => 'index',
                        'params' => [
                            'source' => 'csv',
                        ],
                        'allow' => [
                            HM_Role_Abstract_RoleModel::ROLE_ADMIN
                        ],
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_DEAN,
                            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
                            HM_Role_Abstract_RoleModel::ROLE_HR,
                            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                        ]
                    ],
                ],
            ],
            [
                'delimiter' => 'before',
                'label' => _('Категории должностей'),
                'application' => 'at',
                'module' => 'category',
                'controller' => 'list',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать категорию должности'),
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_HR,
                            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                        ],
                        'application' => 'at',
                        'module' => 'category',
                        'controller' => 'list',
                        'action' => 'new',
                    ]
                ]
            ],
            [
                'label' => _('Профили должностей'),
                'application' => 'at',
                'module' => 'profile',
                'controller' => 'list',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать профиль должности'),
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_HR,
                            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                        ],
                        'application' => 'at',
                        'module' => 'profile',
                        'controller' => 'list',
                        'action' => 'new',
                    ]
                ]
            ],
        ],
    ],
    // База знаний
    [
        'label' => _('База знаний'),
        'label_short' => _('База зн'),
        'icon' => 'KnowledgeBase',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
        ],
        'uri' => 'kbase-for-managers', // только уникальный идентификатор, не uri
        'pages' => [
            [
                'delimiter' => 'after',
                'label' => _('База знаний'),
                'label_short' => _('База зн'),
                'icon' => 'KnowledgeBase',
                'module' => 'kbase',
            ],
            [
                'label' => _('Информационные ресурсы'),
                'module' => 'kbase',
                'controller' => 'resources',
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать инфоресурс'),
                        'module' => 'kbase',
                        'controller' => 'resource',
                        'action' => 'create',
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                        ]
                    ],
                    [
                        'icon' => 'download',
                        'label' => _('Импортировать из CSV'),
                        'module' => 'resource',
                        'controller' => 'import',
                        'action' => 'index',
                        'params' => [
                            'source' => 'csv'
                        ],
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                        ]
                    ],
//                    [
//                        'label' => _('Импортировать информационные ресурсы с привязкой к медиа-контенту'),
//                        'module' => 'resource',
//                        'controller' => 'import',
//                        'action' => 'index',
//                        'params' => [
//                            'source' => 'csv_media'
//                        ],
//                    ],
                ],
            ],
            [
                'label' => _('Учебные модули'),
                'module' => 'kbase',
                'controller' => 'courses',
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать учебный модуль'),
                        'module' => 'kbase',
                        'controller' => 'course',
                        'action' => 'create',
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                        ]
                    ],
                ],
            ],
            [
                'label' => _('Тесты'),
                'module' => 'quest',
                'controller' => 'list',
                'action' => 'tests',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать тест'),
                        'module' => 'quest',
                        'controller' => 'list',
                        'action' => 'new',
                        'params' => [
                            'type' => HM_Quest_QuestModel::TYPE_TEST
                        ],
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                        ]
                    ],
                    [
                        'icon' => 'download',
                        'label' => _('Импортировать тест'),
                        'module' => 'quest',
                        'controller' => 'list',
                        'action' => 'import',
                        'params' => [
                            'type' => HM_Quest_QuestModel::TYPE_TEST
                        ],
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                        ]
                    ],
                ],
            ],
            [
                'label' => _('Задания'),
                'icon' => 'enter',
                'module' => 'task',
                'controller' => 'list',
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать задание'),
                        'module' => 'task',
                        'controller' => 'index',
                        'action' => 'new',
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                        ]
                    ]
                ],
                'pages' => [
                    [
                        'label' => _('Варианты задания'),
                        'module' => 'task',
                        'controller' => 'variant',
                        'action' => 'list',
                        'actions' => [
                            [
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
                ],
            ],
            [
                'label' => _('Опросы'),
                'module' => 'quest',
                'controller' => 'list',
                'action' => 'polls',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать опрос'),
                        'module' => 'quest',
                        'controller' => 'list',
                        'action' => 'new',
                        'params' => [
                            'type' => HM_Quest_QuestModel::TYPE_POLL
                        ],
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                        ]
                    ]
                ],
            ],
            [
                'id' => 'mca:quest:list:psycho',
                'label' => _('Психологические опросы'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER
                ],
                'module' => 'quest',
                'controller' => 'list',
                'action' => 'psycho',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать психологический опрос'),
                        'module' => 'quest',
                        'controller' => 'list',
                        'action' => 'new',
                        'params' => [
                            'type' => HM_Quest_QuestModel::TYPE_PSYCHO
                        ]
                    ]
                ],
            ],
        ],
    ],
    // Идеи
    [
        'label' => _('Идеи'),
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
        ],
        'module' => 'idea',
        'controller' => 'list',
        'action' => 'index',
        'actions' => [
            [
                'icon' => 'add',
                'label' => _('Создать идею'),
                'module' => 'idea',
                'controller' => 'list',
                'action' => 'new',
            ]
        ],
    ],
    // Справочники
    [
        'label' => _('Справочники'),
        'label_short' => _('Справочн'),
        'icon' => 'ReferenceBooks',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
        ],
        'application' => 'els',
        'pages' => [
            [
                'label' => _('Классификаторы'),
                'module' => 'classifier',
                'controller' => 'list-types',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать классификатор'),
                        'module' => 'classifier',
                        'controller' => 'list-types',
                        'action' => 'new',
                    ],
                ],
            ],
//            [
//                'delimiter' => 'before',
//                'label' => _('Профстандарты'),
//                'deny' => [
//                    HM_Role_Abstract_RoleModel::ROLE_MANAGER,
//                ],
//                'application' => 'at',
//                'module' => 'standard',
//                'controller' => 'list',
//                'actions' => [
//                    [
//                        'icon' => 'add',
//                        'label' => _('Создать профстандарт'),
//                        'application' => 'at',
//                        'module' => 'standard',
//                        'controller' => 'list',
//                        'action' => 'new',
//                    ],
//                ],
//            ],
            [
                'delimiter' => 'before',
                'label' => _('Кластеры компетенций'),
                'application' => 'at',
                'module' => 'criterion',
                'controller' => 'cluster',
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать кластер'),
                        'application' => 'at',
                        'module' => 'criterion',
                        'controller' => 'cluster',
                        'action' => 'new',
                    ],
                ],
            ],
            [
                'label' => _('Компетенции'),
                'application' => 'at',
                'module' => 'criterion',
                'controller' => 'competence',
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать компетенцию'),
                        'application' => 'at',
                        'module' => 'criterion',
                        'controller' => 'competence',
                        'action' => 'new',
                    ],
                ],
            ],
            [
                'delimiter' => 'before',
                'id' => 'mca:criterion:test:index',
                'label' => _('Квалификации'),
                'application' => 'at',
                'module' => 'criterion',
                'controller' => 'test',
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать квалификацию'),
                        'application' => 'at',
                        'module' => 'criterion',
                        'controller' => 'test',
                        'action' => 'new',
                        'params' => [
                            'key' => '%key%',
                        ],
                    ],
                ],
            ],
            [
                'delimiter' => 'before',
                'id' => 'mca:criterion:personal:index',
                'label' => _('Личностные характеристики'),
                'application' => 'at',
                'module' => 'criterion',
                'controller' => 'personal',
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать характеристику'),
                        'application' => 'at',
                        'module' => 'criterion',
                        'controller' => 'personal',
                        'action' => 'new',
                    ],
                ],
            ],
            [
                'delimiter' => 'before',
                'label' => _('Кластеры показателей эффективности'),
                'application' => 'at',
                'module' => 'kpi',
                'controller' => 'cluster',
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать кластер'),
                        'application' => 'at',
                        'module' => 'kpi',
                        'controller' => 'cluster',
                        'action' => 'new',
                    ],
                ],
            ],
            [
                'label' => _('Показатели эффективности'),
                'application' => 'at',
                'module' => 'kpi',
                'controller' => 'list',
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать показатель эффективности'),
                        'application' => 'at',
                        'module' => 'kpi',
                        'controller' => 'list',
                        'action' => 'new',
                    ],
                ],
            ],
            [
                'id' => 'mca:criterion:kpi:index',
                'label' => _('Способы достижения показателей'),
                'application' => 'at',
                'module' => 'criterion',
                'controller' => 'kpi',
                'action' => 'index',
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать критерий'),
                        'application' => 'at',
                        'module' => 'criterion',
                        'controller' => 'kpi',
                        'action' => 'new',
                    ],
                ],
            ],
            [
                'delimiter' => 'before',
                'id' => 'mca:quest:list:form',
                'label' => _('Произвольные оценочные формы'),
                'module' => 'quest',
                'controller' => 'list',
                'action' => 'form',
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать оценочную форму'),
                        'module' => 'quest',
                        'controller' => 'list',
                        'action' => 'new',
                        'params' => [
                            'type' => 'form',
                        ],
                    ],
                ],
            ],
            [
                'delimiter' => 'before',
                'label' => _('Периоды'),
                'module' => 'cycle',
                'controller' => 'list',
                'action' => 'index',
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать период'),
                        'module' => 'cycle',
                        'controller' => 'list',
                        'action' => 'new',
                    ],
                ],
            ],
            [
                'delimiter' => 'before',
                'label' => _('Профстандарты'),
                'application' => 'at',
                'module' => 'standard',
                'controller' => 'list',
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_MANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                ],
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать профстандарт'),
                        'application' => 'at',
                        'module' => 'standard',
                        'controller' => 'list',
                        'action' => 'new',
                    ],
                ],
            ],
            [
                'delimiter' => 'before',
                'label' => _('Места проведения обучения'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_CURATOR,
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                ],
                'module' => 'room',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать место проведения обучения'),
                        'module' => 'room',
                        'controller' => 'index',
                        'action' => 'new',
                    ],
                ],
            ],
            [
                'label' => _('Выходные и праздничные дни'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_CURATOR,
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                ],
                'module' => 'holiday',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать выходной день'),
                        'module' => 'holiday',
                        'controller' => 'index',
                        'action' => 'edit',
                    ],
                    [
                        'icon' => 'add',
                        'label' => _('Создать периодические выходные дни недели'),
                        'module' => 'holiday',
                        'controller' => 'index',
                        'action' => 'edit-periodic',
                    ],
                ]
            ],

        ],
    ],
    // Отчеты
    [
        'label' => _('Отчёты'),
        'icon' => 'Reports',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_CURATOR,
            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
            HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
        ],
        'module' => 'report',
        'pages' => [
            [
                'label' => _('Отчёты'),
                'module' => 'report',
                'controller' => 'index',
                'action' => 'list',
            ],
            [
                'delimiter' => 'before',
                'label' => _('Конструктор отчётов'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_CURATOR,
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
                ],
                'module' => 'report',
                'controller' => 'list',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать отчётную форму'),
                        'module' => 'report',
                        'controller' => 'list',
                        'action' => 'edit',
                    ],
                ],
            ],
            [
                'label' => _('Шаблон отчётов'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                ],
                'module' => 'template',
                'controller' => 'report',
            ]
        ],
    ],
    // Настройки
    [
        'label' => _('Настройки'),
        'icon' => 'Settings',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_CURATOR,
            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
            HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
        ],
        'module' => 'options',
        'pages' => [
            [
                'label' => _('Параметры системы'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_CURATOR,
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL

                ],
                'module' => 'option',
                'controller' => 'index',
            ],
            [
                'delimiter' => 'after',
                'label' => _('Тема оформления'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_CURATOR,
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL

                ],
                'module' => 'interface',
                'controller' => 'edit',
                'action' => 'design-settings',
            ],
            [
                'delimiter' => 'before',
                'label' => _('Регистрация'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_CURATOR,
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
                ],
                'module' => 'contract',
            ],
            [
                'delimiter' => 'after',
                'label' => _('Парольная политика'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_CURATOR,
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
                ],
                'module' => 'password',
                'controller' => 'setup',
            ],
            [
                'label' => _('Типы занятий'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_MANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
                ],
                'module' => 'event',
                'controller' => 'list',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать тип занятия'),
                        'module' => 'event',
                        'controller' => 'list',
                        'action' => 'new',
                        'deny' => [
                            HM_Role_Abstract_RoleModel::ROLE_TEACHER
                        ]
                    ],
                ]
            ],
            [
                'label' => _('Формулы'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_MANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
                ],
                'module' => 'formula',
                'controller' => 'list',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать формулу'),
                        'module' => 'formula',
                        'controller' => 'list',
                        'action' => 'new',
                    ],
                ]
            ],
            [
                'label' => _('Шкалы оценивания'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_CURATOR,
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                    HM_Role_Abstract_RoleModel::ROLE_MANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
                ],
                'module' => 'scale',
                'controller' => 'list',
                'actions' => [
                    [
                        'icon' => 'add',
                        'label' => _('Создать шкалу оценивания'),
                        'module' => 'scale',
                        'controller' => 'list',
                        'action' => 'new',
                    ],
                ],
                'pages' => [
                    [
                        'label' => _('Значения шкалы'),
                        'module' => 'scale',
                        'controller' => 'value',
                        'action' => 'index',
                        'actions' => [
                            [
                                'icon' => 'add',
                                'label' => _('Создать значение шкалы'),
                                'module' => 'scale',
                                'controller' => 'value',
                                'action' => 'new',
                                'params' => [
                                    'scaleId' => '%scaleId%',
                                ],
                                'allow' => [
                                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                                ],
                                'deny' => [
                                    HM_Role_Abstract_RoleModel::ROLE_GROUP_ALL,
                                ],
                            ],
                        ],

                    ],
                ]
            ],
            [
                'delimiter' => 'before',
                'label' => _('Настройки публикации вакансий'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_CURATOR,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_MANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
                ],
                'application' => 'recruit',
                'module' => 'option',
                'controller' => 'index',
                'action' => 'publication'
            ],
            [
                'label' => _('Настройки методик оценки'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_CURATOR,
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_MANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
                ],
                'application' => 'at',
                'module' => 'option',
                'controller' => 'index',
                'action' => 'index'
            ],
            [
                'label' => _('Шаблон сертификатов'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_CURATOR,
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
                ],
                'module' => 'template',
                'controller' => 'certificate',
            ],
            [
                'delimiter' => 'before',
                'label' => _('Шаблоны сообщений'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                    HM_Role_Abstract_RoleModel::ROLE_MANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL
                ],
                'module' => 'notice'
            ],
            [
                'label' => _('Шаблоны документов'),
                'deny' => [
                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_ADMIN,
                    HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
                ],
                'module' => 'option',
                'controller' => 'upload-templates',
            ],
//            [
//                'delimiter' => 'before',
//                'label' => _('Установка обновлений'),
//                'deny' => [
//                    HM_Role_Abstract_RoleModel::ROLE_CURATOR,
//                    HM_Role_Abstract_RoleModel::ROLE_TEACHER,
//                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
//                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
//                    HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
//                    HM_Role_Abstract_RoleModel::ROLE_HR,
//                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
//                    HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
//                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
//                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
//                ],
//                'module' => 'update',
//                'controller' => 'list',
//                'actions' => [
//                    [
//                        'label' => _('Установить обновление'),
//                        'module' => 'update',
//                        'controller' => 'list',
//                        'action' => 'install',
//                    ],
//                ]
//            ],
        ]
    ],


    // Форум
    [
        'label' => _('Форум'),
        'icon' => 'Services',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
        ],
        'hidden' => $aclService->checkRoles(HM_Role_Abstract_RoleModel::ROLE_ADMIN),
        'module' => 'forum',
        'controller' => 'sections',
        'actions' => [
            [
                'icon' => 'add',
                'label' => _('Создать раздел'),
                'module' => 'forum',
                'controller' => 'sections',
                'action' => 'new',
                'allow' => [
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                ],
            ],
        ],
    ],

    //Сервисы отключены до выяснения их нужности
    /*[
        'resource' => HM_Navigation_Page_Mvc::RESOURCE_ACTIVITY_PREFIX, // обязательно!
        'icon' => 'Services',
        'label' => _('Сервисы'),
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
            HM_Role_Abstract_RoleModel::ROLE_DEVELOPER,
            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
        ],
        'module' => '',
        'pages' => [
            // здесь полный набор страниц;
            // динамически отключаются в HM_Controller_Action_Subject
            [
                'label' => _('Форум'),
                //'uri' => 'forum/subject/subject',
                'module' => 'forum',
                'controller' => 'subject',
                'action' => 'subject',
                'params' => [
                    'context_type' => '%context%',
                    'context_id' => '%context_id%',
                    'subject_id' => '%subject_id%',
                ],
                'aliases' => [
                    [
                        'module' => 'forum',
                        'controller' => 'index',
                        'action' => 'index',
                    ],
                ],
                'modes' => [
                    [
                        'label' => _('Дерево'),
                        'module' => 'forum',
                        'controller' => 'index',
                        'action' => 'index',
                        'params' => [
                            'context_type' => '%context%',
                            'context_id' => '%context_id%',
                            'subject_id' => '%subject_id%',
                            'section_id' => '%section_id%',
                            'switcher' => 2,
                            'mode' => 'tree',
                        ],
                        'icon' => 'mode-list',
                    ],
                    [
                        'label' => _('Список'),
                        'module' => 'forum',
                        'controller' => 'index',
                        'action' => 'index',
                        'params' => [
                            'context_type' => '%context%',
                            'context_id' => '%context_id%',
                            'subject_id' => '%subject_id%',
                            'section_id' => '%section_id%',
                            'switcher' => 1,
                            'mode' => 'list',
                        ],
                        'icon' => 'table_chart',
                    ],
                ],
            ],
//          [
//                'resource' => sprintf('%s:%s', HM_Navigation_Page_Mvc::RESOURCE_ACTIVITY_PREFIX, HM_Activity_ActivityModel::ACTIVITY_BLOG),
//                'label' => _('Блог'),
//                'module' => 'blog',
//                'modes' => [
//                    [
//                        'label' => _('стандартный'), // icon?
//                        'module' => 'blog',
//                        'controller' => 'index',
//                        'action' => 'index',
//                        'params' => [
//                            'context_type' => '%context%',
//                            'context_id' => '%context_id%',
//                            'subject_id' => '%subject_id%',
//                        ],
//                        'icon' => 'mode-list',
//                    ],
//                    [
//                        'label' => _('таблица'), // icon?
//                        'module' => 'blog',
//                        'controller' => 'index',
//                        'action' => 'index-grid',
//                        'params' => [
//                            'context_type' => '%context%',
//                            'context_id' => '%context_id%',
//                            'subject_id' => '%subject_id%',
//                        ],
//                        'icon' => 'mode-table',
//                        'deny' => [
//                            HM_Role_Abstract_RoleModel::ROLE_GROUP_ALL
//                        ],
//                        'allow' => [
//                            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
//                            HM_Role_Abstract_RoleModel::ROLE_DEAN,
//                            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
//                            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
//                            HM_Role_Abstract_RoleModel::ROLE_HR,
//                            HM_Role_Abstract_RoleModel::ROLE_CURATOR
//                        ],
//                    ],
//                ],
//            ],
//            [
//                'resource' => sprintf('%s:%s', HM_Navigation_Page_Mvc::RESOURCE_ACTIVITY_PREFIX, HM_Activity_ActivityModel::ACTIVITY_WIKI),
//                'label' => _('Wiki'),
//                'module' => 'wiki',
//                'controller' => 'index',
//                'action' => 'index',
//            ],
//            [
//                'label' => _('Wiki-content'),
//                'module' => 'wiki',
//                'controller' => 'index',
//                'action' => 'content',
//                'hidden' => true,
//                'modes' => [
//                    [
//                        'label' => _('стандартный'), // icon?
//                        'module' => 'wiki',
//                        'controller' => 'index',
//                        'action' => 'content',
//                        'icon' => 'mode-list',
//                    ],
//                    [
//                        'label' => _('таблица'), // icon?
//                        'module' => 'wiki',
//                        'controller' => 'index',
//                        'action' => 'content-grid',
//                        'icon' => 'mode-table',
//                        'deny' => [
//                            HM_Role_Abstract_RoleModel::ROLE_GROUP_ALL
//                        ],
//                        'allow' => [
//                            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
//                            HM_Role_Abstract_RoleModel::ROLE_DEAN,
//                            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
//                            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
//                            HM_Role_Abstract_RoleModel::ROLE_HR,
//                            HM_Role_Abstract_RoleModel::ROLE_CURATOR
//                        ],
//                    ],
//                ],
//            ],
//            [
//                'resource' => sprintf('%s:%s', HM_Navigation_Page_Mvc::RESOURCE_ACTIVITY_PREFIX, HM_Activity_ActivityModel::ACTIVITY_CHAT),
//                'label' => _('Чат'),
//                'module' => 'chat',
//                'actions' => [
//                    [
//                        'icon' => 'add',
//                        'label' => _('Создать канал'),
//                        'module' => 'chat',
//                        'controller' => 'index',
//                        'action' => 'new',
//                        'params' => [
//                            'subject_id' => '%subject_id%',
//                            'context_type' => '%context%',
//                            'context_id' => '%context_id%',
//                        ],
//                        'deny' => [
//                            HM_Role_Abstract_RoleModel::ROLE_GROUP_ALL
//                        ],
//                        'allow' => [
//                            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
//                            HM_Role_Abstract_RoleModel::ROLE_DEAN,
//                            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
//                            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
//                            HM_Role_Abstract_RoleModel::ROLE_HR,
//                            HM_Role_Abstract_RoleModel::ROLE_CURATOR
//                        ],
//                    ],
//                ],
//                'modes' => [
//                    [
//                        'label' => _('стандартный'), // icon?
//                        'module' => 'chat',
//                        'controller' => 'index',
//                        'action' => 'index',
//                        'params' => [
//                            'context_type' => '%context%',
//                            'context_id' => '%context_id%',
//                            'subject_id' => '%subject_id%',
//                        ],
//                        'icon' => 'mode-list',
//                    ],
//                    [
//                        'label' => _('таблица'), // icon?
//                        'module' => 'chat',
//                        'controller' => 'index',
//                        'action' => 'index-grid',
//                        'params' => [
//                            'context_type' => '%context%',
//                            'context_id' => '%context_id%',
//                            'subject_id' => '%subject_id%',
//                        ],
//                        'icon' => 'mode-table',
//                        'deny' => [
//                            HM_Role_Abstract_RoleModel::ROLE_GROUP_ALL
//                        ],
//                        'allow' => [
//                            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
//                            HM_Role_Abstract_RoleModel::ROLE_DEAN,
//                            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
//                            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
//                            HM_Role_Abstract_RoleModel::ROLE_HR,
//                            HM_Role_Abstract_RoleModel::ROLE_CURATOR
//                        ],
//                    ],
//                ],
//            ],
//            [
//                'resource' => sprintf('%s:%s', HM_Navigation_Page_Mvc::RESOURCE_ACTIVITY_PREFIX, HM_Activity_ActivityModel::ACTIVITY_CONTACT),
//                'label' => _('Контакты'),
//                'module' => 'message',
//                'controller' => 'contact',
//                'modes' => [
//                    [
//                        'label' => _('стандартный'), // icon?
//                        'module' => 'message',
//                        'controller' => 'contact',
//                        'action' => 'index',
//                        'icon' => 'mode-list',
//                    ],
//                    [
//                        'label' => _('таблица'), // icon?
//                        'module' => 'message',
//                        'controller' => 'contact',
//                        'action' => 'index-grid',
//                        'icon' => 'mode-table',
//                        'deny' => [
//                            HM_Role_Abstract_RoleModel::ROLE_GROUP_ALL
//                        ],
//                        'allow' => [
//                            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
//                            HM_Role_Abstract_RoleModel::ROLE_DEAN,
//                            HM_Role_Abstract_RoleModel::ROLE_MANAGER,
//                            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
//                            HM_Role_Abstract_RoleModel::ROLE_HR,
//                            HM_Role_Abstract_RoleModel::ROLE_CURATOR
//                        ],
//                    ],
//                ],
//            ],
//            [
//                'resource' => sprintf('%s:%s', HM_Navigation_Page_Mvc::RESOURCE_ACTIVITY_PREFIX, HM_Activity_ActivityModel::ACTIVITY_MESSAGES),
//                'label' => _('Сообщения'),
//                'module' => 'message',
//                'controller' => 'view',
//            ],
//            [
//                'resource' => sprintf('%s:%s', HM_Navigation_Page_Mvc::RESOURCE_ACTIVITY_PREFIX, HM_Activity_ActivityModel::ACTIVITY_LIBRARY),
//                'label' => _('Файловое хранилище'),
//                'module' => 'storage',
//            ],
        ]
    ],*/
];

if($optionService->getOption('use_techsupport')) {
    // Техподдержка
    $return[] = [
        'label' => _('Техподдержка'),
        'label_short' => _('Техподд'),
        'icon' => 'Support',
        'allow' => [
            HM_Role_Abstract_RoleModel::ROLE_ADMIN,
            HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
        ],
        'module' => 'techsupport',
        'controller' => 'list',
    ];
}

return $return;