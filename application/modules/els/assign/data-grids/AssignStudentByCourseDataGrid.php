<?php

/**
 * Датагрид для отображения слушателей определённого курса
 *
 */
class HM_Assign_DataGrid_AssignStudentByCourseDataGrid extends HM_DataGrid
{
    protected $baseTable = array('t1' => 'People');

    protected function getList()
    {
        $this->select
            ->joinLeft(array('d'  => 'structure_of_organ'), 'd.mid          = t1.MID',         array())
            ->joinLeft(array('d2' => 'structure_of_organ'), 'd.owner_soid   = d2.soid',        array())
            ->joinLeft(array('g'  => 'study_groups_users'), 'g.user_id      = t1.MID',         array());

        $subSelect = $this->userService->getSelect();
        $subSelect->from(
            array('t2' => 'Students'),
            array('MID', 'CID','time_registered', 'time_ended_planned', 'newcomer_id')
        )
            ->joinInner(
                array('subjects'),
                't2.CID'.' = subjects.subid',
                array('period_restriction_type', 'state')
            )
            ->where('t2.CID'.' = ?', $this->options['courseId']);

        $this->select->joinLeft(array('subSelect' => $subSelect), 't1.MID = subSelect.'.'MID', array());

        $this->select->group(array(
            't1.MID',
            't1.LastName',
            't1.FirstName',
            't1.Patronymic',
            'subSelect.CID',
            'subSelect.time_registered',
            'subSelect.time_ended_planned',
            'subSelect.period_restriction_type',
            'subSelect.state',
            'subSelect.newcomer_id'));

        parent::getList();
    }

    protected function setSwitcherRestrictions()
    {
        if ($this->switcher[0] == Assign_StudentController::FILTER_LISTENERS_COURSE) {
            $this->select->joinInner(array('t2' => 'Students'), 't1.MID = t2.'.'MID', array());
            $this->select
                ->where('subSelect.'.'CID'.' = ?', $this->options['courseId']);
        } elseif ($this->switcher[0] == Assign_StudentController::FILTER_LISTENERS) {
            $this->select->joinInner(array('t2' => 'Students'), 't1.MID = t2.'.'MID', array());
        } else {
            $this->select->joinLeft( array('t2' => 'Students'), 't1.MID = t2.'.'MID', array());
            $this->select
                ->where("(t1.blocked IS NULL")
                ->orWhere("t1.blocked = 0)")
                ->order('CID DESC');
        }
    }

    protected function setRoleRestrictions()
    {
        if ($this->serviceContainer->getService('Acl')->inheritsRole(
            $this->userService->getCurrentUserRole(),
            HM_Responsibility_ResponsibilityModel::getResponsibilityRoles()
        )) {
            $this->select = $this->serviceContainer->getService('Responsibility')->checkUsers($this->select, '', 't1.MID');
            if ($this->switcher[0] == Assign_StudentController::FILTER_LISTENERS_COURSE ||
                $this->switcher[0] == Assign_StudentController::FILTER_LISTENERS) {
                $this->select = $this->serviceContainer->getService('Responsibility')->checkSubjects($this->select, 't2.CID', false, 'g.group_id');
            }
        }
    }

