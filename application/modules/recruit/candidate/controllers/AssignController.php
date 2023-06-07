<?php

class Candidate_AssignController extends HM_Controller_Action_Vacancy
{
    use HM_Controller_Action_Trait_Grid;

    protected $_candidateId = 0;
    protected $_candidate = null;

    protected $_candidatesCache = null;
    protected $_programmEventsCache = null;

    public function init()
    {
        parent::init();

        $form = new HM_Form_Candidates();
        $this->_setForm($form);

        $this->_candidateId = $this->_getParam('candidate_id', 0);
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
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            'source' => 'rc.source',
            'srcId' => 'rc.source',
            'url' => 'rc.resume_external_url',
//             'department' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT pso.soid)'),
//             'position' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT so.soid)'),
            'resume' => 'rc.file_id',
            'file_name' => 'f.name',
            'events' => new Zend_Db_Expr("COUNT(DISTINCT ase.session_event_id)"),
            'result' => new Zend_Db_Expr('CASE WHEN ((rvc.result = 0) OR (rvc.result IS NULL)) THEN 0 ELSE rvc.result END'),
            'statusId' => 'rvc.status',
            'vacancy_all_ids' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT rv_all.vacancy_id)"),
            'vacancy_all' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT rv_all.name)"),
            'last_state' => 'prge.name',
            'comments' => new Zend_Db_Expr(
                "GROUP_CONCAT(
                    DISTINCT CONCAT(
                        '@@@', CONCAT(
                            CONVERT(varchar, sopd.comment_date, 104), CONCAT(
                                ' ', CONCAT(
                                    CONVERT(varchar, sopd.comment_date, 8), CONCAT(
                                        ' ', CONCAT(
                                            CONCAT(CONCAT(CONCAT(CONCAT(p2.LastName, ' ') , 
                                                p2.FirstName), ' '), 
                                                p2.Patronymic), 
                                                CONCAT(' \"', CONCAT(sopd.comment, '\"')
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                )"
            ),
            'duplicate' => new Zend_Db_Expr('CASE WHEN (p.duplicate_of = 0) THEN 0 ELSE 1 END'),
            'status' => 'rvc.status',
        );

        $select->from(array('rvc' => 'recruit_vacancy_candidates'), $from)
            ->join(array('rc' => 'recruit_candidates'), 'rvc.candidate_id = rc.candidate_id', array())
            ->join(array('rv' => 'recruit_vacancies'), 'rvc.vacancy_id = rv.vacancy_id', array())
            ->join(array('p' => 'People'), 'p.MID = rc.user_id', array())
            ->joinLeft(array('ase' => 'at_session_events'), 'ase.session_id = rv.session_id AND ase.user_id = rc.user_id', array())
            ->joinLeft(array('so' => 'structure_of_organ'), 'so.mid = rc.user_id', array())
            ->joinLeft(array('pso' => 'structure_of_organ'), 'pso.soid = so.owner_soid', array())
            ->joinLeft(array('f' => 'files'), 'rc.file_id = f.file_id', array())

            ->joinLeft(array('rc_all' => 'recruit_vacancy_candidates'), 'rc_all.user_id = rc.user_id', array())
            ->joinLeft(array('rvc_all' => 'recruit_vacancy_candidates'), 'rvc_all.candidate_id = rc_all.candidate_id', array())
            ->joinLeft(array('rv_all' => 'recruit_vacancies'), 'rvc_all.vacancy_id = rv_all.vacancy_id', array())

            ->joinLeft(array('sop' => 'state_of_process'), 'rvc.vacancy_candidate_id = sop.item_id AND process_type = ' . HM_Process_ProcessModel::PROCESS_PROGRAMM_RECRUIT, array())
            ->joinLeft(array('sopd' => 'state_of_process_data'), 'sopd.state_of_process_id = sop.state_of_process_id  AND sopd.comment IS NOT NULL', array())

            // @todo: если это работает слишком долго - переделать на last_passed_programm_event_id
            ->joinLeft(array('prge' => 'programm_events'), new Zend_Db_Expr("prge.programm_event_id = CAST(REPLACE(sop.last_passed_state, 'HM_Recruit_Vacancy_Assign_State_', '') AS INT)"), array())

            ->joinLeft(array('p2' => 'people'), 'p2.mid = sopd.comment_user_id', array())

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
                'prge.name',
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
            'fio' => array(
                'title' => _('ФИО'),
                'decorator' => $this->view->cardLink(
                    // $this->view->url(
                    //     array(
                    //         'module' => 'user', 
                    //         'controller' => 'list', 
                    //         'action' => 'view', 
                    //         'gridmod' => null, 
                    //         'baseUrl' => '', 
                    //         'user_id' => '')) . '{{MID}}',
                    '/recruit/candidate/index/resume/candidate_id/' .
                    '{{candidate_id}}' . '/blank/1',
                    null,
                    'candidate',
                    'candidate',
                    'candidate',
                    true,
                    'candidate')
                    . '<a href="' . $this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'baseUrl' => '', 'recruit_candidate' => 1, 'user_id' => ''), null, true) . '{{MID}}' . '">' . '{{fio}}</a>'
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
            'vacancy_all_ids' => array('hidden' => true),
            'vacancy_all' => array(
                'title' => _('История подбора'),
            ),
            'comments' => array(
                'title' => _('Комментарии'),
                'callback' => array(
                    'function' => array($this, 'updateComments'),
                    'params' => array('{{comments}}')
                )
            ),

//            array(
//                'title' => _('Количество оценочных форм'),
//                'decorator' => '<a href="' . $this->view->url(array('module' => 'session', 'controller' => 'event', 'action' => 'list', 'gridmod' => null, 'baseUrl' => 'at', 'filter' => 1, 'usergrid' => '')) . '{{name}}">{{events}}</a>'
//            ),
            'statusId' => array('hidden' => true),
            'status' => array(
                'title' => _('Статус'),
                'callback' => array(
                    'function' => array($this, 'updateResultStatus'),
                    'params' => array('{{result}}', '{{status}}')
                )
            ),
            'last_state' => $bStrict ? array('hidden' => true) : array(
                'title' => _('Последнее действие'),
            ),
            'duplicate' => array(
                'title' => _('Дубликат'),
                'callback' => array(
                    'function' => array($this, 'updateDuplicate'),
                    'params' => array('{{duplicate}}')
                )
            ),
        );

        $filters = array(
            'fio' => null,
            'events' => null,
            'source' => array('values' => $this->getService('RecruitProvider')->getList()),
            'comments' => null,
            'vacancy_all' => null,
            'status' => array(
                'values'     => HM_Recruit_Vacancy_Assign_AssignModel::getFullStatuses(),
                'callback' => array(
                    'function' => array($this, 'filterResultStatus'),
                    'params'   => array()
                )
            ),
            'last_state' => null,
            'duplicate' => array(
                'values'     => array(
                    1 => _('Да'),
                    0 => _('Нет')
                ),
                'searchType' => '='
        ));

        if($bStrict) {
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

        $grid->setClassRowCondition("{{duplicate}} > 0",'highlighted');

        $grid->updateColumn('vacancy_all', array(
            'callback' => array(
                'function' => array($this, 'vacanciesCache'),
                'params' => array('{{vacancy_all_ids}}', $vacancy_id)
            ))
        );

//        $grid->setClassRowCondition('{{result}} == ' . HM_Recruit_Vacancy_Assign_AssignModel::RESULT_SUCCESS, 'selected');
//        $grid->setClassRowCondition(sprintf('in_array({{result}}, array(%s, %s, %s))', HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_DEFAULT, HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_RESERVE, HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_BLACKLIST), 'highlighted');

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
            'module' => 'candidate',
            'controller' => 'assign',
            'action' => 'unassign',
        ),
            array('vacancy_candidate_id'),
            $this->view->svgIcon('delete', 'Удалить')
//            _('Вы действительно желаете удалить данного кандидата из списка? При этом учётная запись удалена не будут и кандидат останется в общей базе резюме. Продолжить?')
        );

        $grid->addAction(array(
            'module' => 'candidate',
            'controller' => 'assign',
            'action' => 'decline',
        ),
            array('vacancy_candidate_id'),
            _('Назначить статус: отклонён')
        );

        $grid->addAction(array(
            'module' => 'candidate',
            'controller' => 'list',
            'action' => 'assign-active',
        ), array('vacancy_candidate_id'),
            _('Назначить статус: активный')
        );


        $grid->addAction(array(
                'baseUrl' => '',
                'module' => 'user',
                'controller' => 'list',
                'action' => 'login-as',
                'vacancy_id' => null,
            ),
            array('MID'),
            _('Войти от имени пользователя'),
            _('Вы действительно хотите войти в систему от имени данного пользователя? При этом все функции Вашей текущей роли будут недоступны. Вы сможете вернуться в свою роль при помощи обратной функции "Выйти из режима". Продолжить?') // не работает??
        );

        $grid->addAction(array(
            'module' => 'user',
            'controller' => 'list',
            'action' => 'duplicate-merge',
            'from' => 'vacancy',
            'baseUrl' => '',
        ),
            array('MID'),
            _('Объединить дубликаты')
        );

        $grid->addAction(array(
            'module' => 'candidate',
            'controller' => 'calendar',
            'action' => 'index',
            'vacancy_id' => $vacancy_id,
        ),
            array('vacancy_candidate_id'),
            _('Календарь мероприятий')
        );

//        $grid->addAction(array(
//            'baseUrl' => 'at',
//            'module' => 'session',
//            'controller' => 'event',
//            'action' => 'calendar',
//            'vacancy_id' => $vacancy_id,
//        ),
//            array('vacancy_candidate_id'),
//            _('Календарь мероприятий')
//        );
//
        // <-- Потом, в зависимости от наличия того или иного типа резюме, сделаем unsetAction() для ненужных в setActionsCallback()
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
            'module' => 'candidate',
            'controller' => 'index',
            'action' => 'download',
            'gridmod' => null,
        ),
            array('candidate_id'),
            _('Резюме')
        );
        // -->

        $grid->addAction(array(
                'module' => 'vacancy',
                'controller' => 'report',
                'action' => 'user',
            ), array('vacancy_candidate_id'), _('Отчёт')
        );

        // на этапе трудоустройства не позволяем менять статусы
        if (!is_a($this->_vacancyState, 'HM_Recruit_Vacancy_State_Hire')) {
            $grid->addMassAction(
                array(
                    'module' => 'candidate',
                    'controller' => 'list',
                    'action' => 'change-status',
                    'vacancy_id' => $vacancy_id,
                ),
                _('Назначить статус'),
                _('Вы уверены, что хотите изменить статус выбранных кандидатов? Если кандидат становится активным, ему будут заново назначены мероприятия программы подбора. Продолжить?')
            );

            $grid->addSubMassActionSelect(
                array($this->view->url(array('controller' => 'list', 'action' => 'change-status', 'vacancy_id' => $vacancy_id))),
                'status',
                HM_Recruit_Vacancy_Assign_AssignModel::getFullStatuses(),
                false
            );
        }

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

        $grid->addMassAction(
            array(
                'module' => 'candidate',
                'controller' => 'assign',
                'action' => 'unassign',
            ),
            _('Удалить'),
            _('Вы действительно желаете удалить отмеченных кандидатов из списка? При этом учётные записи удалены не будут и кандидаты останутся в общей базе резюме. Продолжить?')
        );

        $grid->setActionsCallback(
            array('function' => array($this, 'updateActions'),
                'params' => array('{{duplicate}}', '{{statusId}}', '{{candidate_id}}')
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


    public function _sendResumeAction()
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

        if ($vacancyDataFields) {
            $initiator = $this->getService('User')->fetchOne(array(
                'MID = ?' => $vacancyDataFields->user_id
            ));

            $recruiter = $this->getService('User')->getCurrentUser();

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
        } else {
            $this->_flashMessenger->addMessage(array('message' => _('Инициатор подбора не определён'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
        }

        $this->_redirectToIndex();
    }

    public function sendResumeAction()
    {
        $form = new HM_Form_Notifications();

        $notice = $this->getOne($this->getService('Notice')->fetchAll(array(
            'type = ?' => HM_Messenger::TEMPLATE_VACANCY_RESUME_SEND
        )));

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

        if ($vacancyDataFields) {
            $mainInitiator = $this->getService('User')->fetchOne(array(
                'MID = ?' => $vacancyDataFields->user_id ? $vacancyDataFields->user_id : 0
            ));

            if ($this->_getParam('sendnotifications',0)) {
                $request = $this->getRequest();
                if ($form->isValid($request->getParams())) {
                    $notice->title = $form->getValue('title');
                    $notice->message = $form->getValue('message');

                    $initiators   = array();
                    $initiators[] = $mainInitiator;
                    $additionals  = $form->getValue('initiators');

                    if (!empty($additionals)) {
                        foreach ($additionals as $mid) {
                            $initiators[] = $this->getService('User')->find($mid)->current();
                        }
                    }

                    $candidates = ($form->getValue('report_link')) ?
                        $this->sendReportAndResume($userNames, $candidatesList, $vacancyId, $vacancyCandidates) :
                        $this->sendResumeOnly($userNames, $candidatesList, $vacancyCandidates);

                    foreach ($initiators as $initiator) {
                        $messageParam = array(
                            'INITIATOR_LASTNAME' => $initiator->LastName,
                            'INITIATOR_FIRSTNAME' => $initiator->FirstName,
                            'INITIATOR_PATRONYMIC' => $initiator->Patronymic,
                            'RECRUITER' => $this->getService('Recruiter')->getCurrentRecruiterInfo(),
                            'CANDIDATES_LIST' => $candidates,
                            'VACANCY' => $vacancy->name,
                        );

                        $messenger = $this->getService('Messenger');

                        $messenger->setOptions($notice->type, $messageParam);
                        $messenger->forceTemplate($notice);
                        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $initiator->MID);
                    }
                    $message = _('Сообщение успешно отправлено');
                    if (false === $initiators[0])
                        $message = _('В сессии не назначен инициатор');
                    $this->_flashMessenger->addMessage($message);
                    $this->_redirectToIndex();
                }
            } else {
                $form->initWithData($postMassIds);
                $form->populate(array(
                    'title' => $notice->title,
                    'message' => $notice->message,
                ));
                $this->view->form = $form;
                $this->view->initiator = $mainInitiator;
                $this->view->listUsers = $users;

                $this->_helper->viewRenderer->setNoRender();
                echo $this->view->render('assign/send-notifications.tpl');
            }
        } else {
            $this->_flashMessenger->addMessage(array('message' => _('Инициатор подбора не определён'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirectToIndex();
        }
    }

    protected function sendResumeOnly($userNames, $candidatesList, $vacancyCandidates)
    {
        foreach ($vacancyCandidates as $vacancyCandidate) {
            $url = $this->view->serverUrl($this->view->url(
                array(
                    'module' => 'candidate',
                    'controller' => 'index',
                    'action' => 'resume',
                    'candidate_id' => $vacancyCandidate->candidate_id,
                ),
                null,
                true
            ));

            $href = '<a href="' . $url . '">' . $userNames[$vacancyCandidate->user_id] . '</a>';
            $candidatesList[] = '<li>'.$href.'</li>';
        }

        return '<ul>'.implode('',$candidatesList).'</ul>';
    }

    protected function sendReportAndResume($userNames, $candidatesList, $vacancyId, $vacancyCandidates) {
        foreach ($vacancyCandidates as $vacancyCandidate) {
            $hash = $this->getService('RecruitVacancyAssign')->getHash($vacancyCandidate->vacancy_candidate_id);

            $urlReport = $this->view->serverUrl($this->view->url(
                array(
                    'baseUrl' => 'recruit',
                    'module' => 'vacancy',
                    'controller' => 'report',
                    'action' => 'user',
                    'vacancy_id' => $vacancyId,
                    'vacancy_candidate_id' => $vacancyCandidate->vacancy_candidate_id,
                    'hash' => $hash,
                    'report_only' => 1
                ),
                null,
                true
            ));

            $urlResume = $this->view->serverUrl($this->view->url(
                array(
                    'module' => 'candidate',
                    'controller' => 'index',
                    'action' => 'resume',
                    'candidate_id' => $vacancyCandidate->candidate_id,
                )
            ));

            $hrefReport = '<a href="' . $urlReport . '">' . 'отчёт'  . '</a>';
            $hrefResume = '<a href="' . $urlResume . '">' . 'резюме' . '</a>';
            $candidatesList[] = '<li>'.$userNames[$vacancyCandidate->user_id].' '.$hrefReport.' '.$hrefResume.'</li>';
        }

        return '<ul>'.implode('',$candidatesList).'</ul>';
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

    public function updateActions($duplicate, $status, $candidateId, $actions)
    {
        // @todo: кэшировать
        $candidate = $this->getService('RecruitCandidate')->find($candidateId)->current();

        if ($candidate->hasResumeJson() || $candidate->hasResumeHtml()) {
            $this->unsetAction($actions,
                array(
                    'baseUrl' => 'recruit',
                    'module' => 'candidate',
                    'controller' => 'index',
                    'action' => 'download'
                )
            );
        } elseif ($candidate->hasResumeFile()) {
            $this->unsetAction(
                $actions,
                array(
                    'baseUrl' => 'recruit',
                    'module' => 'candidate',
                    'controller' => 'index',
                    'action' => 'resume'
                )
            );
        } else {
            $this->unsetAction(
                $actions,
                array(
                    'baseUrl' => 'recruit',
                    'module' => 'candidate',
                    'controller' => 'index',
                    'action' => 'download'
                )
            );

            $this->unsetAction(
                $actions,
                array(
                    'baseUrl' => 'recruit',
                    'module' => 'candidate',
                    'controller' => 'index',
                    'action' => 'resume'
                )
            );
        }


        if (
            ($status == HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE) ||
            is_a($this->_vacancyState, 'HM_Recruit_Vacancy_State_Hire')
        ) {
            $this->unsetAction($actions, array('module' => 'candidate', 'controller' => 'list', 'action' => 'assign-active'));
        }

        if (
            ($status == HM_Recruit_Vacancy_Assign_AssignModel::STATUS_PASSED) ||
            is_a($this->_vacancyState, 'HM_Recruit_Vacancy_State_Hire')
        ) {
            $this->unsetAction($actions, array('module' => 'candidate', 'controller' => 'assign', 'action' => 'decline'));
        }

        if ($duplicate != _('Да')) {
            $this->unsetAction($actions, array('module' => 'user', 'controller' => 'list', 'action' => 'duplicate-merge', 'baseUrl' => ''));
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
        $result = $this->_getParam('result');
        if (($programmEventId = $this->_getParam('programm_event_id')) && ($vacancyCandidateId = $this->_getParam('vacancy_candidate_id'))) {
            
            if (count($collection = $this->getService('RecruitVacancyAssign')->findDependence(array('SessionUser', 'RecruitCandidate'), $vacancyCandidateId))) {
                
                $vacancyCandidate = $collection->current();
                $resultStatus = $result ? $result : HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_DEFAULT;
                $this->getService('RecruitVacancyAssign')->setStatus($vacancyCandidate, $resultStatus);
                
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
    
    public function updateComments($comments)
    {
        $comments = explode('@@@', $comments);
        unset($comments[0]);
        if(!count($comments)) return '';

        $result =  array();
        foreach ($comments as $comment) {
            list($date, $time) = explode(' ', $comment);
            $unixDatetime = strtotime($date . ' ' . $time);
            $result[$unixDatetime] = "<p class='smaller'>".str_replace('",', '"', $comment)."</p>";
        }
        krsort($result);
        if (count($result) > 1) {
            array_unshift($result, '<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Comment')->pluralFormCount(count($result)) . '</p>');
        }

        return implode('',$result);
    }

    public function declineAction()
    {
        $gridId        = 'grid';
        $postMassField = 'postMassIds_' . $gridId;
        $postMassIds   = $this->_getParam($postMassField, '');
        $postMassIds = $postMassIds ? $postMassIds : $this->_getParam('vacancy_candidate_id', '');//mass или инди
        $candidates   = explode(',', $postMassIds);

        if($backUrl = $this->_getParam('backUrl', '')) {
            $result = $this->_getParam('result');
            $collection = $this->getService('RecruitVacancyAssign')->fetchAll(array('vacancy_candidate_id IN (?)' => $candidates));
            foreach($collection as $vacancyCandidate)  {
                $this->getService('RecruitVacancyAssign')->setStatus($vacancyCandidate, $result);
                $this->getService('Process')->goToFail($vacancyCandidate);
            }
            $this->_redirector->gotoUrl($backUrl);
        }//die


        if (count($candidates)) {
            $agreedForm  = new HM_Form();
            $agreedForm->setMethod(Zend_Form::METHOD_POST)->setName('agreed');

            $agreedForm->addElement('hidden', 'backUrl', array(
                'Required' => false,
                'Value' => $_SERVER['HTTP_REFERER']
            ));

            $agreedForm->addElement(
                'hidden',
                $postMassField,
                array(
                    'required' => false,
                    'Filters'  => array('StripTags'),
                    'Value'    => $postMassIds
                )
            );

            $agreedForm->addElement(
                'hidden',
                'result',
                array(
                    'required' => false,
                    'Filters'  => array('StripTags'),
                    'Value'    => 0
                )
            );

            $agreedForm->addElement(
                'submit',
                'cancel_no_status',
                array(
                    'Label' => _('Без статуса')
                )
            );

            $agreedForm->addElement(
                'submit',
                'cancel_blacklist',
                array(
                    'Label' => _('В черный список')
                )
            );

            $agreedForm->addElement(
                'submit',
                'cancel_reserve',
                array(
                    'Label' => _('В кадровый резерв')
                )
            );
            $agreedForm->addElement(
                'button',
                'prev',
                array(
                    'Label' => _('Отмена')
                )
            );

            $agreedForm->addDisplayGroup(
                array(
                    'cancel_no_status',
                    'cancel_blacklist',
                    'cancel_reserve',
                    'prev'
                ),
                'agreedGroup',
                array('legend' => 'Выберите один из способов отклонения')
            );

            $agreedForm->init();
//            $this->view->usersName     = $usersName;
            $this->view->form          = $agreedForm;
            $this->view->postMassField = $postMassField;
//            $this->view->userList      = $grdUsers;

        }                                                               // работа в обычном режиме
    }

    public function updateResultStatus($result, $status)
    {
        $statuses = HM_Recruit_Vacancy_Assign_AssignModel::getFullStatuses();

        $key = $status == HM_Recruit_Vacancy_Assign_AssignModel::STATUS_PASSED ? implode('_', array($status, $result)) : $status;
        if (isset($statuses[$key])) {
            return $statuses[$key];
        }
        return '';
    }

    public function filterResultStatus()
    {
        $args = func_get_args();
        extract($args[0]);

        list($status, $result) = HM_Recruit_Vacancy_Assign_AssignModel::extractFullStatus($value);
        $select->where('rvc.status = ?', $status);
        if ($result) {
            $select->where('rvc.result = ?', $result);
        }
    }

}
