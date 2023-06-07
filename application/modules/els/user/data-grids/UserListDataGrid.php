<?php

/**
 * Датагрид для отображения всех пользователей
 *
 */
class HM_User_DataGrid_UserListDataGrid extends HM_DataGrid
{
    protected $baseTable = ['t1' => 'People'];
    protected $subSelect = null;

    protected function getList()
    {
        $this->subSelect = $this->getServiceContainer()->getService('Absence')->getSelect();
        $this->subSelect->from(['a' => 'absence'], [
            'a.absence_id',
            'a.user_id',
            'is_absent' => new Zend_Db_Expr('COUNT(DISTINCT a.absence_id)')
        ]);

        $this->subSelect->where('(DATEDIFF(day, NOW(), a.absence_begin) <= 0) AND (DATEDIFF(day, NOW(), a.absence_end)>= 0)');
        $this->subSelect->group(['a.absence_id', 'a.user_id', 'a.absence_begin', 'a.absence_end']);

        $this->select
            ->joinLeft(['d' => 'structure_of_organ'], 'd.mid = t1.MID', [])
            ->joinLeft(['d1' => 'structure_of_organ'], 'd.owner_soid = d1.soid', [])
            ->joinLeft(['sub' => $this->subSelect], 'sub.user_id = t1.MID', [])
            ->group([
                't1.MID',
                't1.LastName',
                't1.FirstName',
                't1.Patronymic',
                't1.Login',
                't1.Email',
                't1.email_confirmed',
                't1.Registered',
                't1.Password',
                't1.blocked',
                't1.isAD',
                'd.soid',
                'd1.soid',
                'd1.name',
                'd.name',
                'd.is_manager',
                't1.duplicate_of',
                'sub.is_absent',
                't1.push_token'
            ]);

        parent::getList();
    }

    protected function setSwitcherRestrictions()
    {
    }

    protected function setRoleRestrictions()
    {
    }

    protected function initColumns()
    {
        $this->hiddenColumns([
            'MID'             => new HM_Db_Expr('t1.MID'),
            'notempty'        => new HM_Db_Expr('t1.notempty'),
            'soid'            => new HM_Db_Expr('d.soid'),
            'owner_soid'      => new HM_Db_Expr('d1.soid'),
            'is_manager'      => new HM_Db_Expr('d.is_manager'),
            'email_confirmed' => new HM_Db_Expr('t1.email_confirmed'),
            'is_absent'       => new HM_Db_Expr("CASE WHEN sub.is_absent IS NULL THEN 0 ELSE sub.is_absent END"),
            'ldap'            => new HM_Db_Expr('t1.isAD'),
            'duplicate_of'    => new HM_Db_Expr('t1.duplicate_of')
        ]);

        $this->addColumn('fio', [
            'title'      => _('ФИО'),
            'position'   => 1,
            'expression' => new HM_Db_Expr('t1.fio'),
            'decorator'  => HM_DataGrid_Column_Decorator_UserCardLink::create($this, ['userId' => '{{MID}}', 'userName' => '{{fio}}']),
            'callback'   => HM_DataGrid_Column_Callback_UpdateFio::create($this, ['{{MID}}', '{{fio}}']),
            'filter'     => HM_DataGrid_Column_Filter::create($this)
        ]);

        $this->addColumn('department', [
            'title'      => _('Подразделение'),
            'position'   => 2,
            'expression' => new HM_Db_Expr('d1.name'),
            'callback'   => HM_DataGrid_Column_Callback_PositionCache::create($this, ['{{department}}', '{{owner_soid}}', HM_Orgstructure_OrgstructureModel::TYPE_POSITION, '{{is_manager}}']),
            'filter'     => HM_DataGrid_Column_Filter::create($this)
        ]);

        $this->addColumn('position', [
            'title'      => _('Должность'),
            'position'   => 3,
            'expression' => new HM_Db_Expr('d.name'),
            'callback'   => HM_DataGrid_Column_Callback_PositionCache::create($this, ['{{position}}', '{{soid}}', HM_Orgstructure_OrgstructureModel::TYPE_POSITION, '{{is_manager}}']),
            'filter'     => HM_DataGrid_Column_Filter::create($this)
        ]);

        $this->addColumn('login', [
            'title'      => _('Логин'),
            'position'   => 3,
            'expression' => new HM_Db_Expr('t1.Login'),
            'filter'     => HM_DataGrid_Column_Filter::create($this)
        ]);

        $this->addColumn('email', [
            'title'      => _('Email'),
            'position'   => 4,
            'expression' => new HM_Db_Expr('t1.Email'),
            'callback'   => HM_User_DataGrid_Callback_UpdateEmail::create($this, ['{{email}}', '{{email_confirmed}}']),
            'filter'     => HM_DataGrid_Column_Filter::create($this)
        ]);

        $this->addColumn('roles', [
            'title'      => _('Роли'),
            'position'   => 5,
            'expression' => new HM_Db_Expr('1'),
            'callback'   => HM_User_DataGrid_Callback_UpdateRole::create($this, ['{{MID}}']),
            'filter'     => HM_DataGrid_Column_Filter_Roles::create($this),
            'color'      => HM_DataGrid_Column::colorize('roles')
        ]);

        $this->addColumn('Registered', [
            'title'      => _('Дата регистрации'),
            'position'   => 6,
            'expression' => new HM_Db_Expr('t1.Registered'),
            'callback'   => HM_User_DataGrid_Callback_UpdateDate::create($this, ['{{Registered}}']),
            'filter'     => HM_DataGrid_Column_Filter_DateSmart::create($this)
        ]);

        $this->addColumn('status', [
            'title'      => _('Статус'),
            'position'   => 7,
            'expression' => new HM_Db_Expr('t1.blocked'),
            'callback'   => HM_User_DataGrid_Callback_UpdateStatus::create($this, ['{{status}}']),
            'filter'     => HM_User_DataGrid_Filter_Status::create($this, ['values' => [
                0 => _('Все'),
                1 => _('Активный'),
                2 => _('Заблокирован')]
            ])
        ]);

        $this->addColumn('tags', [
            'title'      => _('Метки'),
            'position'   => 8,
            'expression' => new HM_Db_Expr('t1.MID'),
            'callback'   => HM_DataGrid_Column_Callback_DisplayTags::create($this, ['{{tags}}', $this->serviceContainer->getService('TagRef')->getUserType()]),
            'filter'     => HM_DataGrid_Column_Filter_Tags::create($this),
            'color'      => HM_DataGrid_Column::colorize('tags')
        ]);

        $this->addColumn('push_token', [
            'title'      => _('Приложение'),
            'position'   => 9,
            'expression' => new HM_Db_Expr('t1.push_token'),
            'callback'   => HM_User_DataGrid_Callback_UpdateToken::create($this, ['{{push_token}}']),
            'filter'     => HM_DataGrid_Column_Filter::create($this)
        ]);
    }

