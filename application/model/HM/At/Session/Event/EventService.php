<?php
class HM_At_Session_Event_EventService extends HM_Service_Abstract
{
    public function updateStatus($sessionEventIds, $status)
    {
        if (!is_array($sessionEventIds)) {
            $sessionEventIds = array($sessionEventIds);
        }
        
        foreach ($sessionEventIds as $sessionEventId) {
            
            if ($event = $this->getOne($this->findDependence(array('Evaluation', 'SessionPair'), $sessionEventId))) {
            
                $event->status = $status;
                if ($status == HM_At_Session_Event_EventModel::STATUS_COMPLETED) {
                    $event->date_filled = date('Y-m-d H:i');
                }
                $this->update($event->getValues()); 
    
                if ($status == HM_At_Session_Event_EventModel::STATUS_COMPLETED) {
                    if (is_subclass_of($event, 'HM_At_Session_Event_Method_Form_FinalizeModel')) {
                            $event->finalize();
                    } elseif($event->programm_event_user_id) {
                        // это нужно когда несколько event'ов соответствуют одному шагу workflow (например, оценка коллегами)
                        $this->updateProcess($event);
                    }
                }
            }
        }
    }
    
    // DEPRECATED!
    public function updateStatusByLesson($lesson, $userId, $method)
    {
        $sessionEvents = $this->fetchAllManyToMany('Lesson', 'SessionEventLesson', array(
            'session_id = ?' => $lesson->session_id,        
            'user_id = ?' => $userId,        
            'method = ?' => $method,        
        ));
        if (count($sessionEvents)) {
            foreach ($sessionEvents as $sessionEvent) {
                
                $status = HM_At_Session_Event_EventModel::STATUS_IN_PROGRESS;
                $lessonIds = $sessionEvent->lessons->getList('lesson_id');
                unset($lessonIds[$lesson->SHEID]); // текущее не считается
                
                $undoneLessons = array();
                if (count($lessonIds)) { 
                    $undoneLessons = $this->getService('LessonAssign')->fetchAll(array(
                        'SHEID IN (?)' => $lessonIds,
                        'MID = ?' => $userId,
                        'V_STATUS = ?' => HM_Scale_Value_ValueModel::VALUE_NA,
                    ));
                }
                
                if (!count($undoneLessons)) {
                    $status = HM_At_Session_Event_EventModel::STATUS_COMPLETED;
                }
                
                $this->updateStatus($sessionEvent->session_event_id, $status);
                $userStatus = $this->getService('AtSessionUser')->updateStatus($sessionEvent->session_user_id);
                if ($userStatus == HM_At_Session_User_UserModel::STATUS_COMPLETED) {
                    $this->getService('AtEvaluationResults')->saveResultsByLessons($sessionEvent, HM_At_Evaluation_EvaluationModel::TYPE_TEST);
                }
            }
        }
    }
    
    // SiblingsEvents - такие же оценки других людей из подразделения
    public function getSiblingsEvents($event)
    {
        $return = array();
        if (count($event->position)) {
            $positions = $this->getService('Orgstructure')->getDescendants($event->position->current()->owner_soid, true);
            if (count($positions)) {
                $return = $this->getService('AtSessionEvent')->fetchAllDependence(array('SessionEventUser', 'EvaluationResult'), array(
                    'position_id IN (?)' => $positions,        
                    'evaluation_id = ?' => $event->evaluation_id,        
                    'session_id = ?' => $event->session_id,        
                ));
            }
        }
        return $return;        
    }
    
    public function getSameMethodEvents($event)
    {
        $collection = $this->getService('AtSessionEvent')->fetchAll(array(
            'method = ?' => $event->method,        
            'session_event_id != ?' => $event->session_event_id,        
            'session_user_id = ?' => $event->session_user_id,        
            'session_id = ?' => $event->session_id,        
        ));
        return $collection;        
    }
    
