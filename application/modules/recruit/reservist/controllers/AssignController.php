<?php

class Reservist_AssignController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;


    protected $_candidateId = 0;
    protected $_candidate = null;
    protected $_vacancyId = 0;
    protected $_vacancy = null;
    protected $_candidatesCache = null;
    protected $_programmEventsCache = null;
    

    public function init()
    {
        $form = new HM_Form_Candidates();
        $this->_setForm($form);

        $this->_candidateId = $this->_getParam('candidate_id', 0);
        $this->_vacancyId = $this->_getParam('vacancy_id', 0);

        if ($this->_vacancyId > 0) {

            $this->_vacancy = $this->getOne(
                $this->getService('RecruitVacancy')->findDependence(array('Session', 'Recruiter'), $this->_vacancyId)
            );

            $this->view->setExtended(
                array(
                    'subjectName' => 'RecruitVacancy',
                    'subjectId' => $this->_vacancyId,
                    'subjectIdParamName' => 'vacancy_id',
                    'subjectIdFieldName' => 'vacancy_id',
                    'extraSubjectIdParamName' => 'session_id',
                    'extraSubjectId' => $this->_vacancy->session_id,
                    'subject' => $this->_vacancy
                )
            );
        }

        parent::init();
    }

    protected function _redirectToIndex()
    {
        if ($this->_vacancyId > 0) {
            $this->_redirector->gotoSimple('index', null, null, array('vacancy_id' => $this->_vacancyId));
        }
        $this->_redirector->gotoSimple('index');
    }

    public function indexAction()
    {
        if (count($collection = $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY, $this->_vacancy->vacancy_id, HM_Programm_ProgrammModel::TYPE_RECRUIT))) {
            $programm = $collection->current();
            if (count($programm->process)) {
                $process = $programm->process->current();
            }
        }

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == "") {
            $this->_request->setParam("ordergrid", 'result_DESC');
        }

        $vacancy_id = $this->_request->getParam("vacancy_id");
        $programm = $this->getService('Programm')->getOne($this->getService('Programm')->fetchAllDependence('Event', array(
                'programm_type = ?' => HM_Programm_ProgrammModel::TYPE_RECRUIT,
                'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY,
                'item_id = ?' => $vacancy_id,
        )));
        $bStrict = $programm->mode_strict==HM_Programm_ProgrammModel::MODE_STRICT_ON;

        $select = $this->getService('RecruitVacancyAssign')->getSelect();
        $from = array(
            'MID' => 'p.MID',
            'user_id' => 'p.MID',
            'rc.candidate_id',
            'rv.session_id',
            'vacancy_candidate_id',
            'workflow_id' => 'rvc.vacancy_candidate_id',
            'name' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            'source' => 'rc.source',
            'srcId' => 'rc.source',
            'url' => 'rc.resume_external_url',
//             'department' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT pso.soid)'),
//             'position' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT so.soid)'),
            'resume' => 'rc.file_id',
            'file_name' => 'f.name',
            'events' => new Zend_Db_Expr("COUNT(DISTINCT ase.session_event_id)"),
            'result' => new Zend_Db_Expr('CASE WHEN ((rvc.result = 0) OR (rvc.result IS NULL)) THEN 0 ELSE rvc.result END'),
            'status' => 'rvc.status',
            'statusId' => 'rvc.status',
            'duplicate' => new Zend_Db_Expr('CASE WHEN (p.duplicate_of = 0) THEN 0 ELSE 1 END'),
        );

        if(!$bStrict) {
            $from['last_state'] = 'sop.passed_states';
        }

        $select->from(array('rvc' => 'recruit_vacancy_candidates'), $from)
            ->join(array('rc' => 'recruit_candidates'), 'rvc.candidate_id = rc.candidate_id', array())
            ->join(array('rv' => 'recruit_vacancies'), 'rvc.vacancy_id = rv.vacancy_id', array())
            ->join(array('p' => 'People'), 'p.MID = rc.user_id', array())
            ->joinLeft(array('ase' => 'at_session_events'), 'ase.session_id = rv.session_id AND ase.user_id = rc.user_id', array())
            ->joinLeft(array('so' => 'structure_of_organ'), 'so.mid = rc.user_id', array())
            ->joinLeft(array('pso' => 'structure_of_organ'), 'pso.soid = so.owner_soid', array())
            ->joinLeft(array('f' => 'files'), 'rc.file_id = f.file_id', array())
            ->joinLeft(array('sop' => 'state_of_process'), 'rvc.vacancy_candidate_id = sop.item_id AND process_type = ' . HM_Process_ProcessModel::PROCESS_PROGRAMM_RECRUIT, array())
            ->where('rvc.vacancy_id = ?', $this->_vacancyId)
            ->group(array(
                'p.MID',
                'rv.vacancy_id',
                'rc.candidate_id',
                'rv.session_id',
                'p.LastName',
                'p.FirstName',
                'p.Patronymic',
                'rc.source',
                'rc.file_id',
                'f.name',
                'rvc.result',
                'rvc.status',
                'rvc.vacancy_candidate_id',
                'rc.resume_external_url',
                'p.duplicate_of',
                'sop.passed_states',
            ));

        $columnsOptions = array(
            'MID' => array('hidden' => true),
            'user_id' => array('hidden' => true),
            'candidate_id' => array('hidden' => true),
            'session_id' => array('hidden' => true),
            'vacancy_candidate_id' => array('hidden' => true),
            'result' => array('hidden' => true),
            'workflow_id' => array(
                'title' => _('Бизнес-процесс'), // бизнес проуцесс
                'callback' => array(
                    'function' => array($this, 'printWorkflow'),
                    'params' => array('{{workflow_id}}'),
                ),
            ),
            'file_name' => array('hidden' => true),
            'name' => array(
                'title' => _('ФИО'),
                'decorator' => $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . '{{MID}}') . ' <a href="' . $this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . '{{MID}}' . '">' . '{{name}}</a>'
            ),
            'source' => array(
                'title' => _('Источник'),
                'callback' => array(
                    'function' => array($this, 'updateSource'),
                    'params' => array('{{source}}')
                )
            ),
            'url' => array('hidden' => true),
            'srcId' => array('hidden' => true),