    protected function initActions()
    {
        $actions = [
            HM_DataGrid_Action_Edit::create(
                $this,
                $this->setTitle('Редактировать', 'edit'),
                [
                    'module' => 'user',
                    'controller' => 'list',
                    'params' => ['MID']
                ]
            ),
            HM_DataGrid_Action_Delete::create(
                $this,
                $this->setTitle('Удалить', 'delete'),
                [
                    'module' => 'user',
                    'controller' => 'list',
                    'params' => ['MID']
                ]
            ),
//            HM_User_DataGrid_Action_DuplicateMerge::create(
//                $this,
//                $this->setTitle('Объединение дубликатов', 'calendar'),
//                ['params' => ['MID']]
//            ),
            HM_DataGrid_Action_SendMessage::create(
                $this,
                $this->setTitle('Отправить сообщение', 'say-bubble')
            ),
            HM_DataGrid_Action_LoginAs::create(
                $this,
                $this->setTitle('Войти от имени пользователя', 'enter'), [
                    'url' => [
                        'module' => 'user',
                        'controller' => 'list',
                        'action' => 'login-as'
                    ]
                ]
            ),
        ];
        $this->addActions($actions);
    }

    protected function initMassActions()
    {
        $massActions = [
            HM_DataGrid_MassAction::create($this, _('Выберите действие'), ['url' => ['action' => 'index']]),
            HM_User_DataGrid_MassAction_AssignRole::create($this, _('Назначить роль'), ['multiple' => true]),
            HM_User_DataGrid_MassAction_UnassignRole::create($this, _('Отменить назначение роли')),
            HM_User_DataGrid_MassAction_AssignTag::create($this, _('Назначить метку'), ['AllowNewItems' => true]),
            HM_User_DataGrid_MassAction_UnassignTag::create($this, _('Отменить назначение меток')),
            HM_DataGrid_MassAction_SendMessage::create($this, _('Отправить сообщение'))
        ];

        if ($this->getServiceContainer()->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_ADMIN) {
            $massActions[] = HM_User_DataGrid_MassAction_BlockUser::create($this, _('Заблокировать'));
            $massActions[] = HM_User_DataGrid_MassAction_UnblockUser::create($this, _('Разблокировать'));
            $massActions[] = HM_User_DataGrid_MassAction_SendConfirmation::create($this, _('Выслать письмо для подтверждения Email-адреса'));
            $massActions[] = HM_User_DataGrid_MassAction_SetConfirmed::create($this, _('Подтвердить Email-адрес'));
            $massActions[] = HM_User_DataGrid_MassAction_SetPassword::create($this, _('Назначить пароль'));
            $massActions[] = HM_User_DataGrid_MassAction_Delete::create($this, _('Удалить'));
        }
        $this->addMassActions($massActions);
    }

    protected function initSwitcher()
    {
    }

    protected function initClassRowConditions()
    {
        $this->addClassRowCondition("{{is_absent}} > 0",'highlighted');
        $this->addClassRowCondition("{{duplicate_of}} > 0",'highlighted');
    }

    protected function initActionsCallback()
    {
        $this->addActionsCallback([
            'function' => [$this, 'updateActions'],
            'params'   => ['{{ldap}}', '{{duplicate_of}}']
        ]);
    }
}