    public function updateProcess($event) 
    {
        $allowUpdateProcess = true;
        
        if (count($event->evaluation)) {
            
            $evaluation = $event->evaluation->current();
            $allowUpdateProcess = $allowUpdateProcess && $evaluation->isAutoPassing();
            
            if ($evaluation->isFullCompletionRequired()) {
                $sameProgrammEvents = $this->_getSameProgrammEvents($event);
                $allowUpdateProcess = $allowUpdateProcess && !count($sameProgrammEvents);
            }
        }
        
        if ($allowUpdateProcess) {
            
            $processSubjects = array();
            if ($evaluation->isMultiUserEvents()) {
                
                // @todo: надо будет рефакторить когда появятся другие MultiUser-методики; пока тольк опарные сравнения
                if (count($event->pairs)) {
                    $userIds = array_unique(array_merge($event->pairs->getList('first_user_id'), $event->pairs->getList('second_user_id')));
                    if (count($userIds)) {
                        $processSubjects = $this->getService('AtSessionUser')->fetchAll(array(
                            'session_id = ?' => $event->session_id,
                            'user_id IN (?)' => $userIds,
                        ));
                    }
                }
            } else {
                if ($processSubject = $this->getService('AtSessionUser')->getProcessSubject($event->session_user_id)) {
                    $processSubjects = array($processSubject);
                }
            }
            
            if (count($processSubjects)) {
                foreach ($processSubjects as $processSubject) {
                    $processAbstract = $processSubject->getProcess()->getProcessAbstract();
                    if ($processAbstract && $processAbstract->isStrict()) {
                        $currentState = $this->getService('Process')->getCurrentState($processSubject);
                        if ($programmEventUser = $this->getService('ProgrammEventUser')->getOne($this->getService('ProgrammEventUser')->find($event->programm_event_user_id))) {
                            if ($programmEventUser->programm_event_id == $currentState->getProgrammEventId()) {
                                $this->getService('Process')->goToNextState($processSubject);
                            } else {
                                // такое может случиться, если разрешено прохождения мероприятия после его финализации (напр., проф.тест)
                            }
                        }
                    } else {
                        if ($stateClass = $this->getProcessStateClass($event)) {
                            $this->getService('Process')->setStateStatus($processSubject, $stateClass, HM_State_Abstract::STATE_STATUS_PASSED);
                        }
                    }
                }
            }
            
            $data['status'] = HM_Programm_Event_User_UserModel::STATUS_PASSED;
            $this->getService('ProgrammEventUser')->updateWhere($data, array(
                'programm_event_user_id = ?' => $event->programm_event_user_id,
            ));
        }
    }
    
    // возвращает незавершенные оц.формы, которые "скрываются" за тем же квадратиком workflow
    // например другие тесты профтестирования или другие оценки коллег
    protected function _getSameProgrammEvents($event) 
    {
        $collection = $this->fetchAll(array(
            'programm_event_user_id = ?' => $event->programm_event_user_id,        
            'session_id = ?' => $event->session_id,  
            'status != ?' => HM_At_Session_Event_EventModel::STATUS_COMPLETED,        
        ));
        return $collection;
    }
    
    public function getEventContext($sessionEventId)
    {
        $return = array();
        $collection = Zend_Registry::get('serviceContainer')->getService('AtSessionEvent')->findDependence(array('Session', 'CriterionTest', 'CriterionPersonal'), $sessionEventId);
        if (count($collection)) {
            $event = $collection->current();
            if (count($event->session)) {
                $session = $event->session->current();
            }
            $criterionName = '';
            $collection = Zend_Registry::get('serviceContainer')->getService('AtEvaluation')->find($event->evaluation_id);
            if (count($collection)) {
                $evaluation = $collection->current();

                $vacancy = false;
                if ($evaluation->profile_id) {
                    $profileId = $evaluation->profile_id;
                } elseif($evaluation->vacancy_id) {
                    $vacancy = Zend_Registry::get('serviceContainer')->getService('RecruitVacancy')->find($evaluation->vacancy_id)->current();
                    $profileId = $vacancy->profile_id;
                }
                
                $collection = Zend_Registry::get('serviceContainer')->getService('AtProfile')->find($profileId);
                if (count($collection)) {
                    $profile = $collection->current();
                    $profileCriteria = Zend_Registry::get('serviceContainer')->getService('AtProfileCriterionValue')->fetchAllDependence(array('Criterion', 'CriterionTest', 'CriterionPersonal'), array('profile_id = ?' => $profileId));
                }
            }
                        
            $return = array(
                'event' => $event,
                'evaluation' => $evaluation,
                'session' => $session,
                'vacancy' => $vacancy,
                'profile' => $profile,
                'profileCriteria' => $profileCriteria,
            );
        }
        
        return $return;
    }    
    
