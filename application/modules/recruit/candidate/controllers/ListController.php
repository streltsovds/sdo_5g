<?php

class Candidate_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;


    protected $_candidateId = 0;
    protected $_candidate = null;
    protected $_programmEventsCache = null;

    public function init()
    {
        $form = new HM_Form_Candidates();
        $this->_setForm($form);

        $this->_candidateId = $this->_getParam('candidate_id', 0);
        parent::init();
    }

    protected function _getMessages() 
    {
        return array(
            self::ACTION_INSERT => _('Кандидат успешно добавлен'),
            self::ACTION_UPDATE => _('Кандидат успешно обновлен'),
            self::ACTION_DELETE => _('Кандидат успешно удален'),
            self::ACTION_DELETE_BY => _('Кандидаты успешно удалены')
        );
    }

    protected function _redirectToIndex() 
    {
        $this->_redirector->gotoSimple('index');
    }

    public function create($form) 
    {
        $values = $form->getValues();
        $resume = $form->getElement('resume');
        if ($resume->isUploaded()) {
            $resume->receive();
            $file = $this->getService('Files')->addFile($resume->getFileName(), basename($resume->getFileName()));
            $values['file_id'] = $file->file_id;
        }
        unset($values['resume']);
        $values['source'] = HM_Recruit_Candidate_CandidateModel::SOURCE_EXTERNAL;
        $res = $this->getService('RecruitCandidate')->insert($values);

        $vacancyId = $this->_getParam('vacancy_id', 0);

        if ($vacancyId > 0) {

            $vacancy = $this->getService('RecruitVacancy')->find($vacancyId)->current();
            $this->getService('RecruitVacancy')->updateWorkFlow($vacancy, 104); //Отбор кандидатов
            $this->getService('RecruitVacancyAssign')->insert(array('candidate_id' => $res->candidate_id, 'vacancy_id' => $vacancyId));

            $this->_forward('assign-poll', null, null, array(
                'position_id' => $vacancy->position_id,
                'position_id' => $vacancy->position_id,
            ));
        }
    }

    public function delete($id) 
    {
        $this->getService('RecruitCandidate')->delete($id);
    }


    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $candidateVacancyIds = explode(',', $postMassIds);
            if (count($candidateVacancyIds)) {
                    $candidates = $this->getService('RecruitVacancyAssign')->fetchAll(array('vacancy_candidate_id IN (?)' => $candidateVacancyIds));
                    $ids = $candidates->getList('candidate_id');
                    foreach($ids as $id) {
                        $this->delete($id);
                    }
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE_BY));
            }
        }
        $this->_redirectToIndex();
    }

    public function indexAction() 
    {
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'vacancy_date_DESC');
        }        
        
        $select = $this->getService('User')->getSelect();
        $from = array(
            'MID' => 'p.MID',
            'user_id' => 'p.MID',
            'candidate_id' => 'rc.candidate_id',
            'vacancy_id' => 'rvc.vacancy_id',
            'vacancy_candidate_id' => 'rvc.vacancy_candidate_id',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            'source' => 'rc.source',
            'srcId' => 'rc.source',
            'statusId' => 'rvc.status',
            'url' => 'rc.resume_external_url',

            'vacancy_id' => 'rv.vacancy_id',
            'vacancy_name' => 'rv.name',

            'vacancy_all_ids' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT rv_all.vacancy_id)"),
            'vacancy_all' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT rv_all.name)"),

            'vacancy_date' => 'rv.create_date',
            'recruiter_ids' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT r.recruiter_id)"),
            'recruiter_names' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT CONCAT(CONCAT(CONCAT(CONCAT(pr.LastName, ' ') , pr.FirstName), ' '), pr.Patronymic))"),
            'resume' => 'rc.candidate_id',
            'last_state' => new Zend_Db_Expr('CASE WHEN rc.source = 4 THEN rvc.external_status ELSE prge.name END'),
            'comments' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT CONCAT('@@@', CONCAT(CONVERT(varchar, sopd.comment_date, 104), CONCAT(' ', CONCAT(CONCAT(CONCAT(CONCAT(CONCAT(p2.LastName, ' ') , p2.FirstName), ' '), p2.Patronymic), CONCAT(' \"', CONCAT(sopd.comment, '\"')))))))"),
            'duplicate_of' => new Zend_Db_Expr('CASE WHEN (p.duplicate_of = 0) THEN 0 ELSE 1 END'),
            'status' => 'rvc.status',
            'result' => 'rvc.result',
        );

        $select->from(array('rvc' => 'recruit_vacancy_candidates'), $from)
            ->joinLeft(array('rc' => 'recruit_candidates'), 'rvc.candidate_id = rc.candidate_id', array())
            ->join(array('p' => 'People'), 'p.MID = rc.user_id', array())
            ->joinLeft(array('rv' => 'recruit_vacancies'), 'rvc.vacancy_id = rv.vacancy_id', array())
            ->joinLeft(array('rvr' => 'recruit_vacancy_recruiters'), 'rvr.vacancy_id = rv.vacancy_id', array())
            ->joinLeft(array('r' => 'recruiters'), 'r.recruiter_id = rvr.recruiter_id', array())
            ->joinLeft(array('pr' => 'People'), 'pr.MID = r.user_id', array())
            ->joinLeft(array('so' => 'structure_of_organ'), 'so.mid = rc.user_id', array())
            ->joinLeft(array('sop' => 'state_of_process'), 'rvc.vacancy_candidate_id = sop.item_id AND process_type = ' . HM_Process_ProcessModel::PROCESS_PROGRAMM_RECRUIT, array())
            ->joinLeft(array('sopd' => 'state_of_process_data'), 'sopd.state_of_process_id = sop.state_of_process_id  AND sopd.comment IS NOT NULL', array())

            // @todo: если это работает слишком долго - переделать на last_passed_programm_event_id
            ->joinLeft(array('prge' => 'programm_events'), new Zend_Db_Expr("prge.programm_event_id = CAST(REPLACE(sop.last_passed_state, 'HM_Recruit_Vacancy_Assign_State_', '') AS INT)"), array())

            ->joinLeft(array('p2' => 'people'), 'p2.mid = sopd.comment_user_id', array())

            ->joinLeft(array('rc_all' => 'recruit_vacancy_candidates'), 'rc_all.user_id = rc.user_id', array())
            ->joinLeft(array('rvc_all' => 'recruit_vacancy_candidates'), 'rvc_all.candidate_id = rc_all.candidate_id', array())
            ->joinLeft(array('rv_all' => 'recruit_vacancies'), 'rvc_all.vacancy_id = rv_all.vacancy_id', array())

            ->group(array(
                'rc.candidate_id',
                'rv.vacancy_id',
                'rv.name',
                'rv.create_date',
                'rvc.vacancy_id',
                'rvc.vacancy_candidate_id',
                'rvc.status',
                'rvc.external_status',
                'rvc.result',
                'p.MID',
                'p.LastName',
                'p.FirstName',
                'p.Patronymic',
                'p.duplicate_of',
                'rc.source',
                'rc.resume_external_url',
                'prge.name'
        ));
        
        $currentUser = $this->getService('User')->getCurrentUser();
        // в зависимости от роли пользователя показываем разные учётные записи
        switch ($currentUser->role) {
            case HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL:
                $soid = $this->getService('Responsibility')->get();
                $responsibilityPosition = $this->getOne($this->getService('Orgstructure')->find($soid));
                if ($responsibilityPosition) {
                    $subSelect = $this->getService('Orgstructure')->getSelect()
                        ->from('structure_of_organ', array('soid'))
                        ->where('lft > ?', $responsibilityPosition->lft)
                        ->where('rgt < ?', $responsibilityPosition->rgt);
                    $select->where("(so.mid IS NULL) OR (rv.position_id IN (?))", $subSelect);
                } else {
                    $select->where('1 = 0');
                }                
                break;
        }

        $columnsOptions = array(
            'MID' => array('hidden' => true),
            'user_id' => array('hidden' => true),
            'vacancy_id' => array('hidden' => true),
            'recruiter_ids' => array('hidden' => true),
            'candidate_id' => array('hidden' => true),
            'vacancy_candidate_id' => array('hidden' => true),
            'vacancy_id' => array('hidden' => true),
            'vacancy_all_ids' => array('hidden' => true),
            'file_name' => array('hidden' => true),
            'srcId' => array('hidden' => true),
            'statusId' => array('hidden' => true),
            'resume' => array('hidden' => true),
            'result' => array('hidden' => true),
            'fio' => array(
                'title' => _('ФИО'),
                'decorator' => '<span style="white-space:nowrap;">'.
                $this->view->cardLink(
                    '/recruit/candidate/index/resume/candidate_id/' .
                    '{{candidate_id}}' . '/blank/1',
                    null,
                    'candidate',
                    'candidate',
                    'candidate',
                    true,
                    'candidate'
                    )
                . ' <a href="' . $this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . '{{user_id}}' . '">' . '{{fio}}</a>'.'</span>'
            ),
            'source' => array(
                'title' => _('Источник'),
                'callback' => array(
                    'function' => array($this, 'updateSource'),
                    'params' => array('{{source}}')
                )
            ),
            'status' => array(
                'title' => _('Статус'),
                'callback' => array(
                    'function' => array($this, 'updateResultStatus'),
                    'params' => array('{{result}}', '{{status}}')
                ),
            ),
            'url' => array('hidden' => true),
            'vacancy_name' => array(
                'title' => _('Сессия подбора')
            ),
            'vacancy_all' => array(
                'title' => _('История подбора'),
            ),
            'recruiter_names' => array(
                'title' => _('Специалисты по подбору'),
                'callback' => array(
                    'function' => array($this, 'recruitersCache'),
                    'params' => array('{{recruiter_ids}}')
                ),
            ),
            'vacancy_date' => array(
                'title' => _('Дата начала сессии подбора'),
                'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
                'callback' => array(
                    'function' => array($this, 'updateDate'),
                    'params' => array('{{vacancy_date}}')
                )
            ),
            'comments' => array(
                'title' => _('Комментарии'),
                'callback' => array(
                    'function' => array($this, 'updateComments'),
                    'params' => array('{{comments}}')
                )
            ),
            'last_state' => array(
                'title' => _('Последнее действие'),
                'callback' => array(
                    'function' => array($this, 'updateLastState'),
                    'params' => array('{{last_state}}', '{{srcId}}')
                ),
            ),
            'duplicate_of' => array(
                'title' => _('Дубликат'),
                'callback' => array(
                    'function' => array($this, 'updateDuplicate'),
                    'params' => array('{{duplicate_of}}')
                )
            ),
        );
