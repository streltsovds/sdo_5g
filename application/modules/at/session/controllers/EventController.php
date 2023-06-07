<?php
class Session_EventController extends HM_Controller_Action_Session
{
    protected $_eventsCache = null;
    protected $_usersCache = null;

    use HM_Controller_Action_Trait_Grid;
    
    public function listAction()
    {
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", $sorting = 'name_ASC');
        }
        
        $select = $this->getService('AtSessionEvent')->getSelect();

/*        
        нарушает дефолтный порядок сортировки, в pdo_mssql вообще ломается пагинатор
        уж лучше пусть не сбрасывается фильтр по ФИО, это всегда можно сделать руками
         
        $default = Zend_Registry::get('session_namespace_default');
        $page = sprintf('%s-%s-%s', 'session', 'event', 'list');
        $filter = $this->_request->getParam("filter");
        if($filter){
            if ($this->_getParam('usergrid')){
                $default->grid[$page]['grid']['filters']["user"] = $this->_getParam('usergrid');
                $default->grid[$page]['grid']['filters']["respondent"] = '';
            } elseif($this->_getParam('respondentgrid')) {
                $default->grid[$page]['grid']['filters']["respondent"] = $this->_getParam('respondentgrid');
                $default->grid[$page]['grid']['filters']["user"] = '';
            }
        } else {
            $default->grid[$page]['grid']['filters']["respondent"] = '';
            $default->grid[$page]['grid']['filters']["user"] = '';
        }
*/        
        
        $select->from(
            array(
                'ase' => 'at_session_events'
            ),
            array(
                'session_event_id' => new Zend_Db_Expr("DISTINCT(ase.session_event_id)"),
                'user_id' => 'ase.user_id',
                'quest_id' => 'ase.quest_id',
                'respondent_id' => 'ase.respondent_id',
                'ase.name',
                'user' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'respondent' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(pr.LastName, ' ') , pr.FirstName), ' '), pr.Patronymic)"),
                'method' => 'aet.method',
                'method_id' => 'aet.method',
                'relation_type' => 'aet.relation_type',
                'date' => new Zend_Db_Expr('MAX(asea.date_begin)'),
                'status',
                'status_id' => 'ase.status',
                'session_status_id' => 's.state',
                'programm_type' => 's.programm_type',
                'session_user_status_id' => 'asu.status',
                'first_user_ids' => new Zend_Db_Expr('GROUP_CONCAT(asp.first_user_id)'),
                'second_user_ids' => new Zend_Db_Expr('GROUP_CONCAT(asp.second_user_id)'),
            )
        );

        $select
             ->joinLeft(array('peu' => 'programm_events_users'), "ase.programm_event_user_id = peu.programm_event_user_id", array())
             ->joinLeft(array('pe' => 'programm_events'), "peu.programm_event_id = pe.programm_event_id", array())
            ->join(array('asu' => 'at_session_users'), 'ase.session_user_id = asu.session_user_id', array())
            ->join(array('s' => 'at_sessions'), 'ase.session_id = s.session_id', array())
            ->join(array('so' => 'structure_of_organ'), 'so.soid = asu.position_id', array())
            ->joinLeft(array('pr' => 'People'), 'pr.MID = ase.respondent_id', array())
            ->joinLeft(array('p' => 'People'), 'p.MID = ase.user_id', array())
            ->join(array('aet' => 'at_evaluation_type'), 'ase.evaluation_id = aet.evaluation_type_id', array())
            ->joinLeft(array('asea' => 'at_session_event_attempts'), 'asea.session_event_id = ase.session_event_id', array())
            ->joinLeft(array('asp' => 'at_session_pairs'), 'ase.session_event_id = asp.session_event_id', array())
            ->where('(pe.hidden IS NULL OR pe.hidden = 0)')
            ->where('ase.is_empty_quest IS NULL OR ase.is_empty_quest = ?', 0)
            ->where('ase.session_id = ?', $this->_session->session_id);

        if ($this->_currentPosition && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER))) {
            $select->where('so.owner_soid = ?', $this->_currentPosition->owner_soid); // @todo: нужно ещё всех вложенных
        }
        
        $select->group(array(
            'p.MID',
            'p.LastName',
            'p.FirstName',
            'p.Patronymic',
            'pr.MID',
            'pr.LastName',
            'pr.FirstName',
            'pr.Patronymic',
            'ase.status',
            'aet.relation_type',
            'aet.method',
            'ase.name',
            'ase.respondent_id',
            'ase.user_id',
            'ase.session_event_id',
            's.state',
            'asu.status',
            's.programm_type',
            'ase.quest_id',
            'ase.is_empty_quest'
        ));

        $methods = HM_At_Evaluation_EvaluationModel::getMethods(false);