    // @todo: не стоит вызывать этот метод в цикле
    public function getProcessStateClass($event)
    {
        if (!isset($event->programmEventUser)) {
            $programmEventUser = $this->getService('ProgrammEventUser')->getOne($this->getService('ProgrammEventUser')->find($event->programm_event_user_id));
        } else {
            $programmEventUser = $event->programmEventUser->current();
        }
        
        if (!isset($event->session)) {
            $session = $this->getService('AtSession')->getOne($this->getService('AtSession')->find($event->session_id));
        } else {
            $session = $event->session->current();
        }
        
        if ($programmEventUser && $session) {
            
            if ($session->programm_type == HM_Programm_ProgrammModel::TYPE_RECRUIT) {
                $prefix = HM_Process_Type_Programm_RecruitModel::getStatePrefix();
            } elseif ($session->programm_type == HM_Programm_ProgrammModel::TYPE_ASSESSMENT) {
                $prefix = HM_Process_Type_Programm_AssessmentModel::getStatePrefix();
            }
            
            if ($prefix) {
                return $prefix . $programmEventUser->programm_event_id;
            }
        }
        return false;
    }
    
    public function deleteResults($sessionEventId)
    {
        $this->getService('AtEvaluationResults')->deleteBy(array('session_event_id = ?' => $sessionEventId));
        $this->getService('AtEvaluationIndicator')->deleteBy(array('session_event_id = ?' => $sessionEventId));
        $this->getService('AtEvaluationMemoResult')->deleteBy(array('session_event_id = ?' => $sessionEventId));
        
        $this->updateWhere(array(
            'status' => HM_At_Session_Event_EventModel::STATUS_PLANNED
        ), array(
            'session_event_id = ?' => $sessionEventId
        ));
    }
    
    public function delete($sessionEventId)
    {
        $this->deleteResults($sessionEventId);
        parent::delete($sessionEventId);
    }


    public function addEventToSession($session, $user, $sessionUser, $sessionRespondent, $evaluation, $programm)
    {
        $beginDate = new HM_Date($session->begin_date);
        $endDate = new HM_Date($session->end_date);

        $beginEventDate = clone $beginDate;
        $endEventDate = clone $endDate;

        $programmEventUserId = null;
        $curProgrammEvent = null;

        foreach ($programm->events as $programmEvent) {
            if ($programmEvent->item_id == $evaluation->evaluation_type_id) {
                $curProgrammEvent = $programmEvent;
                foreach ($programm->eventUsers as $programmEventUser) {
                    if ($programmEventUser->programm_event_id == $programmEvent->programm_event_id && $user->MID == $programmEventUser->user_id) {
                        $programmEventUserId = $programmEventUser->programm_event_user_id;
                        break;
                    }
                }
                break;
            }
        }

        if ($curProgrammEvent->day_begin) {
            $beginEventDate = HM_Date::getRelativeDate($beginEventDate, $curProgrammEvent->day_begin);
        }
        if ($curProgrammEvent->day_end) {
            $endEventDate = HM_Date::getRelativeDate($endEventDate, $curProgrammEvent->day_end);
        }

        $sessionEvent = $this->getOne(
            $this->fetchAll(
                $this->quoteInto(
                    array(
                        'session_id = ?',
                        ' AND user_id = ?',
                        ' AND evaluation_id = ?',
                        ' AND session_user_id = ?',
                        ' AND method LIKE ?',
                        ' AND programm_event_user_id = ?',
                    ),
                    array(
                        $session->session_id,
                        $user->MID,
                        $evaluation->evaluation_type_id,
                        $sessionUser->session_user_id,
                        $evaluation->method,
                        $programmEventUserId,
                    )
                )
            )
        );


        $eventDefaults = $evaluation->getDefaults($user);

        $sessionEventData = array(
            'session_id' => $session->session_id,
            'position_id' => $sessionRespondent->position_id,
            'evaluation_id' => $evaluation->evaluation_type_id,
            'respondent_id' => $sessionRespondent->user_id,
            'session_user_id' => $sessionUser->session_user_id,
            'user_id' => $sessionUser->user_id,
            'session_respondent_id' => $sessionRespondent->session_respondent_id,
            'method' => $evaluation->method,
            'name' => $eventDefaults['name'],
            'programm_event_user_id' => $programmEventUserId,
            'date_begin' => $beginEventDate->get('Y-M-d'),
            'date_end' => $endEventDate->get('Y-M-d'),
        );


        if ($sessionEvent->session_respondent_id == 0) {
            $sessionEventData['session_event_id'] = $sessionEvent->session_event_id;
            $sessionEvent = $this->update($sessionEventData);
        } else {
            $sessionEvent = $this->insert($sessionEventData);
        }

        return $sessionEvent;
    }