//        echo $select;die();

        $grid = $this->getGrid(
            $select, 
            $columnsOptions, 
            array(
                'fio' => null,
                'source' => array(
                    'values'     => $this->getService('RecruitProvider')->getList(),
                    'searchType' => '='
                ),

                'recruiter_names' => null,
                'vacancy_name' => null,
                'comments' => null,
                'vacancy_all' => null,
                'vacancy_date' => array('render' => 'Date'),
                'status' => array(
                    'values'     => HM_Recruit_Vacancy_Assign_AssignModel::getFullStatuses(),
                    'callback' => array(
                        'function' => array($this, 'filterResultStatus'),
                        'params'   => array()
                    )
                ),
                'last_state' => null,
                'duplicate_of' => array(
                    'values'     => array(
                        1 => _('Да'),
                        0 => _('Нет')
                    ),
                    'searchType' => '='
                ),
            )
        );

        $grid->updateColumn('vacancy_name', array(
            'callback' => array(
                'function' => array($this, 'updateVacancy'),
                'params' => array('{{vacancy_id}}', '{{vacancy_name}}', '{{source}}')
            ))
        );

        $grid->updateColumn('vacancy_all', array(
            'callback' => array(
                'function' => array($this, 'vacanciesCache'),
                'params' => array('{{vacancy_all_ids}}', '{{vacancy_id}}')
            ))
        );

        $grid->addAction(array(
            'module' => 'candidate',
            'controller' => 'list',
            'action' => 'edit',
        ),
            array('MID', 'candidate_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'candidate',
            'controller' => 'list',
            'action' => 'delete',
        ),
            array('candidate_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addAction(array(
            'module' => 'user',
            'controller' => 'list',
            'action' => 'delete',
            'baseUrl' => '',
            ), 
            array('MID'),
            _('Удалить учётную запись')
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
        ),
            array('user_id'),
            _('Войти от имени пользователя'),
            _('Вы действительно хотите войти в систему от имени данного пользователя? При этом все функции Вашей текущей роли будут недоступны. Вы сможете вернуться в свою роль при помощи обратной функции "Выйти из режима". Продолжить?') // не работает??
        );

        $grid->addAction(array(
            'module' => 'user',
            'controller' => 'list',
            'action' => 'duplicate-merge',
            'from' => 'resume-base',
            'baseUrl' => '',
        ),
            array('MID'),
            _('Объединить дубликаты')
        );

        $grid->addAction(array(
            'module' => 'candidate',
            'controller' => 'calendar',
            'action' => 'index',
        ),
            array('vacancy_candidate_id'),
            _('Календарь мероприятий')
        );

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
            'gridmod' => null,
        ), array('vacancy_candidate_id'),
            _('Отчёт')
        );

        $grid->addMassAction(
            array(
                'module' => 'candidate',
                'controller' => 'list',
                'action' => 'change-status',
            ),
            _('Назначить статус'),
            _('Вы уверены, что хотите изменить статус выбранных кандидатов? Если кандидат становится активным, ему будут заново назначены мероприятия программы подбора. Продолжить?')
        );

        $grid->addSubMassActionSelect(
            array($this->view->url(array('action' => 'change-status'))),
            'status',
            HM_Recruit_Vacancy_Assign_AssignModel::getFullStatuses(),
            false
        );

        $vacancies = $this->getService('Recruiter')->getVacanciesForDropdownSelect();
        $vacancies = $vacancies[$currentUser->getName()];
        if (count($vacancies)) {


            $grid->addMassAction(array(
                'module' => 'candidate',
                'controller' => 'list',
                'action' => 'assign',
            ),
                _('Включить в другую сессию'),
                _('Вы уверены, что хотите назначить выбранных кандидатов в качестве активных кандидатов на другую вакансию?')
            );

            $grid->addSubMassActionSelect(array($this->view->url(array('action' => 'assign'))), 'assign_vacancy_id', $vacancies, false);

//            $grid->addMassAction(array(
//                'module' => 'candidate',
//                'controller' => 'list',
//                'action' => 'assign-hold-on',
//                ),
//                _('Включить в сессию подбора в качестве потенциального кандидата'),
//                _('Вы уверены, что хотите включить выбранных кандидатов или пользователей в данную сессию подбора в качестве потенциальных кандидатов? При этом оценочные мероприятия назначены не будут. Внешние кандидаты не могут проходить одновременно несколько сессий подбора, они не будут обработаны.')
//            );
//
//            $grid->addSubMassActionSelect(array($this->view->url(array('action' => 'assign-hold-on'))), 'vacancy_id', $vacancies, false);
        }
        
        $grid->addMassAction(
            array(
                'module' => 'candidate',
                'controller' => 'list',
                'action' => 'delete-by',
            ), 
            _('Удалить'),
            _('Вы уверены, что хотите удалить данного кандидата из базы резюме? При этом учётная запись пользователя останется. Чтобы полностью удалить учётную запись, используйте функцию "Удалить учётную запись".')
        );

        $grid->setClassRowCondition("{{duplicate_of}} > 0",'highlighted');


        $grid->setActionsCallback(
            array('function' => array($this, 'updateActions'),
                'params' => array('{{candidate_id}}', '{{vacancy_id}}', '{{srcId}}', '{{duplicate_of}}', '{{statusId}}')
            )
        );

        $this->view->grid = $grid;
        $this->view->isAjaxRequest = $this->isAjaxRequest();
    }

    public function reservePositionCandidatesAction()
    {
        $this->view->setHeader(_('Кандидаты на должность КР'));
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'vacancy_date_DESC');
        }

        $reservePositionId = $this->_request->getParam("reserve_position_id", 0);

        $select = $this->getService('User')->getSelect();
        $from = array(
            'MID' => 'p.MID',
            'user_id' => 'p.MID',
            'candidate_id' => 'rc.candidate_id',
            'vacancy_id' => 'rvc.vacancy_id',
            'vacancy_candidate_id' => 'rvc.vacancy_candidate_id',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            'source' => 'rc.source',
            'srcId' => 'rc.source',
            'statusId' => 'rvc.status',
            'url' => 'rc.resume_external_url',

            'vacancy_id' => 'rv.vacancy_id',
            'vacancy_name' => 'rv.name',

            'vacancy_all_ids' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT rv_all.vacancy_id)"),
            'vacancy_all' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT rv_all.name)"),

            'vacancy_date' => 'rv.create_date',
            'recruiter_ids' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT r.recruiter_id)"),
            'recruiter_names' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT CONCAT(CONCAT(CONCAT(CONCAT(pr.LastName, ' ') , pr.FirstName), ' '), pr.Patronymic))"),
            'resume' => 'rc.candidate_id',
            'last_state' => 'prge.name',
            'comments' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT CONCAT('@@@', CONCAT(CONVERT(varchar, sopd.comment_date, 104), CONCAT(' ', CONCAT(CONCAT(CONCAT(CONCAT(CONCAT(p2.LastName, ' ') , p2.FirstName), ' '), p2.Patronymic), CONCAT(' \"', CONCAT(sopd.comment, '\"')))))))"),
            'duplicate_of' => new Zend_Db_Expr('CASE WHEN (p.duplicate_of = 0) THEN 0 ELSE 1 END'),
            'status' => 'rvc.status',
            'result' => 'rvc.result',
        );

        $select->from(array('rvc' => 'recruit_vacancy_candidates'), $from)
            ->joinLeft(array('rc' => 'recruit_candidates'), 'rvc.candidate_id = rc.candidate_id', array())
            ->join(array('p' => 'People'), 'p.MID = rc.user_id', array())
            ->joinLeft(array('rv' => 'recruit_vacancies'), 'rvc.vacancy_id = rv.vacancy_id', array())
            ->joinLeft(array('rvr' => 'recruit_vacancy_recruiters'), 'rvr.vacancy_id = rv.vacancy_id', array())
            ->joinLeft(array('r' => 'recruiters'), 'r.recruiter_id = rvr.recruiter_id', array())
            ->joinLeft(array('pr' => 'People'), 'pr.MID = r.user_id', array())
            ->joinLeft(array('so' => 'structure_of_organ'), 'so.mid = rc.user_id', array())
            ->joinLeft(array('sop' => 'state_of_process'), 'rvc.vacancy_candidate_id = sop.item_id AND process_type = ' . HM_Process_ProcessModel::PROCESS_PROGRAMM_RECRUIT, array())
            ->joinLeft(array('sopd' => 'state_of_process_data'), 'sopd.state_of_process_id = sop.state_of_process_id  AND sopd.comment IS NOT NULL', array())

            // @todo: если это работает слишком долго - переделать на last_passed_programm_event_id
            ->joinLeft(array('prge' => 'programm_events'), new Zend_Db_Expr("prge.programm_event_id = CAST(REPLACE(sop.last_passed_state, 'HM_Recruit_Vacancy_Assign_State_', '') AS INT)"), array())

            ->joinLeft(array('p2' => 'people'), 'p2.mid = sopd.comment_user_id', array())

            ->joinLeft(array('rc_all' => 'recruit_vacancy_candidates'), 'rc_all.user_id = rc.user_id', array())
            ->joinLeft(array('rvc_all' => 'recruit_vacancy_candidates'), 'rvc_all.candidate_id = rc_all.candidate_id', array())
            ->joinLeft(array('rv_all' => 'recruit_vacancies'), 'rvc_all.vacancy_id = rv_all.vacancy_id', array())

            ->group(array(
                'rc.candidate_id',
                'rv.vacancy_id',
                'rv.name',
                'rv.create_date',
                'rvc.vacancy_id',
                'rvc.vacancy_candidate_id',
                'rvc.status',
                'rvc.result',
                'p.MID',
                'p.LastName',
                'p.FirstName',
                'p.Patronymic',
                'p.duplicate_of',
                'rc.source',
                'rc.resume_external_url',
                'prge.name',
                'rvc.reserve_position_id'
            ));
        $select->where('rvc.reserve_position_id = ?', $reservePositionId);

        $columnsOptions = array(
            'MID' => array('hidden' => true),
            'duplicate_of' => array(
                'title' => _('Дубликат'),
                'callback' => array(
                    'function' => array($this, 'updateDuplicate'),
                    'params' => array('{{duplicate_of}}')
                )
            ),
            'user_id' => array('hidden' => true),
            'vacancy_id' => array('hidden' => true),
            'recruiter_ids' => array('hidden' => true),
            'candidate_id' => array('hidden' => true),
            'vacancy_candidate_id' => array('hidden' => true),
            'vacancy_id' => array('hidden' => true),
            'vacancy_all_ids' => array('hidden' => true),
            'file_name' => array('hidden' => true),
            'srcId' => array('hidden' => true),
            'statusId' => array('hidden' => true),
            'resume' => array('hidden' => true),
            'result' => array('hidden' => true),
            'fio' => array(
                'title' => _('ФИО'),
                'decorator' => '<span style="white-space:nowrap;">'.
                    $this->view->cardLink(
                        '/recruit/candidate/index/resume/candidate_id/' .
                        '{{candidate_id}}' . '/blank/1',
                        null,
                        'candidate',
                        'candidate',
                        'candidate',
                        true,
                        'candidate'
                    )
                    . ' <a href="' . $this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'baseUrl' => '', 'user_id' => ''), null, true) . '{{user_id}}' . '">' . '{{fio}}</a>'.'</span>'
            ),
            'source' => array(
                'title' => _('Источник'),
                'callback' => array(
                    'function' => array($this, 'updateSource'),
                    'params' => array('{{source}}')
                )
            ),
            'status' => array(
                'title' => _('Статус'),
                'callback' => array(
                    'function' => array($this, 'updateResultStatus'),
                    'params' => array('{{result}}', '{{status}}')
                ),
            ),
            'url' => array('hidden' => true),
            'vacancy_name' => array(
                'title' => _('Сессия подбора')
            ),
            'vacancy_all' => array(
                'title' => _('История подбора'),
            ),
            'recruiter_names' => array(
                'title' => _('Специалисты по подбору'),
                'callback' => array(
                    'function' => array($this, 'recruitersCache'),
                    'params' => array('{{recruiter_ids}}')
                ),
            ),
            'vacancy_date' => array(
                'title' => _('Дата начала сессии подбора'),
                'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
                'callback' => array(
                    'function' => array($this, 'updateDate'),
                    'params' => array('{{vacancy_date}}')
                )
            ),
            'comments' => array(
                'title' => _('Комментарии'),
                'callback' => array(
                    'function' => array($this, 'updateComments'),
                    'params' => array('{{comments}}')
                )
            ),
            'last_state' => array(
                'title' => _('Последнее действие'),
            )
        );

        $grid = $this->getGrid(
            $select,
            $columnsOptions,
            array(
                'fio' => null,
                'source' => array(
                    'values'     => $this->getService('RecruitProvider')->getList(),
                    'searchType' => '='
                ),

                'recruiter_names' => null,
                'vacancy_name' => null,
                'comments' => null,
                'vacancy_all' => null,
                'vacancy_date' => array('render' => 'Date'),
                'status' => array(
                    'values'     => HM_Recruit_Vacancy_Assign_AssignModel::getFullStatuses(),
                    'callback' => array(
                        'function' => array($this, 'filterResultStatus'),
                        'params'   => array()
                    )
                ),
                'last_state' => null,
                'duplicate_of' => array(
                    'values'     => array(
                        1 => _('Да'),
                        0 => _('Нет')
                    ),
                    'searchType' => '='
                ),
            )
        );

        $grid->updateColumn('vacancy_name', array(
                'callback' => array(
                    'function' => array($this, 'updateVacancy'),
                    'params' => array('{{vacancy_id}}', '{{vacancy_name}}', '{{source}}')
                ))
        );

        $grid->updateColumn('vacancy_all', array(
                'callback' => array(
                    'function' => array($this, 'vacanciesCache'),
                    'params' => array('{{vacancy_all_ids}}', '{{vacancy_id}}')
                ))
        );

        $grid->addMassAction(
            array(
                'module' => 'candidate',
                'controller' => 'list',
                'action' => 'remove-reserve-position-link',
                'reserve_position_id' => $reservePositionId
            ),
            _('Отменить связь с должностью КР'),
            _('Вы уверены, что хотите отменить связь с должностью КР?')
        );

        $this->view->grid = $grid;
        $this->view->isAjaxRequest = $this->isAjaxRequest();
    }

    public function removeReservePositionLinkAction()
    {
        $reservePositionId = $this->_request->getParam("reserve_position_id", 0);
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $candidateVacancyIds = explode(',', $postMassIds);
            if (count($candidateVacancyIds)) {
                $candidates = $this->getService('RecruitVacancyAssign')->fetchAll(
                    array(
                        'vacancy_candidate_id IN (?)' => $candidateVacancyIds
                    )
                );
                $ids = $candidates->getList('vacancy_candidate_id');
                foreach($ids as $id) {
                    $this->getService('RecruitVacancyAssign')->update(
                        array(
                            'vacancy_candidate_id' => $id,
                            'reserve_position_id'  => 0,
                        )
                    );
                }

                $reservePosition = $this->getService('HrReservePosition')->find($reservePositionId)->current();
                $this->_flashMessenger->addMessage('Связь выбранных кандидатов с должностью кадрового резерва "' . $reservePosition->name . '" успешно отменена.');
            }
        }
        $this->_redirector->gotoSimple(
            'reserve-position-candidates',
            'list',
            'candidate',
            array('reserve_position_id' => $reservePositionId));
    }
    
    public function updateDuplicate($duplicate) 
    {
        return $duplicate ? _('Да') : _('Нет');
    }

    public function updateVacancy($vacancyId, $vacancy_name, $source)
    {
        if ($source == 'E-Staff') return $vacancy_name;
                                  
        $url = $this->view->url(array('module' => 'vacancy', 'controller' => 'report', 'action' => 'card', 'vacancy_id' => $vacancyId));

        return "<a href='{$url}'>{$vacancy_name}</a>";
    }

    public function assignAction()
    {
        $vacancyId = $this->_getParam('assign_vacancy_id', false);
        if(!$vacancyId){
            $vacancyId = $this->_getParam('vacancy_id', false);
        }
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $candidateVacancyIds = explode(',', $postMassIds);

        $candidates = $this->getService('RecruitVacancyAssign')->fetchAll(array('vacancy_candidate_id IN (?)' => $candidateVacancyIds));

        $this->_assign($vacancyId, $candidates->getList('candidate_id'));
    }

    public function assignFromVacancyAction()
    {
        $assignVacancyId = $this->_getParam('assign_vacancy_id', false);
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $vacancyCandidateIds = explode(',', $postMassIds);

        if (count($candidates = $this->getService('RecruitVacancyAssign')->fetchAll(array('vacancy_candidate_id IN (?)' => $vacancyCandidateIds)))) {
            $candidateIds = array();
            foreach ($candidates as $candidate) {
                $candidateIds[] = $candidate->candidate_id;
            }
            $this->_assign($assignVacancyId, $candidateIds);
        }
    }

    public function assignHoldOnAction() 
    {
        $vacancyId = $this->_getParam('vacancy_id', false);
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $candidateIds = explode(',', $postMassIds);
        
        $this->_assign($vacancyId, $candidateIds, HM_Recruit_Vacancy_Assign_AssignModel::STATUS_HOLD_ON);   
    }
    
    public function assignNewAction() 
    {
        $vacancyId = $this->_getParam('vacancy_id', false);
        $postMassIds = $this->_getParam('postMassIds_grid', ''); // это массив user_id
        
        if (count($userIds = explode(',', $postMassIds))) {
            
            $candidateIds = array();
            if (count($candidates = $this->getService('RecruitCandidate')->fetchAll(array('user_id IN (?)' => $userIds)))) {
                $candidateIds = $candidates->getList('user_id', 'candidate_id');
            }
            
            if (count($newCandidateIds = array_diff($userIds, array_keys($candidateIds)))) {
                foreach ($newCandidateIds as $userId) {
                    $candidate = $this->getService('RecruitCandidate')->insert(array(
                        'user_id' => $userId,
                        'source' => HM_Recruit_Provider_ProviderModel::ID_PERSONAL,
                    ));
                    $candidateIds[$userId] = $candidate->candidate_id;
                }
            }
        }

        $this->_assign($vacancyId, $candidateIds);        
    }

    public function assignNewHoldOnAction() 
    {
        $vacancyId = $this->_getParam('vacancy_id', false);
        $postMassIds = $this->_getParam('postMassIds_grid', ''); // это массив user_id
        
        if (count($userIds = explode(',', $postMassIds))) {
            
            $candidateIds = array();
            if (count($candidates = $this->getService('RecruitCandidate')->fetchAll(array('user_id IN (?)' => $userIds)))) {
                $candidateIds = $candidates->getList('user_id', 'candidate_id');
            }
            
            if (count($newCandidateIds = array_diff($userIds, array_keys($candidateIds)))) {
                foreach ($newCandidateIds as $userId) {
                    $candidate = $this->getService('RecruitCandidate')->insert(array(
                        'user_id' => $userId,
                        'source' => HM_Recruit_Candidate_CandidateModel::SOURCE_INTERNAL,
                    ));
                    $candidateIds[$userId] = $candidate->candidate_id;
                }
            }
        }

        $this->_assign($vacancyId, $candidateIds, HM_Recruit_Vacancy_Assign_AssignModel::STATUS_HOLD_ON);        
    }
    
