<?php
use HM_At_Evaluation_EvaluationModel as EvaluationModel;

class HM_At_Session_SessionService extends HM_Service_Abstract
{
    const MSG_NO_ANY_POSITIONS = 0;
    const MSG_NO_ANY_PROFILES = 1;
    const MSG_VACANCT_POSITION = 2;
    const MSG_INVALID_PROFILE = 3;
    const MSG_INVALID_EVALUATION = 4;
    const MSG_INVALID_POSITION_DATE = 5;
    const MSG_MAIN_WORK_PLACE = 7;
    
    private $_positionsCache = array();
    private $_restrictions = array();

    private $_absenceCache = null;
    protected $usersCache = array();
    protected $sessionCache = array();
    protected $profileCache = array();
    protected $processCache = array();
    protected $sessionEventCache = array();
    protected $sessionRespondentsCache = array();
    protected $sessionMultiUserEventCache = array();
    protected $programmEventsCache = array();
    protected $programmEventUsersCache = array();
    protected $vacancyEvaluationsCache = null;
    protected $adaptingEvaluationsCache = null;
    protected $reserveEvaluationsCache = null;
    protected $profilesEvaluationsCache = null;

    /**
     * Создание оценочной сессии
     * @param $data
     * @param bool $onlySession
     * @return HM_Model_Abstract
     */
    public function insert($data, $onlySession = false)
    {
        if (isset($data['checked_items']) && is_array($data['checked_items'])) {
            // start of #29881
            if (count($data['checked_items']) > 1) array_shift($data['checked_items']);
            // end of #29881
            $data['checked_items'] = implode(',',$data['checked_items']);
        }

        switch ($data['item_type']) {
            case 'criteria-test':
                $this->_restrictions = array(
                    'method' => HM_At_Evaluation_EvaluationModel::TYPE_TEST,
                );
                $data['checked_soids'] = $this->_getSoidsByCriteria($data['checked_items'], HM_At_Evaluation_EvaluationModel::TYPE_TEST);
            break;
            default:
                $data['checked_soids'] = (string) $data['checked_items'];
            break;
        }
        unset($data['checked_items']);
        unset($data['item_type']);
        
        $session = parent::insert($data);
        $soids = explode(',', $data['checked_soids']);

        if (!$onlySession && count($descendants = $this->getService('Orgstructure')->getDescendansForMultipleSoids($soids))) {
            
            
            // для сессий подбора есть отдельный процесс, он стартует в VacancyService 
            // здесь задаются все параметры, которые можно будет использовать внутри Action 
            // почему-то надо задавать отдельно для каждого шага..
            $this->getService('Process')->startProcess($session, array(
                'HM_At_Session_State_Open' => array(
                    'session_id' => $session->session_id,
                ),
                'HM_At_Session_State_Publish' => array(
                    'session_id' => $session->session_id,
                ),
                'HM_At_Session_State_Closed' => array(
                    'session_id' => $session->session_id,
                ),                    
            ));

            $messages = array();
            $this->addSoids($session->session_id, $descendants, $messages);
            $this->_setEventProgrammDates();
        }

        return $session;
    }

    /**
     * Множественное добавление пользователей в оценочную сессию по их позициям в оргструктуре
     * 
     * @param int $session_id - идентификатор сессии
     * @param array $soids - позиции в оргструктуре
     * @param array $messages - указатель на массив ошибок 
     */
    public function addSoids($session_id, $soids, &$messages)
    {
        $profileIds = array();
        if (count($soids)) {
            $this->getSession($session_id);            
            $positions = $this->getService('Orgstructure')->fetchAllDependenceJoinInner('Employee', $this->quoteInto('soid IN (?)', $soids));
            $positions = $this->_checkPositions($positions, false, $session_id);
            
            $newSessionUsersData = array();
            $profileIds = array();
            $mids = array();

            foreach ($positions as $position) {

                $this->addPositionToCache($position);

                if (is_a($position, 'HM_Orgstructure_Unit_UnitModel')) continue;

                if ($positionMessage = $this->_checkPosition($position, false, $session_id)) {
                    $messages[$positionMessage] = $position;
                    continue; // исключить из участников 
                }

                // кэшируем необходимые данные по пользователям
                $newSessionUsersData[] = array(
                    'at_session_id' => $session_id,
                    'mid' => $position->mid,
                    'soid' => $position->soid,
                    'profile_id' => $position->profile_id
                );
                $mids[$position->mid] = $position->mid;
                $profileIds[$position->profile_id] = $position->profile_id;

            }

            // грузим кэш необходимых профилей должности, чтоб не грузить их многократно
            $this->loadProfilesCache($profileIds);
            $this->loadProgrammEventUsersCache(HM_Programm_ProgrammModel::TYPE_ASSESSMENT, $profileIds);
            $this->loadUsersCache($mids);

            // создаём динамический процесс; в addUser() он стартует
            $this->updateUserProcesses($session_id);
            
            // добавляем пользователей в оценочную сессию
            foreach ($newSessionUsersData as $item) {
                
                $processId = false;
                if ($process = $this->getProcess($item['profile_id'])) {
                    $processId = $process->process_id;
                } 
                
                $sessionUser = $this->addUser($item['at_session_id'], $item['mid'], $item['soid'], $item['profile_id'], $processId, null, null, null, $messages);
                
                if ($process) {
                    $sessionUser->process = $process;
                    if (isset($this->sessionEventCache[$sessionUser->user_id]) && count($programmEventIds = array_keys($this->sessionEventCache[$sessionUser->user_id]))) {
                        $process->addStateSameParam($programmEventIds, $sessionUser->session_id, 'session_id');
                        $process->addStateSameParam($programmEventIds, $sessionUser->session_user_id, 'session_user_id');
                        $process->addStateParams($this->sessionEventCache[$sessionUser->user_id], 'session_events');
                    }
                    $this->getService('Process')->startProcess($sessionUser, $process->getStateParams()); // старт процесса должен быть после создания even'ов
                }
            }
            
            $this->commitSessionMultiUserEventTransaction();
            
        } else {
            $messages[self::MSG_NO_ANY_POSITIONS] = true;
        }
    }
    
