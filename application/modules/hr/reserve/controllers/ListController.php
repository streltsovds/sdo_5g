<?php
class Reserve_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $_reservesCache = null;

    public function init()
    {
        $this->_defaultService = $this->getService('HrReserve');
        return parent::init();
    }

    public function indexAction()
    {
        if ((!$this->isGridAjaxRequest()) && ($this->_request->getParam('cyclegrid') === null)) {
            $this->_request->setParam('year', date('Y'));
        }

        $this->view->setHeader(_('Кадровый резерв'));
        
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'created_DESC');
        }
        
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)) {
            $default = Zend_Registry::get('session_namespace_default');
            $page = sprintf('%s-%s-%s', 'reserve', 'list', 'index');
            $filter = $this->_request->getParam("filter");
            if (empty($filter) && empty($default->grid[$page]['grid']['filters'])){
                $default->grid[$page]['grid']['filters']['recruiters'] = $this->getService('User')->getCurrentUser()->LastName;
            }
        }        

        /* @todo: когда грид стабилизируется - выкинуть все hidden-поля */
        $select = $this->getService('HrReserve')->getSelect();

        $nowLaterEnd    = "(DATEDIFF(day, sopd.end_date_planned, NOW()) < 1)";
        $beforeOpenEnd  = "(DATEDIFF(day, sopd.end_date_planned, NOW()) >= -6 )";
        $beforePlanEnd  = "(DATEDIFF(day, sopd.end_date_planned, NOW()) >= -13)";
        $stateOpen      = "(sopd.state = 'HM_Hr_Reserve_State_Open')";
        $statePlan      = "(sopd.state = 'HM_Hr_Reserve_State_Plan')";
        $hrStateOpen    = "hr.state_id=".HM_Hr_Reserve_ReserveModel::PROCESS_STATE_OPEN;
        $hrStatePlan    = "hr.state_id=".HM_Hr_Reserve_ReserveModel::PROCESS_STATE_PLAN;

        $select->from(
            array(
                'hr' => 'hr_reserves'
            ),
            array(
                'MID' => 'p.MID',
                'user_id' => 'p.MID',
                'hr.reserve_id',
                'hr.created',
                'workflow_id' => 'hr.reserve_id',
                'hr_name' => 'hr.name',
                'department' => 'so.name',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),

                // привёл к общему виду, как в адаптации и ротации
                // красные выделения нужны не только для этих двух, но для всех имеющихся этапов
                'debt' => new Zend_Db_Expr("
                    CASE WHEN(DATEDIFF(day, NOW(), sopd.end_date_planned) < 0) AND hr.status != " . HM_Hr_Reserve_ReserveModel::STATE_CLOSED . " THEN 1 ELSE 0 END "),

                // <-- про week знаю, но почему-то 2 недели дают 17 дней ...
                'pre_ipr' => new Zend_Db_Expr("
                    CASE WHEN ".
                        $nowLaterEnd.  " AND ".
                        $beforeOpenEnd." AND ".
                        $stateOpen.    " AND ".
                        $hrStateOpen.  " THEN 1 ELSE 0 END "),
                'pre_res' => new Zend_Db_Expr("
                    CASE WHEN ".
                        $nowLaterEnd.  " AND ".
                        $beforePlanEnd." AND ".
                        $statePlan.    " AND ".
                        $hrStatePlan.  " THEN 1 ELSE 0 END "),
                // -->
                'position_name' => 'hrp.name',
                'cycle' => 'c.name',
                'asu.total_competence',
                'asu.total_kpi',
            )
        );

        // @todo: руководитель может быть в другом подразделении;
        // нужно кэшировать при создании сессии адаптации
        $select
            ->joinLeft(array('hrp' => 'hr_reserve_positions'), 'hr.reserve_position_id = hrp.reserve_position_id', array())
            ->joinLeft(array('sop' => 'state_of_process'), 'hr.reserve_id = sop.item_id AND sop.process_type = '.HM_Process_ProcessModel::PROCESS_PROGRAMM_RESERVE, array())
            ->joinLeft(array('sopd' => 'state_of_process_data'), 'sop.current_state = sopd.state AND sop.state_of_process_id = sopd.state_of_process_id', array())
            ->joinLeft(array('sp' => 'structure_of_organ'), 'sp.soid = hr.position_id', array())
            ->joinLeft(array('so' => 'structure_of_organ'), 'so.soid = sp.owner_soid', array())
            ->joinLeft(array('p' => 'People'), 'p.MID = hr.user_id', array())
            ->joinLeft(array('c' => 'cycles'), 'c.cycle_id = hr.cycle_id', array())
            ->joinLeft(array('asu' => 'at_session_users'), 'asu.reserve_id = hr.reserve_id', array())
            ->group(
                array(
                    'p.MID',
                    'p.LastName',
                    'p.FirstName',
                    'p.Patronymic',
                    'hr.reserve_id',
                    'hr.created',
                    'hr.name',
                    'hr.result',
                    'hr.status',
                    'hr.state_id',
                    'hrp.name',
                    'so.name',
                    'sopd.end_date_planned',
                    'sopd.state',
                    'c.name',
                    'asu.total_competence',
                    'asu.total_kpi',
                )
            );

