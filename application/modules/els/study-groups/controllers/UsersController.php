<?php

class StudyGroups_UsersController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Context;
    use HM_Controller_Action_Trait_Grid;

    protected $departmentCache = [];

    protected $_subject;
    protected $_subjectId;

    public function init()
    {
        $this->_subjectId = $subjectId = $this->_getParam('subject_id', $this->_getParam('subject_id', 0));
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
    }

    protected function _getGridId()
    {
        return 'grid' . $this->_getParam('group_id', 0);
    }

    public function indexAction()
    {
        $groupId = $this->_getParam('group_id', 0);
        $group = $this->getOne($this->getService('StudyGroup')->find($groupId));
        if (!$group) {
            $this->_redirector->gotoSimple('index', 'list', 'study-groups');
        }

        $this->view->setHeader($group->name);

        $backUrl = [
            'module' => 'study-groups',
            'controller' => 'list',
            'action' => $this->_subjectId == 0 ? 'index' : 'subject',
        ];

        // Даже с 0
        $backUrl['subject_id'] = $this->_subjectId;

        $this->view->setBackUrl($this->view->url($backUrl, null, true));

        $switcher = $this->getSwitcherSetOrder($groupId, 'stgid_DESC');

        $select = $this->getService('User')->getSelect();

        $select->from(
            ['t1' => 'People'],
            [
                'MID' => 't1.MID',
                'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),

                'departments' => new Zend_Db_Expr('GROUP_CONCAT(d_dep.name)'),
                'positions' => new Zend_Db_Expr('GROUP_CONCAT(d.name)'),
                'login' => 't1.Login',
                'groups' => new Zend_Db_Expr('GROUP_CONCAT(sgc.group_id)'),
                'email' => 't1.Email',
                'email_confirmed' => 't1.email_confirmed',
                'status' => 't1.blocked',
                'ldap' => 't1.isAD',
                'tags' => 't1.MID',
            ]
        )
            ->joinLeft(['d' => 'structure_of_organ'],
                'd.mid = t1.MID',
                []
            )
            ->joinLeft(['d_dep' => 'structure_of_organ'],
                'd.owner_soid = d_dep.soid',
                []
            );

        if (!$switcher) {
            $select->joinInner(
                ['sgc' => 'study_groups_users'],
                'sgc.user_id = t1.MID AND sgc.group_id = ' . $groupId,
                ['stgid' => 'CASE WHEN(' . $groupId . ' IN (SELECT st.group_id FROM study_groups_users st WHERE st.user_id = t1.MID)) THEN 1 ELSE 0 END']
            );
        } else {
            $select->joinLeft(
                ['sgc' => 'study_groups_users'],
                'sgc.user_id IN (t1.MID, NULL)',
                ['stgid' => 'CASE WHEN(' . $groupId . ' IN (SELECT st.group_id FROM study_groups_users st WHERE st.user_id = t1.MID)) THEN 1 ELSE 0 END']
            );
        }

        if ($this->currentUserRole([
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ])) {
            $soid = $this->getService('Responsibility')->get();
            $responsibilityPosition = $this->getOne($this->getService('Orgstructure')->find($soid));
            if ($responsibilityPosition) {
                $subSelect = $this->getService('Orgstructure')->getSelect()
                    ->from('structure_of_organ', ['soid'])
                    ->where('lft > ?', $responsibilityPosition->lft)
                    ->where('rgt < ?', $responsibilityPosition->rgt);
                $select->where("d.soid IN (?)", $subSelect);
            } else {
                $select->where('1 = 0');
            }
        }
        $select->where('t1.blocked <> ?', 1);
        $select->group(['t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic', 't1.Login', 't1.Email', 't1.email_confirmed', 't1.blocked', 't1.isAD']);

        $grid = $this->getGrid($select,
            [
                'MID' => ['hidden' => true],
                'notempty' => ['hidden' => true],
                'email_confirmed' => ['hidden' => true],
                'in_group' => ['hidden' => true],
                'stgid' => ['hidden' => true],
                'fio' => [
                    'title' => _('ФИО'),
                    'decorator' => $this->view->cardLink($this->view->url([
                        'module' => 'user',
                        'controller' => 'list',
                        'action' => 'view',
                        'gridmod' => null,
                        'report' => 1,
                        'user_id' => ''
                        ], null, true) . '{{MID}}')
                        . '<a href="' . $this->view->url(
                            [
                            'module' => 'user',
                            'controller' => 'edit',
                            'action' => 'card',
                            'gridmod' => null,
                            'report' => 1,
                            'user_id' => ''
                            ], null, true) . '{{MID}}' . '">' . '{{fio}}</a>'
                ],
                'login' => ['hidden' => true],
                'email' => ['hidden' => true],
                'departments' => [
                    'title' => _('Подразделение'),
                ],
                'positions' => [
                    'title' => _('Должность'),
                ],
                'groups' => [
                    'title' => _('Группы'),
                    'callback' => [
                        'function' => [$this, 'groupsCache'],
                        'params' => ['{{groups}}', $select, true]
                    ]
                ],
                'status' => ['title' => _('Статус')],
                'ldap' => ['hidden' => true],
                'tags' => [
                    'title' => _('Метки'),
                    'callback' => [
                        'function' => [$this, 'displayTags'],
                        'params' => ['{{tags}}', $this->getService('TagRef')->getUserType()]
                    ],
                    'color' => HM_DataGrid_Column::colorize('tags')
                ],
            ],
            [
                'fio' => null,
                'login' => null,
                'departments' => [
                    'callback' => [
                        'function' => [$this, 'deparmentFilter'],
                        'params' => []
                    ]
                ],
                'positions' => [
                    'callback' => [
                        'function' => [$this, 'positionFilter'],
                        'params' => []
                    ]
                ],
                'groups' => [
                    'callback' => [
                        'function' => [$this, 'groupsFilter'],
                        'params' => []
                    ]
                ], 'email' => null,
                'status' => ['values' => [0 => _('Активный'), 1 => _('Заблокирован')]],
                'tags' => ['callback' => ['function' => [$this, 'filterTags']]],
            ],
            'grid',
            'notempty'
        );

        $grid->setClassRowCondition("'{{stgid}}' == 1", 'success');

        $grid->updateColumn('status',
            [
                'callback' =>
                    [
                        'function' => [$this, 'updateStatus'],
                        'params' => ['{{status}}']
                    ]
            ]
        );

        $grid->updateColumn('fio',
            [
                'callback' =>
                    [
                        'function' => [$this, 'updateName'],
                        'params' => ['{{fio}}', '{{MID}}']
                    ]
            ]
        );

        if ((int)$group->type == HM_StudyGroup_StudyGroupModel::TYPE_CUSTOM) {

            $grid->setGridSwitcher([
                'modes' => [0, 1],
                'param' => 'all',
                'label' => _('Показать всех'),
                'title' => _('Показать всех пользователей, в том числе не включенных в данную группу'),
            ]);

            $grid->setClassRowCondition("in_array(" . $groupId . ", array({{groups}}))", "success");
        }

        if ((int)$group->type == HM_StudyGroup_StudyGroupModel::TYPE_CUSTOM && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
            $grid->addMassAction(['module' => 'study-groups', 'controller' => 'users', 'action' => 'include'], _('Включить в группу'));
            $grid->addMassAction(['module' => 'study-groups', 'controller' => 'users', 'action' => 'exclude'], _('Исключить из группы'));
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function deparmentFilter($data)
    {
        $field = $data['field'];
        $value = $data['value'];
        $select = $data['select'];

        // Только больше 4 символов чтобы много не лезло в in
        if (strlen($value) > 4) {
            $fetch = $this->getService('Orgstructure')->fetchAll(['name LIKE LOWER(?)' => "%" . $value . "%"]);

            $data = $fetch->getList('soid', 'name');
            $select->where('d.owner_soid IN (?)', array_keys($data));
        }
    }


    public function positionFilter($data)
    {
        $value = $data['value'];
        $select = $data['select'];

        // Только больше 4 символов чтобы много не лезло в in
        if (strlen($value) > 4) {
            $fetch = $this->getService('Orgstructure')->fetchAll(['name LIKE LOWER(?)' => "%" . $value . "%"]);

            $data = $fetch->getList('soid', 'name');
            $select->where('d.soid IN (?)', array_keys($data));
        }
    }

    public function includeAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $groupId = $this->_getParam('group_id', 0);

        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
            $this->_flashMessenger->addMessage([
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Не достаточно прав')
            ]);
            $this->_redirector->gotoSimple('index', 'users', 'study-groups', ['group_id' => $groupId]);
        }

        /** @var HM_StudyGroup_Users_UsersService $studyGroupUsersService */
        $studyGroupUsersService = $this->getService('StudyGroupUsers');

        foreach ($ids as $id) {
            if (!$studyGroupUsersService->isGroupUser($groupId, $id)) {
                $studyGroupUsersService->addUser($groupId, $id);
            }
        }

        $this->_flashMessenger->addMessage(_('Пользователи успешно включены в группу'));
        $this->_redirector->gotoSimple('index', 'users', 'study-groups', ['group_id' => $groupId]);
    }

    public function excludeAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $groupId = $this->_getParam('group_id', 0);

        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
            $this->_flashMessenger->addMessage([
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Не достаточно прав')
            ]);
            $this->_redirector->gotoSimple('index', 'users', 'study-groups', ['group_id' => $groupId]);
        }

        /** @var HM_StudyGroup_Users_UsersService $studyGroupUsersService */
        $studyGroupUsersService = $this->getService('StudyGroupUsers');

        foreach ($ids as $id) {
            $studyGroupUsersService->removeUser($groupId, $id);
        }

        $this->_flashMessenger->addMessage(_('Пользователи успешно исключены из группы'));
        $this->_redirector->gotoSimple('index', 'users', 'study-groups', ['group_id' => $groupId]);

    }

    public function updateName($name, $userId)
    {
        $name = trim($name);
        if (!strlen($name)) {
            $name = sprintf(_('Пользователь #%d'), $userId);
        }
        return $name;
    }

}