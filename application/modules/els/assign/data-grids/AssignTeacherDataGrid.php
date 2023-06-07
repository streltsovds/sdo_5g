<?php

/**
 * Датагрид для отображения всех тьюторов
 *
 */
class HM_Assign_DataGrid_AssignTeacherDataGrid extends HM_DataGrid
{
    protected $baseTable = ['t1' => 'People'];

    protected function getList()
    {
        parent::getList();

        $this->select
            ->joinLeft(['s' => 'subjects'], 's.subid = t2.CID', [])
            ->joinLeft(['classifiers_links'], 's.subid = classifiers_links.item_id AND classifiers_links.type = 0',  [])
            ->joinLeft(['d' => 'structure_of_organ'], 'd.mid = t1.MID', [] )
            ->joinLeft(['d2' => 'structure_of_organ'], 'd.owner_soid = d2.soid', [])
            ->group(['t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic']);
    }

    protected function setSwitcherRestrictions()
    {
        if ($this->switcher[0] == Assign_TeacherController::FILTER_TEACHERS_COURSE ||
            $this->switcher[0] == Assign_TeacherController::FILTER_TEACHERS) {
            $this->select->joinInner(['t2' => 'Teachers'], 't1.MID = t2.MID', []);
        } else {
            $this->select->joinLeft(['t2' => 'Teachers'], 't1.MID = t2.MID', []);
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

        $this->addColumn('classifiers', [
            'title'      => _('Классификация'),
            'position'   => 5,
            'expression' => new HM_Db_Expr('GROUP_CONCAT(DISTINCT(classifiers_links.classifier_id))'),
            'callback'   => HM_DataGrid_Column_Callback_ClassifiersCache::create($this, ['{{classifiers}}']),
            'filter'     => HM_DataGrid_Column_Filter::create($this),
            'color' => HM_DataGrid_Column::colorize('classifiers')
        ]);

        $this->addColumn('courses', [
            'title'      => _('Курсы'),
            'position'   => 6,
            'expression' => new HM_Db_Expr('GROUP_CONCAT(DISTINCT(s.subid))'),
            'callback'   => HM_DataGrid_Column_Callback_CoursesCache::create($this, ['{{courses}}']),
            'filter'     => HM_Assign_DataGrid_Filter_Courses::create($this, 's.subid'),
            'color' => HM_DataGrid_Column::colorize('subjects')
        ]);
    }

    protected function initActions()
    {
        $actions = array(
            HM_DataGrid_Action_Calendar::create(
                $this,
                $this->setTitle('Календарь', 'calendar'),
                ['switcher' => $this->switcher]
            ),
            HM_DataGrid_Action_SendMessage::create(
                $this,
                $this->setTitle('Отправить сообщение', 'say-bubble')
            ),
        );
        $this->addActions($actions);
    }

    protected function initMassActions()
    {
        $massActions = array(
            HM_Assign_DataGrid_MassAction_AssignTeacherAtCourses::create($this, _('Назначить тьюторов на курсы'), ['postMassIdsColumn' => 'MID', 'multiple' => true]),
            HM_Assign_DataGrid_MassAction_UnAssignTeacherFromCourses::create($this, _('Отменить назначения тьюторов'), ['postMassIdsColumn' => 'MID']),
            HM_Assign_DataGrid_MassAction_Calendar::create($this, _('Общий календарь'), ['postMassIdsColumn' => 'MID']),
            HM_DataGrid_MassAction_SendMessage::create($this, _('Отправить сообщение'))
        );
        $this->addMassActions($massActions);
    }

    protected function initSwitcher()
    {
        if ($this->serviceContainer->getService('Acl')->inheritsRole(
            $this->serviceContainer->getService('User')->getCurrentUserRole(),
            HM_Role_Abstract_RoleModel::ROLE_DEAN)) {

            $switcherOptions = [
                'label' => _('Показать всех'),
                'title' => _('Показать всех пользователей, в том числе неназначенных на данный курс'),
                'param' => self::SWITCHER_PARAM_DEFAULT,
                'modes' => [
                    Assign_TeacherController::FILTER_TEACHERS,
                    Assign_TeacherController::FILTER_ALL,
                ],
            ];

            $this->addSwitcher($switcherOptions);
        }
    }

    protected function initClassRowConditions()
    {
        // Когда открыт список только тьюторов, то они все будут без подсветки
        if ($this->switcher[0] == Assign_TeacherController::FILTER_ALL) {

            $this->addClassRowCondition("'{{courses}}' != '' ", "success");
        }
    }
}