//             'department' => array(
//                 'title' => _('Подразделение (если применимо)')
//             ),
//             'position' => array(
//                 'title' => _('Должность (если применимо)')
//             ),
            'resume' => array(
                'hidden' => true
            ),
            'events' => array(
                'hidden' => true
            ),
//            array(
//                'title' => _('Количество оценочных форм'),
//                'decorator' => '<a href="' . $this->view->url(array('module' => 'session', 'controller' => 'event', 'action' => 'list', 'gridmod' => null, 'baseUrl' => 'at', 'filter' => 1, 'usergrid' => '')) . '{{name}}">{{events}}</a>'
//            ),
            'statusId' => array('hidden' => true),
            'status' => array(
                'title' => _('Статус'),
                'callback' => array(
                    'function' => array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                )
            ),
            'duplicate' => array(
                'title' => _('Дубл.'),
                'callback' => array(
                    'function' => array($this, 'updateDuplicate'),
                    'params' => array('{{duplicate}}')
                )
            ),
        );

        if(!$bStrict) {
            $columnsOptions['last_state'] = array(
                'title' => _('Последнее действие'),
                'callback' => array(
                    'function' => array($this, 'updatePassedStates'),
                    'params' => array('{{last_state}}')
                )
            );
        }



        $this->_initProgrammEventsCache();
        

        $filters = array(
            'name' => null,
            'events' => null,
            'source' => array('values' => $this->getService('RecruitProvider')->getList()),
            'status' => array('values' => HM_Recruit_Vacancy_Assign_AssignModel::getStatuses()),
            'duplicate' => array(
                'values'     => array(
                    1 => _('Да'),
                    0 => _('Нет')
                ),
                'searchType' => '='
        ));

        if(!$bStrict) {
            $filters['last_state'] = array(
                'values' => $this->_programmEventsCache,
                'callback' => array(
                    'function' => array($this, 'lastStateFilter'),
                    'params'   => array()
            ));
        } else {
            $filters['workflow_id'] = array(
                'render' => 'process',
                //правильно получить префикс черезе HM_Process_Type_Programm_RecruitModel::getStatePrefix(), но как туды попасть???
                'values' => Bvb_Grid_Filters_Render_Process::getStatesProgramm('HM_Recruit_Vacancy_Assign_State_', HM_Programm_ProgrammModel::TYPE_RECRUIT, HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY, $vacancy_id),
                'field4state' => 'sop.current_state',
            );
        }



        $grid = $this->getGrid(
            $select,
            $columnsOptions,
            $filters
        );

        $grid->setClassRowCondition('{{result}} == ' . HM_Recruit_Vacancy_Assign_AssignModel::RESULT_SUCCESS, 'selected');
        $grid->setClassRowCondition(sprintf('in_array({{result}}, array(%s, %s, %s))', HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_DEFAULT, HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_RESERVE, HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_BLACKLIST), 'highlighted');


        $grid->addAction(array(
            'module' => 'user',
            'controller' => 'list',
            'action' => 'edit',
            'baseUrl' => '',
        ),
            array('MID', 'candidate_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'user',
            'controller' => 'list',
            'action' => 'delete',
            'baseUrl' => '',
        ),
            array('MID'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addAction(array(
            'module' => 'candidate',
            'controller' => 'index',
            'action' => 'resume',
            'gridmod' => null,
        ),
            array('candidate_id'),
            _('Резюме')
        );

        $grid->addAction(array(
            'module' => 'vacancy',
            'controller' => 'report',
            'action' => 'user',
        ), array('vacancy_candidate_id'), _('Индивидуальный отчёт')
        );

        $grid->addAction(array(
            'module' => 'candidate',
            'controller' => 'list',
            'action' => 'assign-active',
        ), array('vacancy_candidate_id'), _('Назначить активным кандидатом')
        );


        /*$grid->addAction(array(
            'baseUrl' => '',
            'module' => 'user',
            'controller' => 'index',
            'action' => 'resume',
            ), 
            array('user_id','candidate_id'), 
            _('Просмотреть резюме')
        ); */

        $grid->addAction(array(
            'baseUrl' => '',
            'module' => 'user',
            'controller' => 'list',
            'action' => 'login-as',
            'vacancy_id' => null,
        ), array('MID'), _('Войти от имени пользователя'), _('Вы действительно хотите войти в систему от имени данного пользователя? При этом все функции Вашей текущей роли будут недоступны. Вы сможете вернуться в свою роль при помощи обратной функции "Выйти из режима". Продолжить?') // не работает??
        );


//        $grid->addMassAction(
//            array(
//                'module' => 'candidate',
//                'controller' => 'list',
//                'action' => 'assign-active-mass',
//            ),
//            _('Назначить активными кандидатами'),
//            _('Вы действительно желаете назначить активными отмеченных кандидатов? Если сессия уже стартовала, им будут автоматически назначены оценочные мероприятия и отправлены уведомления.')
//        );

        $grid->addMassAction(
            array(
                'module' => 'candidate',
                'controller' => 'assign',
                'action' => 'unassign',
            ),
            _('Удалить из списка кандидатов'),
            _('Вы действительно желаете удалить отмеченных кандидатов из списка? При этом учётные записи удалены не будут и кандидаты останутся в общей базе резюме.')
        );

        $vacancies = $this->getService('Recruiter')->getVacanciesForDropdownSelect();

        if (count($vacancies)) {

            $grid->addMassAction(array(
                'module' => 'candidate',
                'controller' => 'list',
                'action' => 'assign-from-vacancy',
            ),
                _('Включить в сессию подбора'),
                _('Вы уверены, что хотите включить выбранных кандидатов или пользователей в данную сессию подбора?')
            );

            $grid->addSubMassActionSelect(array($this->view->url(array(
                'module' => 'candidate',
                'controller' => 'list',
                'action' => 'assign-from-vacancy',
            ))), 'assign_vacancy_id', $vacancies, false);

//            $grid->addMassAction(array(
//                'module' => 'candidate',
//                'controller' => 'list',
//                'action' => 'assign-hold-on',
//                ),
//                _('Включить в сессию подбора в качестве потенциального кандидата'),
//                _('Вы уверены, что хотите включить выбранных кандидатов или пользователей в данную сессию подбора в качестве потенциальных кандидатов? При этом оценочные мероприятия назначены не будут. Внешние кандидаты не могут проходить одновременно несколько сессий подбора, они не будут обработаны.')
//            );

//            $grid->addSubMassActionSelect(array($this->view->url(array('action' => 'assign-hold-on'))), 'vacancy_id', $vacancies, false);
        }

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))) {
            $grid->addMassAction(
                array(
                    'module' => 'candidate',
                    'controller' => 'assign',
                    'action' => 'send-resume',
                ),
                _('Отправить резюме кандидатов инициатору'),
                _('Вы действительно желаете отправить выбранные резюме кандидатов инициатору?')
            );
        }

        $grid->setActionsCallback(
            array('function' => array($this, 'updateActions'),
                'params' => array('{{candidate_id}}', '{{srcId}}', '{{statusId}}')
            )
        );

        $this->view->grid = $grid;
        $this->view->isAjaxRequest = $this->isAjaxRequest();
    }

    public function printWorkflow($vacancyCandidateId)
    {
        if (count($this->_vacancy->session)) {
            if (in_array($this->_vacancy->session->current()->state, array(HM_At_Session_SessionModel::STATE_ACTUAL, HM_At_Session_SessionModel::STATE_CLOSED))) {
                if ($this->_candidatesCache === null) {
                    $this->_candidatesCache = array();

                    $vacancy = $this->getService('RecruitVacancy')->getOne(
                        $this->getService('RecruitVacancy')->findMultiDependence(array(
                            'candidates' => 'CandidateAssign',
                            'sessionEvents' => array('SessionEvents', 'SessionUser'),
                            'programmEvent' => array('ProgrammEvent', 'ProgrammEventUser'),
                        ), $this->_vacancy->vacancy_id)
                    );

                    if (count($vacancy->candidates)) {
                        foreach ($vacancy->candidates as $vacancyCandidate) {
                            $this->_candidatesCache[$vacancyCandidate->vacancy_candidate_id] = $vacancyCandidate;
                        }
                    }
                }
                if (intval($vacancyCandidateId) > 0 && count($this->_candidatesCache) && array_key_exists($vacancyCandidateId, $this->_candidatesCache)) {
                    $model = $this->_candidatesCache[$vacancyCandidateId];
                    $this->getService('Process')->initProcess($model);
                    return $this->view->workflowBulbs($model);
                }
            }
        }
        return _('не начат');
    }

    public function lastStateFilter($data)
    {
        $field = $data['field'];
        $value = $data['value'];
        $select = $data['select'];
        
        
        $select->where('sop.passed_states LIKE ?', '%_'.$value);
    }
    
    
    public function updatePassedStates($passedStates){
        $result = '';
        if($passedStates){
            if($this->_programmEventsCache == null){
                $this->_initProgrammEventsCache();
            }
            $states = explode(',', $passedStates);
            $lastElement = end($states);
            if($lastElement){
                $lastElementParts = explode('_', $lastElement);
                $programmEventId = end($lastElementParts);
                $result = $this->_programmEventsCache[$programmEventId];
            }
        }
        
        return $result;
    }
    
    protected function _initProgrammEventsCache(){
        $processIds = $this->getService('RecruitVacancyAssign')->fetchAll(
            array('vacancy_id = ?' => $this->_vacancyId)
        )->getList('process_id');

        if(count($processIds)){
            $programmIds = $this->getService('Process')->fetchAll(
                array('process_id IN (?)' => $processIds)
            )->getList('programm_id');
            
            $programmEvents = $this->getService('ProgrammEvent')->fetchAll(
                array('programm_id IN (?)' => $programmIds)
            )->getList('programm_event_id', 'name');
        }
        $this->_programmEventsCache = $programmEvents;
    }
    
    
    public function unassignAction()
    {
        if ($vacancyCandidateId = $this->_getParam('vacancy_candidate_id')) {
            $ids = array($vacancyCandidateId);
        } else {
            $postMassIds = $this->_getParam('postMassIds_grid', '');
            $ids = explode(',', $postMassIds);
        }
        foreach ($ids as $vacancyCandidateId) {
            $this->getService('RecruitVacancyAssign')->unassign($vacancyCandidateId);
        }
        $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => _('Кандидаты успешно исключены из списка')
        ));

        $this->_redirector->gotoSimple('index', 'assign', 'candidate', array('vacancy_id' => $this->_vacancyId));
    }


    public function sendResumeAction()
    {

        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $vacancyId = $this->_getParam('vacancy_id', '');

        $ids = explode(',', $postMassIds);

        $vacancy = $this->getService('RecruitVacancy')->fetchOne(
            $this->getService('RecruitVacancy')->quoteInto(
                array(
                    ' vacancy_id = ? ',
                ),
                array(
                    $vacancyId
                )
            )
        );

        $vacancyDataFields = $this->getService('RecruitVacancyDataFields')->fetchOne(
            $this->getService('RecruitVacancyDataFields')->quoteInto(
                array(
                    ' item_id = ? ',
                    ' AND item_type = ? '
                ),
                array(
                    $vacancyId,
                    HM_Recruit_Vacancy_DataFields_DataFieldsModel::ITEM_TYPE_VACANCY
                )
            )
        );

        $vacancyCandidates = $this->getService('RecruitVacancyAssign')->fetchAllDependence('Vacancy',
            $this->getService('RecruitVacancyAssign')->quoteInto(
                array(
                    'vacancy_candidate_id IN (?)'
                ),
                array(
                    $ids
                )
            )
        );

        $candidatesList = array();

        $userId = array();
        foreach ($vacancyCandidates as $vacancyCandidate) {
            $userId[] = $vacancyCandidate->user_id;
        }

        $users = $this->getService('User')->fetchAll(
            $this->getService('User')->quoteInto(
                array(
                    'MID IN (?)'
                ),
                array(
                    $userId
                )
            )
        );

        $userNames = array();
        foreach ($users as $user) {
            $userNames[$user->MID] = $user->getName();
        }

        foreach ($vacancyCandidates as $vacancyCandidate) {
            $hash = $this->getService('RecruitVacancyAssign')->getHash($vacancyCandidate->vacancy_candidate_id);

            $url = $this->view->serverUrl($this->view->url(
                array(
                    'baseUrl' => 'recruit',
                    'module' => 'vacancy',
                    'controller' => 'report',
                    'action' => 'user',
                    'vacancy_id' => $vacancyId,
                    'vacancy_candidate_id' => $vacancyCandidate->vacancy_candidate_id,
                    'hash' => $hash
                ),
                null,
                true
            ));

            $href = '<a href="' . $url . '">' . $userNames[$vacancyCandidate->user_id] . '</a>';
            $candidatesList[] = '<li>'.$href.'</li>';
        }

        $candidates = '<ul>'.implode('',$candidatesList).'</ul>';

        $initiator = $this->getService('User')->fetchOne(array(
            'MID = ?' => $vacancyDataFields->user_id
        ));

        $messenger = $this->getService('Messenger');
        $messenger->setOptions(
            HM_Messenger::TEMPLATE_VACANCY_RESUME_SEND,
            array(
                'INITIATOR_LASTNAME' => $initiator->LastName,
                'INITIATOR_FIRSTNAME' => $initiator->FirstName,
                'INITIATOR_PATRONYMIC' => $initiator->Patronymic,
                'RECRUITER' => $this->getService('Recruiter')->getCurrentRecruiterInfo(),
                'CANDIDATES_LIST' => $candidates,
                'VACANCY' => $vacancy->name,
            )
        );
        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $initiator->MID);

        $this->_flashMessenger->addMessage(_('Сообщение успешно отправлено'));
        $this->_redirectToIndex();
    }



    public function updateResume($fileId, $fileName) 
    {
        $url = $this->view->url(array('module' => 'file', 'controller' => 'get', 'action' => 'file', 'file_id' => $fileId));
        return "<a href='{$url}'>" . $this->view->escape($fileName) . "</a>";
    }

    public function updateName($candidateId, $name) 
    {
        return $this->view->escape($name);
        //return '<a href="' . $this->view->url(array('action' => 'index', 'vacancy_id' => null, 'candidate_id' => $candidateId)) . '">' . $this->view->escape($name) . '</a>';
    }

    public function updateSource($source) 
    {
        $sources = $this->getService('RecruitProvider')->getList();
        return $sources[$source];
    }

    public function updateStatus($status) 
    {
        return HM_Recruit_Vacancy_Assign_AssignModel::getStatus($status);
    }

    public function updateDuplicate($duplicate) 
    {
        return $duplicate ? _('Да') : _('Нет');
    }

    public function workflowAction() 
    {
        $vacancyCandidateId = $this->_getParam('index', 0);

        if (intval($vacancyCandidateId) > 0) {

            $model = $this->getService('RecruitVacancyAssign')->find($vacancyCandidateId)->current();
            $this->getService('Process')->initProcess($model);
            $this->view->model = $model;

            if ($this->isAjaxRequest()) {
                $this->view->workflow = $this->view->getHelper("Workflow")->getFormattedDataWorkflow($model);
            }
        }
    }

    public function updateActions($candidateId, $source, $status, $actions)
    {
        $state = $this->getService('Process')->getCurrentState($this->_vacancy);
        
        // продублировано в Candidate_ListController

        /*
        if (!in_array($source, array(
            HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER,
            HM_Recruit_Provider_ProviderModel::ID_SUPERJOB
        ))){
            $this->unsetAction($actions, array(
                'module' => 'user',
                'controller' => 'index',
                'action' => 'resume',
                'baseUrl' => '',
            ));
        }*/

        if (
            is_a($state, 'HM_Recruit_Vacancy_State_Hire') ||
            ($status != HM_Recruit_Vacancy_Assign_AssignModel::STATUS_HOLD_ON)
        ) {
            $this->unsetAction($actions, array('module' => 'candidate', 'controller' => 'list', 'action' => 'assign-active'));
        }
        return $actions;
    }
    
    public function messageAction()
    {
        $users = $userIds = array();
        $date = date('Y-m-d');
        if (($programmEventId = $this->_getParam('programm_event_id')) && ($vacancyCandidateId = $this->_getParam('vacancy_candidate_id'))) {
            
            // кандидат
            if (count($collection = $this->getService('RecruitVacancyAssign')->find($vacancyCandidateId))) {
                $vacancyCandidate = $collection->current();
            }

            $candidateUser = $this->getService('User')->getOne(
                $this->getService('User')->find($vacancyCandidate->user_id)
            );

            // все кому назначено по программе
            if ($vacancyCandidate && count($collection = $this->getService('ProgrammEvent')->fetchAllManyToMany('SessionEvent', 'Evaluation', array('programm_event_id = ?' => $programmEventId)))) {
                $programmEvent = $collection->current();
                if (count($programmEvent->sessionEvents)) {
                    foreach ($programmEvent->sessionEvents as $sessionEvent) {
                        if ($sessionEvent->user_id == $vacancyCandidate->user_id) {
                            $userIds[$sessionEvent->respondent_id] = true;
                            $date = $sessionEvent->date_begin;
                        }
                    }
                }
            }

            // если письмо инициатору - берем его из свойств вакансии
            $isInitiator = $this->_getParam('initiator', false);
            if ($isInitiator) {
                $userIds = array();
                $vacancy = $this->getService('RecruitVacancy')->getOne(
                        $this->getService('RecruitVacancy')->findDependence('DataFields', $vacancyCandidate->vacancy_id)
                );

                if ($vacancy && count($vacancy->dataFields)) {
                    $vacancyDataFields = $vacancy->dataFields->getList('item_type', 'user_id');
                    $userIds[$vacancyDataFields[HM_Recruit_Vacancy_DataFields_DataFieldsModel::ITEM_TYPE_VACANCY]] = true;
                }
            }
        }

        $template = $this->getService('Notice')->getOne(
            $this->getService('Notice')->fetchAll(
                $this->getService('Notice')->quoteInto('type = ?', HM_Messenger::TEMPLATE_RECRUIT_EVENT
                )
            )
        );

        $form = new HM_Form_Message();
        $request = $this->getRequest();
        if ($request->isPost() && $this->_hasParam('message')) {
            if ($form->isValid($request->getParams())) {

                $message = $form->getValue('message');
                $messenger = $this->getService('Messenger');

                $postMassIds = $form->getValue('users');
                if (strlen($postMassIds)) {
                    $ids = explode(',', $postMassIds);
                    if (count($ids)) {

                        $options = array(
                            'vacancy' => $this->_vacancy->name,
                            'event' => $programmEvent->name,
                            'candidate_name' => $candidateUser->getName(true),
                            'date' => date('d.m.Y', strtotime($date)),
                            'RECRUITER' => $this->getService('Recruiter')->getCurrentRecruiterInfo(),
                        );

                        $users = $this->getService('User')->fetchAll(array('MID IN (?)' => $ids));
                        foreach($users as $user) {

                            $options['name'] = implode(' ', array($user->FirstName, $user->Patronymic));
                            $options['login'] = $user->Login;

                            if ((strpos($message, '[NEW_PASSWORD]') !== false) && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))) {
                                $data = $user->getData();
                                $password = $this->getService('User')->getRandomString();
                                $data['Password'] = new Zend_Db_Expr("PASSWORD(" . $this->getService('User')->getSelect()->getAdapter()->quote($password) . ")");
                                $this->getService('User')->update($data);

                                $options['new_password'] = $password;
                            }
                            // пока игнорируем тот факт, чт ов календаре можно растянуть мероприятие на несколько дней
                            $start    = date('Y-m-d', strtotime($date)) . ' 09:00';
                            $dateTime = new DateTime($start);
                            $interval = new DateInterval('PT9H'); $dateTime->add($interval);
                            $end      = $dateTime->format('Y-m-d H:i');

                            $messenger->setMessage($message);
                            $messenger->setOptions(HM_Messenger::TEMPLATE_RECRUIT_EVENT, $options);
                            $messenger->setIcal(HM_Messenger::getCalendar($messenger->replace($template->title), $messenger->replace($template->title), $start, $end));

                            try {
                                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $user->MID);
                            } catch (Exception $e) {

                            }
                        }
                    }
                }

                $this->_flashMessenger->addMessage( ( count($ids) == 1)? _('Сообщение отправлено') : _('Сообщения отправлены'))  ;
                $this->_redirector->gotoSimple('index', 'assign', 'candidate', array('vacancy_id' => $this->_vacancy->vacancy_id));
            }
        } else {
            $form->setDefault('vacancy_id', $this->_vacancy->vacancy_id);
            if ($template) {
                $form->setDefault('message', $template->message);
            }
            
            if (count($userIds)) {
                $users = $this->getService('User')->fetchAll(array('MID IN (?)' => array_keys($userIds)));
                $form->setDefault('users', implode(',', array_keys($userIds)));
            }            
        }

        $this->view->users = $users;
        $this->view->form = $form;

    }
    
    public function skipEventAction()
    {
        if (($programmEventId = $this->_getParam('programm_event_id')) && ($vacancyCandidateId = $this->_getParam('vacancy_candidate_id'))) {
            
            if (count($collection = $this->getService('RecruitVacancyAssign')->findDependence('Candidate', $vacancyCandidateId))) {
                $vacancyCandidate = $collection->current();
                
                $processAbstract = $vacancyCandidate->getProcess()->getProcessAbstract();
                if ($processAbstract->isStrict()) {
                    $this->getService('Process')->goToNextState($vacancyCandidate);                
                } else {
                    $stateClass = HM_Process_Type_Programm_RecruitModel::getStatePrefix() . $programmEventId;
                    $this->getService('Process')->setStateStatus($vacancyCandidate, $stateClass, HM_State_Abstract::STATE_STATUS_PASSED);
                }     

                if (count($vacancyCandidate->candidates)) {
                    $candidate = $vacancyCandidate->candidates->current();
                    $data['status'] = HM_Programm_Event_User_UserModel::STATUS_PASSED;
                    $this->getService('ProgrammEventUser')->updateWhere($data, array(
                        'programm_event_id = ?' => $programmEventId,
                        'user_id = ?' => $candidate->user_id,
                    ));                    
                }

                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Мероприятие завершено')));
                $this->_redirectToIndex();
                                
            }            
        }
        
        $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не удалось завершить данное мероприятие')));
        $this->_redirectToIndex();
    }
    
    // DEPRECATED! вызывалось раньше из окошка бизнес-процесса когда там был select
    public function updateStateAction()
    {
        $result = $this->_getParam('result');
        if (($programmEventId = $this->_getParam('programm_event_id')) && ($vacancyCandidateId = $this->_getParam('vacancy_candidate_id'))) {
            
            if (count($collection = $this->getService('RecruitVacancyAssign')->findDependence(array('SessionUser', 'RecruitCandidate'), $vacancyCandidateId))) {
                
                $vacancyCandidate = $collection->current();
                $this->getService('RecruitVacancyAssign')->setStatus($vacancyCandidate, $result);
                
                $processAbstract = $vacancyCandidate->getProcess()->getProcessAbstract();
                if ($processAbstract->isStrict()) {
                    $this->getService('Process')->goToFail($vacancyCandidate);                
                } else {
                    $stateClass = HM_Process_Type_Programm_RecruitModel::getStatePrefix() . $programmEventId;
                    $this->getService('Process')->setStateStatus($vacancyCandidate, $stateClass, HM_State_Abstract::STATE_STATUS_FAILED);
                }                         

                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Статус кандидата успешно изменен')));
                $this->_redirectToIndex();
            }
        }

        $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Произошла ошибка при изменении статуса')));
        $this->_redirectToIndex();
    }
    
    public function denyAction()
    {
        if (($programmEventId = $this->_getParam('programm_event_id')) && ($vacancyCandidateId = $this->_getParam('vacancy_candidate_id'))) {
            
            if (count($collection = $this->getService('RecruitVacancyAssign')->findDependence(array('SessionUser', 'RecruitCandidate'), $vacancyCandidateId))) {
                
                $vacancyCandidate = $collection->current();
                $this->getService('RecruitVacancyAssign')->setStatus($vacancyCandidate, HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_DEFAULT);
                
                $processAbstract = $vacancyCandidate->getProcess()->getProcessAbstract();
                if ($processAbstract->isStrict()) {
                    $this->getService('Process')->goToFail($vacancyCandidate);                
                } else {
                    $stateClass = HM_Process_Type_Programm_RecruitModel::getStatePrefix() . $programmEventId;
                    $this->getService('Process')->setStateStatus($vacancyCandidate, $stateClass, HM_State_Abstract::STATE_STATUS_FAILED);
                }                         

                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Статус кандидата успешно изменен')));
                $this->_redirectToIndex();
            }
        }

        $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Произошла ошибка при изменении статуса')));
        $this->_redirectToIndex();
    }
    
}