    public function addUserFromVacancy($vacancyCandidate, $process)
    {
        $messages = array();
        if ($process && count($vacancyCandidate->vacancies) && count($vacancyCandidate->candidates)) {
            $vacancy = $vacancyCandidate->vacancies->current();
            $candidate = $vacancyCandidate->candidates->current();
            
            $this->loadProgrammEventUsersCache(HM_Programm_ProgrammModel::TYPE_RECRUIT, array($vacancy->vacancy_id));
            $this->loadRespondentCache($vacancy->session_id);

            $position = $this->getPosition($vacancy->position_id, $vacancy->vacancy_id);
            $sessionUser = $this->addUser($vacancy->session_id, $candidate->user_id, $vacancy->position_id, $vacancy->profile_id, $process->process_id, $vacancyCandidate->vacancy_candidate_id, null, null, $messages);
            
            if ($process) {
                
                if (isset($this->sessionEventCache[$candidate->user_id]) && count($programmEventIds = array_keys($this->sessionEventCache[$candidate->user_id]))) {
                
                    $process->addStateSameParam($programmEventIds, $vacancy->vacancy_id, 'vacancy_id');
                    $process->addStateSameParam($programmEventIds, $vacancyCandidate->vacancy_candidate_id, 'vacancy_candidate_id');
                    $process->addStateParams($this->sessionEventCache[$candidate->user_id], 'session_events');
                }
                $this->getService('Process')->startProcess($vacancyCandidate, $process->getStateParams());
            }
        }
    }

    public function addUserFromAdapting($newcomer)
    {
        $messages = array();
        if ($newcomer) {
            
            $this->loadProgrammEventUsersCache(HM_Programm_ProgrammModel::TYPE_ADAPTING, array($newcomer->newcomer_id));
            $this->loadRespondentCache($newcomer->session_id);

            $this->getPosition($newcomer->position_id, false, $newcomer->newcomer_id);
            $this->addUser(
                $newcomer->session_id,
                $newcomer->user_id,
                $newcomer->position_id,
                $newcomer->profile_id,
                $newcomer->getProcess() ? $newcomer->getProcess()->process_id : -1,
                null,
                $newcomer->newcomer_id,
                null,
                $messages
            );
        }
    }