//        exit ($select->__toString());
        $grid = $this->getGrid($select, array(
            'session_event_id' => array('hidden' => true),
            'quest_id' => array('hidden' => true),
            'relation_type' => array('hidden' => true),
            'programm_type' => array('hidden' => true),
            'user_id' => array('hidden' => true),
            'respondent_id' => array('hidden' => true),
            'status_id' => array('hidden' => true),
            'session_status_id' => array('hidden' => true),
            'session_user_status_id' => array('hidden' => true),
            'method_id' => array('hidden' => true),
            'first_user_ids' => array('hidden' => true),
            'second_user_ids' => array('hidden' => true),
            'name' => array(
                'title' => _('Название'),
                'callback' => array(
                    'function'=> array($this, 'updateName'),
                    'params' => array('{{session_event_id}}', '{{name}}', '{{respondent_id}}', '{{status_id}}', '{{session_status_id}}', '{{session_user_status_id}}', '{{programm_type}}', '{{method}}', $select)
                )
            ),
            'user' => array(
                'title' => _('Участник'),
                'callback' => array(
                    'function'=> array($this, 'updateMultipleUsers'),
                    'params' => array('{{user_id}}', '{{first_user_ids}}', '{{second_user_ids}}', $select)
                ),
                //'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => ''), null, true) . '{{user_id}}') . '{{user}}'
            ),
            'respondent' => array(
                'title' => _('Респондент'),
                'callback' => array(
                        'function'=> array($this, 'updateUser'),
                        'params' => array('{{respondent_id}}', '{{respondent}}', $select)
                ),
                //'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => ''), null, true) . '{{respondent_id}}') . '{{respondent}}'
            ),
            'method' => $this->_vacancy ? array('hidden' => true) : array(
                'title' => _('Методика оценки'),
                'callback' => array(
                    'function'=> array($this, 'updateMethod'),
                    'params' => array('{{method}}', '{{relation_type}}')
                )
            ),
            'date' => array('hidden' => true),
//             array(
//                 'title' => _('Дата последнего входа'),
//                 'format' => 'dateTime',
//             ),
            'status' => array(
                'title' => _('Статус'),
                'callback' => array(
                    'function'=> array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                )
            ),
        ),
        array(
            'name' => null,
            'user' => null,
            'respondent' => null,
            'date' => array('render' => 'date'),
            'status' => array('values' => HM_At_Session_Event_EventModel::getStatuses()),
            'method' => array('values' => $methods),
        ));