    public function removeEventFromSession($session, $user, $sessionUser, $removeUserId, $evaluation, $programm)
    {
        $programmEventUserId = null;
        $curProgrammEvent = null;

        foreach ($programm->events as $programmEvent) {
            if ($programmEvent->item_id == $evaluation->evaluation_type_id) {
                $curProgrammEvent = $programmEvent;
                foreach ($programm->eventUsers as $programmEventUser) {
                    if ($programmEventUser->programm_event_id == $programmEvent->programm_event_id && $user->MID == $programmEventUser->user_id) {
                        $programmEventUserId = $programmEventUser->programm_event_user_id;
                        break;
                    }
                }
                break;
            }
        }

        $sessionEvents = $this->fetchAll(
            $this->quoteInto(
                array(
                    'session_id = ?',
                    ' AND user_id = ?',
                    ' AND evaluation_id = ?',
                    ' AND session_user_id = ?',
                    ' AND method LIKE ?',
                    ' AND programm_event_user_id = ?',
                ),
                array(
                    $session->session_id,
                    $sessionUser->user_id,
                    $evaluation->evaluation_type_id,
                    $sessionUser->session_user_id,
                    $evaluation->method,
                    $programmEventUserId,
                )

            )
        );

        $deleted = false;
        foreach ($sessionEvents as $sessionEvent) {
            if ($sessionEvent->respondent_id == $removeUserId) {
                $this->getService('AtSessionEvent')->delete($sessionEvent->session_event_id);
                $deleted = true;
            }
        }

        if ($deleted && count($sessionEvents) == 1) {
            $beginDate = new HM_Date($session->begin_date);
            $endDate = new HM_Date($session->end_date);

            $eventDefaults = $evaluation->getDefaults($user);
            $beginEventDate = clone $beginDate;
            $endEventDate = clone $endDate;

            if ($curProgrammEvent->day_begin) {
                $beginEventDate = HM_Date::getRelativeDate($beginEventDate, $curProgrammEvent->day_begin);
            }
            if ($curProgrammEvent->day_end) {
                $endEventDate = HM_Date::getRelativeDate($endEventDate, $curProgrammEvent->day_end);
            }
            $sessionEventData = array(
                'session_id' => $session->session_id,
                'evaluation_id' => $evaluation->evaluation_type_id,
                'respondent_id' => 0,
                'session_user_id' => $sessionUser->session_user_id,
                'user_id' => $sessionUser->user_id,
                'session_respondent_id' => 0,
                'method' => $evaluation->method,
                'name' => $eventDefaults['name'],
                'programm_event_user_id' => $programmEventUserId,
                'date_begin' => $beginEventDate->get('Y-M-d'),
                'date_end' => $endEventDate->get('Y-M-d'),
            );
            $this->getService('AtSessionEvent')->insert($sessionEventData);
        }
    }
}