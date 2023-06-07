<?php

/**
 * Датагрид для отображения тьюторов определённого курса
 *
 */
class HM_Assign_DataGrid_AssignTeacherByCourseDataGrid extends HM_DataGrid
{
    protected $baseTable = array('t1' => 'People');

    protected function getList()
    {
        $this->select
            ->joinLeft(['d'  => 'structure_of_organ'], 'd.mid = t1.MID', [])
            ->joinLeft(['d2' => 'structure_of_organ'], 'd.owner_soid = d2.soid', []);

        parent::getList();

        $this->select
            ->joinLeft(['s' => 'subjects'], 's.subid = sub.CID', [])
            ->joinLeft(['t2' => 'Teachers'], 't1.MID = t2.MID', [])
            ->where('( t1.blocked IS NULL OR t1.blocked = ?)', 0);

        $this->select->group([
            't1.MID',
            't1.LastName',
            't1.FirstName',
            't1.Patronymic' ,
            'sub.CID']);
    }

    protected function setSwitcherRestrictions()
    {
        $subSelect = $this->userService->getSelect();
        $subSelect
            ->from(['Teachers'], ['MID', 'CID'] )
            ->joinInner(['s' => 'subjects'], 's.subid = Teachers.CID', [])
            ->where('Teachers.CID'.' = ?', $this->options['courseId']);

        if ($this->switcher[0] == Assign_TeacherController::FILTER_TEACHERS_COURSE)
            $this->select->joinInner(['sub' => $subSelect], 't1.MID = sub.MID', []);
        else
            $this->select->joinLeft(['sub' => $subSelect], 't1.MID = sub.MID', []);
    }

    protected function setRoleRestrictions()
    {
        if ($this->serviceContainer->getService('Acl')->checkRoles(HM_Responsibility_ResponsibilityModel::getResponsibilityRoles())) {
            $this->select = $this->serviceContainer->getService('Responsibility')->checkUsers($this->select, 'd');
        }
    }

    protected function initColumns()
    {
        $this->hiddenColumns([
            'MID'         => new HM_Db_Expr('t1.MID'),
            'notempty'    => new HM_Db_Expr('t1.notempty')
        ]);

        $this->addColumn('fio', [
            'title'      => _('ФИО'),
            'position'   => 1,
            'expression' => new HM_Db_Expr('t1.fio'),
            'decorator'  => HM_DataGrid_Column_Decorator_UserCardLink::create($this, ['userId' => '{{MID}}', 'userName' => '{{fio}}']),
            'callback'   => HM_DataGrid_Column_Callback_UpdateFio::create($this, ['{{MID}}', '{{fio}}']),
            'filter'     => HM_DataGrid_Column_Filter::create($this)
        ]);

        $this->addColumn('departments', [
            'title'      => _('Подразделение'),
            'position'   => 2,
            'expression' => new HM_Db_Expr('GROUP_CONCAT(DISTINCT d2.name)'),
            'filter'     => HM_DataGrid_Column_Filter::create($this)
        ]);

        $this->addColumn('positions', [
            'title'      => _('Должность'),
            'position'   => 3,
            'expression' => new HM_Db_Expr('GROUP_CONCAT(DISTINCT d.name)'),
            'filter'     => HM_DataGrid_Column_Filter::create($this)
        ]);

        $this->addColumn('course', [
            'title'      => _('Назначен на этот курс'),
            'position'   => 4,
            'expression' => new HM_Db_Expr('sub.CID'),
            'callback'   => HM_DataGrid_Column_Callback_UpdateGroupColumn::create($this, ['{{course}}', $this->options['courseId']])
        ]);
        
        $this->addColumn('courses', [
            'title'      => _('Курсы'),
            'position'   => 5,
            'expression' => new HM_Db_Expr('GROUP_CONCAT(DISTINCT(s.subid))'),
            'callback'   => HM_DataGrid_Column_Callback_CoursesCache::create($this, ['{{courses}}']),
            'filter'     => HM_Assign_DataGrid_Filter_Courses::create($this, 's.subid'),
            'color' => HM_DataGrid_Column::colorize('subjects')
        ]);
    }

    protected function initActions()
    {
        if (!$this->serviceContainer->getService('Acl')->inheritsRole(
            $this->serviceContainer->getService('User')->getCurrentUserRole(),
            array(HM_Role_Abstract_RoleModel::ROLE_TEACHER, HM_Role_Abstract_RoleModel::ROLE_TEACHER))) {

            $actions = array(
//            HM_DataGrid_Action_Calendar::create(
//                $this,
//                $this->setTitle('Календарь', 'calendar'),
//                ['switcher' => $this->switcher]
//            ),
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
        if (!$this->serviceContainer->getService('Acl')->inheritsRole(
            $this->serviceContainer->getService('User')->getCurrentUserRole(),
            array(HM_Role_Abstract_RoleModel::ROLE_TEACHER, HM_Role_Abstract_RoleModel::ROLE_TEACHER))) {

            $massActions = array(
                HM_Assign_DataGrid_MassAction_AssignTeacherAtCourses::create($this, _('Назначить тьюторов на курс'), ['courseId' => $this->options['courseId']]),
                HM_Assign_DataGrid_MassAction_UnAssignTeacherFromCourses::create($this, _('Отменить назначение тьюторов'), ['courseId' => $this->options['courseId']]),
                HM_DataGrid_MassAction_SendMessage::create($this, _('Отправить сообщение')),
            );
            $this->addMassActions($massActions);
        }
    }

    protected function initSwitcher()
    {
        if ($this->serviceContainer->getService('Acl')->inheritsRole(
            $this->serviceContainer->getService('User')->getCurrentUserRole(),
            array(HM_Role_Abstract_RoleModel::ROLE_TEACHER, HM_Role_Abstract_RoleModel::ROLE_DEAN))) {

            $switcherOptions = [
                'label' => _('Показать всех'),
                'title' => _('Показать всех пользователей, в том числе неназначенных на данный курс'),
                'param' => self::SWITCHER_PARAM_DEFAULT,
                'modes' => [
                    Assign_TeacherController::FILTER_TEACHERS_COURSE,
                    Assign_TeacherController::FILTER_ALL,
                ],
            ];

            $this->addSwitcher($switcherOptions);
        }
    }

    protected function initClassRowConditions()
    {
        // Когда открыт список только тьюторов курса, то они все будут без подсветки
        if ($this->switcher[0] == Assign_TeacherController::FILTER_ALL) {

            $this->addClassRowCondition("strlen('{{courses}}') > 0", "success");
        }
    }
}