//       $grid->addMassAction(
//           array(
//               'module' => 'session',
//               'controller' => 'event',
//               'action' => 'delete-by',
//           ),
//           _('Удалить'),
//           _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
//       );

        $grid->addMassAction(
           array(
               'module' => 'session',
               'controller' => 'event',
               'action' => 'delete-results',
           ),
           _('Удалить результаты'),
           _('Вы уверены, что хотите полностью удалить результаты отмеченных мероприятий? Повторно заполнить форму можно будет только в случае нестрогой последовательности прохождения программы оценки.')
       );

        $grid->addAction(
            array('module' => 'session', 'controller' => 'report', 'action' => 'event'), // @todo: hardcode, event'ы бывают разных типов
            array('session_event_id'),
            _('Отчёт')
        );

        $grid->addAction(
            array('module' => 'session', 'controller' => 'report', 'action' => 'psycho'), // @todo: hardcode, event'ы бывают разных типов
            array('session_event_id'),
            _('Результаты')
        );

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                  'params'   => array('{{status_id}}', '{{method_id}}', '{{respondent_id}}', '{{quest_id}}')
            )
        );

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->calendarAllowed = in_array($this->_session->programm_type, array(HM_Programm_ProgrammModel::TYPE_ADAPTING, HM_Programm_ProgrammModel::TYPE_RECRUIT));

        if (
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) &&
            $this->getService('User')->isRoleExists($this->getService('User')->getCurrentUserId(), HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR) &&
            in_array($this->_session->programm_type, array(HM_Programm_ProgrammModel::TYPE_ADAPTING))
        ) {
            // если потенциально имеет роль - переключаем автоматом
            $this->view->switchRole = HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR;
        }

    }

    public function myAction()
    {
        $groups = $programmEventsCache = [];

        $userId = $this->getService('User')->getCurrentUserId();

        $condition = $this->getService('AtSessionEvent')->quoteInto([
            'session_id = ? AND ',
            'respondent_id = ?'
        ], [
            $this->_session->session_id,
            $userId,
        ]);

        $events = $this->getService('AtSessionEvent')->fetchAllHybrid([
            'Session',
            'SessionUser',
            'SessionRespondent',
            'SessionEventRespondent',
            'SessionEventUser',
            'SessionEventMultiUser',
            'Evaluation',
            'EvaluationResult',
            'ProgrammEventUser',
            'Quest',
        ],
            ['Programm', 'ProgrammEvent'],
            'ProgrammEventUser',
            $condition); // @todo: найти правильный критерий сортировки
        if (count($events)) {

            $events = $events->asArrayOfObjects();
            usort($events, ['Session_EventController', '_sortByProgramm']);

            foreach ($events as $event) {

                if (!count($event->evaluation)) continue;

                /** @var HM_At_Evaluation_EvaluationModel $evaluation */
                $evaluation = $event->evaluation->current();

                if ($event->method == 'finalize' && ($userId != $event->respondent_id) && !$evaluation->isOtherRespondentsEventsVisible()) continue;
                $groupId = $evaluation->method;

                if (!isset($groups[$groupId])) {

                    $name = $evaluation->getMethodName();
                    $groups[$groupId] = [
                        'name' => $name,
                        'events' => [],
                    ];
                }

                $groups[$groupId]['events'][] = $event;
            }
        }

        $this->view->groups = $groups;
        $this->view->sessionDescription = $this->_session->description;
    }

    public static function myPlainify($data, $view = null)
    {
        foreach ($data['groups'] as $method => $group) {

            $plainEvents = [];

            /** @var HM_At_Session_Event_EventModel $event */
            foreach ($group['events'] as $event) {

                // Взято в старом view event-preview
                $isReportAvailable = $event->isReportAvailable()
                    && ($event->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED)
                    && in_array($event->method, [HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE, HM_At_Evaluation_EvaluationModel::TYPE_TEST]);

                $reportLink = Zend_Registry::get('view')->url([
                    'module' => 'session',
                    'controller' => 'report',
                    'action' => 'event',
                    'session_id' => $event->session_id,
                    'session_event_id' => $event->session_event_id,
                ]);

                $executeLink = Zend_Registry::get('view')->url([
                    'module' => 'event',
                    'controller' => 'index',
                    'action' => 'index',
                    'session_event_id' => $event->session_event_id
                ]);

                $plainEvent = [
                    'session_event_id' => $event->session_event_id,
                    'session_id' => $event->session_id,
                    'statusCompleted' => $event->status,
                    'isExecutable' => $event->isExecutable(),
                    'executeLink' => $executeLink,
                    'icon' => Zend_Registry::get('view')->serverUrl() . '/' . $event->getIcon(),
                    'title' => $event->title,
                    'name' => $event->name,
                    'description' => $event->description,
                    'isReportAvailable' => $isReportAvailable,
                    'reportLink' => $reportLink,
                    'method' => $event->method,
                    'messages' => $event->getMessages(),
                    'programmEvent' => []
                ];

                if ($event->programmEvent) {
                    $programmEvent = $event->programmEvent->current();

                    $plainEvent['programmEvent'] = [
                        'name' => $programmEvent->name
                    ];
                }

                $plainEvents[] = $plainEvent;
            }

            $data['groups'][$method]['events'] = $plainEvents;
        }

        return $data;
    }

    public function resultsAction()
    {
        $this->view->setHeader(_('Результаты заполнения анкеты'));
        $sessionEventId = $this->_getParam('session_event_id');
        if ($collection = $this->getService('AtSessionEvent')->find($sessionEventId)) {
            $event = $collection->current();
            $totalResults = $event->getResults();
            $this->view->addScriptPath(APPLICATION_PATH . "/views/methods/{$event->method}/");
            $this->view->event = $event;
            $this->view->totalResults = $totalResults;
        }
    }

    public function updateActions($status, $method, $respondent_id, $quest_id, $actions)
    {
        if ($status != HM_At_Session_Event_EventModel::STATUS_COMPLETED) {
            $actions = '';
        }

        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER))
            && $respondent_id!=$this->getService('User')->getCurrentUserId()) {
            $method = -1;
        } 

        switch ($method) {
            case HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE:
            case HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO:
            case HM_At_Evaluation_EvaluationModel::TYPE_FORM:
            case HM_At_Evaluation_EvaluationModel::TYPE_TEST:
                // отчет есть;
            break;
            default:
                $this->unsetAction($actions, array('module' => 'session', 'controller' => 'report', 'action' => 'event'));
            break;
        }
                              
        if(!($method==HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO && isset(HM_Quest_Type_PsychoModel::getTypes()[$quest_id]))) {
            $this->unsetAction($actions, array('module' => 'session', 'controller' => 'report', 'action' => 'psycho'));
        }

        return $actions;
    }
    
    public function updateUser($userId, $userName)
    {
        return $userId ? $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => $userId, 'baseUrl' => ''), null, true)) . $userName : '';
    }    
    
    public function updateMultipleUsers($userId, $firstUserIds, $secondUserIds)
    {
        if ($this->_usersCache === null) {
            $this->_usersCache = array();
            if (count($collection = $this->getService('AtSessionUser')->fetchAll(array('session_id = ?' => $this->_session->session_id)))) {
                $userIds = $collection->getList('user_id');
                $this->_usersCache = $this->getService('User')->fetchAll(array('MID IN (?)' => $userIds))->asArrayOfObjects(); // @todo: оптимизировать
            }
        }
        
        $result = $users = array();
        if (!empty($firstUserIds) && !empty($secondUserIds)) {
            $users = array_unique(array_merge(explode(',', $firstUserIds), explode(',', $secondUserIds)));
        } elseif ($userId) {
            $users = array($userId);
        }
        
        $result = ($count = count($users)) > 1 ? array('<p class="total">' . sprintf(_n('участник plural', '%s участник', $count), $count) . '</p>') : array();
        foreach ($users as $userId) {
            if (!isset($this->_usersCache[$userId])) continue;
            $result[] = '<p>' . $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => $userId, 'baseUrl' => ''), null, true)) . $this->_usersCache[$userId]->getName() . '</p>';
        }
        return implode('', $result);
    }

    public function updateMethod($method, $relationType)
    {
        return HM_At_Evaluation_EvaluationModel::getMethodTitle($method, $relationType);
    }

    public function updateStatus($status)
    {
        return HM_At_Session_Event_EventModel::getStatusName($status);
    }

    public function updateName($sessionEventId, $name, $respondentId, $status, $sessionStatus, $sessionUserStatus, $programm_type, $method)
    {
        if ($this->_eventsCache === null) {
            $this->_eventsCache = array();
            
            $condition = array(
                'session_id = ?' => $this->_session->session_id,
            );
            if (!in_array($programm_type, array(HM_Programm_ProgrammModel::TYPE_ADAPTING, HM_Programm_ProgrammModel::TYPE_RESERVE))) {//$this->_session->programm_type
                $condition['respondent_id = ?'] = $this->getService('User')->getCurrentUserId();
            } else {
                // менеджер и специалист в программе адаптации имеют доступ к форме, даже если она назначена кому-то другому (руководителю);
            }
            
            // такой же набор dependences, как в myAction()  
            $events = $this->getService('AtSessionEvent')->fetchAllHybrid(array(
                'Session', 
                'SessionUser', 
                'SessionRespondent', 
                'SessionEventRespondent', 
                'SessionEventUser', 
                'SessionEventMultiUser', 
                'Evaluation', 
                'EvaluationResult',
                'ProgrammEventUser',
                'Quest',
            ), 
            array('Programm', 'ProgrammEvent'),
            'ProgrammEventUser', 
            $condition);

            foreach ($events as $event) {
                if (!isset($isStrict)) {
                    if (count($event->programm)) {
                        $isStrict = $event->programm->current()->mode_strict;
                    }
                }
                // не стал рассматривать isStrict по умолчанию = 1, т.к. боюсь нарушить существующую логику - сделал отдельное условие для адаптации
                $this->_eventsCache[$event->session_event_id] = $programm_type==HM_Programm_ProgrammModel::TYPE_ADAPTING ? $event->isExecutable() : (!$isStrict || $event->isExecutable());
            }
        }

        $newcomerId = $this->_getParam('newcomer_id', 0);
        if (
            //$status != HM_At_Session_Event_EventModel::STATUS_COMPLETED &&
            $sessionStatus == HM_At_Session_SessionModel::STATE_ACTUAL &&
            $sessionUserStatus != HM_At_Session_User_UserModel::STATUS_COMPLETED && 
            ($this->_eventsCache[$sessionEventId])
        ) {
            $url = $this->view->url(array(
                'module' => 'event',
                'controller' => 'index',
                'action' => 'index',
                'session_id' => $this->_session->session_id,
                'session_event_id' => $sessionEventId,
            ), null, true, false);
            return '<a href="' . $url . '">' . $this->view->escape($name) . '</a>';

        } else {
// эта страница с результатами не очень-то информативна            
//             $url = $this->view->url(array(
//                 'module' => 'session',
//                 'controller' => 'event',
//                 'action' => 'results',
//                 'session_id' => $this->_session->session_id,
//                 'session_event_id' => $sessionEventId,
//             ), null, true, false);
        }
        return $this->view->escape($name);
    }

    // DEPRECATED!
    public function adaptingFinalAction(){
        $form = new HM_Form_AdaptingFinal();
        $newcomerId = $this->_getParam('newcomer_id', 0);
        $recruitNewcomerService = $this->getService('RecruitNewcomer');            
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $recruitNewcomerService->update($form->getValues());
                $this->_flashMessenger->addMessage(_('Изменения успешно сохранены!'));
                $this->_redirector->gotoSimple('list', 'event', 'session', array('newcomer_id' => $newcomerId));
            }
        } else {
            $form->populate($recruitNewcomerService->find($newcomerId)->current()->getValues());
        }
        
        $this->view->form = $form;
    } 
    
    protected function _sortByProgramm($event1, $event2) 
    {
        $programmEvent1 = count($event1->programmEvent) ? $event1->programmEvent->current() : false; 
        $programmEvent2 = count($event2->programmEvent) ? $event2->programmEvent->current() : false;

        if ($programmEvent1 && $programmEvent2) {
            return $programmEvent1->ordr < $programmEvent2->ordr ? -1 : 1;  
        } else {
            return $event1->name < $event2->name ? -1 : 1;
        }
    }
    
    public function calendarAction()
    {
        if ($this->_getParam('start',0) && $this->_getParam('end',0)) {

            $begin = date('Y-m-d', $this->_getParam('start'));
            $end = date('Y-m-d 23:59:59', $this->_getParam('end'));
            $this->_setParam('timezone', 'Europe/Moscow');

            $select = $this->getService('AtSessionEvent')->getSelect();
            $select->from(
                array('a' => 'at_session_events'),
                array('session_event_id' => 'session_event_id')
            )->joinInner(
                array('asu' => 'at_session_users'),
                "a.session_user_id = asu.session_user_id",
                array()
            )->joinInner(
                array('b' => 'programm_events_users'),
                "a.programm_event_user_id = b.programm_event_user_id",
                array()
            )->joinInner(
                array('c' => 'programm_events'),
                " b.programm_event_id = c.programm_event_id",
                array()
            )->where(
                "a.session_id = ?", $this->_session->session_id
            )->where(
                "a.date_begin <= ?", $end
            )->where(
                "a.date_end >= ?", $begin
            )->where(
                "c.hidden != 1"
            );

            $onlyVacancyCandidateId = $this->_getParam('vacancy_candidate_id', 0);
            if ($onlyVacancyCandidateId) {
                $select->where('asu.vacancy_candidate_id = ?', $onlyVacancyCandidateId);
            }

            $r = $select->query()->fetchAll();
            $ids = array();
            foreach ($r as $item) $ids[$item['session_event_id']] = $item['session_event_id'];



            $events = $this->getService('AtSessionEvent')->fetchAllDependence(array('Evaluation', 'SessionEventUser'), $this->quoteInto(
                'session_event_id IN (?)', $ids
            ));

//            $events = $this->getService('AtSessionEvent')->fetchAllDependence(array('Evaluation', 'SessionEventUser'), array(
//                'session_id = ?' => $this->_session->session_id,
//                'date_begin <= ?' => $end,
//                'date_end >= ?' => $begin,
//            ));
            
            $eventsSources = array();
            $methodColors = HM_At_Evaluation_EvaluationModel::getMethodColors();
            $relationTypeColors = HM_At_Evaluation_EvaluationModel::getRelationTypeColors();
            foreach ($events as $event) {
                    
                if (count($event->user)) {
                    $user = $event->user->current();
                }
                if (count($event->evaluation)) {
                    $evaluation = $event->evaluation->current();
                    list($methodId, $relationTypeId) = explode('_', trim($evaluation->submethod));
                }
                
                $bug = HM_Date::GMT_DIFF;
                $dateBegin = new HM_Date($event->date_begin);
                $dateEnd = new HM_Date($event->date_end);
                $eventsSources[] = array(
                    'id'    => $event->session_event_id,
                    'title' => sprintf('[%s] %s', $user->LastName, $event->name),
                    'color' => ($methodId == HM_At_Evaluation_EvaluationModel::TYPE_FORM) ? '#888' : $relationTypeColors[$relationTypeId],
                    //первая секунда дня
                    'start' => $dateBegin->getTimestamp() + (60*60*4),
                    'end'   => $dateEnd->getTimestamp() + (60*60*4),
                    'editable' => true,
                    'borderColor' => $methodColors[$methodId],
                );                    
            }
            $this->view->assign($eventsSources);
        } else {

            $onlyVacancyCandidateId = $this->_getParam('vacancy_candidate_id', 0);
            if ($onlyVacancyCandidateId) {
                $vacancyCandidate = $this->getService('RecruitVacancyAssign')->getOne($this->getService('RecruitVacancyAssign')->findManyToMany('User', 'Candidate', $onlyVacancyCandidateId));
                if (count($vacancyCandidate->user)) {
                    $this->view->setSubHeader($vacancyCandidate->user->current()->getName());
                }
            }

            $this->view->setHeader(_('Календарь мероприятий'));
            $this->view->source = array('module'=>'session', 'controller'=>'event', 'action'=>'calendar');
        }
    }

    public function saveCalendarAction()
    {
        $eventId = $this->_getParam('eventid',0);
        $begin     = $this->_getParam('start',0);
        $end       = $this->_getParam('end', $begin);
        
        $result    = _('При сохранении данных произошла ошибка');
        $status    = 'fail';

        if ($this->_request->isPost() && $eventId && $begin && $end) {

            $event = $this->getService('AtSessionEvent')->getOne($this->getService('AtSessionEvent')->find($eventId));
            if ($event) {
                $event->date_begin = $this->getService('AtSessionEvent')->getDateTime($begin/1000, true);
                $event->date_end = $this->getService('AtSessionEvent')->getDateTime($end/1000, true);
                $res = $this->getService('AtSessionEvent')->update($event->getValues());
                if ($res) {
                    $result = _('Данные успешно обновлены');
                    $status = 'success';
                }
            }
        }
        $this->view->status = $status;
        $this->view->msg    = $result;
    }
    
    public function delete($sessionEventId)
    {
        $this->getService('AtSessionEvent')->delete($sessionEventId);
    }

