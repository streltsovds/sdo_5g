<?php

class Assign_StaffController extends HM_Controller_Action_Assign
{
    use HM_Controller_Action_Trait_Grid;
    use HM_Controller_Action_Trait_Context;

    protected $service = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id = 0;

    public function init()
    {
        parent::init();

        if (!$this->isAjaxRequest()) {
            $subjectId = (int)$this->_getParam('subject_id', 0);
            if ($subjectId) { // Делаем страницу расширенной
                $this->id = (int)$this->_getParam($this->idParamName, 0);
                $subject = $this->getOne($this->getService($this->service)->find($this->id));

                $this->view->setExtended(
                    [
                        'subjectName' => $this->service,
                        'subjectId' => $this->id,
                        'subjectIdParamName' => $this->idParamName,
                        'subjectIdFieldName' => $this->idFieldName,
                        'subject' => $subject
                    ]
                );
            }
        }
    }

    public function indexAction()
    {
        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');
        $userId = $this->getService('User')->getCurrentUserId();

        try {
            $department = $this->getService('Orgstructure')->getDefaultParent();
        } catch (HM_Responsibility_ResponsibilityException $e) {
            $this->_flashMessenger->addMessage([
                'message' => $e->getMessage(),
                'type' => HM_Notification_NotificationModel::TYPE_ERROR
            ]);

            $this->_redirector->gotoSimple('index', 'index', 'default');
        }

        $select = $this->getService('User')->getSelect();
        $select->from(
            ['t1' => 'People'],
            [
                'pKey' => new Zend_Db_Expr("CONCAT(CONCAT(t1.MID, '_'), s.subid)"),
                'MID',
                'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                'positions' => new Zend_Db_Expr('GROUP_CONCAT(du.soid)'),
                'email' => 't1.Email',
                'email_confirmed' => 't1.email_confirmed',
                'subid' => 's.subid',
                'courses' => 's.name',
                'price' => 's.price',
                'time_registered' => 't2.begin',
                'time_ended' => 't2.end',
                'status' => 't2.status',
                'mark' => 'cm.mark'
            ]
        )
            ->joinLeft(['du' => 'structure_of_organ'], 'du.mid = t1.MID', [])
            ->joinLeft(['ds' => 'structure_of_organ'], 'du.owner_soid = ds.owner_soid', [])
            ->joinLeft(['dp' => 'structure_of_organ'], 'du.owner_soid = dp.soid', [])
            ->joinLeft(['t2' => 'subjects_users'], 't1.MID = t2.user_id', [])
            ->joinInner(['s' => 'subjects'], 's.subid = t2.subject_id', [])
            ->joinLeft(['cm' => 'courses_marks'], 'cm.cid = t2.subject_id AND cm.mid = t2.user_id', [])
            ->where('t1.blocked != 1')
            ->where('t1.MID != ?', $userId);

        if ($department->lft && $department->rgt) {
            $select
                ->where('du.lft > ?', $department->lft)
                ->where('du.rgt < ?', $department->rgt);
        }

        $group_fields = [
            't1.MID',
            't1.LastName',
            't1.FirstName',
            't1.Patronymic',
            't1.Email',
            't1.email_confirmed',
            't2.begin',
            's.subid',
            's.name',
            't2.end',
            't2.status',
            'mark',
            's.price'
        ];

        $select->group($group_fields);

        //Область ответственности
        if (in_array($this->getService('User')->getCurrentUserRole(), [
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
        ])) $select = $this->getService('Responsibility')->checkSubjects($select, 's.subid');

        $grid = $this->getGrid(
            $select,
            [
                'MID' => ['hidden' => true],
                'subid' => ['hidden' => true],
                'notempty' => ['hidden' => true],
                'email' => ['hidden' => true],
                'email_confirmed' => ['hidden' => true],
                'fio' => [
                    'title' => _('ФИО'),
                    'decorator' => $this->view->cardLink(
                            $this->view->url([
                                'module' => 'user',
                                'controller' => 'list',
                                'action' => 'view',
                                'user_id' => '']) . '{{MID}}',
                            _('Карточка пользователя')
                        ) . '<a href="' . $this->view->url([
                            'module' => 'user',
                            'controller' => 'report',
                            'action' => 'index',
                            'user_id' => ''
                        ]) . '{{MID}}' . '">' . '{{fio}}</a>',
                    'callback' =>
                        [
                            'function' => [$this, 'updateFio'],
                            'params' => ['{{fio}}', '{{MID}}']
                        ],
                    'position' => 1
                ],
                'price' => [
                    'title' => _('Стоимость, руб.'),
                    'position' => 2
                ],
                'courses' => [
                    'title' => _('Курс'),
                    'callback' => [
                        'function' => [$this, 'updateSubjectName'],
                        'params' => ['{{subid}}', '{{courses}}', '{{MID}}']
                    ],
                    'position' => 3
                ],
                'positions' => [
                    'title' => _('Должность'),
                    'callback' => [
                        'function' => [$this, 'departmentsCache'],
                        'params' => ['{{positions}}', $select, 'pluralFormPositionsCount']
                    ],
                    'position' => 4
                ],
                'time_registered' => [
                    'title' => _('Дата начала обучения'),
                    'callback' => [
                        'function' => [$this, 'updateDate'],
                        'params' => ['{{time_registered}}']
                    ],
                    'position' => 5
                ],
                'time_ended' => [
                    'title' => _('Дата окончания обучения'),
                    'callback' =>
                        ['function' => [$this, 'updateDate'],
                            'params' => ['{{time_ended}}']
                        ],
                    'position' => 6
                ],
                'status' => [
                    'title' => _('Статус'),
                    'callback' => [
                        'function' => [$this, 'updateStatus'],
                        'params' => ['{{status}}']
                    ],
                    'position' => 7
                ],
                'mark' => [
                    'title' => _('Оценка'),
                    'callback' => [
                        'function' => [$this, 'updateMark'],
                        'params' => ['{{mark}}']
                    ],
                    'position' => 8
                ]
            ],
            [
                'fio' => null,
                'positions' => ['render' => 'department'],
                'email' => null,
                'courses' => null,
                'price' => null,
                'time_registered' => ['render' => 'date'],
                'time_ended' => ['render' => 'date'],
                'status' => ['values' => HM_Subject_User_UserModel::getLearningStatuses()],
                'mark' => null
            ]
        );

        $grid->setPrimaryKeyField('pKey');

        // Этот ACL отсутствует вовсе
        if ($acl->isCurrentAllowed('mca:assign:staff:login-as')) {
            $grid->addAction([
                'module' => 'assign',
                'controller' => 'staff',
                'action' => 'login-as'
            ],
                ['MID'],
                $this->view->svgIcon('users', _('Войти от имени пользователя')),
                _('Вы действительно хотите войти в систему от имени данного пользователя? При этом все функции Вашей текущей роли будут недоступны. Вы сможете вернуться в свою роль при помощи обратной функции "Выйти из режима". Продолжить?')
            );
        }

        // Судя по ACL тут ещё может быть LABOR_SAFETY_*, ему, видимо, нельзя?
        if ($acl->checkRoles([HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR])) {
            $grid->addMassAction(
                ['action' => 'assign'],
                _('Назначить на курсы'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );

            $grid->addMassAction(
                ['action' => 'unassign'],
                _('Отменить назначение на курсы'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
        }

        $coursesPrompt = _('Выберите курс');

        /** @var HM_Role_DeanService $deanService */
        $deanService = $this->getService('Dean');

        //для назначения на курсы должны отображать список активных курсов, для удаления - берём из строки грида
        $collection = $deanService->getActiveSubjectsResponsibilities($userId);

        if (count($collection)) {
            $courses = $collection->getList(
                'subid',
                'name',
                $coursesPrompt
            );
        }

        $grid->addSubMassActionSelect([
                $this->view->url(['action' => 'assign'])
            ],
            'courseId',
            $courses
        );

        $grid->addAction([
            'module' => 'message',
            'controller' => 'send',
            'action' => 'index'
        ],
            ['MID'],
            $this->view->svgIcon('say-bubble', _('Отправить сообщение'))
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    protected function _assign($personId, $subjectId)
    {
        return $this->getService('Subject')->assignUser($subjectId, $personId);
    }

    protected function _unassign($personId, $subjectId)
    {
        return $this->getService('Subject')->unassignStudent($subjectId, $personId);
    }

    public function updateMark($mark)
    {
        switch ($mark) {
            case -1:
                return '';
            case 1:
                return _('Пройдено');
            default:
                return $mark;
        }
    }

    public function updateStatus($status)
    {
        $statuses = HM_Subject_User_UserModel::getLearningStatuses();
        if (isset($statuses[$status])) {
            return $statuses[$status];
        }

        return _('');
    }

    public function blockAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        // Нельзя заблокировать себя
        if ($key = array_search($this->getService('User')->getCurrentUserId(), $ids)) {
            unset($ids[$key]);
        }

        $array = array('blocked' => 1);
        $res = $this->getService('User')->updateWhere($array, array('MID IN (?)' => $ids));
        if ($res > 0) {
            $this->_flashMessenger->addMessage(_('Пользователи успешно заблокированы!'));
            $this->_redirector->gotoSimple('index', 'staff', 'assign');
        } else {
            $this->_flashMessenger->addMessage(_('Произошла ошибка во время блокировки пользователей!'));
            $this->_redirector->gotoSimple('index', 'staff', 'assign');
        }
    }

    public function unblockAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));

        $array = array('blocked' => 0);

        $res = $this->getService('User')->updateWhere($array, array('MID IN (?)' => $ids));
        if ($res > 0) {
            $this->_flashMessenger->addMessage(_('Пользователи успешно разблокированы!'));
            $this->_redirector->gotoSimple('index', 'staff', 'assign');
        } else {
            $this->_flashMessenger->addMessage(_('Произошла ошибка во время разблокировки пользователей!'));
            $this->_redirector->gotoSimple('index', 'staff', 'assign');
        }
    }

    public function deleteByAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $service = $this->getService('User');
        foreach ($ids as $value) {

            if ($value != $this->getService('User')->getCurrentUserId()) {
                $service->delete(intval($value));
            } else {
                $this->_flashMessenger->addMessage(_('Вы не можете удалить себя!'));
                $this->_redirector->gotoSimple('index', 'staff', 'assign');
            }
        }
        $this->_flashMessenger->addMessage(_('Пользователи успешно удалены'));
        $this->_redirector->gotoSimple('index', 'staff', 'assign');
    }