    public function addUserFromReserve($reserve)
    {
        $messages = array();
        if ($reserve) {

            $this->loadProgrammEventUsersCache(HM_Programm_ProgrammModel::TYPE_RESERVE, array($reserve->reserve_id));
            $this->loadRespondentCache($reserve->session_id);

            $position = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->fetchAll(array('mid = ?' => $reserve->user_id)));

            $this->addUser(
                $reserve->session_id,
                $reserve->user_id,
                $position ? $position->soid : false,
                $reserve->profile_id,
                $reserve->getProcess() ? $reserve->getProcess()->process_id : -1,
                null,
                null,
                $reserve->reserve_id,
                $messages
            );
        }
    }

    /**
     * Добавление пользователя в оценочную сессию
     *
     * @todo: РЕФАКТОРИТЬ!
     * 
     * @param int $session_id  - идентификатор оценочной сессии
     * @param int $mid         - идентификатор пользователя, добавляемого в оценочную сессию
     * @param int $position_id - позиция в оргструктуре, которую занимает (или на которую претендует) пользователь
     * @param int $profile_id  - профиль должности, по которому будет оцениваться пользователь
     * @param array $messages  - указатель на массив, в который будут передаваться различные ошибки
     */
    public function addUser($session_id, $mid, $position_id, $profile_id, $process_id, $vacany_candidate_id = null, $newcomer_id = null, $reserve_id = null, &$messages)
    {
        $sessionUser = $this->getService('AtSessionUser')->insert(array(
            'user_id'     => $mid,
            'position_id' => $position_id,
            'session_id'  => $session_id,
            'profile_id'  => $profile_id,
            'process_id'  => $process_id,
            'vacancy_candidate_id'  => $vacany_candidate_id,
            'newcomer_id'  => $newcomer_id,
            'reserve_id'  => $reserve_id,
            'status'      => HM_At_Session_User_UserModel::STATUS_NOT_STARTED,
        ));

        $this->assignSessionEvents($session_id, $sessionUser, $position_id, $profile_id, $messages);

        return $sessionUser;
    }

    protected function assignSessionEvents($session_id, $sessionUser, $position_id, $profile_id, &$messages)
    {
        $profile  = $this->getProfile($profile_id);
        $position = $this->getPosition($position_id);
        $session  = $this->getSession($session_id);
        $mid      = $sessionUser->user_id;
        $user     = $this->getUser($mid);
        
        switch ($session->programm_type) {
            case HM_Programm_ProgrammModel::TYPE_RECRUIT:
                $evaluationContainer = $this->getVacancyEvaluations($session_id);
                break;
            case HM_Programm_ProgrammModel::TYPE_ADAPTING:
                $evaluationContainer = $this->getAdaptingEvaluations($session_id);
                break;
            case HM_Programm_ProgrammModel::TYPE_RESERVE:
                $evaluationContainer = $this->getReserveEvaluations($session_id);
                break;
            default:
                $evaluationContainer = $this->getProfileEvaluations($profile);
        }

        // устанавливаем даты event'ов на весь срок сессии
        // для календаря будем использовать state_of_process_data!
        $beginEventDate = new HM_Date($this->_session->begin_date);
        $endEventDate = new HM_Date($this->_session->end_date);

        if (count($evaluationContainer)) {
            
            foreach ($evaluationContainer as $evaluation) {
                
                // при создании сессии оценки не создаём мероприятия по подбору и т.д.
                if ($evaluation->programm_type != $session->programm_type) continue;

// не стоит пропускать создание event'ов, даже если они по каким-то причинам не могут состояться; 
//                 if (!$evaluation->isValid($mid, $session->cycle_id)) {
//                     $messages[self::MSG_INVALID_EVALUATION][] = $position;
//                     continue;
//                 }
    
                if (isset($this->_restrictions['method']) && ($this->_restrictions['method'] != $evaluation->method)) continue;
                
                $eventDefaults = $evaluation->getDefaults($user);
                
                $programmEvent = $this->getProgrammEvent($evaluation->evaluation_type_id);
                $programmEventUserId = $this->getProgrammEventUserId($sessionUser->user_id, $evaluation->evaluation_type_id);
                
//                $beginEventDate = clone $beginDate;
//                $endEventDate = clone $beginDate;
//                if ($programmEvent->day_begin) {
//                    $beginEventDate = HM_Date::getRelativeDate($beginEventDate, $programmEvent->day_begin);
//                }
//                if ($programmEvent->day_end) {
//                    $endEventDate = HM_Date::getRelativeDate($endEventDate, $programmEvent->day_end);
//                }

                $respondents = $evaluation->getRespondents($position, $user);
                $respondentsCustom = array();
                if ($evaluation->isAllowCustomRespondent()) {

                    $respondentsCustom = $evaluation->getRespondentsCustom($position);
                    if (!empty($respondentsCustom)) {
                        $respondents = $respondentsCustom;
                    }
                }

                //$respondents = $this->_checkPositions($respondents, true, $session_id);

                if (count($respondents)) {
    
                    if (!count($respondentsCustom) && ($num = $this->getService('Option')->getOption('competenceRandom' . $evaluation->relation_type))) {
                        $respondents = $this->_randomize($num, $respondents);
                    }
                    
                    foreach ($respondents as $respondent) {
    
                        if (!isset($this->sessionRespondentsCache[$session_id][$respondent->mid])) {
                            $sessionRespondent = $this->getService('AtSessionRespondent')->insert(array(
                                'user_id' => $respondent->mid,
                                'position_id' => $respondent->soid,
                                'session_id' => $session_id
                            ));
                            $this->sessionRespondentsCache[$session_id][$respondent->mid] = $sessionRespondent;
                        } else {
                            $sessionRespondent = $this->sessionRespondentsCache[$session_id][$respondent->mid];
                        }

                        $sessionEventData = array(
                            'session_id'            => $session->session_id,
                            'position_id'           => $position->soid,
                            'evaluation_id'         => $evaluation->evaluation_type_id,
                            'respondent_id'         => $respondent->mid,
                            'session_user_id'       => $sessionUser->session_user_id,
                            'user_id'               => $sessionUser->user_id,
                            'session_respondent_id' => $sessionRespondent->session_respondent_id,
                            'method'                => $evaluation->method,
                            'name'                  => $eventDefaults['name'],
                            'programm_event_user_id'=> $programmEventUserId,
                            'date_begin'            => $beginEventDate->get('Y-M-d'),
                            'date_end'              => $endEventDate->get('Y-M-d'),
                            'is_empty_quest'        => $evaluation->is_empty_quest
                        );
                        
                        $evaluationEvents = array();
                        if ($evaluation->isMultiUserEvents()) { // например, парные сравнения
                            
                            unset($sessionEventData['user_id']);
                            $this->addMultiUserEventToCache($sessionEventData, $user);
                            
                        } elseif ($evaluation->isMultiEventEvaluation()) { 
                            // это все методы на основе quest; например, тестирование: одни evaluation, а тестов много; здесь же произвольные и итоговые формы, хотя там всегда одна форма;
                            // оценка по компетенциям - нельзя трактовать как MultiEventEvaluation, т.к. там все разные респонденты и надо сильно рефакторить  
                            foreach ($evaluation->getMultiEventData() as $multiEventData) {
                                $sessionEventData = array_merge($sessionEventData, $multiEventData);
                                $sessionEvent = $this->getService('AtSessionEvent')->insert($sessionEventData);
                                $evaluationEvents[$sessionEvent->session_event_id] = $sessionEvent->getData();
                            }
                            
                        } else {
                            $sessionEvent = $this->getService('AtSessionEvent')->insert($sessionEventData);
                            $evaluationEvents[$sessionEvent->session_event_id] = $sessionEvent->getData();
                        }

                        // даже если нет evaluationEvent'а - нужно сохранить programmEventId, понадобится дальше
                        if (!isset($this->sessionEventCache[$sessionEvent->user_id][$programmEvent->programm_event_id])) {
                            $this->sessionEventCache[$sessionEvent->user_id][$programmEvent->programm_event_id] = array();
                        }
                        if (count($evaluationEvents)) {
                            $this->sessionEventCache[$sessionEvent->user_id][$programmEvent->programm_event_id] = $this->sessionEventCache[$sessionEvent->user_id][$programmEvent->programm_event_id] + $evaluationEvents; 
                        }
                    }
                }
                if (!isset($this->sessionEventCache[$sessionUser->user_id][$programmEvent->programm_event_id])) {
                    $this->sessionEventCache[$sessionUser->user_id][$programmEvent->programm_event_id] = array();
                }
            }
        }
    }

    /**
     * Для таких метод, где event создается один на весь профиль
     * Логика назначения вынесена в прикладной класс методы (может быть разная для разных метод).
     * Пример: парные сравнения
     */
    protected function commitSessionMultiUserEventTransaction()
    {
       foreach ($this->sessionMultiUserEventCache as $evaluationId => $respondent) {
           
           $evaluation = $this->getService('AtEvaluation')->find($evaluationId)->current();
           foreach ($respondent as $respondentId => $respondent) {
               
                $userNames = array();
                $sessionEvent = $this->getService('AtSessionEvent')->insert($respondent['event']);
               
                //  пока непонятно как такие event'ы участвуют в процессах
                //$this->sessionEventCache[$sessionEvent->user_id][$programmEvent->programm_event_id][$sessionEvent->session_event_id] = $sessionEvent->getData();
               
                $evaluation->insertMultiUserEvent($sessionEvent, $respondent['users']);                   
                foreach ($respondent['users'] as $user) {
                    $userNames[] = $user->LastName;
                }
                $respondent['event']['name'] = implode(' - ', $userNames);
            }
        }
    }
    
    protected function addMultiUserEventToCache($event, $userId)
    {
        if (!isset($this->sessionMultiUserEventCache[$event['evaluation_id']])) {
            $this->sessionMultiUserEventCache[$event['evaluation_id']] = array();
        }
        if (!isset($this->sessionMultiUserEventCache[$event['evaluation_id']][$event['respondent_id']])) {
            $this->sessionMultiUserEventCache[$event['evaluation_id']][$event['respondent_id']] = array(
                'event' => $event,
                'users' => array($userId)
            );
        } else {
            $this->sessionMultiUserEventCache[$event['evaluation_id']][$event['respondent_id']]['users'][] = $userId;
        }
    }
    
    protected function addPositionToCache($position)
    {
        $this->_positionsCache[$position->soid] = $position;
    }
    
    protected function getSession($session_id)
    {
        if (!isset($this->sessionCache[$session_id])) { 
            $sessions = $this->fetchAllDependence('Vacancy', $this->quoteInto('session_id = ?', $session_id));
            if (count($sessions)) {
                $this->sessionCache[$session_id] = $sessions->current();
            } else {
                $this->sessionCache[$session_id] = false;
            }
        }
        return $this->sessionCache[$session_id];
    }
    
    protected function getPosition($position_id, $vacancy_id = false, $newcomer_id = false, $reserve_id = false)
    {
        if (!isset($this->_positionsCache[$position_id])) {
            $positions = $this->getService('Orgstructure')->fetchAllDependence(array('Vacancy', 'Newcomer', 'Reserve'), $this->quoteInto('soid = ?', $position_id));
            if (count($positions)) {
                $position = $positions->current();
                if ($vacancy_id && count($position->vacancy)) {
                    $vacancies = $position->vacancy->asArrayOfArrays();
                    if (isset($vacancies[$vacancy_id])) {
                        $position->vacancy = new HM_Collection(array(), 'HM_Recruit_Vacancy_VacancyModel');
                        $position->vacancy->offsetSet(0, $vacancies[$vacancy_id]);
                    }
                } 
                if ($newcomer_id && count($position->newcomer)) {
                    $newcomers = $position->newcomer->asArrayOfArrays();
                    if (isset($newcomers[$newcomer_id])) {
                        $position->newcomer = new HM_Collection(array(), 'HM_Recruit_Newcomer_NewcomerModel');
                        $position->newcomer->offsetSet(0, $newcomers[$newcomer_id]);
                    }
                } 
                if ($reserve_id && count($position->reserve)) {
                    $reserves = $position->reserve->asArrayOfArrays();
                    if (isset($reserves[$reserve_id])) {
                        $position->reserve = new HM_Collection(array(), 'HM_Hr_Reserve_ReserveModel');
                        $position->reserve->offsetSet(0, $reserves[$reserve_id]);
                    }
                }
                $this->_positionsCache[$position_id] = $position;
            } else {
                $this->_positionsCache[$position_id] = false;
            }
        }
        return $this->_positionsCache[$position_id];
    }

    protected function loadUsersCache($mids = array())
    {
        if (empty($mids)) {
            return;
        }

        $users = $this->getService('User')->fetchAll($this->quoteInto('MID IN (?)', $mids));

        foreach ($users as $user) {
            $this->usersCache[$user->MID] = $user;
        }
    }

    protected function getUser($mid)
    {
        if (!isset($this->usersCache[$mid])) {
            $users = $this->getService('User')->fetchAll($this->quoteInto('MID = ?', $mid));
            if (!count($users)) {
                $this->usersCache[$mid] = false;
            } else {
                $this->usersCache[$mid] = $users->current();
            }
        }
        return $this->usersCache[$mid];
    }
    
    protected function loadProfilesCache($profileIds = array())
    {
        if (empty($profileIds)) {
            return;
        }

        $profiles = $this->getService('AtProfile')->fetchAllDependence(array('Evaluation'), $this->quoteInto('profile_id IN (?)', $profileIds));
        
        foreach ($profiles as $profile) {
            $this->profileCache[$profile->profile_id] = $profile;
        }
        
    }
    
    protected function getProfile($profile_id)
    {
        if (!isset($this->profileCache[$profile_id])) {
            $profiles = $this->getService('AtProfile')->fetchAllDependence(array('Evaluation'), $this->quoteInto('profile_id = ?', $profile_id));
            if (!count($profiles)) {
                $this->profileCache[$profile_id] = false;
            } else {
                $this->profileCache[$profile_id] = $profiles->current();
            } 
        }
        return $this->profileCache[$profile_id];
    }

    public function getProfileEvaluations($profile)
    {
        if (!isset($this->profileEvaluationsCache[$profile->profile_id])) {
            if (count($profile->evaluations)) {
                $this->profileEvaluationsCache[$profile->profile_id] = $this->initEvaluations($profile->evaluations);
            }
        }
        return $this->profileEvaluationsCache[$profile->profile_id];
    }
    
    public function getVacancyEvaluations($session_id)
    {
        if (!isset($this->vacancyEvaluationsCache)) {
            $evaluations = array();
            $session = $this->getService('AtSession')->findManyToMany('Evaluation', 'Vacancy', $session_id);
            if (count($session)) {
                if (count($session->current()->evaluations)) {
                    $this->vacancyEvaluationsCache = $this->initEvaluations($session->current()->evaluations);
                }
            }  
        }
        return $this->vacancyEvaluationsCache;
    }

    public function getAdaptingEvaluations($session_id)
    {
        if (!isset($this->adaptingEvaluationsCache)) {
            $evaluations = array();
            $session = $this->getService('AtSession')->findManyToMany('Evaluation', 'Newcomer', $session_id);
            if (count($session)) {
                if (count($session->current()->evaluations)) {
                    $this->adaptingEvaluationsCache = $this->initEvaluations($session->current()->evaluations);
                }
            }  
        }
        return $this->adaptingEvaluationsCache;
    }

    public function getReserveEvaluations($session_id)
    {
        if (!isset($this->reserveEvaluationsCache)) {
            $evaluations = array();
            $session = $this->getService('AtSession')->findManyToMany('Evaluation', 'Reserve', $session_id);
            if (count($session)) {
                if (count($session->current()->evaluations)) {
                    $this->reserveEvaluationsCache = $this->initEvaluations($session->current()->evaluations);
                }
            }
        }
        return $this->reserveEvaluationsCache;
    }

    protected function initEvaluations($evaluations)
    {
        $return = array();
        foreach($evaluations as $evaluation) {
            switch ($evaluation->method) {
                case HM_At_Evaluation_EvaluationModel::TYPE_TEST:
                    $evaluation = $this->getService('AtEvaluation')->findMultiDependence(array(
                        'criteriaTest'    => array('CriterionTest', 'EvaluationCriterion'),
                        'quest'           => 'Quest',
                    ), $evaluation->evaluation_type_id);
                    if (count($evaluation)) $return[] = $evaluation->current();
                    break;
                case HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO:
                    $evaluation = $this->getService('AtEvaluation')->findMultiDependence(array(
                        'criteriaPersonal'    => array('CriterionPersonal', 'EvaluationCriterion'),
                        'quest'               => 'Quest',
                    ), $evaluation->evaluation_type_id);
                    if (count($evaluation)) $return[] = $evaluation->current();
                    break;
                case HM_At_Evaluation_EvaluationModel::TYPE_FORM:
                    list(,$questId) = explode('_', $evaluation->submethod);
//                    $quest = $this->getService('Quest')->find($questId);
                    $quest = $this->getService('Quest')->findManyToMany('Question', 'QuestionQuest', $questId);
                    $questions = $quest->getDependences();
                    $questionsCount = count($questions['quest_id'][$questId]['questions']);
                    if ($questId) {
                        $evaluation->quest = $quest;
                        $evaluation->is_empty_quest = $questionsCount ? 0 : 1;
                    }
                    $return[] = $evaluation;
                    break;
                case HM_At_Evaluation_EvaluationModel::TYPE_FINALIZE:
                    $evaluation->quest = $this->getService('Quest')->find($evaluation->getQuestId());
                    $return[] = $evaluation;
                    break;
                    
                default:
                    $return[] = $evaluation;
                    break;
            }
        }
        return $return;
    }
    
    protected function loadProgrammEventUsersCache($programmType, $evaluationContainerIds = array())
    {
        if (empty($evaluationContainerIds)) {
            return;
        }

        // ВНИМАНИЕ!
        // для TYPE_ADAPTING и TYPE_RESERVE программа фактически не настраивается на уровне сессии
        // но сделано для единообразия с TYPE_RECRUIT и возможно на будущее (если будет настраиваться)

        if ($programmType == HM_Programm_ProgrammModel::TYPE_ASSESSMENT) {
            $programms = $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE, $evaluationContainerIds, HM_Programm_ProgrammModel::TYPE_ASSESSMENT);
        } elseif ($programmType == HM_Programm_ProgrammModel::TYPE_RECRUIT) {
            $programms = $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY, $evaluationContainerIds, HM_Programm_ProgrammModel::TYPE_RECRUIT);
        } elseif ($programmType == HM_Programm_ProgrammModel::TYPE_ADAPTING) {
            $programms = $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_NEWCOMER, $evaluationContainerIds, HM_Programm_ProgrammModel::TYPE_ADAPTING);
        } elseif ($programmType == HM_Programm_ProgrammModel::TYPE_RESERVE) {
            $programms = $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_RESERVE, $evaluationContainerIds, HM_Programm_ProgrammModel::TYPE_RESERVE);
        }
        foreach ($programms as $programm) {
            if (count($programm->events)) {
                $events = $programm->events->getList('programm_event_id', 'item_id'); // item_id == evaluation_type_id
                foreach ($programm->eventUsers as $eventUser) {
                    $this->programmEventUsersCache[$eventUser->user_id][$events[$eventUser->programm_event_id]] = $eventUser->programm_event_user_id;
                }
                foreach ($programm->events as $event) {
                    $this->programmEventsCache[$events[$event->programm_event_id]] = $event;
                }
            }
        }
    }

    public function getProgrammEventUserId($userId, $evaluationId)
    {
        return $this->programmEventUsersCache[$userId][$evaluationId];    
    }
    
    public function getProgrammEvent($evaluationId)
    {
        return $this->programmEventsCache[$evaluationId];    
    }
    
    protected function loadRespondentCache($at_session_id)
    {
        $respondents = $this->getService('AtSessionRespondent')->fetchAll($this->quoteInto(array('session_id = ?'), array($at_session_id)));
        
        $cache = &$this->sessionRespondentsCache[$at_session_id];
        
        foreach ($respondents as $respondent) {
            if (!empty($cache[$respondent->user_id])) {
                continue;
            }
            $cache[$respondent->user_id] = $respondent;
        }
    }


    private function _setEventProgrammDates()
    {
    }

    private function _randomize($num, $respondents)
    {
        // оптимизировать
        if ($num && ($num < count($respondents))) {
            $respondentsArr = array();
            foreach ($respondents as $respondent) {
                if (!empty($respondent->mid)) $respondentsArr[] = $respondent;
            }
            $random = array_rand($respondentsArr, $num);
            if($num==1) {
                $random = array($random);
            }
            foreach ($random as $key) {
                $return[] = $respondentsArr[$key];
            }
            return $return;
        }
        return $respondents;
    }

    public function delete($sessionId)
    {
        if (!$sessionId) return true;
        
        if (count($events = $this->getService('AtSessionEvent')->fetchAll(array('session_id = ?' => $sessionId)))) {
            $eventIds = $events->getList('session_event_id');
            $this->getService('AtSessionPair')->deleteBy(array('session_event_id IN (?)' => $eventIds));
        }
        
        if (count($sessionUsers = $this->getService('AtSessionUser')->fetchAll(array('session_id = ?' => $sessionId)))) {
            $sessionUserIds = $sessionUsers->getList('session_user_id');
            $this->getService('State')->deleteBy(array('item_id IN (?)' => $sessionUserIds, 'process_type IN (?)' => array(HM_Process_ProcessModel::PROCESS_PROGRAMM_ASSESSMENT, HM_Process_ProcessModel::PROCESS_PROGRAMM_RECRUIT)));
        }
        
        $this->getService('AtSessionEvent')->deleteBy(array('session_id = ?' => $sessionId));
        $this->getService('AtSessionUser')->deleteBy(array('session_id = ?' => $sessionId));
        $this->getService('AtSessionRespondent')->deleteBy(array('session_id = ?' => $sessionId));
        
        $this->getService('State')->deleteBy(array('item_id = ?' => $sessionId, 'process_type = ?' => HM_Process_ProcessModel::PROCESS_SESSION));
        
        return parent::delete($sessionId);
    }

    public function startSession($sessionId)
    {
        if ($session = $this->getOne($this->findDependence('SessionRespondent', $sessionId))) {

            $session->state = HM_At_Session_SessionModel::STATE_ACTUAL; 
            $this->update($session->getValues());
            
            $this->getService('Process')->goToNextState($session);

            // шлём эти 4 уведомления только в рег.оценке
            // в КР эти мероприятия случатся только через год - нет смысла
            if (count($session->respondents) && ($session->programm_type == HM_Programm_ProgrammModel::TYPE_ASSESSMENT)) {
                $this->sendMailOnStartSession($session);
            }
        }
        return $session;
    }
    
    public function sendMailOnStartSession($session)
    {
        $atSessionEventService = $this->getService('AtSessionEvent');
        $atEvaluationService   = $this->getService('AtEvaluation');
        
        $events = $atSessionEventService->fetchAll($atSessionEventService->quoteInto(
            array(
                'session_id = ?',
                ' AND session_respondent_id IN (?)'
            ),
            array(
                $session->session_id,
                $session->respondents->getList('session_respondent_id')
            )
        ))->getList('session_respondent_id', 'evaluation_id');
        
        $evaluationTypes = $atEvaluationService->fetchAll($atEvaluationService->quoteInto(
            array(
                'evaluation_type_id IN (?)'
            ),
            array(
                $events
            )
        ))->getList('evaluation_type_id', 'relation_type');
        
        $respondentsByType = array();
        foreach($events as $respondentId => $evaluationId){
            if(!is_array($respondentsByType[$respondentId])){
                $respondentsByType[$respondentId] = array();
            }
            $respondentsByType[$respondentId][] = $evaluationTypes[$evaluationId];
        }
        
        $this->sendMailByType($respondentsByType, $session);
    }


    private function sendMailByType($respondentsByType, $session)
    {
        $sessionBegin = new HM_Date($session->begin_date);
        $sessionEnd = new HM_Date($session->end_date);
//        $sessionBegin = $sessionBegin->toString(HM_Date::SQL_DATE);
//        $sessionEnd = $sessionEnd->toString(HM_Date::SQL_DATE);
        $sessionBegin = $sessionBegin->toString('dd.MM.y');
        $sessionEnd = $sessionEnd->toString('dd.MM.y');
        $sessionId = $session->session_id;
        $url = Zend_Registry::get('view')->serverUrl('/');
        $sessionUrl = '<a href="'.$url.'at/session/event/my/session_id/' .  $sessionId . '" target="_blank">' . $session->name . '</a>';

//        foreach($session->respondents as $respondent) {
//            foreach($respondentsByType[$respondent->session_respondent_id] as $evaluationType) {
//                $template = $this->_getTemplateType($evaluationType);
//                if($template){
//                    $messenger = $this->getService('Messenger');
//                    $messenger->setOptions(
//                        $template,
//                        array(
//                            'session_id'    => $sessionId,
//                            'url_session' => $sessionUrl,
//                            'session_begin' => $sessionBegin,
//                            'session_end'   => $sessionEnd,
//                            'contacts'      => $this->getManagerContacts($session->initiator_id),
//                        ),
//                        'session',
//                        $sessionId
//                    );
//                    $messenger->send(HM_Messenger::SYSTEM_USER_ID, $respondent->user_id);
//                }
//            }
//
//        }

        // Новый метод получения списка респондентов по типам
        $select = $this->getSelect();
        $select->from(
            array('a' => 'at_session_events'),
            array()
        )->joinInner(
            array('b' => 'at_session_respondents'),
            "a.session_respondent_id = b.session_respondent_id and a.session_id = b.session_id",
            array(
                'user_id' => 'user_id'
            )
        )->joinInner(
            array('c' => 'at_evaluation_type'),
            "a.evaluation_id = c.evaluation_type_id",
            array(
                'type' => new Zend_Db_Expr("MAX(c.relation_type)")
            )
        )->where(
            "a.session_id = ?", $session->session_id
        )->group(
            "b.user_id"
        );

        $r = $select->query()->fetchAll();


        // Новый метод отправки сообщений респондентам
        foreach ($r as $respondent) {

            $userId = $respondent['user_id'];
            $relationTypeForMail = $respondent['type'];
            $template = $this->_getTemplateType($relationTypeForMail);

            if($template){
                $messenger = $this->getService('Messenger');
                $messenger->setOptions(
                    $template,
                    array(
                        'session_id'    => $sessionId,
                        'url_session' => $sessionUrl,
                        'session_begin' => $sessionBegin,
                        'session_end'   => $sessionEnd,
                        'begin' => $sessionBegin,
                        'end'   => $sessionEnd,
                        'contacts'      => $this->getManagerContacts($session->initiator_id),
                    ),
                    'session',
                    $sessionId
                );
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $userId);
            }


        }
    }


    public function getManagerContacts($userId){
        $result = '';
        $userService = $this->getService('User');
        if($userId){
            $user = $userService->find($userId)->current();
            $result = array();
            if ($user->MID){
                $result[] = $user->getName() . ";";
                $result[] = ($user->EMail ? _('Email: ') . sprintf('<a href="mailto:%s">%s</a>', $user->EMail, $user->EMail) . ";" : "");
                $result[] = ($user->Phone ? _('Телефон: ') . $user->Phone . ";" : "");
            }
            $result = '<br>' . implode('<br>', $result);
        }
        return $result;
    }
    
    private function _getTemplateType($relationTypeForMail)
    {
        // отключил эту ересь с разными письмами
//        switch ($relationTypeForMail) {
//
//            case EvaluationModel::RELATION_TYPE_SELF:
//                return HM_Messenger::TEMPLATE_ASSIGN_SESSION_SELF;
//
//            case EvaluationModel::RELATION_TYPE_CHILDREN:
//                return HM_Messenger::TEMPLATE_ASSIGN_SESSION_CHILDREN;
//
//            case EvaluationModel::RELATION_TYPE_SIBLINGS:
//            case EvaluationModel::RELATION_TYPE_CLIENTS:
//                return HM_Messenger::TEMPLATE_ASSIGN_SESSION_SIBLINGS;
//
//            case EvaluationModel::RELATION_TYPE_PARENT:
//            case EvaluationModel::RELATION_TYPE_PARENT_FUNCTIONAL:
//            case EvaluationModel::RELATION_TYPE_PARENT_RESERVE:
//                return HM_Messenger::TEMPLATE_ASSIGN_SESSION_PARENT;
//        }

        // default
        return HM_Messenger::TEMPLATE_ASSIGN_SESSION;
    }

    public function stopSession($sessionId)
    {
        if ($session = $this->getOne($this->find($sessionId))) {

            $this->updateWhere(array(
                'state' => HM_At_Session_SessionModel::STATE_CLOSED,
                'end_date' => date('Y-m-d'),
            ), array('session_id = ?' => $sessionId));
            
            if ($session->state == HM_At_Session_SessionModel::STATE_ACTUAL) {
                $this->getService('Process')->goToNextState($session);
            } else {
                $this->getService('Process')->goToFail($session);
            }
        }
    }

    public function getDefaultUri($sessionId)
    {
        return Zend_Registry::get('view')->url(array('baseUrl' => 'at', 'action' => 'my', 'controller' => 'event', 'module' => 'session', 'session_id' => $sessionId, 'list-switcher' => null));
    }
    
    private function _getSoidsByCriteria($criteria, $method)
    {
        $soids = $profiles = array();
        $criteriaIds = explode(',', $criteria);
        switch ($method) {
            case HM_At_Evaluation_EvaluationModel::TYPE_TEST:
                
                $criteriaWithDescendants = $criteriaIds;
                foreach ($criteriaIds as $criterionId) {
                    $criteriaWithDescendants = array_merge($criteriaWithDescendants, $this->getService('AtCriterionTest')->getChildren($criterionId)->getList('criterion_id'));
                }
                
//                $criterionIds = $this->getService('AtCriterionTest')->getLeaves($criteriaWithDescendants)->getList('criterion_id');
                                
                $criteriaTest = $this->getService('AtCriterionTest')->fetchAllManyToMany('Evaluation', 'EvaluationCriterion', array(
                    'criterion_id IN (?)' => $criteriaWithDescendants,
                    new Zend_Db_Expr('lft = rgt - 1'), // only leaves
                ));
                if (count($criteriaTest)) {
                    
                    // это ограничение пока нигде не сохраняется и не обрабатывается
                    //$this->_restrictions['criteria'] = $criteriaTest->getList('criterion_id');
                    
                    foreach ($criteriaTest as $criterion) {
                        if (count($criterion->evaluation)) {
                            foreach ($criterion->evaluation as $evaluation) {
                                if ($evaluation->method != $method) continue;
                                $profiles[] = $evaluation->profile_id;
                            }
                        }
                    }
                }
                if (count($profiles)) {
                    $soids = $this->getService('Orgstructure')->fetchAll(array(
                        'profile_id IN (?)' => array_unique($profiles)        
                    ));
                    if (count($soids)) {
                        return implode(',', $soids->getList('soid'));
                    }
                }
            break;
            
            default:
                ;
            break;
        }
    }
    
    public function _checkPositions($positions, $asRespondent, $session_id)
    {
        if (($this->_absenceCache === null) && $session->begin_date && $session->end_date) {
            $this->_absenceCache = array();
            $sessions = $this->sessionCache;
            $session = array_shift($sessions);
            if (count($collection = $this->getService('Absence')->fetchAll(array(
                'absence_begin <= ?' => $session->begin_date,
                'absence_end >= ?' => $session->end_date,
            )))) {
                $this->_absenceCache = $collection->getList('user_id'); 
            }
        }

        $session = $this->getSession($session_id);
        if ($session->programm_type != HM_Programm_ProgrammModel::TYPE_ASSESSMENT) {
            return $positions;
        }

        $return = array();
        if (count($positions)) {
            if (!count($positions[0]->employee)) {
                foreach ($positions as $position) {
                    $soids[] = $position->soid;
                }
                $positions = $this->getService('Orgstructure')->fetchAllDependenceJoinInner('Employee', $this->quoteInto('soid IN (?)', $soids)); 
            }

            $key = $asRespondent ? HM_At_Session_SessionModel::ASSIGN_RESPONDENTS_KEY : HM_At_Session_SessionModel::ASSIGN_USERS_KEY;

            foreach ($positions as $position) {
                if ($errorCode = $this->_checkPosition($position, $asRespondent, $session_id)) {
                    $this->_saveError($errorCode, $position, $key);
                    continue;
                }

                if ($this->_checkPosition($position, $asRespondent, $session_id)) continue;
//#18031        
                if($position->blocked) continue; //смотрим, чтобы не был заблокирован
//                if(isset($usedMids[$position->mid])) continue; //смотрим, чтобы один и тот же чел не попал дважды (если у него более 1 должности)
//                $usedMids[$position->mid] = 1;
//                
                $return[] = $position;
            }    
        }
        return $return;
    }
    
    public function _checkPosition($position, $asRespondent, $session_id)
    {
        if (empty($position->mid)) {
            return self::MSG_VACANCT_POSITION;
        }
        if (empty($position->profile_id) && !$asRespondent) { // респонденту не обязательно иметь профиль
            return self::MSG_INVALID_PROFILE;
        }   
        $session = $this->getSession($session_id);
        if (($session->programm_type == HM_Programm_ProgrammModel::TYPE_ASSESSMENT) && $position->position_date) {

            if ($days = $this->getService('Option')->getOption('competenceEmployedBeforeDays')) {
                $date = date('Y-m-d', strtotime("- {$days} days"));
                $dateUser = new HM_Date($position->position_date);
                $date = new HM_Date($date);

                if ($dateUser > $date) return self::MSG_INVALID_POSITION_DATE;

            }
//            elseif (HM_At_Session_SessionModel::MIN_RECORD_OF_SERVICE) {
//                if (HM_At_Session_SessionModel::MIN_RECORD_OF_SERVICE > HM_Date::getPeriodSinceDate($position->position_date, false)) {
//                    return self::MSG_INVALID_POSITION_DATE;
//                }
//            }
        }

        return false;      
    }    
    
    // в processes хранятся все возможнжные его состояния (chain)
    // перед созданием оц.сессии нужно актуализировать все processes,
    // т.к. программу могли поменять; 
    // если по каким-то причинам не был создан ранее - создать process
    // для совсем старых профилей и программы могло не быть - такие пропускаем; для них процесса не будет.( 
    protected function updateUserProcesses($session_id)
    { 
        $profileProcesses = array();
        $collection = $this->getService('Programm')->fetchAllDependence(array('Process', 'Event'), array(
            'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_PROFILE,     
            'programm_type = ?' => HM_Programm_ProgrammModel::TYPE_ASSESSMENT,     
        ));
        $programms = $collection->asArrayOfObjects();
        $profileProgramms = $collection->getList('item_id', 'programm_id');
        foreach ($collection as $programm) {
            if (count($programm->process)) {
                $profileProcesses[$programm->item_id] = $programm->process->current();
            }
        }        
        
        $session = $this->getSession($session_id);
        foreach ($this->profileCache as $profile) {
            if (!isset($profileProcesses[$profile->profile_id])) {
                $process = $this->getService('Process')->insert(array(
                    'type' => HM_Process_ProcessModel::PROCESS_PROGRAMM_ASSESSMENT,        
                    'programm_id' => $profileProgramms[$profile->profile_id],        
                ));
            } else {
                $process = $profileProcesses[$profile->profile_id];
            }
            if (isset($programms[$profileProgramms[$profile->profile_id]])) {
                $process->update($programms[$profileProgramms[$profile->profile_id]]);
            }
            $this->processCache[$profile->profile_id] = $process;
        }
    }
    
    protected function getProcess($profile_id)
    {
        return isset($this->processCache[$profile_id]) ? $this->processCache[$profile_id] : false;
    }



    public function generateColor()
    {
        $rand = array(0,1,2);
        shuffle($rand);
        $c[0] = rand(130,200);
        $c[1] = rand(130,200);
        $c[2] = 130;


        $color_r = $c[$rand[0]];
        $color_g = $c[$rand[1]];
        $color_b = $c[$rand[2]];
        return sprintf("%02x%02x%02x",$color_r,$color_g,$color_b);
    }

    public function getSubjectColor($sessionId)
    {
        if ($this->_subjectsColorsCache === null) {
            $this->_subjectsColorsCache = $this->fetchAll()->getList('session_id','base_color');
        }
        if ($sessionId && array_key_exists($sessionId,$this->_subjectsColorsCache)) {
            return $this->_subjectsColorsCache[$sessionId];
        }

        return '';
    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('сессия plural', '%s сессия', $count), $count);
    }

    private function _saveError($errorCode, $position, $key)
    {
        // Форматируем текущую ошибку
        $fio = '';

        /** @var HM_User_UserModel $user */
        if ($position->employee && $position->employee[0]) {
            $user = $position->employee[0];

            try {
                $fio = $user->getName();
            } catch (Exception $e) {
                // На всякий случай
            }
        }

        // Сохраняем во view
        /** @var Zend_View $view */
        $view = Zend_Registry::get('view');
        $vars = $view->getVars();

        $errors = $vars[Session_ListController::SESSION_NEW_ERRORS];
        if (!$errors) {
            $errors = [HM_At_Session_SessionModel::ASSIGN_RESPONDENTS_KEY  => [], HM_At_Session_SessionModel::ASSIGN_USERS_KEY => []];
        }

        // Группируем по принадлежности участник/респондент и должности
        $errors[$key][$errorCode][$position->getName()][] = $fio;
        Zend_Registry::get('view')->assign(Session_ListController::SESSION_NEW_ERRORS, $errors);
    }

}