    protected function initColumns()
    {
        $this->hiddenColumns(array(
            'MID'         => new HM_Db_Expr('t1.MID'),
            'user_id'     => new HM_Db_Expr('t1.MID'),
            'tags'        => new HM_Db_Expr('t1.MID'),
            'notempty'    => new HM_Db_Expr('t1.notempty'),
            'state'       => new HM_Db_Expr('subSelect.state'),
            'CID'         => new HM_Db_Expr('subSelect.CID'),
            'newcomer_id' => new HM_Db_Expr('subSelect.newcomer_id'),
            'period_restriction_type'=> new HM_Db_Expr('subSelect.period_restriction_type')
        ));

        $this->addColumn('fio', array(
            'title'      => _('ФИО'),
            'position'   => 1,
            'expression' => new HM_Db_Expr('t1.fio'),
            'decorator'  => HM_DataGrid_Column_Decorator_UserCardLink::create($this, ['userId' => '{{MID}}', 'userName' => '{{fio}}']),
            'callback'   => HM_DataGrid_Column_Callback_UpdateFio::create($this, ['{{MID}}', '{{fio}}']),
            'filter'     => HM_DataGrid_Column_Filter::create($this)
        ));

        $this->addColumn('departments', array(
            'title'      => _('Подразделение'),
            'position'   => 2,
            'expression' => new HM_Db_Expr('GROUP_CONCAT(DISTINCT d2.name)'),
            'filter'     => HM_DataGrid_Column_Filter::create($this)
        ));

        $this->addColumn('positions', array(
            'title'      => _('Должность'),
            'position'   => 3,
            'expression' => new HM_Db_Expr('GROUP_CONCAT(DISTINCT d.name)'),
            'filter'     => HM_DataGrid_Column_Filter::create($this)
        ));

        $this->addColumn('groups', array(
            'title'      => _('Учебные группы'),
            'position'   => 4,
            'expression' => new HM_Db_Expr('GROUP_CONCAT(DISTINCT(g.group_id))'),
            'callback'   => HM_DataGrid_Column_Callback_GroupsCache::create($this, ['{{groups}}', true]),
            'filter'     => HM_DataGrid_Column_Filter_Groups::create($this, array('tableName' => 'g'))
        ));

        $this->addColumn('time_registered', array(
            'title'      => _('Дата начала обучения'),
            'position'   => 5,
            'expression' => new HM_Db_Expr('subSelect.time_registered'),
            'callback'   => HM_Assign_DataGrid_Callback_UpdateDateBegin::create($this, ['{{time_registered}}']),
            'filter'     => HM_DataGrid_Column_Filter_DateSmart::create($this)
        ));

        $this->addColumn('time_ended_planned', array(
            'title'      => _('Плановая дата окончания'),
            'position'   => 6,
            'expression' => new HM_Db_Expr('subSelect.time_ended_planned'),
            'callback'   => HM_Assign_DataGrid_Callback_UpdateTimeEndedPlanned::create($this, ['{{time_ended_planned}}', '{{CID}}', '{{newcomer_id}}']),
            'filter'     => HM_DataGrid_Column_Filter_DateSmart::create($this)
        ));

        $this->addColumn('course', array(
            'title'      => _('Назначен на этот курс'),
            'position'   => 7,
            'expression' => new HM_Db_Expr('subSelect.CID'),
            'callback'   => HM_DataGrid_Column_Callback_UpdateGroupColumn::create($this, ['{{course}}', $this->options['courseId']])
        ));

        $this->addColumn('tags', array(
            'title'      => _('Метки'),
            'position'   => 8,
            'expression' => new HM_Db_Expr('t1.MID'),
            'callback'   => HM_DataGrid_Column_Callback_DisplayTags::create($this, ['{{tags}}', $this->serviceContainer->getService('TagRef')->getUserType()]),
            'filter'     => HM_DataGrid_Column_Filter_Tags::create($this),
            'color' => HM_DataGrid_Column::colorize('tags')
        ));

    }

