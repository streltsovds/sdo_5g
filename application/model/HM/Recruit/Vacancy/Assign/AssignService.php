<?php class HM_Recruit_Vacancy_Assign_AssignService extends HM_Service_Abstract
{
    public function assign($vacancyId, $candidateId, $status = HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE, $customInsertData = array())
    {
        if ($vacancy = $this->getService('RecruitVacancy')->getOne($vacancyCollection = $this->getService('RecruitVacancy')->fetchAllHybrid('Session', 'Candidate', 'CandidateAssign', array('vacancy_id = ?' => $vacancyId)))) {
            
            // обновили процесс - на случай если после создания вакансии были изменения в программе подбора
            // процесс стартует в момент публикации вакансии 
            $userProcess = $this->updateUserProcess($vacancyId);             
            
            $vacancyCandidates = array();
            if (count($vacancy->candidates)) {
                $vacancyCandidates = $vacancy->candidates->asArrayOfObjects();
            } else {
                // переводим на след.шаг при назначении первого кандидата
                $currentState = $this->getService('Process')->getCurrentState($vacancy);
                if (is_a($currentState, 'HM_Recruit_Vacancy_State_Open')) {
                    $this->getService('Process')->goToNextState($vacancy);
                }
            }
            
            if (!isset($vacancyCandidates[$candidateId])) {
                
                if ($candidate = $this->getService('RecruitCandidate')->getOne($candidateCollection = $this->getService('RecruitCandidate')->fetchAllHybrid('User', 'Vacancy', 'VacancyAssign', array('candidate_id = ?' => $candidateId)))) {

                    if (!$candidate->isAllowed()) {
                        return false;
                    }
                    
                    $insertArray = array(
                        'vacancy_id' => $vacancyId,
                        'candidate_id' => $candidateId,
                        'user_id' => $candidate->user_id,
                        'process_id' => $userProcess->process_id,
                        'status' => $status,
                    );
                    
                    
                    if($customInsertData && is_array($customInsertData)){
                        foreach($customInsertData as $key => $value) {
                            if(!isset($insertArray[$key])){
                                $insertArray[$key] = $value;
                            }
                        }
                    }
                    $vacancyCandidate = $this->insert($insertArray);
                    
                    // если сессия подбора уже запущена - автоматически добавляем в неё нового кандидата
                    if (
                        count($vacancy->session) &&
                        ($status != HM_Recruit_Vacancy_Assign_AssignModel::STATUS_HOLD_ON)
                    ) {

                        if ($programm = $this->getService('Programm')->getOne($this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY, $vacancyId, HM_Programm_ProgrammModel::TYPE_RECRUIT))) {
                            $this->getService('Programm')->assignToUser($candidate->user_id, $programm->programm_id);
                        }

                        $vacancyCandidate->vacancies = $vacancyCollection;
                        $vacancyCandidate->candidates = $candidateCollection;                        
                        $this->getService('AtSession')->addUserFromVacancy($vacancyCandidate, $userProcess);
                    }
                    return $vacancyCandidate;
                }
            }
        }
    }
    
    // в этом случае мы чистим всю историю подбора;
    // если надо историю сохранить - то не надо исключать из списка кандидатов, а надо переводить процесс в состояние Fail 
    public function unassign($vacancyCandidateId)
    {
        if ($vacancyCandidate = $this->getService('RecruitVacancyAssign')->getOne($this->getService('RecruitVacancyAssign')->findDependence(array('Vacancy'), $vacancyCandidateId))) {
            
            $vacancyId = $vacancyCandidate->vacancy_id;
            $userId = $vacancyCandidate->user_id;
            
            $candidateService = $this->getService('RecruitCandidate');
            
            $candidates = $candidateService->fetchAll($candidateService->quoteInto(
                'user_id = ?',
                $userId
            ))->getList('candidate_id');

            if (count($candidates) > 1){
                foreach($candidates as $candidate){
                    if($candidate == $vacancyCandidate->candidate_id){
                        $candidateService->delete($candidate);
                        break;
                    }
                }
            }
//            if (count($vacancyCandidate->candidates)) {
//                $candidate = $vacancyCandidate->candidates->current();
//                $candidate->setAutoBlocked();
//            }
            if (count($vacancyCandidate->vacancies)) {
                $sessionId = $vacancyCandidate->vacancies->current()->session_id;
            }

            if ($sessionId && $userId) {
                if (count($collection = $this->getService('AtSessionUser')->fetchAll(array('session_id = ?' => $sessionId, 'user_id = ?' => $userId)))) {
                    $sessionUser = $collection->current();
                    $this->getService('AtSessionUser')->delete($sessionUser);
                }
            }

            if ($vacancyId) {
                if (count($collection = $this->getService('Programm')->fetchAll(array('item_id = ?' => $vacancyId, 'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY)))) {
                    $programmId = $collection->current()->programm_id;
                    $this->getService('ProgrammUser')->unassign($userId, $programmId);
                }
            }

            if ($vacancyCandidateId) {
                $this->getService('State')->deleteBy(array('item_id = ?' => $vacancyCandidateId, 'process_type = ?' => HM_Process_ProcessModel::PROCESS_PROGRAMM_RECRUIT));
            }
        }

        $this->delete($vacancyCandidateId);
    }

    // для перевода обратно в отклик
    public function unassignProcess($vacancyCandidateId)
    {
        if ($vacancyCandidate = $this->getService('RecruitVacancyAssign')->getOne($this->getService('RecruitVacancyAssign')->findDependence(array('Vacancy'), $vacancyCandidateId))) {

            $vacancyId = $vacancyCandidate->vacancy_id;
            $userId = $vacancyCandidate->user_id;

            if (count($vacancyCandidate->vacancies)) {
                $sessionId = $vacancyCandidate->vacancies->current()->session_id;
            }

            if (count($collection = $this->getService('AtSessionUser')->fetchAll(array('session_id = ?' => $sessionId, 'user_id = ?' => $userId)))) {
                $sessionUser = $collection->current();
                $this->getService('AtSessionUser')->delete($sessionUser);
            }

            if (count($collection = $this->getService('AtSessionEvent')->fetchAll(array('session_id = ?' => $sessionId, 'user_id = ?' => $userId)))) {
                $sessionEvent = $collection->current();
                $this->getService('AtSessionEvent')->delete($sessionEvent);
            }

            if (count($collection = $this->getService('Programm')->fetchAll(array('item_id = ?' => $vacancyId, 'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY)))) {
                $programmId = $collection->current()->programm_id;
                $this->getService('ProgrammUser')->unassign($userId, $programmId);
            }

            $this->getService('State')->deleteBy(array('item_id = ?' => $vacancyCandidateId, 'process_type = ?' => HM_Process_ProcessModel::PROCESS_PROGRAMM_RECRUIT));
        }
    }

    public function assignActive($vacancyCandidateId)
    {
        if ($vacancyCandidate = $this->getService('RecruitVacancyAssign')->getOne($this->getService('RecruitVacancyAssign')->fetchAllHybrid(array('Vacancy', 'Candidate'), 'Session', 'Vacancy', array('vacancy_candidate_id = ?' => $vacancyCandidateId)))) {

            // если реанимируем кандидата - удалить предыдущий процесс и event'ы
            if ($vacancyCandidate->status == HM_Recruit_Vacancy_Assign_AssignModel::STATUS_PASSED) {
                $this->unassignProcess($vacancyCandidate->vacancy_candidate_id);
            }

            if ($programm = $this->getService('Programm')->getOne($this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY, $vacancyCandidate->vacancy_id, HM_Programm_ProgrammModel::TYPE_RECRUIT))) {
                $this->getService('Programm')->assignToUser($vacancyCandidate->user_id, $programm->programm_id);
            }

            // если сессия подбора уже запущена - автоматически добавляем в неё активного кандидата
            if (
                count($vacancyCandidate->session) && 
                ($vacancyCandidate->session->current()->state == HM_At_Session_SessionModel::STATE_ACTUAL)
            ) {
                // обновили процесс - на случай если после создания вакансии были изменения в программе подбора
                // процесс стартует в момент публикации вакансии 
                $userProcess = $this->updateUserProcess($vacancyCandidate->vacancy_id);             
                $this->getService('AtSession')->addUserFromVacancy($vacancyCandidate, $userProcess);
            }
            $vacancyCandidate->status = HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE;
            $this->getService('RecruitVacancyAssign')->update($vacancyCandidate->getValues());
        }
        return $vacancyCandidate;
    }
    
    // в отличие от оценочной сесии, здесь один динамический процесс для всех кандидатов
    // уже после создания вакансии программа изменилась, то при следующем добавлении кандидата у всех процесс поменяется (будет приведён к общему виду)
    // вообще, это неправильно - менять программу в процессе подбора..
    public function updateUserProcess($vacancyId)
    { 
        $process = false;
        $collection = $this->getService('Programm')->fetchAllDependence(array('Process', 'Event'), array(
            'item_id = ?' => $vacancyId,     
            'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_VACANCY,     
            'programm_type = ?' => HM_Programm_ProgrammModel::TYPE_RECRUIT,    
        ));
        
        if (count($collection)) {
            
            $programm = $collection->current();
            if (count($programm->process)) {
                $process = $programm->process->current();
            } else {
                $process = $this->getService('Process')->insert(array(
                    'type' => HM_Process_ProcessModel::PROCESS_PROGRAMM_RECRUIT,        
                    'programm_id' => $programm->programm_id,        
                ));                
            }
            $process->update($programm);
        }
        return $process;
    }     
    
    public function setStatus($vacancyCandidate, $result)
    {
        $vacancyCandidate->status = HM_Recruit_Vacancy_Assign_AssignModel::STATUS_PASSED;
        $vacancyCandidate->result = $result;
        $this->update($vacancyCandidate->getValues());   
    }

    public function getHash($vacancyCandidateId) {
        return md5($vacancyCandidateId.HM_Recruit_Vacancy_Assign_AssignModel::SALT);
    }

    public function printResume($candidateId)
    {
        $path = $this->getService('User')->getPath(Zend_Registry::get('config')->path->upload->resume, $candidateId);
        $filePath = $path. $candidateId . '.docx';
        if (file_exists($filePath) && is_file($filePath)) {
            $pathParts = pathinfo($filePath);
            $resourceReader = new HM_Resource_Reader($filePath, $pathParts['basename']);
            $resourceReader->readFile();
        }
    }
}