//    public function assignHhAction() 
//    {
//        $candidates = $this->_getParam('resumes');
//        $k = sizeof($candidates);
//        $vacancyId = $this->_getParam('vacancy_id');
//        $status = $this->_getParam('status', HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE);
//
//        $userId = $this->getService('User')->getCurrentUserId();
//        $vacancy = $this->getService('RecruitVacancy')->find($vacancyId)->current();
//        $candidateSearchServiceName = Zend_Registry::get('config')->vacancy->externalSource;
//        /* @var $huntingService HM_Recruit_RecruitingServices_PlacementBehavior */
//        $huntingService = $this->getService('RecruitServiceFactory')->getRecruitingService($candidateSearchServiceName);
//        $ids = array();
//        $response = array();
//
//        foreach ($candidates as $resumeId => $resumeData) {
//            try {
//                $resumeHash = $resumeData['resumeHash'];
//                /* @var $resume HM_Recruit_RecruitingServices_Entity_AbstractCandidate */
//                $resume = $huntingService->getCandidateResume(array('resumeHash' => $resumeData['resumeHash']));
//
//                // создаем пользователя
//                $user = $this->getService('User')->insert(array(
//                    'LastName' => $resume->getLastName(),
//                    'FirstName' => $resume->getFirstName(),
//                    'Patronymic' => $resume->getPatronymic(),
//                    'BirthDate' => $resume->getBirthDate(),
//                    'EMail' => $resume->getEmail(),
//                    'Phone' => $resume->getPhone(),
//                    'Login' => 'hh_' . $resumeId,
//                    'Password' => new Zend_Db_Expr("PASSWORD('hh_$resumeId')"),
//                    'blocked' => 1
//                ));
//
//                // добавляем кандидата
//                $candidate = $this->getService('RecruitCandidate')->insert(array(
//                    'user_id' => $user->MID,
//                    'source' => HM_Recruit_Candidate_CandidateModel::SOURCE_EXTERNAL_HH,
//                    'resume_external_url' => $resume->getUrl(),
//                ));
//                
//                $vacancyCandidate = $this->getService('RecruitVacancyAssign')->assign($vacancyId, $candidate->candidate_id, $status);
//
//                // делаем невидимым
//                $this->getService('RecruitVacancyResumeHhIgnore')->insert(array(
//                    'vacancy_id' => (int) $vacancyId,
//                    'hh_resume_id' => (int) $resumeId,
//                    'date' => new Zend_Db_Expr('NOW()'),
//                    'create_user_id' => $userId
//                ));
//                $ids[] = $resumeId;
//                
//            } catch (Exception $e) {
//                
//            }
//        }
//        $response['state'] = 'ok';
//        $response['ids'] = $ids;
//        if ($k > 0 && sizeof($ids) != $k) {
//            $response['state'] = 'error';
//        }
//
//        $this->_helper->json($response);
//    }


    public function assignAllAction()
    {
        $resumeIds = explode(',', $this->_getParam('postMassIds_grid', ''));

        $postParams = $this->getRequest()->getPost();
        if($postParams['vacancy_id']){
            $vacancyId = $postParams['vacancy_id'];
        } else {
            $vacancyId = $this->_getParam('vacancy_id');
        }
        
        $status = $this->_getParam('status', HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE);

        foreach($resumeIds as $resumeId) {

            $resumeIdParts = explode(':',$resumeId);

            switch ($resumeIdParts[0]) {
                case HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER:
                    $this->_fetchResumeAndAssignHH($resumeIdParts[1], $vacancyId, $status);
                    break;
                case HM_Recruit_Provider_ProviderModel::ID_SUPERJOB:
                    $this->_assignSJ($resumeIdParts[1], $vacancyId, $status);
                    break;
            }
        }

        $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => _('Кандидаты усешно включены в сессию подбора')
        ));

        $this->_redirector->gotoSimple('index', 'assign', 'candidate', array('vacancy_id' => $vacancyId), null, true);
    }

    
    public function assignSuperjobAction() 
    {        
        $resumeIds = explode(',', $this->_getParam('postMassIds_grid', ''));        
        
        $vacancyId = $this->_getParam('vacancy_id');
        $status = $this->_getParam('status', HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE);

        foreach($resumeIds as $resumeId) {
            $this->_assignSJ($resumeId, $vacancyId, $status);
        }

        $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => _('Кандидаты усешно включены в сессию подбора')
        ));
        
        $this->_redirector->gotoSimple('responses', 'superjob', 'vacancy', array('vacancy_id' => $vacancyId), null, true);
    }


    public function assignHhAction()
    {
        $resumeIds = explode(',', $this->_getParam('postMassIds_grid', ''));

        $vacancyId = $this->_getParam('vacancy_id');
        $status = $this->_getParam('status', HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE);


        foreach($resumeIds as $resumeId) {
            $this->_fetchResumeAndAssignHH($resumeId, $vacancyId, $status);
        }

        $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => _('Кандидаты усешно включены в сессию подбора')
        ));

        $this->_redirector->gotoSimple('responses', 'hh', 'vacancy', array('vacancy_id' => $vacancyId), null, true);
    }


    private function _assignSJ($resumeId, $vacancyId, $status)
    {
        /* @var $huntingService HM_Recruit_RecruitingServices_Rest_Superjob */
        $huntingService = $this->getService('RecruitServiceFactory')->getRecruitingService('superjob');

        $resumeData = $huntingService->getCandidateResume(array('externalResumeId' => $resumeId));

        $birthDate = sprintf('%s-%s-%s', $resumeData->birthyear, str_pad($resumeData->birthmonth, 2, '0', STR_PAD_LEFT), $resumeData->birthday);

        if ($resumeData->lastname || $resumeData->firstname || $resumeData->middlename){
            $lastname   = $resumeData->lastname;
            $firstname  = $resumeData->firstname;
            $middlename = $resumeData->middlename;
        } else {
            $lastname   = '';
            $firstname  = _('Cоискатель') . ' #' . $resumeData->id;
            $middlename = '';
        }

        // создаем пользователя
        $uniqueStr = sprintf('sj_%s_%s', $resumeData->id, time());
        $userArray = array(
            'LastName'   => $lastname,
            'FirstName'  => $firstname,
            'Patronymic' => $middlename,
            'BirthDate'  => $birthDate,
            'EMail'      => $resumeData->email,
            'Phone'      => $resumeData->phone1,
            'vacancy_id' => $vacancyId,
            'resume_external_id' => $resumeId,
            'resume_external_url' => $resumeData->link,
            'Login'      => $uniqueStr,
            'Password'   => $uniqueStr,
            'resume_json' => json_encode($resumeData)
        );

        $candidate = $this->getService('RecruitCandidate')->createCandidate($userArray, HM_Recruit_Provider_ProviderModel::ID_SUPERJOB, $status);

        try {
            $imageStr = $huntingService->getPhoto($resumeData);
            if ($candidate->user && $imageStr) {
                $this->getService('User')->addPhotoFromStr($candidate->user->MID, $imageStr);
            }
        } catch (Exception $e) {
            // ну и фиг с ним
        }

        return true;


    }

    /*
     * Резюме доступно разными способами
     * Опытным путём выбран этот способ
     */
    private function _fetchResumeAndAssignHH($negotiationId, $vacancyId, $status)
    {
        /* @var $huntingService HM_Recruit_RecruitingServices_Rest_Hh */
        $huntingService = $this->getService('RecruitServiceFactory')->getRecruitingService('hh');

        if (!$huntingService->getAuthToken()) {
            $huntingService->getAuth();
        }

        $negotiation = $huntingService->getNegotiation(array('negotiationId' => $negotiationId));
        $resume = $negotiation->resume;

// алтернативный способ получения резюме, как-то не всегда работает
//        $resumeJson = $huntingService->getCandidateResume(array('externalResumeId' => $negotiation->resume->id, 'json' => 1));
//        $resume = json_decode($resumeJson); //$negotiation->resume;
//        $resume->json = $resumeJson;

        $this->_assignHH($negotiationId, $vacancyId, $status, $resume);

        return true;
    }

    protected function _assignHH($negotiationId, $vacancyId, $status, $resume)
    {
        /* @var $huntingService HM_Recruit_RecruitingServices_Rest_Hh */
        $huntingService = $this->getService('RecruitServiceFactory')->getRecruitingService('hh');

        $resumeData = $resume;

        $uniqueStr = sprintf('hh_%s_%s', $resumeData->id, time());
        $dataArray = array(
            'LastName'   => $resumeData->last_name,
            'FirstName'  => $resumeData->first_name,
            'Patronymic' => $resumeData->middle_name,
            'BirthDate'  => $resumeData->birth_date,
            'Login'      => $uniqueStr,
            'Password'   => $uniqueStr,
            'vacancy_id'          => $vacancyId,
            'resume_external_id'  => $resume->id,
            'resume_external_url' => $resumeData->alternate_url,
            'resume_json'         => $resumeData->json ? : $this->_jsonEncodeUtfSafe($resumeData), // в 5.3 нет нужной константы JSON_UNESCAPED_UNICODE
            'hh_area'             => $resumeData->area->id,
            'hh_metro'            => $resumeData->metro->id,
            'hh_salary'           => $resumeData->salary->amount,
            'hh_total_experience' => $resumeData->total_experience->months,
            'hh_education'        => $resumeData->education->level->id,
            'hh_citizenship'      => $resumeData->citizenship->id,
            'hh_age'              => $resumeData->age,
            'hh_gender'           => $resumeData->gender->id,
            'hh_specializations'  => $resumeData->specialization,
            'hh_negotiation_id'   => $negotiationId,
        );

        foreach ($resumeData->contact as $contactInfo) {
            if ($contactInfo->type->id == 'cell') {
                $dataArray['Phone'] = $contactInfo->value->formatted;
            }
            if ($contactInfo->type->id == 'email') {
                $dataArray['EMail'] = $contactInfo->value;
            }
        }

        $candidate = $this->getService('RecruitCandidate')->createCandidate($dataArray, HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER, $status);

        try {
            $imageStr = $huntingService->getPhoto($resume);
            if ($candidate->user && $imageStr) {
                $this->getService('User')->addPhotoFromStr($candidate->user->MID, $imageStr);
            }
        } catch (Exception $e) {
            // ну и фиг с ним
        }

    }

    protected function _jsonEncodeUtfSafe($arr)
    {
        //convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
        array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
        return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
    }

    protected function _assign($vacancyId, $candidateIds, $status = HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE)
    {
        $vacancyService       = $this->getService('RecruitVacancy');
        $candidateService     = $this->getService('RecruitCandidate');
        $vacancyAssignService = $this->getService('RecruitVacancyAssign');
        
        $vacancy = $vacancyService->getOne($vacancyService->findDependence(array('RecruiterAssign', 'Session'), $vacancyId));
        $currentVacancyCandidates = $vacancyAssignService->fetchAllDependence(array('Candidate', 'Vacancy'), array(
            'vacancy_id = ?' => $vacancyId
        ));
        $currentCandidateIds = $currentVacancyCandidates->getList('candidate_id');
        $currentUserIds = $currentVacancyCandidates->getList('user_id');

        $newCandidates = $candidateService->fetchAll(array('candidate_id IN (?)' => $candidateIds));

        $ids = array();
        $assigned = array();

        foreach ($newCandidates as $candidate) {

            // если сделали "включить в другую сессию", а выбрали ту же сессию - игнорируем
            if (in_array($candidate->candidate_id, $currentCandidateIds)) continue;

            // если кандидаты разные, но юзер один - тоже игнорируем
            // т.к. нет смысла иметь одного человека с двумя разными резюме в одной сессии
            if (in_array($candidate->user_id, $currentUserIds)) continue;

            $candidateId = $this->getService('RecruitCandidate')->copyCandidate($candidate->candidate_id);

            $ids[$candidateId] = $candidateId;

            $vacancyCandidate = $this->getService('RecruitVacancyAssign')->assign($vacancyId, $candidateId, $status);
            if ($vacancyCandidate) {
                $assigned[$candidateId] = $candidateId;
                $currentCandidateIds[] = $vacancyCandidate->candidate_id;
                $currentUserIds[] = $vacancyCandidate->user_id;
            }
        }
        $diff = array_diff($ids, $assigned);
        if (count($assigned)) {
            $messageType = HM_Notification_NotificationModel::TYPE_SUCCESS;
            $message = !count($diff) ?
                _('Все пользователи успешно включены в список кандидатов на вакансию') :
                _('Часть пользователей включены в список кандидатов на вакансию; не включены: ' . count($diff));
                
        } else {
             $messageType = HM_Notification_NotificationModel::TYPE_ERROR;
             $message = _('Пользователи не включены в список кандидатов');             
        }

        if (
            !count($currentVacancyCandidates) &&
            $vacancyCandidate &&
            ($vacancy->session->current()->state != HM_At_Session_SessionModel::STATE_ACTUAL)
        ) {
            $session = $this->getService('AtSession')->getOne($this->getService('AtSession')->find(intval($vacancy->session_id)));
            $this->getService('RecruitVacancy')->startSession($vacancy, $session);

        }
        //$this->getService('Process')->goToNextState($vacancy);

        $this->_flashMessenger->addMessage(array(
            'type' => $messageType,
            'message' => $message
        ));
        $this->_redirector->gotoUrl($this->view->url(array('module' => 'candidate', 'controller' => 'assign', 'action' => 'index', 'vacancy_id' => $vacancyId)), array('prependBase' => false));
    }    
    
    public function changeStatusAction()
    {
        $vacancyId = $this->_getParam('vacancy_id', false);
        $fullstatus = $this->_getParam('status', false);
        list($status, $result) = HM_Recruit_Vacancy_Assign_AssignModel::extractFullStatus($fullstatus);

        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $vacancyCandidateId = $this->_getParam('vacancy_candidate_id', '');
        if ($postMassIds) {
            $vacancyCandidateIds = explode(',', $postMassIds);
        } elseif ($vacancyCandidateId) {
            $vacancyCandidateIds = array($vacancyCandidateId);
        }

        if (is_array($vacancyCandidateIds)) {

            $vacancyCandidates = $this->getService('RecruitVacancyAssign')->fetchAllDependence(array('Candidate', 'Vacancy'), array('vacancy_candidate_id IN (?)' => $vacancyCandidateIds));
            foreach ($vacancyCandidates as $vacancyCandidate) {

                $vacancy = $vacancyCandidate->vacancies->current();
                $vacancyState = $this->getService('Process')->getCurrentState($vacancy);

                // на этапе трудоустройства не позволяем менять статусы
                if (is_a($vacancyState, 'HM_Recruit_Vacancy_State_Hire')) continue;

                if (
                    ($vacancyCandidate->status != HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE) &&
                    ($status == HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE)
                ) {
                    // если меняем с любого на активный - стартуем процесс
                    $this->getService('RecruitVacancyAssign')->assignActive($vacancyCandidate->vacancy_candidate_id);

                } else {

                    // обязательно назначить result перед goToFail()
                    // иначе будет считаться успешно завершившим
                    $vacancyCandidate->result = $result;
                    if ($vacancyCandidate->status == HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE) {

                        if ($status == HM_Recruit_Vacancy_Assign_AssignModel::STATUS_PASSED) {
                            if ($result == HM_Recruit_Vacancy_Assign_AssignModel::RESULT_SUCCESS) {
                                // если меняем на рекомендован - стопим все процессы, переводим вакансию на этап Hire
                                $this->getService('Process')->goToComplete($vacancyCandidate);
                            } else {
                                // если меняем на отклонен - стопим процесс
                                $this->getService('Process')->goToFail($vacancyCandidate);
                            }
                        } elseif ($status == HM_Recruit_Vacancy_Assign_AssignModel::STATUS_HOLD_ON) {
                            // если меняем на отклик - удаляем процесс
                            $this->getService('RecruitVacancyAssign')->unassignProcess($vacancyCandidate->vacancy_candidate_id);
                        }
                    }

                    $vacancyCandidate->status = $status;
                    $this->getService('RecruitVacancyAssign')->update($vacancyCandidate->getData());
                }
            }
        }

        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Статусы кандидатов изменены успешно')
            ));

            if ($vacancyId) {
                $this->_redirector->gotoUrl($this->view->url(array(
                    'module' => 'candidate',
                    'controller' => 'assign',
                    'action' => 'index',
                    'vacancy_id' => $vacancyId,
                )), array('prependBase' => false)
                );
            } else {
                $this->_redirector->gotoUrl($this->view->url(array(
                    'module' => 'candidate',
                    'controller' => 'list',
                    'action' => 'index',
                )), array('prependBase' => false)
                );
            }
        } else {
            // @todo: сделать обратную связь, информацию об ошибках и т.п.
            exit();
        }
    }

    public function assignActiveAction()
    {
        $vacancyCandidateId = $this->_getParam('vacancy_candidate_id', false);
        $vacancyCandidate = $this->getService('RecruitVacancyAssign')->assignActive($vacancyCandidateId);

        $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => _('Пользователь успешно включен в список активных кандидатов на вакансию')
        ));
        $this->_redirector->gotoUrl($this->view->url(array('module' => 'candidate', 'controller' => 'assign', 'action' => 'index', 'vacancy_id' => $vacancyCandidate->vacancy_id, 'vacancy_candidate_id' => null)), array('prependBase' => false));
    }

    public function assignActiveMassAction() 
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $vacancyCandidateIds = explode(',', $postMassIds);

        if (is_array($vacancyCandidateIds)) {
            foreach ($vacancyCandidateIds as $vacancyCandidateId) {
                $vacancyCandidate = $this->getService('RecruitVacancyAssign')->assignActive($vacancyCandidateId);
            }
        }

        $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => _('Пользователи успешно включены в список активных кандидатов на вакансию')
        ));
        $this->_redirector->gotoUrl($this->view->url(array('module' => 'candidate', 'controller' => 'assign', 'action' => 'index', 'vacancy_id' => $vacancyCandidate->vacancy_id, 'vacancy_candidate_id' => null)), array('prependBase' => false));
    }


    public function updateName($candidateId, $name) 
    {
        return $this->view->escape($name);
        //return '<a href="' . $this->view->url(array('action' => 'index', 'vacancy_id' => null, 'candidate_id' => $candidateId)) . '">' . $this->view->escape($name) . '</a>';
    }

    public function updateLastState($lastState, $source)
    {
        if ($source == HM_Recruit_Candidate_CandidateModel::SOURCE_EXTERNAL_ESTAFF) {
            return HM_EstaffSpot_EstaffSpotService::getStateName($lastState);
        }
        return $lastState;
    }

    public function updateSource($source)
    {
        $sources = $this->getService('RecruitProvider')->getList();
        return $sources[$source];
    }

    public function updateActions($candidateId, $vacancyId, $source, $duplicate_of, $status, $actions)
    {
        // @todo: кэшировать
        $candidate = $this->getService('RecruitCandidate')->find($candidateId)->current();
        if ($vacancy = $this->getService('RecruitVacancy')->getOne($this->getService('RecruitVacancy')->find($vacancyId))) {
            $vacancyState = $this->getService('Process')->getCurrentState($vacancy);
        }

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

        if ($source == HM_Recruit_Provider_ProviderModel::ID_PERSONAL) {
            $this->unsetAction($actions, array('module' => 'user', 'controller' => 'list', 'action' => 'edit', 'baseUrl' => ''));
            $this->unsetAction($actions, array('module' => 'user', 'controller' => 'list', 'action' => 'delete', 'baseUrl' => ''));
        }

        if (
            ($status == HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE) ||
            is_a($vacancyState, 'HM_Recruit_Vacancy_State_Hire')
        ) {
            $this->unsetAction($actions, array('module' => 'candidate', 'controller' => 'list', 'action' => 'assign-active'));
        }

        if (
            ($status == HM_Recruit_Vacancy_Assign_AssignModel::STATUS_PASSED) ||
            is_a($vacancyState, 'HM_Recruit_Vacancy_State_Hire')
        ) {
            $this->unsetAction($actions, array('module' => 'candidate', 'controller' => 'assign', 'action' => 'decline'));
        }

        if ($duplicate_of != _('Да')) {
            $this->unsetAction($actions, array('module' => 'user', 'controller' => 'list', 'action' => 'duplicate-merge', 'baseUrl' => ''));

        }
        return $actions;
    }
    
    
    protected function initHH()
    {
        if ($this->hh) {
            return;
        }

        $factory = $this->getService('RecruitServiceFactory');
        $config = Zend_Registry::get('config')->vacancy;

        if ($config->hh->enabled) {
            $this->hh = $factory->getRecruitingService($config->externalSource, $config->api);

            $actionName = $this->getRequest()->getActionName();

            if (!$this->hh->getAuthToken()) {
                if($this->hh->getAuth()){
                    $this->_redirector->gotoSimple($actionName);
                }
            }
        }
    }


    protected function initSuperjob()
    {
        if ($this->superjob) {
            return;
        }

        $factory = $this->getService('RecruitServiceFactory');
        $config = Zend_Registry::get('config')->vacancy;

        if ($config->superjob->enabled) {
            $this->superjob = $factory->getRecruitingService('Superjob', HM_Recruit_RecruitingServices_PlacementBehavior::API_REST);

            $actionName = $this->getRequest()->getActionName();

            if (!$this->superjob->getAuthToken()) {
                if($this->superjob->getAuth()){
                    $this->_redirector->gotoSimple($actionName);
                }
            }
        }
    }
    
    public function loadNewResumesAction()
    {
        $vacancyService   = $this->getService('RecruitVacancy');
        $vacancies = $vacancyService->fetchAllManyToMany('Recruiter', 'RecruiterAssign', array(
            '(hh_vacancy_id > 0 OR superjob_vacancy_id > 0) AND status IN (?)' => array(
                HM_Recruit_Vacancy_VacancyModel::STATE_PENDING,
                HM_Recruit_Vacancy_VacancyModel::STATE_ACTUAL,
            )
        ));

        if (!count($vacancies)) {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Вакансии не опубликованы на внешних ресурсах либо подбор по ним завершен')
            ));
            $this->_redirector->gotoSimple('index', 'list', 'vacancy', array(), null, true);
        }

        $recruiterVacancies = array();
        foreach ($vacancies as $vacancy) {
            if (count($vacancy->recruiters)) {
                $recruiterIds = $vacancy->recruiters->getList('user_id');
                if (in_array($this->getService('User')->getCurrentUserId(), $recruiterIds)) {
                    $recruiterVacancies[] = $vacancy;
                }
            }
        }

        if (!count($recruiterVacancies)) {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Вакансии назначены другим специалистам по подбору')
            ));
            $this->_redirector->gotoSimple('index', 'list', 'vacancy', array(), null, true);
        }

        $resumeCounter = $this->_refreshResumesByVacancies($recruiterVacancies);

        $this->_flashMessenger->addMessage(array(
            'type'    => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => _('Загружено ') . $resumeCounter . _(' резюме')
        ));
        
        $this->_redirector->gotoSimple('index', 'list', 'candidate', array(), null, true);
    }

    public function loadNewResumesByAction(){

        $gridId        = 'grid';
        $postMassField = 'postMassIds_' . $gridId;
        $postMassIds   = $this->_getParam($postMassField, '');
        $postMassIds = $postMassIds ? $postMassIds : $this->_getParam('vacancy_id', ''); //mass или инди
        $vacancies   = explode(',', $postMassIds);

        if (count($vacancies)) {
            $vacancies = $this->getService('RecruitVacancy')->fetchAllManyToMany('Recruiter', 'RecruiterAssign', array(
                '(hh_vacancy_id > 0 OR superjob_vacancy_id > 0) AND vacancy_id IN (?)' => $vacancies,
                'status IN (?)' => array(
                    HM_Recruit_Vacancy_VacancyModel::STATE_PENDING,
                    HM_Recruit_Vacancy_VacancyModel::STATE_ACTUAL,
                ),
            ));

            if (!count($vacancies)) {
                $this->_flashMessenger->addMessage(array(
                    'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Вакансии не опубликованы на внешних ресурсах либо подбор по ним завершен')
                ));
                $this->_redirector->gotoSimple('index', 'list', 'vacancy', array(), null, true);
            }

            $recruiterVacancies = array();
            foreach ($vacancies as $vacancy) {
                if (count($vacancy->recruiters)) {
                    $recruiterIds = $vacancy->recruiters->getList('user_id');
                    if (in_array($this->getService('User')->getCurrentUserId(), $recruiterIds)) {
                        $recruiterVacancies[] = $vacancy;
                    }
                }
            }

            if (!count($recruiterVacancies)) {
                $this->_flashMessenger->addMessage(array(
                    'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Вакансии назначены другим специалистам по подбору')
                ));
                $this->_redirector->gotoSimple('index', 'list', 'vacancy', array(), null, true);
            }

            $resumeCounter = $this->_refreshResumesByVacancies($recruiterVacancies);

            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Загружено ') . $resumeCounter . _(' резюме')
            ));
        }

        if (count($postMassIds) > 1) {
            $this->_redirector->gotoSimple('index', 'list', 'vacancy', array(), null, true);
        } else {
            $this->_redirector->gotoSimple('index', 'assign', 'candidate', array('vacancy_id' => $postMassIds), null, true);
        }
    }


    public function _refreshResumesByVacancies($vacancies) {
        
        $this->initHH();
        $this->initSuperjob();

        /** @var  $hhService HM_Recruit_RecruitingServices_Rest_Hh */
        $hhService        = $this->hh;
        /** @var  $superjobService HM_Recruit_RecruitingServices_Rest_Superjob */
        $superjobService  = $this->superjob;
//        $candidateService = $this->getService('RecruitCandidate');
        $vacancyAssignService = $this->getService('RecruitVacancyAssign');
        
        
        $assignedCandidatesHh = $vacancyAssignService->fetchAllDependenceJoinInner('Candidate',
            $vacancyAssignService->quoteInto('source = ?', HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER)
        );
        $assignedCandidatesHhArray = array();
        foreach($assignedCandidatesHh as $assignedCandidateHh){
            $vacancyId = $assignedCandidateHh->vacancy_id;
            $resumeExternalId = $assignedCandidateHh->candidates[0]->resume_external_id;
            if(!$resumeExternalId){
                continue;
            }
            if(!is_array($assignedCandidatesHhArray[$vacancyId])){
                $assignedCandidatesHhArray[$vacancyId] = array();
            }
            $assignedCandidatesHhArray[$vacancyId][$resumeExternalId] = $resumeExternalId;
        }
        
        $assignedCandidatesSj = $vacancyAssignService->fetchAllDependenceJoinInner('Candidate',
            $vacancyAssignService->quoteInto('source = ?', HM_Recruit_Provider_ProviderModel::ID_SUPERJOB)
        );
        $assignedCandidatesSjArray = array();
        foreach($assignedCandidatesSj as $assignedCandidateSj){
            $vacancyId = $assignedCandidateSj->vacancy_id;
            $resumeExternalId = $assignedCandidateSj->candidates[0]->resume_external_id;
            if(!$resumeExternalId){
                continue;
            }
            if(!is_array($assignedCandidatesSjArray[$vacancyId])){
                $assignedCandidatesSjArray[$vacancyId] = array();
            }
            $assignedCandidatesSjArray[$vacancyId][$resumeExternalId] = $resumeExternalId;
        }
        

        $status = HM_Recruit_Vacancy_Assign_AssignModel::STATUS_HOLD_ON;
        $resumeCounter = 0;
        foreach($vacancies as $vacancy){
            if($vacancy->superjob_vacancy_id){
                if ($responsesSJ = $superjobService ? $superjobService->getVacancyResponse($vacancy) : false) {
                    foreach ($responsesSJ->objects as $resp) {
                        if($resp->resume->id && !isset($assignedCandidatesSjArray[$vacancy->vacancy_id][$resp->resume->id])){
                            $this->_assignSJ($resp->resume->id, $vacancy->vacancy_id, $status);
                            $resumeCounter++;
                        }
                    }
                }
            }
            
            if($vacancy->hh_vacancy_id){
                if ($responsesHH = $hhService ? $hhService->getVacancyResponse($vacancy) : false) {
                    foreach ($responsesHH->items as $resp) {
                        if($resp->resume->id && !isset($assignedCandidatesHhArray[$vacancy->vacancy_id][$resp->resume->id])){

                            if (!empty($resp->resume)) {
                                $this->_assignHH($resp->id, $vacancy->vacancy_id, $status, $resp->resume);
                            } else {
                                $this->_fetchResumeAndAssignHH($resp->id, $vacancy->vacancy_id, $status);
                            }

                            $resumeCounter++;
                        }
                    }
                }
            }
        }
        
        return $resumeCounter;
    }

    public function updateDate($date) {
        if(!$date) return false;
        $date = date('d.m.Y', strtotime($date));
        return $date;
    }

    public function updateComments($comments)
    {
        $comments = explode('@@@', $comments);
        unset($comments[0]);
        foreach($comments as &$comment) {
            if(substr($comment, -1, 1)==',') {
                $comment = substr($comment, 0, -1);
            }
        }
        if(!count($comments))
            return '';

        $result =  array();
        foreach ($comments as $key => $comment) {
            $result[] = "<p class='smaller'>{$comment}</p>";
        }
        if (count($result) > 1) {
            array_unshift($result, '<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Comment')->pluralFormCount(count($result)) . '</p>');
        }

        return implode('',$result);
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