    public function updateSubjectName($subjectId, $name, $userId)
    {
        return '<a href="' . $this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $subjectId, 'user_id' => $userId)) . '">' . $name . '</a>';
    }

    public function assignAction()
    {
        $subjectIds = $this->_getParam('courseId', []);
        $primaryKeys = explode(',', $this->_getParam('postMassIds_grid', ''));

        $mids = [];
        foreach ($primaryKeys as $primaryKey) {
            list($mid, $cid) = explode('_', $primaryKey);
            $mids[] = $mid;
        }
        $mids = array_unique($mids);

        if (count($subjectIds)) {
            $usersExists = false;
            if (count($mids)) {
                foreach ($mids as $mid) {
                    $usersExists = true;
                    foreach ($subjectIds as $subjectId) {
                        $this->_assign($mid, $subjectId);
                    }
                }
            }

            if ($usersExists) {
                $this->_flashMessenger->addMessage(_('Назначения успешно удалены'));
            } else {
                $this->_flashMessenger->addMessage([
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Не выбран ни один пользователь')
                ]);
            }
        } else {
            $this->_flashMessenger->addMessage([
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Не выбран ни один курс')
            ]);
        }

        $this->_redirector->gotoSimple('index', 'staff', 'assign');
    }

    public function unassignAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $postMassIds = explode(',', $postMassIds);
        }

        foreach ($postMassIds as $postMassId) {
            list($mid, $cid) = explode('_', $postMassId);

            $this->getService('Subject')->unassignStudent($cid, $mid);
            if ($this->getService('Acl')->checkRoles([HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR])) {

                /** @var HM_User_UserModel $currentUser */
                $currentUser = $this->getService('User')->getCurrentUser();

                $claimant = $this->getService('Claimant')->fetchAll([
                    'MID = ?' => $mid,
                    'CID = ?' => $cid,
                ]);

                if (count($claimant)) {
                    $this->getService('Claimant')->updateWhere([
                        'status' => HM_Role_ClaimantModel::STATUS_REJECTED,
                        'changing_date' => date('Y-m-d'),
                        'comments' => $currentUser->getName() . ': ' . _('отмена заявки')
                    ], [
                        'MID = ?' => $mid,
                        'CID = ?' => $cid,
                    ]);
                }
            }
        }

        $this->_flashMessenger->addMessage(_('Назначения успешно отменены.'));

        $this->_redirectToIndex();
    }

    protected function _preAssign($personId, $courseId)
    {
    }

    protected function _preUnassign($personId, $courseId)
    {
    }

    protected function _postAssign($personId, $courseId)
    {
    }

    protected function _postUnassign($personId, $courseId)
    {
    }

    protected function _finishAssign()
    {
    }

    protected function _finishUnassign()
    {
    }

}