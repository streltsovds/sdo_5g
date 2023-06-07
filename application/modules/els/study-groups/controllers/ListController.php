<?php

class StudyGroups_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Context;
    use HM_Controller_Action_Trait_Grid {
        editAction as editActionTraitGrid;
    }

    protected $_form;

    protected $_subject;
    protected $_subjectId;

    protected $programCache = [];
    protected $idParamName = 'group_id';

    public function init()
    {
        $this->_subjectId = $subjectId = $this->_getParam('subject_id', 0);
        $this->_subject = $this->getOne($this->getService('Subject')->find($subjectId));

        if (!$this->isAjaxRequest()) {
            if ($this->_subject) {
                $this->initContext($this->_subject);

                $this->view->addSidebar('subject', [
                    'model' => $this->_subject,
                ]);
            }
        }

        parent::init();
        $this->_setForm(new HM_Form_StudyGroup());
    }

    public function editAction()
    {
        $this->view->setSubHeader(_('Редактирование учебной группы'));
        $this->editActionTraitGrid();
    }

    protected function _getMessages()
    {
        return [
            self::ACTION_INSERT => _('Учебная группа успешно создана'),
            self::ACTION_UPDATE => _('Учебная группа успешно обновлёна'),
            self::ACTION_DELETE => _('Учебная группа успешно удалёна'),
            self::ACTION_DELETE_BY => _('Учебные группы успешно удалены')
        ];
    }

    public function newAction()
    {
        $this->view->setSubHeader(_('Создание учебной группы'));
        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $result = $this->create($form);
                if ($result != null && $result !== true) {
                    $this->_flashMessenger->addMessage(['type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $this->_getErrorMessage($result)]);
                    $this->_redirectToIndex();
                } else {
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_INSERT));
                    $this->_redirectToIndex();
                }
            }
        }
        $this->view->form = $form;
    }

    public function indexAction()
    {
        /** @var HM_Role_DeanService $deanService */
        $deanService = $this->getService('Dean');

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        $userId = $userService->getCurrentUserId();

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == '') {
            $this->_request->setParam("ordergrid", 'name_ASC');
        }

        $select = $this->getService('StudyGroup')->getSelect();
        $select
            ->from(
                ['study_groups'],
                ['study_groups.group_id', 'study_groups.name']
            )
            ->joinLeft(
                ['study_groups_users'],
                'study_groups.group_id = study_groups_users.group_id',
                ['students' => 'COUNT(DISTINCT study_groups_users.user_id)']
            )
            ->joinLeft(
                ['study_groups_programms'],
                'study_groups.group_id = study_groups_programms.group_id',
                ['programms' => 'GROUP_CONCAT(study_groups_programms.programm_id)']
            )
            ->group(['study_groups.group_id', 'study_groups.name', 'study_groups.type']);

        // Область ответственности
        if ($this->getService('Acl')->checkRoles(HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
            $select = $this->getService('Responsibility')->checkGroups($select, 'study_groups.group_id');
        }


        $grid = $this->getGrid(
            $select,
            [
                'group_id' => ['hidden' => true],
                'name' => [
                    'title' => _('Название'),
                    'decorator' => '<a href="' . $this->view->url([
                            'module' => 'study-groups',
                            'controller' => 'users',
                            'action' => 'index',
                            'gridmod' => null,
                            'subject_id' => $this->_subjectId,
                            'group_id' => ''
                        ], null, true) . '{{group_id}}' . '">' . '{{name}}</a>'
                ],
                'students' => [
                    'title' => _('Количество слушателей')
                ],
                'programms' => [
                    'title' => _('Назначена на программы'),
                    'callback' => [
                        'function' => [$this, 'programmsCache'],
                        'params' => ['{{programms}}', $select]
                    ],
                    'color' => HM_DataGrid_Column::colorize('programms')
                ]
            ],
            [
                'group_id' => null,
                'name' => null,
                'students' => null,
            ],
            'grid'
        );

        if (!$this->currentUserRole([HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL])) {

            $grid->addAction(
                ['module' => 'study-groups', 'controller' => 'list', 'action' => 'edit'],
                ['group_id'],
                $this->view->svgIcon('edit', 'Редактировать')
            );
            $grid->addAction(
                ['module' => 'study-groups', 'controller' => 'list', 'action' => 'delete'],
                ['group_id'],
                $this->view->svgIcon('delete', 'Удалить')
            );

            // Учебные курсы
            $fullCollection = $deanService->getSubjectsResponsibilities($userId, ['base <> ?' => HM_Subject_SubjectModel::BASETYPE_SESSION]);
            if (count($fullCollection)) {
                $allSubjects = $fullCollection->getList('subid', 'name', _('Выберите курс'));

                $grid->addMassAction([
                    'controller' => 'courses',
                    'module' => 'study-groups',
                    'action' => 'assign-course',
                ],
                    _('Назначить группу на учебные курсы'),
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );

                $grid->addSubMassActionSelect([
                    $this->view->url(
                        ['controller' => 'courses',
                            'module' => 'study-groups',
                            'action' => 'assign-course',
                        ]
                    )
                ],
                    'subjectId',
                    $allSubjects
                );

                $grid->addMassAction([
                    'module' => 'study-groups',
                    'controller' => 'courses',
                    'action' => 'unassign-course',
                ],
                    _('Отменить назначение группы на учебные курсы'),
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );

                $grid->addSubMassActionSelect(
                    $this->view->url([
                            'module' => 'study-groups',
                            'controller' => 'courses',
                            'action' => 'unassign-course',
                        ]
                    ),
                    'subjectId',
                    $allSubjects
                );
            }

            // Учебные сессии
            $sessionCollection = $deanService->getSubjectsResponsibilities($userId, ['base = ?' => HM_Subject_SubjectModel::BASETYPE_SESSION]);

            if (count($sessionCollection)) {
                $allSessions = $sessionCollection->getList('subid', 'name', _('Выберите сессию'));


                $grid->addMassAction([
                    'module' => 'study-groups',
                    'controller' => 'courses',
                    'action' => 'assign-session'
                ],
                    _('Назначить группу на учебные сессии'),
                    _('Вы уверены?')
                );

                $grid->addSubMassActionSelect([
                    $this->view->url([
                        'module' => 'study-groups',
                        'controller' => 'courses',
                        'action' => 'assign-session',
                    ])
                ],
                    'subjectId',
                    $allSessions
                );

                $grid->addMassAction([
                    'module' => 'study-groups',
                    'controller' => 'courses',
                    'action' => 'unassign-session'
                ],
                    _('Отменить назначение группы на учебные сессии'),
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );

                $grid->addSubMassActionSelect([
                    $this->view->url([
                            'module' => 'study-groups',
                            'controller' => 'courses',
                            'action' => 'unassign-session',
                        ]
                    )
                ],
                    'subjectId',
                    $allSessions
                );
            }

            // Учебные программы
            $programms = $this->getService('Programm')->fetchAll(['programm_type = ?' => HM_Programm_ProgrammModel::TYPE_ELEARNING, 'item_type IS NULL'], 'name');
            if (count($programms)) {
                $grid->addMassAction([
                    'module' => 'study-groups',
                    'controller' => 'programms',
                    'action' => 'assign-programm',
                ],
                    _('Hазначить группу на учебные программы'),
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );

                $programmNames = $programms->getList('programm_id', 'name');
                $grid->addSubMassActionSelect(
                    $this->view->url([
                        'module' => 'study-groups',
                        'controller' => 'programms',
                        'action' => 'assign-programm',
                    ]),
                    'programmId',
                    $programmNames
                );

                $grid->addMassAction([
                    'module' => 'study-groups',
                    'controller' => 'programms',
                    'action' => 'unassign-programm',
                ],
                    _('Отменить назначение группы на учебные программы'),
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );

                $grid->addSubMassActionSelect(
                    $this->view->url([
                            'module' => 'study-groups',
                            'controller' => 'programms',
                            'action' => 'unassign-programm',
                        ]
                    ),
                    'programmId',
                    $programms->getList('programm_id', 'name')
                );
            }

            $grid->addMassAction([
                'module' => 'study-groups',
                'controller' => 'list',
                'action' => 'delete-by'
            ],
                _('Удалить'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function subjectAction()
    {
        // Получаем ид курса
        $subjectId = $this->_request->getParam('subject_id', 0);
        if ($subjectId < 1) {
            $this->_redirectToIndex();
        }


        $this->view->subjectId = $subjectId;
        $select = $this->getService('StudyGroup')->getSelect();
        $select
            ->from(
                ['sg' => 'study_groups'],
                ['sg.*'])
            ->joinLeft(
                ['sgc' => 'study_groups_courses'],
                'sg.group_id = sgc.group_id',
                ['']
            )
            ->joinLeft(
                ['study_groups_users'],
                'sg.group_id = study_groups_users.group_id',
                ['students' => 'COUNT(study_groups_users.user_id)']
            )
            ->where('sgc.course_id = ?', $subjectId)
            ->group(['sg.group_id', 'sg.name', 'sg.type']);;

        // hack
        $grid = $this->getGrid(
            $select,
            [
                'group_id' => ['hidden' => true],
                'name' => [
                    'title' => _('Название'),
                    'decorator' => '<a href="' . $this->view->url([
                            'module' => 'study-groups',
                            'controller' => 'users',
                            'action' => 'index',
                            'gridmod' => null,
                            'subject_id' => $subjectId,
                            'group_id' => ''
                        ], null, true) . '{{group_id}}' . '">' . '{{name}}</a>'
                ],
                'type' => [
                    'title' => _('Тип'),
                    'hidden' => true
                ],
                'students' => ['title' => _('Количество слушателей')]
            ],
            [
                'group_id' => null,
                'name' => null,
                'type' => [
                    'values' => HM_StudyGroup_StudyGroupModel::getTypes()
                ],
                'students' => null
            ]
        );

        $grid->updateColumn('type',
            [
                'callback' =>
                    [
                        'function' => [$this, 'updateType'],
                        'params' => ['{{type}}']
                    ]
            ]
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;

    }

    public function updateType($type)
    {
        $types = HM_StudyGroup_StudyGroupModel::getTypes();
        return $types[$type];
    }

    public function create(Zend_Form $form)
    {
        $item = $this->getService('StudyGroup')->insert([
            'name' => $form->getValue('name'),
            'type' => HM_StudyGroup_StudyGroupModel::TYPE_CUSTOM
        ]);

        if ($this->getService('Acl')->checkRoles(HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
            $isNeedResponsibilityNotification = $this->getService('Dean')->isNeedResponsibilityNotification(
                $this->getService('User')->getCurrentUserId(), $item->getPrimaryKey(), HM_Responsibility_ResponsibilityModel::TYPE_GROUP
            );

            if ($isNeedResponsibilityNotification) {
                $this->_flashMessenger->addMessage([
                    'type' => HM_Notification_NotificationModel::TYPE_NOTICE,
                    'message' => _("Вы работаете в режиме ограничения области ответственности; группа может быть включена в Вашу в область ответственности администратором системы.")
                ]);
            }
        }

        $tags = array_unique($form->getParam('tags', []));
        $this->getService('StudyGroup')->saveTags($item->group_id, $tags);
    }

    public function update(Zend_Form $form)
    {
        $item = $this->getService('StudyGroup')->update([
            'group_id' => $this->_request->getParam('group_id'),
            'name' => $form->getValue('name'),
            'type' => $form->getValue('type')
        ]);

        $tags = array_unique($form->getParam('tags', []));
        $this->getService('StudyGroup')->saveTags($item->group_id, $tags);

    }

    public function setDefaults(Zend_Form $form)
    {
        $groupId = (int)$this->_getParam('group_id', 0);
        $group = $this->getOne($this->getService('StudyGroup')->find($groupId));
        if ($group) {

            $values = $group->getValues();
            $values['tags'] = $this->getService('Tag')->getTags($groupId, $this->getService('TagRef')->getStudyGroupType());
            $values['tags'] = array_values($values['tags']); // фронт не хочет работать с родными индексами (=id) меток
            $form->setDefaults($values);
        }
    }

    public function delete($id)
    {
        $this->getService('StudyGroup')->delete($id);
        return true;
    }

    public function deleteAction()
    {
        $id = (int)$this->_getParam($this->idParamName, 0);
        if ($id) {
            $this->delete($id);
            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
        }
        $this->_redirectToIndex();
    }


    protected function _redirectToIndex()
    {
        $this->_redirector->gotoRoute([
            'module' => 'study-groups',
            'controller' => 'list',
            'action' => 'index',
            'subject_id' => '0'
        ], null, true);
    }

    public function programmsCache($field, $select)
    {
        if ($this->programCache === []) {
            $smtp = $select->query();
            $res = $smtp->fetchAll();
            $tmp = [];
            foreach ($res as $val) {
                $tmp[] = $val['programms'];
            }
            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);
            $this->programCache = $this->getService('Programm')->fetchAll(['programm_id IN (?)' => $tmp], 'name');
        }

        $fields = array_filter(array_unique(explode(',', $field)));

        if (count($fields)) {
            $programms = $this->getService('Programm')->fetchAll(['programm_id IN (?)' => $fields], 'name');
            $fields = [];
            if (count($programms)) {
                foreach ($programms as $programm) {
                    $fields[] = $programm->programm_id;
                }
            }
        }

        $result = (is_array($fields) && (count($fields) > 1)) ? ['<p class="total">' . $this->getService('Programm')->pluralFormCount(count($fields)) . '</p>'] : [];
        foreach ($fields as $value) {
            $tempModel = $this->programCache->exists('programm_id', $value);
            $result[] = sprintf('<p>%s</p>', $tempModel->name);
        }

        if ($result)
            return implode('', $result);
        else
            return _('Нет');
    }

}