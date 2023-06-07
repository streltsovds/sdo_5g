<?php

/**
 * Датагрид для отображения всех слушателей всех курсов
 *
 */
class HM_Assign_DataGrid_AssignStudentDataGrid extends HM_DataGrid
{
    protected $baseTable = array('t1' => 'People');

    protected function getList()
    {
        $this->select
            ->joinLeft(array('d'  => 'structure_of_organ'), 'd.mid          = t1.MID',         array())
            ->joinLeft(array('d2' => 'structure_of_organ'), 'd.owner_soid   = d2.soid',        array())
            ->joinLeft(array('g'  => 'study_groups_users'), 'g.user_id      = t1.MID',         array())
            ->joinLeft(array('pu' => 'programm_users'    ), 'pu.user_id     = t1.MID',         array())
            ->joinLeft(array('pr' => 'programm'          ), 'pu.programm_id = pr.programm_id', array());

        $this->select->group(array(
            't1.MID',
            't1.LastName',
            't1.FirstName',
            't1.Patronymic'));

        parent::getList();
    }

    protected function setSwitcherRestrictions()
    {
        if ($this->switcher[0] == Assign_StudentController::FILTER_LISTENERS_COURSE ||
            $this->switcher[0] == Assign_StudentController::FILTER_LISTENERS) {
            $this->select->joinInner(array('t2' => 'Students'), 't1.MID = t2.'.'MID', array());
        } else {
            $this->select->joinLeft(array('t2' => 'Students'), 't1.MID = t2.'.'MID', array());
            $this->select
                ->where("(t1.blocked IS NULL")
                ->orWhere("t1.blocked = 0)");
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
        $s = $this->select->__toString();
    }

    protected function initColumns()
    {
        $this->hiddenColumns(array(
            'MID'         => new HM_Db_Expr('t1.MID'),
            'user_id'     => new HM_Db_Expr('t1.MID'),
            'notempty'    => new HM_Db_Expr('t1.notempty'),
            'programms'   => new HM_Db_Expr('GROUP_CONCAT(DISTINCT pu.programm_id)'),
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
        ));

        $this->addColumn('groups', array(
            'title'      => _('Учебные группы'),
            'position'   => 4,
            'expression' => new HM_Db_Expr('GROUP_CONCAT(DISTINCT(g.group_id))'),
            'callback'   => HM_DataGrid_Column_Callback_GroupsCache::create($this, ['{{groups}}', true]),
            'filter'     => HM_DataGrid_Column_Filter_Groups::create($this, array('tableName' => 'g')),
            'color' => HM_DataGrid_Column::colorize('groups')
        ));

        $this->addColumn('courses', array(
            'title'      => _('Курсы'),
            'position'   => 5,
            'expression' => new HM_Db_Expr('GROUP_CONCAT(DISTINCT t2.CID)'),
            'callback'   => HM_DataGrid_Column_Callback_CoursesCache::create($this, ['{{courses}}']),
            'filter'     => HM_Assign_DataGrid_Filter_Courses::create($this, 't2.CID'),
            'color' => HM_DataGrid_Column::colorize('subjects')
        ));

        $this->addColumn('tags', array(
            'title'      => _('Метки'),
            'position'   => 6,
            'expression' => new HM_Db_Expr('t1.MID'),
            'callback'   => HM_DataGrid_Column_Callback_DisplayTags::create($this, ['{{tags}}', $this->serviceContainer->getService('TagRef')->getUserType()]),
            'filter'     => HM_DataGrid_Column_Filter_Tags::create($this),
            'color' => HM_DataGrid_Column::colorize('tags')
        ));
    }

    protected function initActions()
    {
        /** @see HM_View_Helper_SvgIcon::svgIcon() */
        $actions = array(
//            HM_Assign_DataGrid_Action_SetPersonalDates::create(
//                $this,
//                $this->setTitle('Персонально настроить даты занятий', 'calendar'),
//                ['params' => ['user_id']]
//            ),
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
            HM_DataGrid_Action_SendMessage::create(
                $this,
                $this->setTitle('Отправить сообщение', 'say-bubble')
            ),
            /*HM_DataGrid_Action_Edit::create(
                $this,
                $this->setTitle('Редактировать', 'edit'),
                [
                    'module' => 'user',
                    'controller' => 'list',
                    'params' => ['MID']
                ])*/
        );
        $this->addActions($actions);
    }

    protected function initMassActions()
    {
        $massActions = array(
            HM_DataGrid_MassAction_SendMessage::create($this, _('Отправить сообщение')),
            //HM_Assign_DataGrid_MassAction_AssignProgram::create($this, _('Hазначить слушателей на программы мероприятий'), array('primary' => 'MID')),
            //HM_Assign_DataGrid_MassAction_UnAssignProgram::create($this, _('Удалить слушателей c программ мероприятий'), array('primary' => 'MID'))
            HM_Assign_DataGrid_MassAction_AssignCourses::create($this, _('Назначить на курсы'), array('postMassIdsColumn' => 'MID', 'multiple' => true)),
            HM_Assign_DataGrid_MassAction_UnAssignCourses::create($this, _('Отменить назначение на курсы'), array('postMassIdsColumn' => 'MID'))
        );
        $this->addMassActions($massActions);
    }

    protected function initSwitcher()
    {
        if($this->serviceContainer->getService('Acl')->inheritsRole(
            $this->serviceContainer->getService('User')->getCurrentUserRole(),
            HM_Role_Abstract_RoleModel::ROLE_DEAN)) {

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
        // Когда открыт список только слушателей курса, то они все будут без подсветки
        if ($this->switcher[0] == Assign_StudentController::FILTER_ALL) {
            $this->addClassRowCondition("'{{courses}}' != '' ", "success");
        }
    }
}