<?php
class Session_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid {
        newAction as traitNewAction;
        editAction as traitEditAction;
    }

    const SESSION_NEW_ERRORS = 'sessionNewErrors';

    protected $_sessionUsersCache;

    public function init()
    {
        $form = new HM_Form_Sessions();
        $this->_setForm($form);
        parent::init();
    }

    public function colorAction() {


        $subs = $this->getService('AtSession')->fetchAll()->getList('session_id');

        foreach ($subs as $subid) {
            $data = array(
                'session_id' => (int)$subid,
                'base_color' => $this->getService('AtSession')->generateColor()
            );
            $this->getService('AtSession')->update($data);
        }
        exit('OK');
    }

    public function newAction()
    {
        $this->view->setSubHeader(_('Создание оценочной сессии'));
        $sessionComment = $this->getService('Option')->getOption('sessionComment');

        self::traitNewAction();

        $this->view->form->getElement('description')->setValue($sessionComment);
    }

    public function editAction()
    {
        $this->view->setSubHeader(_('Редактирование оценочной сессии'));
        self::traitEditAction();
    }

    public function indexAction()
    {
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", $sorting = 'name_ASC');
        }
        $select = $this->getService('AtSession')->getSelect();

        $select->from(
            array(
                's' => 'at_sessions'
            ),
            array(
                'session_id',
                'workflow_id' => 's.session_id',
                'name',
                'cycle' => 'c.name',
                'checked_soids',
                'begin_date',
                'end_date',
                'state',
            )
        );

        $select
            ->joinLeft(array('c' => 'cycles'), 'c.cycle_id = s.cycle_id', array())
            ->joinLeft(array('sop' => 'state_of_process'), 's.session_id = sop.item_id AND sop.process_type = '.HM_Process_ProcessModel::PROCESS_SESSION, array())
            ->where('programm_type = ?', HM_Programm_ProgrammModel::TYPE_ASSESSMENT);

        /************ responsibility  *************/

        if (!$this->currentUserRole(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER)) {
            if (count($collection = $this->getService('AtSession')->fetchAll(array('programm_type = ?' => HM_Programm_ProgrammModel::TYPE_ASSESSMENT)))) {
                $allowedSessionIds = array();
                if ($this->currentUserRole(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {

                    // для руководителей проверяем не входит ли его подразделение в один из checked_soids (смотрим вверх)
                    // @todo: избавиться от isGrandOwner, использовать возможности NestedSet
                    $department = $this->getService('Orgstructure')->getDefaultParent();
                    foreach ($collection as $session) {

                        if (!$session->checked_soids) continue;
                        $checkedSoids = explode(',', $session->checked_soids);

                        $allow = false;
                        $allowIfAllIncluded = true;

                        foreach ($checkedSoids as $checkedSoid) {

                            if ($this->getService('Orgstructure')->isGrandOwner($department->soid, $checkedSoid)) {
                                $allow = true;
                                break;
                            } elseif (!$this->getService('Orgstructure')->isGrandOwner($checkedSoid, $department->soid)) {
                                $allowIfAllIncluded = false;
                            }
                        }
                        if ($allow || $allowIfAllIncluded) $allowedSessionIds[] = $session->session_id;
                    }
                } elseif ($this->currentUserRole(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL)) {

                    // для менеджеров проверяем входят ли все checked_soids в область отв-сти (смотрим вниз)
                    $userId = $this->getService('User')->getCurrentUserId();
                    foreach ($collection as $session) {
                        if (!$session->checked_soids) continue;
                        $checkedSoids = explode(',', $session->checked_soids);

                        $allow = true;
                        foreach ($checkedSoids as $checkedSoid) {
                            if (!$this->getService('Responsibility')->isResponsibleFor($userId, HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE, $checkedSoid)) {
                                $allow = false;
                                break;
                            }
                        }
                        if ($allow) $allowedSessionIds[] = $session->session_id;
                    }
                }
                if (count($allowedSessionIds)) {
                    $select->where('session_id IN (?)', $allowedSessionIds);
                } else {
                    $select->where('1 = ?', 0);
                }
            }
        }

        /************ responsibility end *************/

        $cycles = $this->getService('Cycle')->fetchAll('newcomer_id IS NULL')->getList('name');

        $grid = $this->getGrid($select, array(
            'session_id' => array('hidden' => true),
            'workflow_id' => array(
                 'title' => _('Бизнес-процесс'), // бизнес проуцесс
                 'callback' => array(
                     'function' => array($this, 'printWorkflow'),
                     'params' => array('{{workflow_id}}'),
                 ),
                 'sortable'=>false
             ),
            'name' => array(
                'title' => _('Название'),
                'callback' => array(
                    'function'=> array($this, 'updateName'),
                    'params'=> array('{{session_id}}', '{{name}}')
                )
            ),
            'cycle' => array(
                'title' => _('Оценочный период'),
            ),
            'checked_soids' => array(
                'title' => _('Участники'),
                'callback' => array(
                    'function'=> array($this, 'departmentsCache'),
                    'params'=> array('{{checked_soids}}')
                )
            ),
            'begin_date' => array(
                'title' => _('Дата начала'),
                'format' => 'date',
            ),
            'end_date' => array(
                'title' => _('Дата окончания'),
                'format' => 'date',
            ),
            'state' => array(
                'title' => _('Статус'),
                'callback' => array(
                    'function'=> array('HM_At_Session_SessionModel', 'getStateTitle'),//$this, 'updateState'),
                    'params'=> array('{{state}}')
                )
            ),
        ),
        array(
            'name' => null,
            'begin_date' => array('render' => 'Date'),
            'end_date' => array('render' => 'Date'),
            'cycle' => array('values' => $cycles),
            'state' => array('values' => HM_At_Session_SessionModel::getStates()),
            'workflow_id' => array(
                'render' => 'process',
                'values' => Bvb_Grid_Filters_Render_Process::getStates('HM_At_Session_SessionModel', 'session_id'),
               'field4state' => 'sop.current_state',
            ),
        ));

        $grid->addAction(array(
            'module' => 'session',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('session_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'session',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('session_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );


        $grid->addMassAction(
            array(
                'module' => 'session',
                'controller' => 'list',
                'action' => 'delete-by',
            ),
            _('Удалить оценочные сессии'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function myAction()
    {
        $listSwitcher = $this->_getParam('list-switcher', HM_At_Session_SessionModel::STATE_ACTUAL);

        if ($this->getService('Acl')->checkRoles([HM_Role_Abstract_RoleModel::ROLE_ENDUSER])) {

            $currentUserId = $this->getService('User')->getCurrentUserId();
            $selfVacancySessions = [0];
            if (count($selfVacancies = $this->getService('RecruitVacancy')->getSelfVacanciesToHide())) {
                $selfVacancySessions = $selfVacancies->getList('session_id');
            }

            // @todo: отсортировать по 'Session.begin_date'; в MSSQL не работает
            $sessionsAsRespondent = $sessionsAsUser = [];
            if (count($collection = $this->getService('AtSession')->fetchAllDependenceJoinInner('SessionUser', $this->quoteInto(
                [
                    'self.session_id NOT IN (?) AND ',
                    'self.state = ? AND ',
//                    'self.programm_type = ? AND ',
                    'SessionUser.user_id = ?',
                ],
                [
                    $selfVacancySessions,
                    $listSwitcher,
//                    HM_Programm_ProgrammModel::TYPE_ASSESSMENT, // не будем мучить рядовых пользователей разными сессиями; только регулярными
                    $currentUserId,
                ]
            )))) {
                $sessionsAsUser = $collection->asArrayOfObjects();
            }

            if (count($collection = $this->getService('AtSession')->fetchAllDependenceJoinInner('SessionRespondent', $this->quoteInto(
                [
                    'self.session_id NOT IN (?) AND ',
                    'self.state = ? AND ',
                    'self.programm_type = ? AND ',
                    'SessionRespondent.user_id = ?',
                ],
                [
                    $selfVacancySessions,
                    $listSwitcher,
                    HM_Programm_ProgrammModel::TYPE_ASSESSMENT,
                    $currentUserId,
                ]
            )))) {
                $sessionsAsRespondent = $collection->asArrayOfObjects();
            }

            $this->view->listSwitcher = $listSwitcher;
            $sessions = $sessionsAsRespondent + $sessionsAsUser;

            foreach ($sessionsAsUser as $sessionId => $session) {
                if (isset($sessionsAsRespondent[$sessionId])) {
                    $sessions[$sessionId]->users = $session->users; // дальше в helper'е они еще фильтруются; здесь в users почему-то все юзеры
                }
            }

            $this->view->sessions = $sessions;
        }
    }

    public static function myPlainify($data, $view = null)
    {
        $plainData = [];

        /** @var HM_At_Session_SessionModel $session */
        foreach ($data['sessions'] as $session) {

            $timeProgress = $session->getTimeProgress();

            // todo: Сейчас перенёс логику из старого хелпера, неплохо бы фильтровать юзеров на выборке
            $userId = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
            $foundedSessionUser = $foundedSessionRespondent = false;
            if (count($session->users)) {
                foreach ($session->users as $sessionUser) {
                    if ($sessionUser->user_id == $userId) {
                        $foundedSessionUser = $sessionUser;
                        break;
                    }
                }
            }

            if (count($session->respondents)) {
                foreach ($session->respondents as $sessionRespondent) {
                    if ($sessionRespondent->user_id == $userId) {
                        $foundedSessionRespondent = $sessionRespondent;
                        break;
                    }
                }
            }

            $assessmentStatistics = [];

            if ($timeProgress !== false) {
                $assessmentStatistics[] = [
                    'title' => _('Прошло времени'),
                    'type' => 'time',
                    'value' => $timeProgress,
                    'maxValue' => 100
                ];
            }

            if ($foundedSessionRespondent) {
                $assessmentStatistics[] = [
                    'title' => _('Процент заполнения'),
                    'type' => 'percent',
                    'value' => (int)$foundedSessionRespondent->progress,
                    'maxValue' => 100
                ];
            }

            if ($foundedSessionUser) {
                $assessmentStatistics[] = [
                    'title' => _('Процент прохождения'),
                    'type' => 'percent',
                    'value' => (int)$foundedSessionUser->progress,
                    'maxValue' => 100
                ];
            }

            $plainSession = [
                'assessmentUrl' => $view->url(['module' => 'session', 'controller' => 'event', 'action' => 'my', 'session_id' => $session->session_id]),
                'assessmentTitle' => $session->name,
                'assessmentDates' => [
                    'begin' => $session->_getBeginPlanify(),
                    'end' => $session->_getEndPlanify(),
                ],
                'assessmentStatistics' => $assessmentStatistics
            ];

            $plainData['sessions'][] = $plainSession;
        }

        return $plainData;
    }


    public function create($form)
    {
        $values = $form->getValues();

        // как правильно?
        $values['begin_date'] = substr($values['begin_date'], 6, 4) . '-' . substr($values['begin_date'], 3, 2) . '-' . substr($values['begin_date'], 0, 2);
        $values['end_date'] = substr($values['end_date'], 6, 4) . '-' . substr($values['end_date'], 3, 2) . '-' . substr($values['end_date'], 0, 2).' 23:59:59';
        $values['initiator_id'] = $this->getService('User')->getCurrentUserId();
        $values['programm_type'] = HM_Programm_ProgrammModel::TYPE_ASSESSMENT;
        unset($values['session_id']);

        $res = $this->getService('AtSession')->insert($values);
    }

    public function update($form)
    {
        $values = $form->getValues();

        $values['begin_date'] = substr($values['begin_date'], 6, 4) . '-' . substr($values['begin_date'], 3, 2) . '-' . substr($values['begin_date'], 0, 2);
        $values['end_date'] = substr($values['end_date'], 6, 4) . '-' . substr($values['end_date'], 3, 2) . '-' . substr($values['end_date'], 0, 2);

        unset($values['checked_items']);
        unset($values['item_type']);

        $res = $this->getService('AtSession')->update($values);
    }

    public function delete($id) {
        $this->getService('AtSession')->delete($id);
    }

    public function createFromStructureAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();
        if (!$this->_getParam('postMassIds_grid')) {
            if ($form->isValid($request->getParams())) {
                $result = $this->create($form);

                $showSuccess = $this->showErrors();
                if ($result != NULL && $result !== TRUE) {
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $this->_getErrorMessage($result)));
                    $this->_redirector->gotoSimple('index');
                } else {
                    if ($showSuccess) {
                        $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_INSERT));
                    }

                    $this->_redirector->gotoSimple('index');
                }
            }
        } else {
            $form->setDefault('item_type', $this->_getParam('item-type', 'soid'));
            $form->setDefault('checked_items', $this->_getParam('postMassIds_grid'));
        }
        $sessionComment = $this->getService('Option')->getOption('sessionComment');
        $form->getElement('description')->setValue($sessionComment);
//        $form->getElement('goal')->setValue(_('Регулярная оценка персонала'));
        $this->view->form = $form;
    }

    public function setDefaults(Zend_Form $form)
    {
        $sessionId = $this->_getParam('session_id', 0);
        $session = $this->getService('AtSession')->find($sessionId)->current();
        $data = $session->getData();
        $data['begin_date'] = date('d.m.Y', strtotime($data['begin_date']));
        $data['end_date'] = date('d.m.Y', strtotime($data['end_date']));
//        $data['goal'] = $session->goal;
        $form->populate($data);
    }

    public function updateName($sessionId, $name)
    {
       return '<a href="' . $this->view->url(array('controller' => 'user', 'action' => 'list', 'session_id' => $sessionId)) . '">' . $this->view->escape($name) . '</a>';
    }

    // в одном списке показываем и подразделения и должности, родительский departmentsCache не годится
    public function departmentsCache($field, $select = null, $isPosition = false)
    {
     $soids = array();
        if(count($this->departmentCache) == 0){
         $collection = $this->getService('AtSession')->fetchAll();
         if (count($collection)) {
                foreach ($collection as $item) {
                    if (!empty($item->checked_soids)) $soids = array_merge($soids, explode(',', $item->checked_soids));
                }
            }
         if (!count($soids)) return _('Нет');
            $this->departmentCache = $this->getService('Orgstructure')->fetchAll(array('soid IN (?)' => $soids))->asArrayOfObjects();
        }

        $fields = array_filter(array_unique(explode(',', $field)));

        $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . sprintf(_n('элемент оргструктуры plural', '%s элемент оргструктуры', count($fields)), count($fields)) . '</p>') : array();
        foreach($fields as $value){
            if (count($this->departmentCache) ) {
                if (isset($this->departmentCache[$value])) {
                          $tempModel = $this->departmentCache[$value];
                }
                if ($tempModel) $result[] = "<p>{$tempModel->name}</p>";
            }
        }
        if($result)
            return implode('',$result);
        else
            return _('Нет');
    }

    public function updateState($state)
    {
        return HM_At_Session_SessionModel::getStateTitle($state);
    }

    public function printWorkflow($sessionId)
    {
        if ($this->_sessionsCache === null) {
            $this->_sessionsCache = array();
            $collection = $this->getService('AtSession')->fetchAll();
            if (count($collection)) {
                foreach ($collection as $item) {
                    $this->_sessionsCache[$item->session_id] = $item;
                }
            }
        }
        if ($sessionId && count($this->_sessionsCache) && array_key_exists($sessionId, $this->_sessionsCache)){
            $model = $this->_sessionsCache[$sessionId];
            $this->getService('Process')->initProcess($model);
            return $this->view->workflowBulbs($model);
        }
        return '';
    }

    public function workflowAction()
    {
        $sessionId = $this->_getParam('index', 0);

        if(intval($sessionId) > 0){
            $model =  $this->getService('AtSession')->find($sessionId)->current();
            $this->getService('Process')->initProcess($model);
            $this->view->model = $model;

            if ($this->isAjaxRequest()) {
                $this->view->workflow = $this->view->getHelper("Workflow")->getFormattedDataWorkflow($model);
            }
        }
    }

    public function showErrors()
    {
        $vars = $this->view->getVars();
        if ($errors = $vars[self::SESSION_NEW_ERRORS]) {

            /** @var HM_Orgstructure_OrgstructureService $orgstructureService */
            $orgstructureService = $this->getService('Orgstructure');

            $msg = sprintf("%s: ", _('При назначении на сессию произошли следующие ошибки'));

            foreach ($errors as $userStatus => $errorCodes) {

                $key = $userStatus == HM_At_Session_SessionModel::ASSIGN_RESPONDENTS_KEY ? _('респондентов') : _('участников');

                if(!count($errors[$userStatus]))
                    continue;

                $msg.= sprintf("%s", _('при назначении') . ' ' . $key . ' - ');

                foreach ($errorCodes as $errorCode => $positions) {
                    $errorMessage = HM_At_Session_SessionModel::getErrorMessage($errorCode);

                    $allFios = array();
                    foreach ($positions as $position => $fios) {
                        $allFios = array_merge($allFios, $fios);
                    }

                    $msg .= sprintf('%s: %s ',
                        lcfirst($errorMessage),
                        implode(', ', array_unique($allFios)) // сильно продублированы фио
                    );

                }
            }

            $this->_flashMessenger->addMessage(['message' => $msg, 'type' => HM_Notification_NotificationModel::TYPE_NOTICE, 'hasMarkup' => true]);
            return !count($errors);
        }
    }
}