//        exit($select->__toString());

        $currentUser = $this->getService('User')->getCurrentUser();

        // в зависимости от роли пользователя показываем разные учётные записи
        switch ($currentUser->role) {

            case HM_Role_Abstract_RoleModel::ROLE_HR:
            case HM_Role_Abstract_RoleModel::ROLE_ATMANAGER:
            case HM_Role_Abstract_RoleModel::ROLE_DEAN:

                $select->joinLeft(array('hrr' => 'hr_reserve_recruiters'), 'hrr.reserve_id = hr.reserve_id', array());
                $select->joinLeft(array('r' => 'recruiters'), 'r.recruiter_id = hrr.recruiter_id', array());

                break;

            case HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL:
            case HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL:
            case HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL:

                $select->joinLeft(array('hrr' => 'hr_reserve_recruiters'), 'hrr.reserve_id = hr.reserve_id', array());
                $select->joinInner(array('r' => 'recruiters'), $this->quoteInto('r.recruiter_id = hrr.recruiter_id AND r.user_id = ?', $currentUser->MID), array());

                break;

            case HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR:

                $responsibility = $this->getService('Responsibility')->fetchOne(
                    $this->getService('Responsibility')->quoteInto(
                        array(
                            ' user_id = ? ',
                            ' AND item_type = ? '
                        ),
                        array(
                            $currentUser->MID,
                            HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE
                        )
                    )
                );

                if ($responsibility) {
                    $parentPosition = $this->getService('Orgstructure')->find($responsibility->item_id)->current();

                    $subSelect = $this->getService('Orgstructure')->getSelect()
                        ->from('structure_of_organ', array('soid'))
                        ->where('lft > ?', $parentPosition->lft)
                        ->where('rgt < ?', $parentPosition->rgt);

                    $select->where("hr.position_id IN (?)", $subSelect);
                } else {
                    $select->where('1 = 0');
                }
            break;

            default:
                $select->where('1 = 0');
        }

        if ($this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
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
                $select->where("hr.position_id IN (?)", $subSelect);
            } else {
                $select->where('1 = 0');
            }
        }

        $grid = $this->getGrid($select, array(
            'position_id' => array('hidden' => true),
            'pre_ipr' => array('hidden' => true),
            'pre_res' => array('hidden' => true),
            'position_type' => array('hidden' => true),
            'position_is_manager' => array('hidden' => true),
            'MID' => array('hidden' => true),
            'user_id' => array('hidden' => true),
            'reserve_id' => array('hidden' => true),
            'created' => array('hidden' => true),
            'workflow_id' => array(
                'title' => _('Бизнес-процесс'), // бизнес процесс
                'callback' => array(
                    'function' => array($this, 'printWorkflow'),
                    'params' => array('{{workflow_id}}'),
                ),
                'sortable'=>false,
                'position' => 1,
             ),
            'hr_name' => array(
                'title' => _('Сессия КР'),
                'position' => 2,
            ),
            'fio' => array(
                'title' => _('Пользователь'),
                'position' => 3,
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
                    ) . '{{MID}}' . '">' . '{{fio}}</a>'
            ),                
            'department' => array(
                'title' => _('Подразделение КР'),
                'position' => 4,
            ),
            'position_name' => array(
                'title' => _('Должность КР'),
                'position' => 5,
            ),
            'cycle' => array(
                'title' => _('Период КР'),
                'position' => 6,
            ),
            'debt' => array(
                'title' => _('Задолженность'),
                'callback' => array(
                    'function' => array($this, 'updateDebt'),
                    'params' => array('{{debt}}')
                ),
                'position' => 7,
            ),
            'total_competence' => array(
                'title' => _('Результат оценки компетенции'),
                'position' => 8,
            ),
            'total_kpi' => array(
                'title' => _('Результат выполнения задач, %'),
                'position' => 9,
            ),
        ),
        array(
            'hr_name' => null,
            'workflow_id' => array(
                'render' => 'process',
                'values' => Bvb_Grid_Filters_Render_Process::getStates('HM_Hr_Reserve_ReserveModel', 'reserve_id'),
                'field4state' => 'sop.current_state',
//                'field4state' => 'state_id',
            ),
            'fio' => null,
            'department' => array('render' => 'department'),
            'cycle' => null,
            'position_name' => null,
            'debt' => array(
                'values' => HM_Hr_Reserve_ReserveModel::getDebts(),
                'callback' => array(
                    'function' => array($this, 'debtFilter'),
                    'params'   => array(
                        'nowLaterEnd' => $nowLaterEnd,
                        'beforeOpenEnd' => $beforeOpenEnd,
                        'stateOpen' => $stateOpen,
                        'hrStateOpen' => $hrStateOpen,
                        'beforePlanEnd' => $beforePlanEnd,
                        'statePlan' => $statePlan,
                        'hrStatePlan' => $hrStatePlan,
                    )
                )
            ),
            'total_competence' => null,
            'total_kpi' => null,
        ));

        $grid->updateColumn('hr_name',
            array(
                'callback' => array(
                    'function'=> array($this, 'updateName'),
                    'params'=> array('{{hr_name}}', '{{reserve_id}}')
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
            'module' => 'reserve',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('reserve_id'),
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
            'module' => 'reserve',
            'controller' => 'report',
            'action' => 'user'
        ),
            array('reserve_id'),
            _('Отчёт')
        );

        $grid->addMassAction(
            array(
                'module' => 'reserve',
                'controller' => 'list',
                'action' => 'delete-by',
            ),
            _('Удалить сессии КР'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->setClassRowCondition("{{debt}}    > 0", 'highlighted' );
        $grid->setClassRowCondition("{{pre_ipr}} > 0", 'highlighted');
        $grid->setClassRowCondition("{{pre_res}} > 0", 'highlighted');

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function newAction()
    {
        $this->view->setHeader(_('Новая сессия КР'));
        $form = new HM_Form_Reserve();
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $values = $form->getValues();

                $this->makeReserveSession($values['reserve_position_id'], $values['user_id'][0], $values['cycle_id']);

                $this->_flashMessenger->addMessage(_('Сессия КР успешно создана'));
                $this->_redirectToIndex();
            }
        }

        $this->view->form = $form;
    }

    public function newFromRequestAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $cycle = $this->getClosestCycle();

        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {

                    $reserveRequest = $this->getService('HrReserveRequest')->find($id)->current();
                    if ($reserveRequest->status != HM_Hr_Reserve_Request_RequestModel::STATUS_NEW) continue;
                    $this->getService('HrReserveRequest')->setStatus($id, HM_Hr_Reserve_Request_RequestModel::STATUS_ACCEPTED);

                    $reservePosition  = $this->getService('HrReservePosition')->find($reserveRequest->position_id)->current();
                    $newReserveId   = $this->makeReserveSession($reservePosition->reserve_position_id, $reserveRequest->user_id, $cycle->cycle_id);
                    $this->getService('HrReserveRequest')->update(
                        array(
                            'reserve_request_id' => $reserveRequest->reserve_request_id,
                            'reserve_id' => $newReserveId
                        )
                    );
                }
                $this->_flashMessenger->addMessage(_('Сессии КР успешно созданы'));
            }
        } else {
            $reserveRequestId = $this->_getParam('reserve_request_id', 0);
            $reserveRequest   = $this->getService('HrReserveRequest')->find($reserveRequestId)->current();
            if ($reserveRequest->status != HM_Hr_Reserve_Request_RequestModel::STATUS_NEW) $this->_redirectToIndex();
            $this->getService('HrReserveRequest')->setStatus($reserveRequestId, HM_Hr_Reserve_Request_RequestModel::STATUS_ACCEPTED);

            $reservePosition  = $this->getService('HrReservePosition')->find($reserveRequest->position_id)->current();

            $newReserveId     = $this->makeReserveSession($reservePosition->reserve_position_id, $reserveRequest->user_id, $cycle->cycle_id);
            $this->getService('HrReserveRequest')->update(
                array(
                    'reserve_request_id' => $reserveRequest->reserve_request_id,
                    'reserve_id' => $newReserveId
                )
            );

            $this->_flashMessenger->addMessage(_('Сессия КР успешно создана'));
        }

        $this->_redirectToIndex();
    }

    public function editAction()
    {
        $form = new HM_Form_Reserve();
        $request = $this->getRequest();
        $reserveId = $request->getParam('reserve_id');
        $reserve = $this->getService('HrReserve')->findOne($reserveId);
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $data = $form->getValues();

                $data['reserve_id'] = $reserveId;
                $data['user_id'] = $data['user_id'][0];
                $data['state_change_date'] = date('Y-m-d');
                $reservePosition = $this->getService('HrReservePosition')->find($data['reserve_position_id'])->current();
                $data['position_id'] = $reservePosition->position_id;
                $this->getService('HrReserve')->update($data);

                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_UPDATE));
                $this->_redirectToIndex();
            }
        } else {
            if ($reserve) {
                $data = array();

                $data['reserve_id'] = $reserve->reserve_id;
                if ($reserve->user_id) {
                    if ($user = $this->getService('User')->findOne($reserve->user_id)) {
                        $data['user_id'] = array($reserve->user_id => $user->getName());
                    }
                }
                $data['reserve_position_id'] = $reserve->reserve_position_id;
                $data['begin_date'] = date('d.m.Y', strtotime($reserve->begin_date));
                $data['end_date'] = date('d.m.Y', strtotime($reserve->end_date));

                $form->populate($data);
            }

            $this->setDefaults($form);
        }
        $this->view->positionId = $reserve->position_id;
        $this->view->form = $form;
    }

    protected function getClosestCycle()
    {
        return $this->getService('Cycle')->fetchAll(
            array(
                'type = ?' => HM_Cycle_CycleModel::CYCLE_TYPE_RESERVE,
                'DATEDIFF(day, NOW(), begin_date) > ?' => 0
            )
        )->current();
    }

    public function makeReserveSession($reservePositionId, $userId, $cycleId)
    {
        $reservePosition = $this->getService('HrReservePosition')->find($reservePositionId)->current();
        $user            = $this->getService('User')->find($userId)->current();
        $cycle           = $this->getService('Cycle')->find($cycleId)->current();

        $reserve = $this->getService('HrReserve')->createByPosition($reservePosition, $user, $cycle->cycle_id);

        $this->getService('Process')->initProcess($reserve);
        $process = $reserve->getProcess();

        $maxPlanDate = '';
        $state = $this->getService('State')->getOne($this->getService('State')->fetchAllDependence('StateData', array(
            'process_type = ?' => $process->getType(),
            'item_id = ?' => $reserve->reserve_id,
        )));

        if ($state && count($state->stateData)) {
            foreach ($state->stateData as $item) {
                if ($item->state == 'HM_Hr_Reserve_State_Open') $maxPlanDate = new HM_Date($item->end_date);
            }
        }

        $href = Zend_Registry::get('view')->serverUrl('/hr/reserve/report/index/reserve_id/'.$reserve->reserve_id);
        $url = '<a href="'.$href.'">'.$href.'</a>';

        $user = $this->getService('User')->findOne($reserve->user_id);

        $messenger = $this->getService('Messenger');
        $messenger->setOptions(
            HM_Messenger::TEMPLATE_RESERVE_PLAN,
            array(
                'name' => $user->FirstName . ' ' . $user->Patronymic,
                'fill_plan_date' => date("d.m.Y", strtotime($maxPlanDate->get("dd.MM.yyyy"))),
                'url' => $url
            ),
            'reserve',
            $reserve->reserve_id
        );
        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $reserve->user_id);

        return $reserve->reserve_id;
    }

    public function mapStatus($status)
    {
        switch ($status) {
            case HM_Hr_Reserve_ReserveModel::STATE_PENDING:
                return _('Не начата');
            case HM_Hr_Reserve_ReserveModel::STATE_ACTUAL:
                return _('Идёт');
            case HM_Hr_Reserve_ReserveModel::STATE_CLOSED:
                return _('Закончена');
        }
    }

    public function evaluationAction()
    {
        $this->view->setHeader(_('Назначение куратора'));

        $form = new HM_Form_EvaluationUser();
        $reserveId = $this->_getParam('reserve_id');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $reserveId = $this->_getParam('reserve_id');
            $evaluationUserId = $this->_getParam('evaluation_user_id');
            if ($form->isValid($request->getParams())) {
                if (is_array($evaluationUserId)) {
                    $evaluationUserId = $evaluationUserId[0];
                }
                if ($evaluationUserId) {
                    $this->getService('HrReserve')->update(
                        array(
                            'reserve_id' => $reserveId,
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
            $reserve = $this->getService('HrReserve')->findOne($reserveId);
            if ($reserve) {
                $data = array();

                $data['reserve_id'] = $reserve->id;
                if ($reserve->evaluation_user_id) {
                    if ($evaluationUser = $this->getService('User')->findOne($reserve->evaluation_user_id)) {
                        $data['evaluation_user_id'] = array($reserve->evaluation_user_id => $evaluationUser->getName());
                    }
                }
                $form->populate($data);
            }
        }
        $this->view->form = $form;
    }

    public function printFormsAction()
    {
        $templateId = HM_PrintForm::FORM_RESERVE_PLAN;
        $reserveData = $this->reserveData($this->_getParam('reserve_id'));
        if (! count($reserveData)) {
            $this->getFrontController()->setBaseUrl('/');
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }
        $userData = array(
            'fio'    => $reserveData['fio'],     //ФИО пользователя, на которго открыта сессия КР в формате:
                                                 //[фамилия]+(пробел)+[имя]+(пробел)+[отчество]
            'job'    => $reserveData['job'],     //Связанная с ним должность из оргструктуры
            'dep'    => $reserveData['dep'],     //Структурное подразделение из оргструктуры
            'date'   => $reserveData['date'],    //Дата завершения сессии подбора по данному пользователю
            'chief'  => $reserveData['chief'],   //ФИО + контакты руководителя (из оргструктуры) в формате:
                                                 //[фамилия]+(пробел)+[имя]+(пробел)+[отчество]  + «(тел: »+[рабочий телефон] + “, email: “ + [Контактный e-mail] +”)”
            'curator'=> $reserveData['curator'], //ФИО + контакты куратора (оценивающее лицо из задачи 25767 + связанная с ним учетная запись) в формате:
                                                 //[фамилия]+(пробел)+[имя]+(пробел)+[отчество]  + «(тел: »+[рабочий телефон] + “, email: “ + [Контактный e-mail] +”)”
        );

        $userCourses = $this->reserveCourses($this->_getParam('reserve_id'));
        $courses = array();
        if (!count($userCourses)) $userCourses = array('', '', '');
        foreach ($userCourses as $course) {
            $courses[] = array('course' => $course);
        }

        $userTasks = $this->reserveTasks($this->_getParam('reserve_id'));
        $tasks = array();
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

        $outFileName = 'reserve_plan_'.$this->_getParam('reserve_id');

        $this->getService('PrintForm')->makePrintForm(HM_PrintForm::TYPE_WORD, $templateId, $data, $outFileName);
    }

    private function reserveData($reserve_id)
    {
        $result = array();
        $reserve = $this->getService('HrReserve')->getOne(
            $this->getService('HrReserve')->fetchAll(
                $this->getService('HrReserve')->quoteInto('reserve_id=?', $reserve_id)
            )
        );

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER))) {
            if ($this->getService('User')->getCurrentUserId() != $reserve->user_id) return array();
        }


        $user = $this->getService('User')->getOne(
            $this->getService('User')->fetchAll(
                $this->getService('User')->quoteInto('MID=?', $reserve->getValue('user_id'))
            )
        );
        $position = $this->getService('Orgstructure')->getOne(
            $this->getService('Orgstructure')->fetchAll(
                $this->getService('Orgstructure')->quoteInto('soid=?', $reserve->getValue('position_id'))
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
                $this->getService('User')->quoteInto('MID=?', $reserve->getValue('evaluation_user_id'))
            )
        );
        $positionDate = $user->getValue('PositionDate');

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

    private function reserveCourses($reserve_id)
    {
        $reserve = $this->getService('HrReserve')->getOne($this->getService('HrReserve')->findDependence(array('User', 'Cycle'), $reserve_id));

        $select = $this->getService('Subject')->getSelect();
        $subSelect = clone $select;
        $subSelectTc = clone $select;
        $subSelectClaimants = clone $select;

        $subSelect->from(array('Students'), array('MID', 'CID'))->where('MID = ?', $reserve->user_id);

        // строго говоря, этого недостаточно;
        // надо еще смотреть статус сессии, даты сессии, период сессии vs период КР и т.п.
        $subSelectTc
            ->from(array('tc_applications'), array('user_id', 'subject_id', 'status'))
            ->where('user_id = ?', $reserve->user_id);

        $subSelectClaimants
            ->from(array('claimants'), array('MID', 'CID'))
            ->where('status = ?', HM_Role_ClaimantModel::STATUS_NEW)
            ->where('MID = ?', $reserve->user_id);

        $select->from(array('s' => 'subjects'), array(
            'subid' => 's.subid',
            'name' => 's.name',
            'tcprovider'   => 'pr.provider_id',
            'provider' => 'pr.name',
            'price' => 's.price',
            'status' => 'd.MID',
            'claimant_status' => 'c.MID',
            'tc_status' => 'a.status',
        ))
            ->joinLeft(array('d' => $subSelect),
                's.subid = d.CID',
                array()
            )
            ->joinLeft(array('c' => $subSelectClaimants),
                's.subid = c.CID',
                array()
            )
            ->joinLeft(array('a' => $subSelectTc),
                's.subid = a.subject_id',
                array()
            )
            ->joinLeft(array('pr' => 'tc_providers'), 'pr.provider_id = s.provider_id', array())
            ->where('s.is_labor_safety != ?', 1)
            ->where('s.category != ?', HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_NECESSARY)
            ->where('d.MID != ?', '')
            ->group(array(
                's.subid',
                's.name',
                'pr.provider_id',
                'pr.name',
                's.price',
                'd.MID',
                'c.MID',
                'a.status',
            ));

        //Область ответственности
        if($this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_DEAN){
            $select = $this->getService('Responsibility')->checkSubjects($select, 's.subid');
        }

        $results = $select->query()->fetchAll();
        $out = array();
        foreach ($results as $result) {
            $out[] = $result['name'];
        }

        return $out;
    }

    private function reserveTasks($reserve_id)
    {
        $reserve = $this->getService('HrReserve')->getOne($this->getService('HrReserve')->findDependence(array('User', 'Cycle'), $reserve_id));
        if (count($reserve->cycle)) {
            $cycleId = $reserve->cycle->current()->cycle_id;
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
            ->where('uk.user_id = ?', $reserve->user_id)
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
        $this->getService('HrReserve')->delete($id);
    }    

    public function updateContactUserId($recruiterIds)
    {
        static $recruiters = false;
        
        if (!$recruiters) {
            $select = $this->getService('Recruiter')->getSelect();
            
            $select->from(array('r' => 'hrs'), array(
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


    public function printWorkflow($reserveId)
    {
        if ($this->_reservesCache === null) {
            $this->_reservesCache = array();
            $collection = $this->getService('HrReserve')->fetchAll();
            if (count($collection)) {
                foreach ($collection as $item) {
                    $this->_reservesCache[$item->reserve_id] = $item;
                }
            }
        }
        if ($reserveId && count($this->_reservesCache) && array_key_exists($reserveId, $this->_reservesCache)){
            $model = $this->_reservesCache[$reserveId];
            $this->getService('Process')->initProcess($model);
       
            return $this->view->workflowBulbs($model);
        }
        return '';
    }

    public function updateName($name, $reserveId)
    {
        return ' <a href="' .
            $this->view->url(
                array(
                    'module' => 'reserve',
                    'controller' => 'report',
                    'action' => 'index',
                    'reserve_id' => $reserveId,
                ), null, true
            ) . '">' . $name . '</a>';
    }

    public function updatePosition($name, $orgId, $type, $isManager, $reserveId)
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
            ) . $name;
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
        $reserveId = $this->_getParam('index', 0);

        if(intval($reserveId) > 0){

            $model =  $this->getService('HrReserve')->find($reserveId)->current();
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
        $reserveId  = $this->_getParam('reserve_id',0);
        $state = (int) $this->_getParam('state_id', 0);

        $currentState = $this->_defaultService->changeState($reserveId, $state);
        if ($currentState) {
            switch ($state) {
                case HM_State_Abstract::STATE_STATUS_FAILED:
                    $message = _('Сессия КР успешно отменена.');
                    break;
                default:
                    $reserve = $this->_defaultService->getOne($this->_defaultService->find($reserveId));
                    $state = $this->getService('Process')->getCurrentState($reserve);

                    $message = $state instanceof HM_Tc_Session_State_Complete
                        ? _('Сессия КР успешно завершена')
                        : _('Сессия КР успешно переведена на следующий этап');
            }
            $this->_flashMessenger->addMessage($message);
        } else {
            $reserve = $this->getOne($this->_defaultService->find($reserveId));
            $sessionState = $this->getService('Process')->getCurrentState($reserve);
            $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => $sessionState->onErrorMessage())
            );
        }
        $this->_redirector->gotoUrl($this->view->url(array(
            'module' => 'reserve',
            'controller' => 'list',
            'action' => 'index',
        )), array('prependBase' => false));
    }

    public function assignSessionsAction()
    {
        $this->view->setHeader(_('Назначение учебных сессий пользователям'));
        $form = new HM_Form_Sessions();

        $isLaborSafety = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL));
        $subjects = $users = $users2subjects = $users2reserves = array();
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {

                $subjectsCache = $this->getService('Subject')->fetchAll()->asArrayOfObjects();

                $collection = $this->getService('HrReserve')->fetchAllHybrid('User', 'Subject', 'Student', array('reserve_id IN (?)' => $ids));
                $users2reserves = $collection->getList('user_id', 'reserve_id');
                foreach ($collection as $reserve) {
                    if (count($reserve->courses)) {
                        foreach ($reserve->courses as $subject) {

                            // массив курсов для отображения в форме
                            $subjectId = ($subject->base == HM_Subject_SubjectModel::BASETYPE_SESSION) ? $subject->base_id : $subject->subid;

                            if ($isLaborSafety) {
                                if (!in_array($subjectId, HM_Subject_SubjectModel::getBuiltInCourses())) continue;
                            } else {
                                if (in_array($subjectId, HM_Subject_SubjectModel::getBuiltInCourses())) continue;
                            }

                            $subjects[$subjectId] = $subjectsCache[$subjectId];

                            // массив текущих назначений на курсы или сессии, чтобы не назначить лишнего
                            if (!count($reserve->user))  continue;
                            $users[$reserve->user_id] = $reserve->user->current();
                            $users2subjects[$reserve->user_id][] = $subjectId;
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

                        $values = $subject->getValues();
                        $sessionValues = $post['subject'][$subject->subid];

                        if ($sessionValues['sessionId']) {
                            $session = $this->getService('Subject')->getOne($this->getService('Subject')->find($sessionValues['sessionId']));
                        } elseif ($sessionValues['begin']) {
                            $values['base'] = HM_Subject_SubjectModel::BASETYPE_SESSION;
                            $values['type'] = HM_Subject_SubjectModel::TYPE_FULLTIME;
                            $values['period'] = HM_Subject_SubjectModel::PERIOD_DATES;
                            $date = new HM_Date($sessionValues['begin']);
                            $values['begin'] = $date->get('Y-MM-dd');
                            $values['end'] = $date->get('Y-MM-dd 23:59:59');
                            $values['name'] = sprintf('%s (сессия %s)', $values['name'], $sessionValues['begin']);
                            $values['base_id'] = $values['subid'];
                            $values['created'] = date('Y-m-d H:i:s');
                            unset($values['subid']);
                            $session = $this->getService('Subject')->insert($values);
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
                                    $this->getService('Subject')->assignStudent($session->subid, $user->MID, array('reserve_id' => $users2reserves[$user->MID]));
                                }
                            }
                        }
                    }

                    if ($this->_getParam('editnotifications',0)) {
                        $url = $this->view->url(array(
                                'module' => 'reserve',
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
                $collection = $this->getService('HrReserve')->fetchAllHybrid('User', 'Subject', 'Student', array('reserve_id IN (?)' => $ids));
                foreach ($collection as $reserve) {
                    if (count($reserve->courses)) {
                        foreach ($reserve->courses as $subject) {
                            if (!$subject->base_id) continue; // отсюда уведомляем только о сессиях
                            $subjects[$subject->subid] = $subject;
                            if (!isset($subjectUsers[$subject->subid])) $subjectUsers[$subject->subid] = array();
                            $subjectUsers[$subject->subid][] = $reserve->user_id;
                            if (count($reserve->user)) $users[$reserve->user_id] = $reserve->user->current();
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
            if ($user = $ls->user->current()) {
                $users[] = $user;
            }
        }


        return $this->_sendNotifications($notice, $users);
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
                $collection = $this->getService('HrReserve')->fetchAllDependence('ManagerUser', array('reserve_id IN (?)' => $ids));
                foreach ($collection as $reserve) {
                    if (count($reserve->managerUser)) {
                        $users[$reserve->manager_id] = $reserve->managerUser->current();
                    }
                }
            }
        }

        return $this->_sendNotifications($notice, $users);
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
                $collection = $this->getService('HrReserve')->fetchAllDependence('CuratorUser', array('reserve_id IN (?)' => $ids));
                foreach ($collection as $reserve) {
                    if (count($reserve->curatorUser)) {
                        $users[$reserve->curator_id] = $reserve->curatorUser->current();
                    }
                }
            }
        }

        return $this->_sendNotifications($notice, $users);
    }

    public function _sendNotifications($notice, $users)
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
                $collection = $this->getService('HrReserve')->fetchAllDependence('User', array('reserve_id IN (?)' => $ids));
                foreach ($collection as $reserve) {
                    if (count($reserve->user)) $listUsers[$reserve->user_id] = $reserve->user->current();
                }
            }
        }

        $list = '<ul>';
        foreach ($listUsers as $listUser) {
            $list .= '<li>'.$listUser->getName().'</li>';
        }
        $list .= '</ul>';

        if ($this->_getParam('sendnotifications',0)) {

            $request = $this->getRequest();
            if ($form->isValid($request->getParams())) {

                $notice->title = $form->getValue('title');
                $notice->message = $form->getValue('message');

                $messageParam = array(
                    'LIST' => $list,
                    'RECRUITER' => $this->getService('Recruiter')->getCurrentRecruiterInfo(),
                );

                $messenger = $this->getService('Messenger');

                $messenger->setOptions($notice->type, $messageParam);
                $messenger->forceTemplate($notice);
                foreach ($users as $user) {
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

    public function updateStatus($status)
    {
        return HM_Hr_Reserve_ReserveModel::getStatus($status);
    }

    public function updateDebt($debt)
    {
        return HM_Hr_Reserve_ReserveModel::getDebt($debt);
    }

    public function updateCurrentState($state, $debt)
    {
        $return = HM_Hr_Reserve_ReserveModel::getState($state);
        if ($debt) {
            $return = '<span class="hm-reserve-debt" title="Задолжность">!</span> '.$return;
        }
        return $return;
    }

    public function updateResult($status, $finalComment)
    {
        $status = HM_Hr_Reserve_ReserveModel::getResultStatus($status);
        $status = '<span title="'.$finalComment.'">'.$status.'</span>';
        return $status;
    }
    
    public function _redirectToReserve()
    {
        if ($reserveId = $this->_getParam('reserve_id')) {
            $url = $this->view->url(array('module' => 'reserve', 'controller' => 'report', 'action' => 'index', 'reserve_id' => $reserveId, 'programm_event_id' => null));
            $this->_redirector->gotoUrl($url, array('prependBase' => false));
        } else {
            parent::_redirectToIndex();
        }
    }

    /* ЭТАПЫ */

    public function planAction()
    {
        if ($reserveId = $this->_getParam('reserve_id')) {
            $this->getService('HrReserve')->planSession($reserveId);
            $this->_flashMessenger->addMessage(_('Процесс успешно переведён на следующий этап'));
        }
        $this->_redirectToReserve();
    }

    public function publishAction()
    {
        if ($reserveId = $this->_getParam('reserve_id')) {
            $this->getService('HrReserve')->publishSession($reserveId);

            $reserve   = $this->getService('HrReserve')->find($reserveId)->current();

            $href = Zend_Registry::get('view')->serverUrl('/hr/rotation/report/index/reserve_id/'.$reserve->reserve_id);
            $url = '<a href="'.$href.'">'.$href.'</a>';

            $user = $this->getService('User')->findOne($reserve->user_id);

            $position   = $this->getService('Orgstructure')->find($reserve->position_id)->current();
            $department = $this->getService('Orgstructure')->find($position->owner_soid)->current();

            $this->getService('Process')->initProcess($reserve);
            $process = $reserve->getProcess();

            $reportPeriod = '';
            $state = $this->getService('State')->getOne($this->getService('State')->fetchAllDependence('StateData', array(
                'process_type = ?' => $process->getType(),
                'item_id = ?' => $reserve->reserve_id,
            )));

            if ($state && count($state->stateData)) {
                foreach ($state->stateData as $item) {
                    if ($item->state == 'HM_Hr_Reserve_State_Publish') $reportPeriod = new HM_Date($item->end_date);
                }
            }

            $messenger = $this->getService('Messenger');
            $messenger->setOptions(
                HM_Messenger::TEMPLATE_RESERVE_REPORT,
                array(
                    'name' => $user->FirstName . ' ' . $user->Patronymic,
                    'begin_date' => date('d.m.Y', strtotime($reserve->begin_date)),
                    'end_date' => date('d.m.Y', strtotime($reserve->end_date)),
                    'reserve_position' => $position->name,
                    'reserve_department' => $department->name,
                    'report_date' => date("d.m.Y", strtotime($reportPeriod->get("dd.MM.yyyy"))),
                    'url' => $url
                ),
                'reserve',
                $reserve->reserve_id
            );
            $messenger->send(HM_Messenger::SYSTEM_USER_ID, $reserve->user_id);

            $this->getService('HrReserve')->update(
                array(
                    'reserve_id' => $reserve->reserve_id,
                    'report_notification_sent' => HM_Hr_Reserve_ReserveModel::REPORT_NOTIFICATION_SENT
                )
            );

            $this->_flashMessenger->addMessage(_('Процесс успешно переведён на следующий этап'));
        }
        $this->_redirectToReserve();
    }

    public function resultAction()
    {
        if ($reserveId = $this->_getParam('reserve_id')) {
            $this->getService('HrReserve')->resultSession($reserveId);

            $this->_flashMessenger->addMessage(_('Процесс успешно переведён на следующий этап'));
        }
        $this->_redirectToReserve();
    }

    public function completeAction()
    {
        if ($reserveId = $this->_getParam('reserve_id')) {
            $this->getService('HrReserve')->completeSession($reserveId);
            $this->_flashMessenger->addMessage(_('Сессия КР успешно завершена'));
        }
        $this->_redirectToReserve();
    }

    public function abortAction()
    {
        if ($reserveId = $this->_getParam('reserve_id')) {
            $this->getService('HrReserve')->abortSession($reserveId);
            $this->_flashMessenger->addMessage(_('Сессия КР отменена'));
        }
        $this->_redirectToReserve();
    }

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

    public function debtFilter($data)
    {
        extract($data);

        switch ($value) {
            case HM_Hr_Reserve_ReserveModel::DEBT_NO:
                $select->where("(DATEDIFF(day, NOW(), sopd.end_date_planned) > 0) OR (hr.status = " . HM_Hr_Reserve_ReserveModel::STATE_CLOSED . ")");
                break;
            case HM_Hr_Reserve_ReserveModel::DEBT_SOON:
                $select->where("($nowLaterEnd AND $beforeOpenEnd AND $stateOpen AND $hrStateOpen) OR ($nowLaterEnd AND $beforePlanEnd AND $statePlan AND $hrStatePlan)");
                break;
            case HM_Hr_Reserve_ReserveModel::DEBT_YES:
                $select->where("(DATEDIFF(day, NOW(), sopd.end_date_planned) < 0) AND (hr.status != " . HM_Hr_Reserve_ReserveModel::STATE_CLOSED . ")");
                break;
        }
    }

}