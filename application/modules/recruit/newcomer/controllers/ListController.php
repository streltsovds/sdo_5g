<?php
class Newcomer_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $_newcomersCache    = null;
    protected $_atCategoriesCache = array();

    protected $_isLaborSafety = null;

    public function init()
    {
        $this->_defaultService = $this->getService('RecruitNewcomer');
        $this->_atCategoriesCache = $this->getService('AtCategory')->fetchAll()->getList('category_id', 'name');
        $userService           = $this->getService('User');          
        $currentUserId = $userService->getCurrentUserId();

        $this->_isLaborSafety = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL));

    if (
        $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) &&
        $this->getService('User')->isRoleExists($currentUserId, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)
    ) {
        // если потенциально имеет роль - переключаем автоматом
        $this->view->switchRole = HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR;

    }

        return parent::init();
    }

    public function indexAction()
    {
        $this->view->setHeader(_('Сессии адаптации'));
        $userService           = $this->getService('User');          
        $currentUserId = $userService->getCurrentUserId();
        
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'position_date_DESC');
        }
        
        if ($this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
        ))) {
            $default = Zend_Registry::get('session_namespace_default');
            $page = sprintf('%s-%s-%s', 'newcomer', 'list', 'index');
            $filter = $this->_request->getParam("filter");
            if (empty($filter) && empty($default->grid[$page]['grid']['filters'])){
                $default->grid[$page]['grid']['filters']['recruiters'] = $this->getService('User')->getCurrentUser()->LastName;
            }
        }

        $this->_request->setParam('no-restore-state', 'true');

        $isRecruiter = $this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_HR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
        ));

        /* @todo: когда грид стабилизируется - выкинуть все hidden-поля */
        $select = $this->getService('RecruitNewcomer')->getSelect();
        $select->from(
            array(
                'rn' => 'recruit_newcomers'
            ),
            array(
                'MID' => 'p.MID',
                'user_id' => 'p.MID',
                'sop.state_of_process_id',
                'rn.newcomer_id',
                'workflow_id' => 'rn.newcomer_id',
                'state' => 'rn.state',
                'rn_name' => 'rn.name',
                'welcome_training' => 'rn.welcome_training',
                'rn.department_path',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'manager_id'  => 'rn.manager_id',
                'manager'  => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p3.LastName, ' ') , p3.FirstName), ' '), p3.Patronymic)"),
                'evaluation_user_position'  => 'so2.name',
                'evaluation_user' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p2.LastName, ' ') , p2.FirstName), ' '), p2.Patronymic)"),
                'recruiters' => new Zend_Db_Expr("(
                    SELECT 
                      GROUP_CONCAT(CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)) 
                    FROM 
                      recruit_newcomer_recruiters rnr
                    INNER JOIN recruiters r ON (r.recruiter_id = rnr.recruiter_id)
                    INNER JOIN People p ON (r.user_id = p.MID)
                    WHERE 
                      rnr.newcomer_id = rn.newcomer_id
                )"),
                'sp.position_date',
                'eval_MID' => 'p2.MID',
                'total_kpi' => new Zend_Db_Expr("round(su.total_kpi*100,0)"),
                'rn.state_change_date',
                'debt' => new Zend_Db_Expr("
                    CASE WHEN(DATEDIFF(day, NOW(), sopd.end_date_planned) < 0) AND rn.state != " . HM_Recruit_Newcomer_NewcomerModel::STATE_CLOSED . " THEN 1 ELSE 0 END "),
                'status' => new Zend_Db_Expr("
                    CASE WHEN rn.state != 2 THEN rn.state ELSE CASE WHEN (rn.state = 2 AND rn.result = 1) THEN 2 ELSE CASE WHEN (rn.state = 2 AND (rn.result IN (-1,-2,-3) OR rn.result IS NULL)) THEN 3 END END END"),
                'status_id' => 'rn.status',
                'result' => 'rn.result',               
                'final_comment' => 'rn.final_comment',               
                'courses' => 'GROUP_CONCAT(DISTINCT subj.subid)',
                'position_name' => 'sp.name',
                'position_id' => 'sp.soid',
                'position_type' => 'sp.type',
                'position_is_manager' => 'sp.is_manager',
                'category' => 'ac.category_id',
            )
        );

        // @todo: руководитель может быть в другом подразделении;
        // нужно кэшировать при создании сессии адаптации
        $select
            ->joinLeft(array('sop' => 'state_of_process'), 'rn.newcomer_id = sop.item_id AND sop.process_type = '.HM_Process_ProcessModel::PROCESS_PROGRAMM_ADAPTING, array())
            ->joinLeft(array('sopd' => 'state_of_process_data'), 'sop.current_state = sopd.state AND sop.state_of_process_id = sopd.state_of_process_id', array())
            ->joinLeft(array('sp' => 'structure_of_organ'), 'sp.soid = rn.position_id', array())
            ->joinLeft(array('ap' => 'at_profiles'), 'sp.profile_id = ap.profile_id', array())
            ->joinLeft(array('ac' => 'at_categories'), 'ac.category_id = ap.category_id', array())
            ->joinLeft(array('so2' => 'structure_of_organ'), 'so2.mid = rn.evaluation_user_id', array())
            ->joinLeft(array('p' => 'People'), 'p.MID = rn.user_id', array())
            ->joinLeft(array('p2' => 'People'), 'p2.MID = rn.evaluation_user_id', array())
            ->joinLeft(array('p3' => 'People'), 'p3.MID = rn.manager_id', array())
            ->joinLeft(array('su' => 'at_session_users'), 'su.newcomer_id = rn.newcomer_id', array())
            ->joinLeft(array('s' => 'Students'), 's.newcomer_id = rn.newcomer_id', array())
            ->joinLeft(array('subj' => 'subjects'), 's.CID = subj.subid', array())
            ->group(
                array(
                    'p.MID',
                    'p.LastName',
                    'p.FirstName',
                    'p.Patronymic',
                    'sop.state_of_process_id',
                    'rn.newcomer_id',
                    'rn.name',
                    'rn.status',
                    'rn.state',
                    'rn.state_change_date',
                    'rn.result',
                    'rn.final_comment',
                    'rn.manager_id',
                    'rn.welcome_training',
                    'rn.department_path',
                    'so2.mid',
                    'so2.name',
                    'p2.MID',
                    'p2.LastName',
                    'p2.FirstName',
                    'p2.Patronymic',
                    'p3.MID',
                    'p3.LastName',
                    'p3.FirstName',
                    'p3.Patronymic',
                    'sopd.end_date_planned',
                    'su.total_kpi',
                    'sp.name',
                    'sp.soid',
                    'sp.type',
                    'sp.is_manager',
                      'sp.position_date',
                    'ac.category_id'
                )
            );

        $currentUser = $this->getService('User')->getCurrentUser();

        // в зависимости от роли пользователя показываем разные учётные записи
        switch ($currentUser->role) {

            case HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY:
            case HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL:
                // показываем всех
                break;

            case HM_Role_Abstract_RoleModel::ROLE_DEAN:
            case HM_Role_Abstract_RoleModel::ROLE_HR:

                $select->joinLeft(array('rnr' => 'recruit_newcomer_recruiters'), 'rnr.newcomer_id = rn.newcomer_id', array());
                $select->joinLeft(array('r' => 'recruiters'), 'r.recruiter_id = rnr.recruiter_id', array());

                break;

            case HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL:
            case HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL:

                $select->joinInner(array('rnr' => 'recruit_newcomer_recruiters'), 'rnr.newcomer_id = rn.newcomer_id', array());
                $select->joinInner(array('r' => 'recruiters'), $this->quoteInto('r.recruiter_id = rnr.recruiter_id AND r.user_id = ?', $currentUser->MID), array());

                break;

            case HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR:

                $userPosition = $this->getOne($this->getService('Orgstructure')->fetchAll($this->quoteInto('mid = ?', $currentUser->MID)));
                $parentPosition = $this->getOne($this->getService('Orgstructure')->find($userPosition->owner_soid));

                if ($userPosition) {
    
                    $subSelect = $this->getService('Orgstructure')->getSelect()
                        ->from('structure_of_organ', array('soid'))
                        ->where('lft > ?', $parentPosition->lft)
                        ->where('rgt < ?', $parentPosition->rgt);
                    
                    $select->where("(rn.evaluation_user_id = {$currentUserId} OR rn.position_id IN (?))", $subSelect);
                } else {
                    $select->where('1 = 0');
                }
            break;

            default:
                $select->where('1 = 0');
        }

        if ($this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ))) {
            // все по области ответственности, даже не назначенные
            $soid = $this->getService('Responsibility')->get();
            $responsibilityPosition = $this->getOne($this->getService('Orgstructure')->find($soid));
            if ($responsibilityPosition) {
                $subSelect = $this->getService('Orgstructure')->getSelect()
                    ->from('structure_of_organ', array('soid'))
                    ->where('lft > ?', $responsibilityPosition->lft)
                    ->where('rgt < ?', $responsibilityPosition->rgt);
                $select->where("sp.soid IN (?)", $subSelect);
            } else {
                $select->where('1 = 0');
            }
        }

        $grid = $this->getGrid($select, array(
            'position_name' => array('hidden' => true),
            'position_id' => array('hidden' => true),
            'position_type' => array('hidden' => true),
            'position_is_manager' => array('hidden' => true),
            'MID' => array('hidden' => true),
            'eval_MID' => array('hidden' => true),
            'evaluation_user_position' => array('hidden' => true),
            'user_id' => array('hidden' => true),
            'newcomer_id' => array('hidden' => true),
            'manager_id' => array('hidden' => true),
            'state_of_process_id' => array('hidden' => true),
            'params' => array('hidden' => true),
            'final_comment' => array('hidden' => true),
            'category' => array('hidden' => true),
            'state' => array(
                'title' => _('Тек. этап'),
                'callback' => array(
                    'function' => array($this, 'updateCurrentState'),
                    'params' => array('{{state}}', '{{debt}}')
                ),
                'hidden' => true
            ),
            'workflow_id' => array(
                'title' => _('Бизнес-процесс'), // бизнес процесс
                'callback' => array(
                    'function' => array($this, 'printWorkflow'),
                    'params' => array('{{workflow_id}}'),
                ),
                'sortable'=>false,
                'position' => 1
             ),
            'rn_name' => array(
                'title' => _('Сессия адаптации (должность)'),
//                'decorator' =>  $this->view->cardLink(
//                        $this->view->url(
//                            array(
//                                'module' => 'newcomer',
//                                'controller' => 'report',
//                                'action' => 'index',
//                                'newcomer_id' => ''
//                            ), null, true
//                        ) . '{{newcomer_id}}') . ' <a href="' .
//                    $this->view->url(
//                        array(
//                            'module' => 'newcomer',
//                            'controller' => 'report',
//                            'action' => 'index',
//                            'newcomer_id' => '',
//                        ), null, true
//                    ) . '{{newcomer_id}}' . '">' . '{{rn_name}}</a>',
                'position' => 2
            ),
            'fio' => array(
                'title' => _('Пользователь'),
                'decorator' =>  $this->view->cardLink(
                        $this->view->url(
                            array(
                                'module' => 'user',
                                'controller' => 'list',
                                'action' => 'view',
                                'gridmod' => null,
                                'baseUrl' => '',
                                'user_id' => ''
                            ), null, true
                        ) . '{{MID}}') . ' <a href="' .
                    $this->view->url(
                        array(
                            'module' => 'user',
                            'controller' => 'edit',
                            'action' => 'card',
                            'gridmod' => null,
                            'baseUrl' => '',
                            'user_id' => ''
                        ), null, true
                    ) . '{{MID}}' . '">' . '{{fio}}</a>',
                'position' => 3
            ),
            'department_path' => array(
                'title' => _('Подразделение'),
                'callback' => array(
                    'function'=> array($this, 'updateDepartmentPath'),
                    'params'=> array('{{department_path}}')
                ),
                'position' => 5
            ),
//            'category' => array(
//                'title' => _('Категория'),
//                'position' => 6
//            ),
            'manager' => array(
                'title' => _('Непосредственный руководитель'),
                'position' => 7
            ),
            'evaluation_user' => array(
                'title' => _('Куратор'),
                'position' => 8
            ),
            'recruiters' => array('hidden' => true),
//            'recruiters' => array(
//                'title' => _('Специалист по подбору'),
//                'hidden' => true,
////                 'callback' => array(
////                     'function' => array($this, 'updateContactUserId'),
////                     'params' => array('{{recruiters}}'),
////                 ),
//            ),
            'position_date' => array(
                'title' => _('Дата приёма на работу'),
                'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
                'position' => 9
            ),
            'trial_end_date' => array(
                'title' => _('Дата окончания испытательного срока'),
                'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
                'position' => 10
            ),
            'total_kpi' => array('hidden' => true),
//            'total_kpi' => array(
//                'title' => _('Итоговая оценка'),
//                'hidden' => true
//            ),
            'status_id' => array('hidden' => true),
            'state_change_date' => array('hidden' => true),
            'welcome_training' => array(
                'title' => _('Welcome-тренинг'),
                'callback' => array(
                    'function' => array($this, 'updateWelcomeTrainingColumn'),
                    'params' => array('{{welcome_training}}'),
                ),
                'position' => 11
            ),
            'status' => array(
                'title' => _('Статус'),
                'position' => 12
            ),
            'debt' => array('hidden' => true),
//            'debt' => array(
//                'title' => _('Задолженность'),
//                'callback' => array(
//                    'function' => array($this, 'updateDebt'),
//                    'params' => array('{{debt}}')
//                )
//            ),
            'result' => array('hidden' => true),
//            'result' => array(
//                'title' => _('Итог'),
//                'callback' => array(
//                    'function' => array($this, 'updateResult'),
//                    'params' => array('{{result}}', '{{final_comment}}'),
//                ),
//                'hidden' => true
//            ),
            'courses' => array('hidden' => true),
//            'courses' => $isRecruiter ? array('hidden' => true) : array(
//                'title' => _('Назначенные курсы начального обучения'),
//                'callback' => array(
//                    'function' => array($this, 'coursesCache'),
//                    'params' => array('{{courses}}', $select)
//                ),
//            ),
        ),
        array(
            'rn_name' => null,
            'workflow_id' => array(
                'render' => 'process',
                'values' => Bvb_Grid_Filters_Render_Process::getStates('HM_Recruit_Newcomer_NewcomerModel', 'newcomer_id'),
                'field4state' => 'sop.current_state',
//                'field4state' => 'state',
            ),
            'fio' => null,
            'category' => array('values' => $this->_atCategoriesCache),
            'state' =>  array('values' => HM_Recruit_Newcomer_NewcomerModel::getStates()),
            'department_path' => null,
            'manager' => null,
            'evaluation_user' => null,
            'evaluation_user_position' => null,
//            'courses' => null,
// если понадобится фильтр по задолженности - сделать как в Reserve_ListController
//            'debt' => array('values' => HM_Recruit_Newcomer_NewcomerModel::getDebts()),
            'position_date' => array('render' => 'Date'),
            'trial_end_date' => array('render' => 'Date'),
            'welcome_training' => array('values' => array(0 => _('Нет'), 1 => _('Да'))),
            'status' => array('values' => HM_Recruit_Newcomer_NewcomerModel::getCustomStatuses()),
        ));

        $grid->updateColumn('rn_name',
            array(
                'callback' => array(
                    'function'=> array($this, 'updateName'),
                    'params'=> array('{{position_name}}', '{{position_id}}', '{{position_type}}', '{{position_is_manager}}', '{{newcomer_id}}')
                )
            )
        );

        $grid->updateColumn('status',
            array(
                'callback' => array(
                    'function'=> array($this, 'mapStatus'),
                    'params'=> array('{{status}}')
                )
            )
        );

        $grid->updateColumn('category',
            array(
                'callback' => array(
                    'function'=> array($this, 'updateCategory'),
                    'params'=> array('{{category}}')
                )
            )
        );

        $grid->updateColumn('evaluation_user',
            array(
                'callback' => array(
                    'function'=> array($this, 'updateEvalUser'),
                    'params'=> array($grid, '{{eval_MID}}', '{{evaluation_user}}')
                )
            )
        );

        $grid->updateColumn('manager',
            array(
                'callback' => array(
                    'function'=> array($this, 'updateManager'),
                    'params'=> array($grid, '{{manager_id}}', '{{manager}}')
                )
            )
        );

        $grid->addAction(array(
            'module' => 'newcomer',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('newcomer_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addAction(array(
            'baseUrl' => '',
            'module' => 'message',
            'controller' => 'send',
            'action' => 'index'
        ),
            array('MID'),
            _('Отправить сообщение')
        );

        $grid->addAction(array(
            'module' => 'newcomer',
            'controller' => 'list',
            'action' => 'evaluation'
        ),
            array('newcomer_id'),
            _('Назначить куратора')
        );

        $grid->addAction(array(
                'module' => 'newcomer',
                'controller' => 'calendar',
                'action' => 'index',
                'baseUrl' => 'recruit',
            ),
            array('newcomer_id'),
            _('Календарь мероприятий')
        );

        $grid->addAction(array(
                'module' => 'newcomer',
                'controller' => 'list',
                'action' => 'print-forms',
                'baseUrl' => 'recruit',
            ),
            array('newcomer_id'),
            _('Скачать шаблон плана адаптации')
        );

        $grid->addMassAction(
            array(
                'module' => 'newcomer',
                'controller' => 'list',
                'action' => 'assign-welcome-training',
            ),
            _('Назначить на welcome-тренинг'),
            _('Вы действительно хотите назначить на welcome-тренинг отмеченных пользователей?')
        );

        $grid->addMassAction(
            array(
                'module' => 'newcomer',
                'controller' => 'list',
                'action' => 'mark-welcome-training',
                'welcome-training' => 1
            ),
            _('Отметить участие в welcome-тренинге'),
            _('Вы действительно хотите отметить участие в welcome-тренинге отмеченных пользователей?')
        );

        $grid->addMassAction(
            array(
                'module' => 'newcomer',
                'controller' => 'list',
                'action' => 'mark-welcome-training',
                'welcome-training' => 0
            ),
            _('Снять отметку об участии в welcome-тренинге'),
            _('Вы действительно хотите снять отметку об участии в welcome-тренинге у отмеченных пользователей?')
        );

        $grid->addMassAction(
            array(
                'module' => 'newcomer',
                'controller' => 'list',
                'action' => 'word-welcome-training',
                'welcome-training' => 0
            ),
            _('Сформировать отчёт об участии в welcome-тренинге'),
            _('Вы действительно хотите сформировать отчёт об участии в welcome-тренинге отмеченных пользователей?')
        );

// этот функционал пока невостребован
// welcome-тренинг теперь не сессия, а просто отметка о прохождении
//        $grid->addMassAction(
//            array(
//                'module' => 'newcomer',
//                'controller' => 'list',
//                'action' => 'assign-sessions',
//            ),
//            _('Назначить сессии обучения'),
//            _('Вы действительно хотите назначить сессии обучения отмеченным пользователям?')
//        );
//
//        $grid->addMassAction(
//            array(
//                'module' => 'newcomer',
//                'controller' => 'list',
//                'action' => 'send-notifications-study',
//            ),
//            _('Отправить уведомление об обучении'),
//            _('Вы действительно хоите отправить уведомления о назначенных сессиях обучения отмеченным пользователям? Если пользователю еще не назначена конкретная сессия обучения, её необходимо пердварительно назначить. Продолжить?')
//        );
//
//        $grid->addMassAction(
//            array(
//                'module' => 'newcomer',
//                'controller' => 'list',
//                'action' => 'send-notifications-ot',
//            ),
//            _('Отправить уведомление специалистам по ОТ'),
//            _('Вы действительно хотите отправить уведомления специалистам по охране труда о необходимости проведения вводных инструктажей для отмеченных пользователей?')
//        );

        $grid->addMassAction(
            array(
                'module' => 'newcomer',
                'controller' => 'list',
                'action' => 'send-notifications-manager',
            ),
            _('Отправить уведомление руководителям'),
            _('Вы действительно хотите отправить уведомление руководителям отмеченных пользователей?')
        );

        $grid->addMassAction(
            array(
                'module' => 'newcomer',
                'controller' => 'list',
                'action' => 'send-notifications-curator',
            ),
            _('Отправить уведомление кураторам'),
            _('Вы действительно хотите отправить уведомление кураторам сессий адаптации отмеченных пользователей?')
        );

        $grid->addMassAction(
            array(
                'module' => 'newcomer',
                'controller' => 'list',
                'action' => 'send-notifications-worker',
            ),
            _('Отправить уведомление пользователям'),
            _('Вы действительно хотите отправить уведомление отмеченным пользователям?')
        );

        $grid->addMassAction(
            array(
                'module' => 'newcomer',
                'controller' => 'list',
                'action' => 'delete-by',
            ),
            _('Удалить сессии адаптации'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->setClassRowCondition("{{debt}} > 0",'highlighted');

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function mapStatus($status)
    {
        // Это кастомные константы из селекта, а не из HM_Recruit_Newcomer_NewcomerModel
        switch ($status) {
            case 0:
                return _('Не начата');
            case 1:
                return _('В процессе');
            case 2:
                return _('Завершена успешно');
            case 3:
                return _('Завершена неуспешно');
        }
    }

    public function updateCategory($categoryId)
    {
        return $this->_atCategoriesCache[$categoryId];
    }

    public function markWelcomeTrainingAction()
    {
        $welcomeTraining = $this->_getParam('welcome-training');
        $feedbackUserService = $this->getService('FeedbackUsers');

        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {

                $newcomers = $this->getService('RecruitNewcomer')->fetchAllDependence('User', array('newcomer_id IN (?)' => $ids));
                foreach ($newcomers as $newcomer) {

                    $this->getService('RecruitNewcomer')->update(
                        array(
                            'newcomer_id' => $newcomer->newcomer_id,
                            'welcome_training' => $welcomeTraining
                        )
                    );

                    // назначение на опрос и соотв.уведомление

                    if ($welcomeTraining && count($newcomer->user)) {

                        $user = $newcomer->user->current();

                        $feedbackUserService->assignUser(
                            $user->MID,
                            HM_Feedback_FeedbackModel::NEWCOMER_FEEDBACK_1
                        );

// вместо этого шлём типовое уведомление об опросе
//                        $messenger = Zend_Registry::get('serviceContainer')->getService('Messenger');
//                        $messenger->setOptions(
//                            HM_Messenger::TEMPLATE_ADAPTING_WELCOME,
//                            array(
//                                'fio' => $user->LastName . ' ' . $user->FirstName . ' ' . $user->Patronymic,
//                            ),
//                            'newcomer',
//                            $newcomer->newcomer_id
//                        );
//                        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                    }
                }
            }
        }

        $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => $welcomeTraining ? _('Отметки успешно установлены') : _('Отметки успешно сняты')
        ));
        $this->_redirectToIndex();
    }

    public function evaluationAction()
    {
        $this->view->setHeader(_('Назначение куратора'));

        $form = new HM_Form_EvaluationUser();
        $newcomerId = $this->_getParam('newcomer_id');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $newcomerId = $this->_getParam('newcomer_id');
            $evaluationUserId = $this->_getParam('evaluation_user_id');
            if ($form->isValid($request->getParams())) {
                if (is_array($evaluationUserId)) {
                    $evaluationUserId = $evaluationUserId[0];
                }
                if ($evaluationUserId) {
                    $this->getService('RecruitNewcomer')->update(
                        array(
                            'newcomer_id' => $newcomerId,
                            'evaluation_user_id' => $evaluationUserId,
                        )
                    );
                    $this->_redirectToIndex();
                }
            } else {
                if (isset($evaluationUserId[0]) && $evaluationUserId[0]) {
                    $evaluationUserId = $evaluationUserId[0];
                    if ($evaluationUser = $this->getService('User')->findOne($evaluationUserId)) {
                        $form->populate(array('evaluation_user_id' => array($evaluationUserId => $evaluationUser->getName())));
                    }
                }
            }
        } else {
            $newcomer = $this->getService('RecruitNewcomer')->findOne($newcomerId);
            if ($newcomer) {
                $data = array();

                $data['newcomer_id'] = $newcomer->id;
                if ($newcomer->evaluation_user_id) {
                    if ($evaluationUser = $this->getService('User')->findOne($newcomer->evaluation_user_id)) {
                        $data['evaluation_user_id'] = array($newcomer->evaluation_user_id => $evaluationUser->getName());
                    }
                }
                $form->populate($data);
            }
        }
        $this->view->form = $form;
    }

    /* DEPRECATED */
    public function createFromRecruitAction()
    {
        $vacancyId = $this->_getParam('vacancy_id');
        if ($vacancy = $this->getService('RecruitVacancy')->getOne($this->getService('RecruitVacancy')->findDependence(array('CandidateAssign', 'RecruiterAssign'), $vacancyId))){

            // создать сессию адаптации
            $position = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->findDependence('Parent', $vacancy->position_id));
            if ($position && count($position->parent)) {
                $newcomer = $position->parent->current()->name;
                if ($position->mid) {
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Невозможно создать сессию адаптации, т.к. в настоящий момент должность занята другим пользователем')));
                    $this->_redirector->gotoSimple('index', 'list', 'vacancy');                       
                }
            }

            $collection = $this->getService('RecruitNewcomer')->fetchAll(array('position_id = ?' => $vacancy->position_id));
            if (count($collection)) {
                foreach($collection as $newcomer) {
                    $this->getService('Process')->initProcess($newcomer);
                    $status = $newcomer->getProcess()->getStatus();
                    if (in_array($status, array(HM_Process_Abstract::PROCESS_STATUS_INIT, HM_Process_Abstract::PROCESS_STATUS_CONTINUING))) {
                        $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Невозможно создать сессию адаптации повторно')));
                        $this->_redirector->gotoSimple('index', 'list', 'vacancy');
                    }
                }                       
            }
            
            $this->getService('RecruitNewcomer')->createByVacancy($vacancy);
            
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Сессия адаптации успешно создана')));
            $this->_redirector->gotoSimple('index', 'list', 'newcomer');
        }
        $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Сессия адаптации не создана')));
        $this->_redirector->gotoSimple('index', 'list', 'vacancy');
    }

    public function createFromStructureAction()
    {
        $positionId = $this->_getParam('org_id');
        if ($position = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')
            ->findDependence(array('Parent', 'User'), $positionId))) {

            // создать сессию адаптации
            if ($position && !$position->mid) {
                $this->_flashMessenger->addMessage(
                    array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Невозможно создать сессию адаптации, т.к. в настоящий момент должность никому не назначена')));
                if (Zend_Controller_Front::getInstance()->getRequest()->getModuleName() != 'newcomer') {
                    $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
                } else {
                    $this->_redirector->gotoSimple('index', 'new-assignments', 'newcomer');
                }
            }

            $collection = $this->getService('RecruitNewcomer')->fetchAll(array('position_id = ?' => $positionId));
            if (count($collection)) {
                foreach($collection as $newcomer) {
                    $this->getService('Process')->initProcess($newcomer);
                    $status = $newcomer->getProcess()->getStatus();
                    if (in_array($status, array(HM_Process_Abstract::PROCESS_STATUS_INIT, HM_Process_Abstract::PROCESS_STATUS_CONTINUING))) {
                        $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Невозможно создать сессию адаптации повторно')));
                        if (Zend_Controller_Front::getInstance()->getRequest()->getModuleName() != 'newcomer') {
                            $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
                        } else {
                            $this->_redirector->gotoSimple('index', 'new-assignments', 'newcomer');
                        }
                    }
                }
            }

            if ($newcomer = $this->getService('RecruitNewcomer')->createByPosition($position)) {

                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Сессия адаптации успешно создана')));
                $this->_redirector->gotoSimple('index', 'list', 'newcomer');
            }
        }

        $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Сессия адаптации не создана')));
        if (Zend_Controller_Front::getInstance()->getRequest()->getModuleName() != 'newcomer') {
            $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
        } else {
            $this->_redirector->gotoSimple('index', 'new-assignments', 'newcomer');
        }
    }

    public function printFormsAction()
    {
        $templateId = HM_PrintForm::FORM_ADAPTATION_PLAN;
        $newcomerData = $this->newcomerData($this->_getParam('newcomer_id'));
        if (! count($newcomerData)) {
            $this->getFrontController()->setBaseUrl('/');
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }
        $userData = array(
            'fio'    => $newcomerData['fio'],     //ФИО пользователя, на которго открыта сессия адаптации в формате:
                                                 //[фамилия]+(пробел)+[имя]+(пробел)+[отчество]
            'job'    => $newcomerData['job'],     //Связанная с ним должность из оргструктуры
            'dep'    => $newcomerData['dep'],     //Структурное подразделение из оргструктуры
            'date'   => $newcomerData['date'],    //Дата завершения сессии подбора по данному пользователю
            'chief'  => $newcomerData['chief'],   //ФИО + контакты руководителя (из оргструктуры) в формате:
                                                 //[фамилия]+(пробел)+[имя]+(пробел)+[отчество]  + «(тел: »+[рабочий телефон] + “, email: “ + [Контактный e-mail] +”)”
            'curator'=> $newcomerData['curator'], //ФИО + контакты куратора (оценивающее лицо из задачи 25767 + связанная с ним учетная запись) в формате:
                                                 //[фамилия]+(пробел)+[имя]+(пробел)+[отчество]  + «(тел: »+[рабочий телефон] + “, email: “ + [Контактный e-mail] +”)”
        );

        $userCourses = array();
        $courses = array(); //На текущей стадии оставить как есть.
                            //Но предусмотреть, чтобы сюда могли выгружаться названия курсов,
                            // назначенных пользователю (это реализация по фазе обучения)
        if (!count($userCourses)) $userCourses = array('', '', '');
        foreach ($userCourses as $course) {
            $courses[] = array('course' => $course);
        }

        $userTasks = $this->newcomerTasks($this->_getParam('newcomer_id'));
        $tasks = array();   //Вместо 1) 2) 3) – задачи указанные здесь:
                            //http://at-dev/recruit/newcomer/kpi/index/newcomer_id/9
                            //по каждому конкретному «адаптанту» (набиваются через пункт аккордеона «задачи на испытательный срок»  создать задачу). С нумерацией в стиле бланка.
                            //Если ни одной задачи нет – печатать как в бланке.
        if (!count($userTasks)) $userTasks = array('', '', '');
        foreach ($userTasks as $task) {
            
            $fact = '';
            $plan = '';
            $values = HM_At_Kpi_User_UserModel::getQualitiveValues();

            if ($task['value_type'] == HM_At_Kpi_User_UserModel::TYPE_QUANTITATIVE) {
                // Количественная задача
                $unitName = _('шт.');
                if ($task['ku_name']) $unitName = $task['ku_name'];

                $fact = trim(sprintf("Результат - %s %s", $task['value_fact'], $unitName));
                $plan = $task['value_plan'] ? trim(sprintf("План - %s %s", $task['value_plan'], $unitName)) : '';
            } else {
                // Качественная задача или задача с неопределенным типом
                if (isset($values[$task['value_fact']])) $fact = trim(sprintf("Результат - %s", $values[$task['value_fact']]));
            }

            $tasks[] = array(
                'task' => $task['kpi_name'],
                'plan' => $plan,
                'fact' => $fact
            );
        }

        $data = array_merge($userData, array('courses' => $courses), array('tasks' => $tasks));

        $outFileName = 'adaptation_plan_'.$this->_getParam('newcomer_id');

        $this->getService('PrintForm')->makePrintForm(HM_PrintForm::TYPE_WORD, $templateId, $data, $outFileName);
    }

    private function newcomerData($newcomer_id)
    {
        $chief  = null;
        $result = array();
        $newcomer = $this->getService('RecruitNewcomer')->getOne(
            $this->getService('RecruitNewcomer')->fetchAll(
                $this->getService('RecruitNewcomer')->quoteInto('newcomer_id=?', $newcomer_id)
            )
        );

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER))) {
            if ($this->getService('User')->getCurrentUserId() != $newcomer->user_id) return array();
        }


        $user = $this->getService('User')->getOne(
            $this->getService('User')->fetchAll(
                $this->getService('User')->quoteInto('MID=?', $newcomer->getValue('user_id'))
            )
        );
        $position = $this->getService('Orgstructure')->getOne(
            $this->getService('Orgstructure')->fetchAll(
                $this->getService('Orgstructure')->quoteInto('soid=?', $newcomer->getValue('position_id'))
            )
        );
        $department = $this->getService('Orgstructure')->getOne(
            $this->getService('Orgstructure')->fetchAll(
                $this->getService('Orgstructure')->quoteInto('soid=?', $position->getValue('owner_soid'))
            )
        );
        if ($this->isManager($user)) {
            if ($department->getValue('owner_soid') != 0) {
                $overDepartment = $this->getService('Orgstructure')->getOne(
                    $this->getService('Orgstructure')->fetchAll(
                        $this->getService('Orgstructure')->quoteInto('soid=?', $department->getValue('owner_soid'))
                    )
                );
                $chief = $this->getDepartmentChief($overDepartment);
            }
        } else {
            $chief = $this->getDepartmentChief($department);
        }

        $curator = $this->getService('User')->getOne(
            $this->getService('User')->fetchAll(
                $this->getService('User')->quoteInto('MID=?', $newcomer->getValue('evaluation_user_id'))
            )
        );
        $positionDate = $position->getValue('position_date');

        $result['fio']     = $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
        $result['job']     = $position->getValue('name');
        $result['dep']     = $department->getValue('name');
        $result['date']    = $positionDate ? date("d.m.Y", strtotime($positionDate)) : '';
        $result['chief']   = ($chief) ? $this->getContacts($chief['LastName'], $chief['FirstName'], $chief['Patronymic'], $chief['Phone'], $chief['EMail']) : "";
        $result['curator'] = ($curator) ? $this->getContacts($curator->LastName, $curator->FirstName, $curator->Patronymic, $curator->Phone, $curator->EMail) : "";
        return $result;
    }


    private function getContacts($lastName, $firstName, $patronymic, $phone, $email)
    {
        if ($phone) {
            if ($email) {
                $res = sprintf("%s %s %s (тел: %s, email: %s)", $lastName, $firstName, $patronymic, $phone, $email);
            } else {
                $res = sprintf("%s %s %s (тел: %s)", $lastName, $firstName, $patronymic, $phone);
            }
        } else {
            if ($email) {
                $res = sprintf("%s %s %s (email: %s)", $lastName, $firstName, $patronymic, $email);
            } else {
                $res = sprintf("%s %s %s", $lastName, $firstName, $patronymic);
            }
        }

        return trim($res);
    }

    private function isManager($user)
    {
        $userPosition = $this->getService('Orgstructure')->getOne(
            $this->getService('Orgstructure')->fetchAll(
                $this->getService('Orgstructure')->quoteInto('mid=?', $user->getValue('MID'))
            )
        );
        return ($userPosition->getValue('is_manager') == 1) ? true : false;
    }

    private function  getDepartmentChief($department)
    {
        $select = $this->getService('User')->getSelect();
        $select->from(
            array( 'p' => 'People'),
            array(
                'MID',
                'FirstName',
                'LastName',
                'Patronymic',
                'Phone',
                'EMail'
            )
        )->joinInner(array('so' => 'structure_of_organ'), 'so.mid = p.MID', array())
        ->where('so.owner_soid = ?', $department->getValue('soid'))
        ->where('so.is_manager = 1');
        $rows = $select->query()->fetchAll();
        $result = array();
        foreach ($rows as $row) $result[] = $row;
        return $result[0];
    }

    private function newcomerTasks($newcomer_id)
    {
        $newcomer = $this->getService('RecruitNewcomer')->getOne($this->getService('RecruitNewcomer')->findDependence(array('User', 'Cycle'), $newcomer_id));
        if (count($newcomer->cycle)) {
            $cycleId = $newcomer->cycle->current()->cycle_id;
        }

        $select = $this->getService('AtKpi')->getSelect();
        $select->from(
            array(
                'uk' => 'at_user_kpis'
            ),
            array(
                'uk.user_kpi_id',
                'kpi_name' => 'k.name',
                'uk.value_plan',
                'uk.value_fact',
                'uk.value_type',
                'uk.weight',
                'ku_name' => 'ku.name'
            )
        );

        $select
            ->join(array('k' => 'at_kpis'), 'uk.kpi_id = k.kpi_id', array())
            ->join(array('c' => 'cycles'), 'uk.cycle_id = c.cycle_id', array())
            ->joinLeft(array('ku' => 'at_kpi_units'), "k.kpi_unit_id = ku.kpi_unit_id", array())
            ->where('c.cycle_id = ?', $cycleId)
            ->where('uk.user_id = ?', $newcomer->user_id)
            ->group(array(
                'uk.user_kpi_id',
                'k.kpi_id',
                'k.name',
                'c.cycle_id',
                'uk.value_plan',
                'uk.value_fact',
                'uk.value_type',
                'uk.weight',
                'ku.name'
            ));
        ;

        $result = $select->query()->fetchAll();

        return $result;
    }
    
    public function delete($id) 
    {
        $this->getService('RecruitNewcomer')->delete($id);
    }    

    public function updateContactUserId($recruiterIds)
    {
        static $recruiters = false;
        
        if (!$recruiters) {
            $select = $this->getService('Recruiter')->getSelect();
            
            $select->from(array('r' => 'recruiters'), array(
                'r.recruiter_id',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            ));
            $select->joinInner(array('p' => 'People'), 'p.MID = r.user_id', array());
            $users = $select->query()->fetchAll();
            $recruiters = array();
            
            foreach ($users as $user) {
                $recruiters[$user['recruiter_id']] = $user['fio'];
            }
        }
        
        $recruiterIds = explode(',', $recruiterIds);
        $result = array();
        
        foreach ($recruiterIds as $recruiterId) {
            if (isset($recruiters[$recruiterId])) {
                $result[] = $recruiters[$recruiterId];
            }
        }
        
        return implode(', ', $result);
    }


    public function printWorkflow($newcomerId)
    {
        if ($this->_newcomersCache === null) {
            $this->_newcomersCache = array();
            $collection = $this->getService('RecruitNewcomer')->fetchAll();
            if (count($collection)) {
                foreach ($collection as $item) {
                    $this->_newcomersCache[$item->newcomer_id] = $item;
                }
            }
        }
        if ($newcomerId && count($this->_newcomersCache) && array_key_exists($newcomerId, $this->_newcomersCache)){
            $model = $this->_newcomersCache[$newcomerId];
            $this->getService('Process')->initProcess($model);
       
            return $this->view->workflowBulbs($model);
        }
        return '';
    }

    public function updateName($name, $orgId, $type, $isManager, $newcomerId)
    {
        return $this->view->cardLink(
            $this->view->url(
                array(
                    'baseUrl' => '',
                    'module' => 'orgstructure',
                    'controller' => 'list',
                    'action' => 'card',
                    'org_id' => ''
                )
            ) . $orgId,
            HM_Orgstructure_OrgstructureService::getIconTitle($type, $isManager),
            'icon-custom',
            'pcard',
            'pcard',
            'orgstructure-icon-small ' . HM_Orgstructure_OrgstructureService::getIconClass($type, $isManager)
            ) . ' <a href="' .
            $this->view->url(
                array(
                    'module' => 'newcomer',
                    'controller' => 'report',
                    'action' => 'index',
                    'newcomer_id' => $newcomerId,
                ), null, true
            ) . '">' . $name . '</a>';
    }

    public function updateEvalUser($grid, $evalUser_id, $evalUser_name)
    {
        if ($evalUser_id != '' && !is_null($evalUser_id)) {
            $grid->updateColumn('evaluation_user',
                array(
                    'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . $evalUser_id) . ' <a href="' . $this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . $evalUser_id . '">' . $evalUser_name . '</a>'
                )
            );
        } else {
            $grid->updateColumn('evaluation_user',
                array(
                    'decorator' => null
                )
            );
        }
    }

    public function updateManager($grid, $managerId, $managerName)
    {
        if ($managerId != '' && !is_null($managerId)) {
            $grid->updateColumn('manager',
                array(
                    'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . $managerId) . ' <a href="' . $this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . $managerId . '">' . $managerName . '</a>'
                )
            );
        } else {
            $grid->updateColumn('manager',
                array(
                    'decorator' => null
                )
            );
        }
    }

    public function workflowAction()
    {
        $newcomerId = $this->_getParam('index', 0);

        if(intval($newcomerId) > 0){

            $model =  $this->getService('RecruitNewcomer')->find($newcomerId)->current();
            $this->getService('Process')->initProcess($model);
            $this->view->model = $model;

            if ($this->isAjaxRequest()) {
                $this->view->workflow = $this->view->getHelper("Workflow")->getFormattedDataWorkflow($model);
            }
        }
    }

    /* ЭТАПЫ */

    public function changeStateAction()
    {
        $newcomerId  = $this->_getParam('newcomer_id',0);
        $state = (int) $this->_getParam('state', 0);

        $currentState = $this->_defaultService->changeState($newcomerId, $state);
        if ($currentState) {

            switch ($state) {
                case HM_State_Abstract::STATE_STATUS_FAILED:
                    $this->_defaultService->update(array(
                        'newcomer_id' => $newcomerId,
                        'result' => HM_Recruit_Newcomer_NewcomerModel::RESULT_FAIL_MANAGER
                    ));
                    $message = _('Сессия адаптации успешно отменена.');
                    break;
                default:
                    $newcomer = $this->_defaultService->getOne($this->_defaultService->find($newcomerId));
                    $state = $this->getService('Process')->getCurrentState($newcomer);

                    $message = $state instanceof HM_Tc_Session_State_Complete
                        ? _('Сессия адаптации успешно завершена')
                        : _('Сессия адаптации успешно переведена на следующий этап');
            }
            $this->_flashMessenger->addMessage($message);
        } else {
            $newcomer = $this->getOne($this->_defaultService->find($newcomerId));
            $sessionState = $this->getService('Process')->getCurrentState($newcomer);
            $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => $sessionState->onErrorMessage())
            );
        }
        $this->_redirector->gotoUrl($this->view->url(array(
            'module' => 'newcomer',
            'controller' => 'report',
            'action' => 'index',
            'newcomer'=> $newcomerId,
        )), array('prependBase' => false));
    }

    public function assignSessionsAction()
    {
        $this->view->setHeader(_('Назначение учебных сессий пользователям'));
        $form = new HM_Form_Sessions();

        $subjects = $users = $users2subjects = $users2newcomers = array();
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {

                $subjectsCache = $this->getService('Subject')->fetchAll()->asArrayOfObjects();

                $collection = $this->getService('RecruitNewcomer')->fetchAllHybrid('User', 'Subject', 'Student', array('newcomer_id IN (?)' => $ids));
                $users2newcomers = $collection->getList('user_id', 'newcomer_id');
                foreach ($collection as $newcomer) {
                    if (count($newcomer->courses)) {
                        foreach ($newcomer->courses as $subject) {

                            // массив курсов для отображения в форме
                            $subjectId = ($subject->base == HM_Subject_SubjectModel::BASETYPE_SESSION) ? $subject->base_id : $subject->subid;

                            if ($subject->is_labor_safety != $this->_isLaborSafety) continue;
                            $subjects[$subjectId] = $subjectsCache[$subjectId];

                            // массив текущих назначений на курсы или сессии, чтобы не назначить лишнего
                            if (!count($newcomer->user))  continue;
                            $users[$newcomer->user_id] = $newcomer->user->current();
                            $users2subjects[$newcomer->user_id][] = $subjectId;
                        }
                    }
                }
            }
        }

        if (!count($subjects)) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Отмеченным пользователям не назначены курсы начального обучения.')
            ));
            $this->_redirectToIndex();
        }

        if ($this->_getParam('assignsessions',0)) {

            $post = $this->_processPost($_POST);
            if (is_array($post['subject'])) {

                $subjectIds = array_keys($post['subject']);

                $subjectsToUnassign = array();
                $collection = $this->getService('Subject')->fetchAll(array('base_id IN (?)' => $subjectIds));
                foreach ($collection as $session) {
                    if (!isset($subjectsToUnassign[$session->base_id])) $subjectsToUnassign[$session->base_id] = array($session->base_id); // родительский курс тоже включаем
                    $subjectsToUnassign[$session->base_id][] = $session->subid;
                }

                $collection = $this->getService('Subject')->fetchAll(array('subid IN (?)' => $subjectIds));
                if (count($collection)) {
                    foreach ($collection as $subject) {

                        if (!$this->_getParam("radio_{$subject->subid}")) continue; // опция "оставить без изменений"

                        if (! in_array($subject->subid, $subjectsToUnassign[$subject->subid])) $subjectsToUnassign[$subject->subid][] = $subject->subid;

                        $values = $subject->getValues();
                        $sessionValues = $post['subject'][$subject->subid];

                        if ($sessionValues['sessionId']) {
                            $session = $this->getService('Subject')->getOne($this->getService('Subject')->find($sessionValues['sessionId']));
                        } elseif ($sessionValues['begin']) {
                            $baseId = $values['subid'];
                            unset($values['subid']);
                            $values['base'] = HM_Subject_SubjectModel::BASETYPE_SESSION;
                            $values['period'] = HM_Subject_SubjectModel::PERIOD_DATES;
                            $date = new HM_Date($sessionValues['begin']);
                            $end  = new HM_Date($sessionValues['end']);
                            $values['begin'] = $date->get('Y-MM-dd');
                            $values['end'] = $end->get('Y-MM-dd 23:59:59');
                            $values['name'] = sprintf('%s (сессия %s)', $values['name'], $sessionValues['begin']);
                            $values['base_id'] = $baseId;
                            if ($values['longtime'] && HM_Date::getRelativeDate($date, $values['longtime']) <= new HM_Date()) {
                                $values['state'] = HM_Subject_SubjectModel::STATE_ACTUAL;
                            }
                            $values['created'] = date('Y-m-d H:i:s');

                            $session = $this->getService('Subject')->insert($values);

                            try {
                                $this->getService('Subject')->copyElements($baseId, $session->subid);

                                $subjectsFolder = Zend_Registry::get('config')->path->upload->subject;
                                $srcSubjIcon = $subjectsFolder.HM_Subject_SubjectModel::getIconFolder($baseId). '/' . $baseId . '.jpg';
                                if(file_exists($srcSubjIcon)) {
                                    $destSubjFolder = $subjectsFolder.HM_Subject_SubjectModel::getIconFolder($session->subid);
                                    if(!file_exists($destSubjFolder)) {
                                        mkdir($destSubjFolder);
                                    }
                                    copy($srcSubjIcon, $destSubjFolder. '/' . $session->subid . '.jpg');
                                }
                            } catch (HM_Exception $e) {}

                            // апдейтим родительский уч.курс - убираем ограничения по времени и месту
                            // автоназначение basetype
                            $changes = array(
                                'base'      => HM_Subject_SubjectModel::BASETYPE_BASE,
                                'period'    => HM_Subject_SubjectModel::PERIOD_FREE,
                            );
                            $this->getService('Subject')->updateWhere($changes, array('subid = ?' => $baseId));
                            $this->getService('Subject')->unlinkRooms($baseId);

                            $this->expandResponsibility($session);

                            $classifiers = $form->getClassifierValues();
                            $this->getService('Classifier')->unlinkItem($session->subid, HM_Classifier_Link_LinkModel::TYPE_SUBJECT);
                            if (is_array($classifiers) && count($classifiers)) {
                                foreach($classifiers as $classifierId) {
                                    if ($classifierId > 0) {
                                        $this->getService('Classifier')->linkItem($session->subid, HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $classifierId);
                                    }
                                }
                            }

                            $this->getService('Subject')->linkRoom($session->subid, $sessionValues['roomId']);
                        } else {
                            $this->_flashMessenger->addMessage(array(
                                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                                'message' => _('Произошла ошибка при назначении учебных сессий')
                            ));
                            $this->_redirectToIndex();
                        }

                        if ($session) {
                            $this->getService('Student')->deleteBy(array(
                                'CID IN (?)' => $subjectsToUnassign[$session->base_id], // отменяем назначения базового или любой из сессий
                                'MID IN (?)' => array_keys($users),
                            ));

                            foreach ($users as $user) {
                                // если юзеру положено иметь такой курс по программе
                                if (in_array($session->base_id, $users2subjects[$user->MID])) {
                                    $this->getService('Subject')->assignStudent($session->subid, $user->MID, array('newcomer_id' => $users2newcomers[$user->MID]));
                                }
                            }
                        }
                    }

                    if ($this->_getParam('editnotifications',0)) {
                        $url = $this->view->url(array(
                                'module' => 'newcomer',
                                'controller' => 'list',
                                'action' => 'send-notifications-study',
                            )) . "/?postMassIds_grid={$postMassIds}";
                        $this->_redirector->gotoUrl($url, array('prependBase' => false));
                    }

                }
            }

            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Назначения успешно выполнены')
            ));
            $this->_redirectToIndex();

        } else {

            $form->initWithData($subjects, $postMassIds);
            $this->view->form = $form;
            $this->view->users = $users;
        }
    }

    public function assignWelcomeTrainingAction()
    {
        $this->view->setHeader(_('Назначение на welcome-тренинг указанных пользователей'));
        $form = new HM_Form_WelcomeTrainings();

        $users =
        $ids = array();
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach ($ids as $id) {
                    $newcomer = $this->getOne($this->getService('RecruitNewcomer')->find($id));
                    $user     = $this->getOne($this->getService('User')->find($newcomer->user_id));
                    $users[]  = $user;
                }
            }
        }

        if ($this->_getParam('assignwelcome',0)) {

            $editNotifications = $this->_getParam('edit', 0);

            switch ($editNotifications) {
                case 0:
                    $userEmails =
                    $managerEmails = array();

                    if (count($ids)) {
                        foreach ($ids as $id) {
                            $newcomer = $this->getOne($this->getService('RecruitNewcomer')->find($id));
                            $user     = $this->getOne($this->getService('User')->find($newcomer->user_id));
                            $this->changeWelcomeEventDate($id, $this->_getParam('date', ''));

                            $org = $this->getService('Orgstructure')->getOne(
                                $this->getService('Orgstructure')->fetchAll(array('mid = ?' => $user->MID))
                            );

                            $manager = $this->getService('Orgstructure')->getManager($org->soid);
                            $manager = $this->getOne($this->getService('User')->find($manager->mid));

                            if ($user->EMail) {
                                $place = $this->getService('Room')->find($this->_getParam('room', ''))->current();
                                $messenger = $this->getService('Messenger');
                                $messenger->setOptions(
                                    HM_Messenger::TEMPLATE_WELCOME_TRAINING_WORKER_NOTIFICATION,
                                    array(
                                        'NAME_PATRONYMIC' => $user->FirstName . ' ' . $user->Patronymic,
                                        'DATE' => $this->_getParam('date', ''),
                                        'PLACE' => $place->name,
                                        'RECRUITER' => $this->getService('Recruiter')->getCurrentRecruiterInfo(),
                                    )
                                );

                                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                                $userEmails[] = $user->getName();
                            } else {
                                if ($manager->EMail) {
                                    $place = $this->getService('Room')->find($this->_getParam('room', ''))->current();
                                    $messenger = $this->getService('Messenger');
                                    $messenger->setOptions(
                                        HM_Messenger::TEMPLATE_WELCOME_TRAINING_MANAGER_NOTIFICATION,
                                        array(
                                            'NAME_PATRONYMIC' => $manager->FirstName . ' ' . $manager->Patronymic,
                                            'FIO_NEWCOMER' => $user->getName(),
                                            'DATE' => $this->_getParam('date', ''),
                                            'PLACE' => $place->name,
                                            'RECRUITER' => $this->getService('Recruiter')->getCurrentRecruiterInfo(),
                                        )
                                    );

                                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, $manager->MID);
                                    if (!in_array($manager->getName(), $managerEmails)) $managerEmails[] = $manager->getName();
                                }
                            }
                        }

                        $this->_flashMessenger->addMessage(array(
                            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                            'message' => _('Уведомления отправлены следующим пользователям: ') . implode(', ', $userEmails)
                        ));

                        $this->_flashMessenger->addMessage(array(
                            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                            'message' => _('Уведомления отправлены следующим руководителям: ') . implode(', ', $managerEmails)
                        ));

                        $this->_redirectToIndex();
                    }
                    break;
                case 1:
                    $url = $this->view->url(
                            array(
                                'module'       => 'newcomer',
                                'controller'   => 'list',
                                'action'       => 'send-notifications-welcome'
                            )
                        ) . "/?postMassIds_grid={$postMassIds}&notification=custom&date={$_POST['date']}&room={$_POST['room']}";

                    $this->_redirector->gotoUrl($url, array('prependBase' => false));
                    break;
            }

            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Назначения успешно выполнены')
            ));
            $this->_redirectToIndex();
        } else {
            $form->initWithData($postMassIds);
            $this->view->form = $form;
            $this->view->users = $users;
        }
    }

    public function sendNotificationsWelcomeAction()
    {
        $this->view->setHeader(_('Отправка уведомлений пользователям'));
        $form = new HM_Form_notificationsCustom();

        $users =
        $managers = array();
        $postMassIds = $this->_getParam('postMassIds_grid', '');

        $notice1 = $this->getOne($this->getService('Notice')->fetchAll(array(
            'type = ?' => HM_Messenger::TEMPLATE_WELCOME_TRAINING_WORKER_NOTIFICATION
        )));

        $notice2 = $this->getOne($this->getService('Notice')->fetchAll(array(
            'type = ?' => HM_Messenger::TEMPLATE_WELCOME_TRAINING_MANAGER_NOTIFICATION
        )));

        if (!$notice1 || !$notice2) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Отсутствует шаблон уведомления')
            ));
            $this->_redirectToIndex();
        }

        if ($this->_getParam('sendnotifications', 0) && $this->_getParam('notification', 'default') == 'custom') {
            if (strlen($postMassIds)) {
                $ids = explode(',', $postMassIds);
                if (count($ids)) {
                    $userEmails =
                    $managerEmails = array();

                    foreach ($ids as $id) {
                        $newcomer = $this->getOne($this->getService('RecruitNewcomer')->find($id));
                        $user     = $this->getOne($this->getService('User')->find($newcomer->user_id));
                        $users[]  = $user;

                        $newcomer = $this->getService('RecruitNewcomer')->getOne(
                            $this->getService('RecruitNewcomer')->fetchAll(
                                $this->getService('RecruitNewcomer')->quoteInto('user_id=?', $user->MID)
                            )
                        );
                        $this->changeWelcomeEventDate($newcomer->newcomer_id, $this->_getParam('date', ''));

                        $request = $this->getRequest();
                        if ($form->isValid($request->getParams())) {
                            $org = $this->getService('Orgstructure')->getOne(
                                $this->getService('Orgstructure')->fetchAll(array('mid = ?' => $user->MID))
                            );

                            $manager = $this->getService('Orgstructure')->getManager($org->soid);
                            $manager = $this->getOne($this->getService('User')->find($manager->mid));

                            if ($user->EMail) {
                                $notice1->title = $form->getValue('title_users');
                                $notice1->message = $form->getValue('message_users');

                                $place = $this->getService('Room')->find($this->_getParam('room', ''))->current();
                                $messenger = $this->getService('Messenger');
                                $messenger->setOptions(
                                    $notice1->type,
                                    array(
                                        'NAME_PATRONYMIC' => $user->FirstName . ' ' . $user->Patronymic,
                                        'DATE' => $this->_getParam('date', ''),
                                        'PLACE' => $place->name,
                                        'RECRUITER' => $this->getService('Recruiter')->getCurrentRecruiterInfo(),
                                    )
                                );

                                $messenger->forceTemplate($notice1);
                                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                                $userEmails[] = $user->getName();
                            } else {
                                if ($manager->EMail) {
                                    $notice2->title = $form->getValue('title_managers');
                                    $notice2->message = $form->getValue('message_managers');

                                    $place = $this->getService('Room')->find($this->_getParam('room', ''))->current();
                                    $messenger = $this->getService('Messenger');
                                    $messenger->setOptions(
                                        $notice2->type,
                                        array(
                                            'NAME_PATRONYMIC' => $manager->FirstName . ' ' . $manager->Patronymic,
                                            'FIO_NEWCOMER' => $user->getName(),
                                            'DATE' => $this->_getParam('date', ''),
                                            'PLACE' => $place->name,
                                            'RECRUITER' => $this->getService('Recruiter')->getCurrentRecruiterInfo(),
                                        )
                                    );

                                    $messenger->forceTemplate($notice2);
                                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, $manager->MID);
                                    if (!in_array($manager->getName(), $managerEmails)) $managerEmails[] = $manager->getName();
                                }
                            }
                        }
                    }

                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                        'message' => _('Уведомления отправлены следующим пользователям: ') . implode(', ', $userEmails)
                    ));

                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                        'message' => _('Уведомления отправлены следующим руководителям: ') . implode(', ', $managerEmails)
                    ));

                    $this->_redirectToIndex();
                }
            }
        } else {
            if (strlen($postMassIds)) {
                $ids = explode(',', $postMassIds);
                if (count($ids)) {
                    foreach ($ids as $id) {
                        $newcomer = $this->getOne($this->getService('RecruitNewcomer')->find($id));
                        $user     = $this->getOne($this->getService('User')->find($newcomer->user_id));
                        if ($user->EMail) {
                            $users[] = $user;
                        } else {
                            $org = $this->getService('Orgstructure')->getOne(
                                $this->getService('Orgstructure')->fetchAll(array('mid = ?' => $user->MID))
                            );

                            $manager = $this->getService('Orgstructure')->getManager($org->soid);
                            $manager = $this->getOne($this->getService('User')->find($manager->mid));
                            if ($manager->EMail && !in_array($manager, $managers)) $managers[] = $manager;
                        }
                    }
                }
            }
        }

        $form->initWithData($postMassIds);
        $form->populate(array(
            'title_users' => $notice1->title,
            'message_users' => $notice1->message,
            'title_managers' => $notice2->title,
            'message_managers' => $notice2->message,
        ));

        $this->view->form     = $form;
        $this->view->users    = $users;
        $this->view->managers = $managers;
    }

    protected function changeWelcomeEventDate($newcomerId, $date)
    {
        $updateWhere = $this->quoteInto(
            array(
                'item_id = ? AND ',
                'step = ? '
            ),
            array(
                $newcomerId,
                'HM_Recruit_Newcomer_State_Welcome'
            )
        );

        $updateData = array(
            'date_begin' => date('Y-m-d H:i:s' , strtotime($date)),
            'date_end'   => date('Y-m-d H:i:s' , strtotime($date)),
        );

        $this->getService('ProcessStep')->updateWhere($updateData, $updateWhere);
    }

    public function sendNotificationsStudyAction()
    {
        $this->view->setHeader(_('Отправка уведомлений пользователям'));
        $form = new HM_Form_Notifications();

        $notice = $this->getOne($this->getService('Notice')->fetchAll(array(
            'type = ?' => HM_Messenger::TEMPLATE_ASSIGN_SUBJECT_SESSION
        )));

        if (!$notice) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Отсутствует шаблон уведомления')
            ));
            $this->_redirectToIndex();
        }

        $subjects = $users = $subjectUsers = array();
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $collection = $this->getService('RecruitNewcomer')->fetchAllHybrid('User', 'Subject', 'Student', array('newcomer_id IN (?)' => $ids));
                foreach ($collection as $newcomer) {
                    if (count($newcomer->courses)) {
                        foreach ($newcomer->courses as $subject) {
                            if (!$subject->base_id) continue; // отсюда уведомляем только о сессиях
                            $subjects[$subject->subid] = $subject;
                            if (!isset($subjectUsers[$subject->subid])) $subjectUsers[$subject->subid] = array();
                            $subjectUsers[$subject->subid][] = $newcomer->user_id;
                            if (count($newcomer->user)) $users[$newcomer->user_id] = $newcomer->user->current();
                        }
                    }
                }
            }
        }

        if (!count($subjects)) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Отмеченным пользователям не назначены учебные сессии')
            ));
            $this->_redirectToIndex();
        }

        if ($this->_getParam('sendnotifications',0)) {

            $request = $this->getRequest();
            if ($form->isValid($request->getParams())) {

                $notice->title = $form->getValue('title');
                $notice->message = $form->getValue('message');

                /** @var HM_Messenger $messenger */
                $messenger = $this->getService('Messenger');
                $roles = HM_Role_Abstract_RoleModel::getBasicRoles();
                foreach ($subjects as $subject) {
                    foreach ($subjectUsers[$subject->subid] as $userId) {
                        $messenger->setOptions(
                            $subject->base_id ? HM_Messenger::TEMPLATE_ASSIGN_SUBJECT_SESSION : HM_Messenger::TEMPLATE_ASSIGN_SUBJECT,
                            [
                                'user_id' => $userId,
                                'subject_id' => $subject->subid,
                                'role' => $roles[HM_Role_Abstract_RoleModel::ROLE_STUDENT]
                            ],
                            'subject',
                            $subject->subid
                        );
                        $messenger->forceTemplate($notice);
                        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $userId);
                    }
                }

                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                    'message' => _('Уведомления успешно отправлены')
                ));
                $this->_redirectToIndex();

            }
        } else {
            $form->initWithData($postMassIds);
            $form->populate(array(
                'title' => $notice->title,
                'message' => $notice->message,
            ));
        }
        $this->view->form = $form;
        $this->view->users = $users;
    }

    public function sendNotificationsOtAction()
    {
        $this->view->setHeader(_('Отправка уведомлений специалистам по охране труда'));

        $notice = $this->getOne($this->getService('Notice')->fetchAll(array(
            'type = ?' => HM_Messenger::TEMPLATE_ADAPTATION_LABOR_SAFETY
        )));

        // получение списка спецов по ОТ
        $users = array();
        $collection = $this->getService('LaborSafety')->fetchAllDependence('User');
        foreach ($collection as $ls) {
            if ($ls->user) {
                $user = $ls->user->current();
                $users[] = $user;
            }
        }

        return $this->_sendNotifications($notice, $users, 'ot');
    }

    public function sendNotificationsManagerAction()
    {
        $this->view->setHeader(_('Отправка уведомлений руководителям'));

        $notice = $this->getOne($this->getService('Notice')->fetchAll(array(
            'type = ?' => HM_Messenger::TEMPLATE_ADAPTATION_MANAGER
        )));

        // получение списка руководителей отмеченных адаптантов
        $users = array();
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $collection = $this->getService('RecruitNewcomer')->fetchAllDependence('ManagerUser', array('newcomer_id IN (?)' => $ids));
                foreach ($collection as $newcomer) {
                    if (count($newcomer->managerUser)) {
                        $users[$newcomer->manager_id] = $newcomer->managerUser->current();
                    }
                }
            }
        }

        return $this->_sendNotifications($notice, $users, 'managers');
    }

    public function sendNotificationsCuratorAction()
    {
        $this->view->setHeader(_('Отправка уведомлений кураторам'));

        $notice = $this->getOne($this->getService('Notice')->fetchAll(array(
            'type = ?' => HM_Messenger::TEMPLATE_ADAPTATION_CURATOR
        )));

        // получение списка кураторов отмеченных адаптантов
        $users = array();
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $collection = $this->getService('RecruitNewcomer')->fetchAllDependence('CuratorUser', array('newcomer_id IN (?)' => $ids));
                foreach ($collection as $newcomer) {
                    if (count($newcomer->curatorUser)) {
                        $curatorUser = $newcomer->curatorUser->current();
                        $users[$curatorUser->MID] = $curatorUser;
                    }
                }
            }
        }

        return $this->_sendNotifications($notice, $users, 'curators');
    }

    public function sendNotificationsWorkerAction()
    {
        $this->view->setHeader(_('Отправка уведомлений пользователям'));

        $notice = $this->getOne($this->getService('Notice')->fetchAll(array(
            'type = ?' => HM_Messenger::TEMPLATE_EMPTY
        )));

        // получение списка кураторов отмеченных адаптантов
        $users = array();
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $collection = $this->getService('RecruitNewcomer')->fetchAllDependence('User', array('newcomer_id IN (?)' => $ids));
                foreach ($collection as $newcomer) {
                    if (count($newcomer->user)) {
                        $users[$newcomer->user_id] = $newcomer->user->current();
                    }
                }
            }
        }

        return $this->_sendCustomMessage($notice, $users);
    }

    public function _sendNotifications($notice, $users, $forWhom)
    {
        $form = new HM_Form_Notifications();

        if (!$notice) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Отсутствует шаблон уведомления')
            ));
            $this->_redirectToIndex();
        }

        if (!count($users)) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Отсутствуют пользователи, которым отправляется уведомление')
            ));
            $this->_redirectToIndex();
        }

        // получение списка инструктируемых пользователей
        $listUsers = array();
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                switch ($forWhom) {
                    case 'curators':
                        $collection = $this->getService('RecruitNewcomer')->fetchAllDependence('CuratorUser', array('newcomer_id IN (?)' => $ids));
                        foreach ($collection as $newcomer) {
                            if (count($newcomer->curatorUser)) {
                                $curatorUserId = $newcomer->curatorUser->current()->MID;
                                $user = $this->getService('User')->find($newcomer->user_id)->current();
                                $listUsers[$curatorUserId][$newcomer->user_id] = $user;
                            }
                        }
                        break;
                    case 'managers':
                        $collection = $this->getService('RecruitNewcomer')->fetchAllDependence('ManagerUser', array('newcomer_id IN (?)' => $ids));
                        foreach ($collection as $newcomer) {
                            if (count($newcomer->managerUser)) {
                                $managerUserId = $newcomer->managerUser->current()->MID;
                                $user = $this->getService('User')->find($newcomer->user_id)->current();
                                $listUsers[$managerUserId][$newcomer->user_id] = $user;
                            }
                        }
                        break;
                    case 'ot':
                        $collection = $this->getService('LaborSafety')->fetchAllDependence('User');
                        foreach ($collection as $ls) {
                            if ($ls->user) {
                                $user = $ls->user->current();
                                $listUsers[$user->MID] = $user;
                            }
                        }
                        break;
                }
            }
        }

        if ($this->_getParam('sendnotifications',0)) {

            $request = $this->getRequest();
            if ($form->isValid($request->getParams())) {
                foreach ($users as $user) {
                    $list = '<ul>';
                    foreach ($listUsers as $receiverId => $listUserItems) {
                        if ($user->MID == $receiverId) {
                            foreach ($listUserItems as $userItem) {
                                $list .= '<li>'.$userItem->getName().'</li>';
                            }
                        }
                    }
                    $list .= '</ul>';

                    $notice->title = $form->getValue('title');
                    $notice->message = $form->getValue('message');

                    $messageParam = array(
                        'LIST' => $list,
                        'RECRUITER' => $this->getService('Recruiter')->getCurrentRecruiterInfo(),
                    );

                    $messenger = $this->getService('Messenger');

                    $messenger->setOptions($notice->type, $messageParam);
                    $messenger->forceTemplate($notice);
                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                }

                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                    'message' => _('Уведомления успешно отправлены')
                ));
                $this->_redirectToIndex();

            }
        } else {
            $form->initWithData($postMassIds);
            $form->populate(array(
                'title' => $notice->title,
                'message' => $notice->message,
            ));
        }
        $this->view->form = $form;
        $this->view->users = $users;
        $this->view->listUsers = $listUsers;

        $this->_helper->viewRenderer->setNoRender();
        echo $this->view->render('list/send-notifications.tpl');
    }

    public function _sendCustomMessage($notice, $users)
    {
        $form = new HM_Form_Notifications();
        $postMassIds = $this->_getParam('postMassIds_grid', '');

        if (!$notice) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Отсутствует шаблон уведомления')
            ));
            $this->_redirectToIndex();
        }

        if (!count($users)) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Отсутствуют пользователи, которым отправляется уведомление')
            ));
            $this->_redirectToIndex();
        }

        if ($this->_getParam('sendnotifications',0)) {
            $hasEmail = array();
            $request = $this->getRequest();
            if ($form->isValid($request->getParams())) {

                $notice->title = $form->getValue('title');
                $notice->message = $form->getValue('message');

                $messageParam = array(
                    'RECRUITER' => $this->getService('Recruiter')->getCurrentRecruiterInfo(),
                );

                $messenger = $this->getService('Messenger');

                $messenger->setOptions($notice->type, $messageParam);
                $messenger->forceTemplate($notice);
                foreach ($users as $user) {
                    if ($user->EMail) $hasEmail[$user->MID] = $user->getName();
                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                }

                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                    'message' => count($hasEmail) ?
                        _('Уведомления успешно отправлены пользователям, у которых указан e-mail: ') . implode(', ', $hasEmail):
                        _('Уведомления успешно отправлены')
                ));
                $this->_redirectToIndex();

            }
        } else {
            $form->initWithData($postMassIds);
            $form->populate(array(
                'title' => $notice->title,
                'message' => $notice->message,
            ));
        }
        $this->view->form = $form;
        $this->view->users = $users;

        $this->_helper->viewRenderer->setNoRender();
        echo $this->view->render('list/send-custom-message.tpl');
    }


    /*
    public function skipEventAction()
    {
        if (($programmEventId = $this->_getParam('programm_event_id')) && ($newcomerId = $this->_getParam('newcomer_id'))) {
            
            if (count($collection = $this->getService('RecruitNewcomer')->find($newcomerId))) {
                $newcomer = $collection->current();
                
                $processAbstract = $newcomer->getProcess()->getProcessAbstract();
                if ($processAbstract->isStrict()) {
                    $this->getService('Process')->goToNextState($newcomer);                
                } else {
                    $stateClass = HM_Process_Type_Programm_AdaptingModel::getStatePrefix() . $programmEventId;
                    $this->getService('Process')->setStateStatus($newcomer, $stateClass, HM_State_Abstract::STATE_STATUS_PASSED);
                }                
                
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Мероприятие завершено')));
            }            
        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не удалось завершить данное мероприятие')));
        }
        $this->_redirectToNewcomer();
    }
    */

    /*
    public function failAction()
    {
        if (($programmEventId = $this->_getParam('programm_event_id')) && ($newcomerId = $this->_getParam('newcomer_id'))) {
            
            if (count($collection = $this->getService('RecruitNewcomer')->find($newcomerId))) {
                $newcomer = $collection->current();

                $stateClass = HM_Process_Type_Programm_AdaptingModel::getStatePrefix() . $programmEventId;
                $this->getService('Process')->goToFail($newcomer, $stateClass);
                
                $newcomer->result = HM_Recruit_Newcomer_NewcomerModel::RESULT_FAIL_DEFAULT;
                $this->getService('RecruitNewcomer')->update($newcomer->getValues());

                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Сессия адаптации завершена')));
            }            
        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не удалось завершить сессию адаптации')));
        }
        $this->_redirectToNewcomer();
    }
    
    public function updateActions($processId, $actions)
    {
        if ($processId) {
            // если процесс уже стартовал, unset the action
            $this->unsetAction($actions, array('module' => 'newcomer', 'controller' => 'list', 'action' => 'start'));
        }
        return $actions;
    }
    */
    
    public function updateStatus($status)
    {
        return HM_Recruit_Newcomer_NewcomerModel::getStatus($status);
    }

    public function updateDebt($debt)
    {
        return HM_Recruit_Newcomer_NewcomerModel::getDebt($debt);
    }

    public function updateCurrentState($state, $debt)
    {
        $return = HM_Recruit_Newcomer_NewcomerModel::getState($state);
        if ($debt) {
            $return = '<span class="hm-newcomer-debt" title="Задолжность">!</span> '.$return;
        }
        return $return;
    }

    public function updateWelcomeTrainingColumn($welcomeTraining)
    {
        return $welcomeTraining ? _('Да') : _('Нет');
    }

    public function updateResult($status, $finalComment)
    {
        $status = HM_Recruit_Newcomer_NewcomerModel::getResultStatus($status);
        $status = '<span title="'.$finalComment.'">'.$status.'</span>';
        return $status;
    }
    
    public function _redirectToNewcomer()
    {
        if ($newcomerId = $this->_getParam('newcomer_id')) {
            $url = $this->view->url(array('module' => 'newcomer', 'controller' => 'report', 'action' => 'index', 'newcomer_id' => $newcomerId, 'programm_event_id' => null));
            $this->_redirector->gotoUrl($url, array('prependBase' => false));
        } else {
            parent::_redirectToIndex();
        }
    }

    //
    protected function _processPost($post)
    {
        $newPost = array();
        foreach ($post as $key => $value) {
            $parts = explode('_', $key);
            if (count($parts) == 3) {
                if (!is_array($newPost[$parts[0]])) $newPost[$parts[0]] = array();
                if (!is_array($newPost[$parts[0]][$parts[1]])) $newPost[$parts[0]][$parts[1]] = array();
                $newPost[$parts[0]][$parts[1]][$parts[2]] = $value;
            }
        }
        return $newPost;
    }

    public function completeAction()
    {
        if ($newcomerId = $this->_getParam('newcomer_id')) {
            $this->getService('RecruitNewcomer')->completeSession($newcomerId);
            $this->_flashMessenger->addMessage(_('Сессия адаптации успешно завершена'));
        }
        $this->_redirectToAdaptation();
    }

    public function abortAction()
    {
        if ($newcomerId = $this->_getParam('newcomer_id')) {
            $this->getService('RecruitNewcomer')->abortSession($newcomerId);
            $this->_flashMessenger->addMessage(_('Сессия адаптации отменена'));
        }
        $this->_redirectToAdaptation();
    }

    public function _redirectToAdaptation()
    {
        if ($newcomerId = $this->_getParam('newcomer_id')) {
            $url = $this->view->url(array('module' => 'newcomer', 'controller' => 'report', 'action' => 'index', 'newcomer_id' => $newcomerId, 'programm_event_id' => null));
            $this->_redirector->gotoUrl($url, array('prependBase' => false));
        } else {
            parent::_redirectToIndex();
        }
    }

    public function wordWelcomeTrainingAction()
    {
        $data = $options = $filesMapping = array();

        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $newcomerIds = explode(',', $postMassIds);
            if (count($newcomerIds)) {
                $collection = $this->getService('RecruitNewcomer')->fetchAllDependence(array('User', 'Position'), array(
                    'newcomer_id IN (?)' => $newcomerIds,
                ));

                if (count($collection)) {

                    $departments = array();
                    $positionIds = $collection->getList('position_id');
                    $positions = $this->getService('Orgstructure')->fetchAllDependence(array('Parent'), array(
                        'soid IN (?)' => $positionIds,
                    ));
                    foreach ($positions as $position) {
                        $department = count($position->parent) ? $position->parent->current() : false;
                        $departments[$position->soid] = $department ? $department->name : '';
                    }

                    $this->_helper->viewRenderer->setNoRender();

                    $data['table_1'] = array(array('','','','',''));
                    if (count($collection)) {

                        $data['table_1'] = array();

                        foreach ($collection as $newcomer) {

                            $user = count($newcomer->user) ? $newcomer->user->current() : false;
                            $position = count($newcomer->position) ? $newcomer->position->current() : false;

                            $row = array(
                                'fio' => $user ? $user->getName() : '',
                                'department' => $departments[$position->soid] ? : '',
                                'position' => $position ? $position->name : '',
                            );
                            $data['table_1'][] = $row;
                        }
                    }

                    //строим отчет!
                    $this->getService('PrintForm')->makePrintForm(HM_PrintForm::TYPE_WORD, HM_PrintForm::FORM_WELCOME_TRAINING, $data, "Список пользователей на welcome-тренинг", $options, true, $filesMapping);

                }
            }
        }

        $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Сессии адаптации не найдены')));
        $this->_redirector->gotoSimple('index', 'index', 'default');
    }
}