//#17411
    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $data = $this->_getAllowedToDeleteEvents($postMassIds);
        if ($data) {
            $bDeleteSome = false;
            foreach($data['allowEventDelete'] as $sessionEventId=>$bAllow) {
                if($bAllow)  {
                    $bDeleteSome = true;
                    $this->delete($sessionEventId);
                }
            }
            $message = $bDeleteSome?_('Формы успешно удалены.'):_('Формы не удалены.');
            if(count($data['deniedPeople'])){
                $message .= ' ';
                $message .= $bDeleteSome ?
                    _('Для некоторых участников формы не удалены ввиду того, что они завершили прохождение сессии'.': ').implode(', ', $data['deniedPeople']):
                    _(' Формы не удалены ввиду того, что сессия имеет статус "Завершивший"');
            }
        }
        $this->getService('AtSessionRespondent')->deleteRespondentsWithoutEvents($this->_session->session_id);
        $this->_redirectToIndex();
    }


    public function deleteResultsAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $data = $this->_getAllowedToDeleteEvents($postMassIds);
        if ($data) {
            $bDeleteSome = false;
            foreach($data['allowEventDelete'] as $sessionEventId=>$bAllow) {
                if($bAllow)  {
                    $bDeleteSome = true;
                    $this->getService('AtSessionEvent')->deleteResults($sessionEventId);
                 }
            }
            $message = $bDeleteSome?_('Результаты успешно удалены.'):_('Результаты не удалены.');
            if(count($data['deniedPeople'])){
                $message .= ' ';
                $message .= $bDeleteSome ?
                    _('Для некоторых участников результаты не удалены ввиду того, они завершили прохождение сессии'.': ').implode(', ', $data['deniedPeople']):
                    _('Результаты не удалены ввиду того, что сессия имеет статус "Завершивший"');
            $this->_flashMessenger->addMessage($message);
            }
        }
        $this->_redirectToIndex();
    }
    
    public function _getAllowedToDeleteEvents($postMassIds)  
    {
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $sessionEventId) {
                    $this->getService('AtSessionEvent')->deleteResults($sessionEventId);
                }

                $deniedPeople = array();
                if(count($deniedMID)) {	
                    $peoples = $this->getService('User')->fetchAll(array('mid in (?)' => $deniedMID));
                    foreach($peoples as $people) {
                        $deniedPeople[] = $people->getName();
                    }
                }

                return array('allowEventDelete'=>$allowEventDelete, 'deniedPeople'=>$deniedPeople);
            }
            
            $this->_redirectToIndex();
        }    
    }    
    
    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('list', 'event', 'session', array('session_id' => $this->_session->session_id));
    }




}