    protected function initActions()
    {
        if (!$this->serviceContainer->getService('Acl')->inheritsRole(
            $this->serviceContainer->getService('User')->getCurrentUserRole(),
            array(HM_Role_Abstract_RoleModel::ROLE_TEACHER, HM_Role_Abstract_RoleModel::ROLE_TEACHER))) {

            /** @see HM_View_Helper_SvgIcon::svgIcon() */
            $actions = array(
                /*HM_DataGrid_Action_Edit::create(
                    $this,
                    $this->setTitle('Редактировать', 'edit'),
                    [
                        'module' => 'user',
                        'controller' => 'list',
                        'params' => ['MID'],
                    ]
                ),*/
                HM_DataGrid_Action_LoginAs::create(
                    $this,
                    $this->setTitle('Войти от имени пользователя', 'enter'), [
                        'url' => [
                            'module' => 'assign',
                            'controller' => 'student',
                            'action' => 'login-as'
                        ]
                    ]
                ),
//                HM_Assign_DataGrid_Action_SetPersonalDates::create(
//                    $this,
//                    $this->setTitle('Персонально настроить даты занятий', 'calendar'),
//                    ['params' => ['user_id']]
//                ),
                HM_Assign_DataGrid_Action_ChangeStudent::create(
                    $this,
                    $this->setTitle('Заменить участника', 'users'),
                    ['params' => ['MID']]
                ),
                HM_DataGrid_Action_SendMessage::create(
                    $this,
                    $this->setTitle('Отправить сообщение', 'say-bubble')
                ),
            );
            $this->addActions($actions);
        }
    }

    protected function initMassActions()
    {
        if ($this->serviceContainer->getService('Acl')->inheritsRole(
            $this->serviceContainer->getService('User')->getCurrentUserRole(),
            array(HM_Role_Abstract_RoleModel::ROLE_TEACHER, HM_Role_Abstract_RoleModel::ROLE_TEACHER))) {

            $massActions = array(
//                HM_Assign_DataGrid_MassAction_AssignCourses::create($this, _('Назначить на курсы'),
//                    array('courseId' => $this->options['courseId'])),
//                HM_Assign_DataGrid_MassAction_UnAssignCourses::create($this, _('Отменить назначение на курсы'),
//                    array('courseId' => $this->options['courseId'])),
//                HM_Assign_DataGrid_MassAction_ReAssignOnSession::create($this, _('Перенести на другую сессию'),
//                    array('courseId' => $this->options['courseId'])),
                HM_Assign_DataGrid_MassAction_GraduateStudents::create($this, _('Перевести в прошедшие обучение')),
                HM_DataGrid_MassAction_SendMessage::create($this, _('Отправить сообщение')),
            );
        } else {
            $massActions = array(
                HM_Assign_DataGrid_MassAction_AssignCourses::create($this, _('Назначить на курсы'),
                    array('courseId' => $this->options['courseId'])),
                HM_Assign_DataGrid_MassAction_UnAssignCourses::create($this, _('Отменить назначение на курсы'),
                    array('courseId' => $this->options['courseId'])),
                HM_Assign_DataGrid_MassAction_ReAssignOnSession::create($this, _('Перенести на другую сессию'),
                    array('courseId' => $this->options['courseId'])),
                HM_Assign_DataGrid_MassAction_GraduateStudents::create($this, _('Перевести в прошедшие обучение')),
                HM_DataGrid_MassAction_SendMessage::create($this, _('Отправить сообщение')),
            );
        }
        $this->addMassActions($massActions);
    }

    protected function initSwitcher()
    {
        if($this->serviceContainer->getService('Acl')->inheritsRole(
            $this->serviceContainer->getService('User')->getCurrentUserRole(),
            array(HM_Role_Abstract_RoleModel::ROLE_TEACHER, HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {

            $switcherOptions = [
                'label' => _('Показать всех'),
                'title' => _('Показать всех пользователей, в том числе неназначенных на данный курс'),
                'param' => self::SWITCHER_PARAM_DEFAULT,
                'modes' => [
                    Assign_StudentController::FILTER_LISTENERS_COURSE,
                    Assign_StudentController::FILTER_ALL,
                ],
            ];

            $this->addSwitcher($switcherOptions);
        }
    }

    protected function initClassRowConditions()
    {
        // Когда открыт список только слушателей, то они все будут без подсветки
        if ($this->switcher[0] == Assign_StudentController::FILTER_ALL) {
            $this->addClassRowCondition("'{{course}}' == {$this->options['courseId']}", "success");
        }
    }
}