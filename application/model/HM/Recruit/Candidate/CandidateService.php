<?php
class HM_Recruit_Candidate_CandidateService extends HM_Service_Abstract
{
    public function getName($candidateId)
    {
        $candidate = $this->fetchAllDependence('User', array('candidate_id = ?' => $candidateId))->current();
        $user = $candidate->user->current();
        return $user->getName();
    }
    
    /**
     * Serching candidates by vacancy and mode
     * @param HM_Recruit_Vacancy_VacancyModel $vacancy
     * @param int $source
     */
    public function searchByVacancy(HM_Recruit_Vacancy_VacancyModel $vacancy, $source = 'internal')
    {
        /*@var HM_Recruit_Candidate_Search_StrategyFactory */
        $factory = $this->getService('RecruitCandidateSearchStrategyFactory');
        /*@var HM_Recruit_Candidate_Search_SearchBehavior */
        $strategy = $factory->getStrategy($source);
        $searchResult = $strategy->search($vacancy);
        return $searchResult;
    }

    public function getResumePath($candidateId)
    {
        $config = Zend_Registry::get('config');
        $maxFilesCount = (int) $config->path->upload->maxfilescount;
        $filePath = $config->path->upload->resume;
        $path = floor($candidateId / $maxFilesCount);
        return  $filePath . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR;
    }

    public function getResumeFile($candidateId)
    {
        $filePath = $this->getResumePath($candidateId);
        return $filePath.$candidateId.'.docx';
    }
    
    public function createCandidate($dataArray, $providerId, $status)
    {
        $candidateService                 = $this->getService('RecruitCandidate');
        $candidateHHSpecializationService = $this->getService('RecruitCandidateHHSpecialization');
        $userService                      = $this->getService('User');

        $vacancy = $this->getService('RecruitVacancy')->find($dataArray['vacancy_id'])->current();

        // создаем пользователя
        $user = $userService->duplicateInsert(array(
            'LastName'   => $dataArray['LastName'],
            'FirstName'  => $dataArray['FirstName'],
            'Patronymic' => $dataArray['Patronymic'],
            'BirthDate'  => $dataArray['BirthDate'],
            'EMail'      => $dataArray['EMail'],
            'Phone'      => $dataArray['Phone'],
            'Login'      => $dataArray['Login'],
            'Password'   => new Zend_Db_Expr("PASSWORD('".$dataArray['Password']."')"),
            'blocked'    => 1
        ));
        
        $now = new HM_Date();
        $now = $now->toString(HM_Date::SQL);

        // добавляем кандидата
        $candidate = $candidateService->insert(array(
            'user_id' => $user->MID,
            'source' => $providerId,
            'resume_external_url' => $dataArray['resume_external_url'],
            'resume_external_id' => $dataArray['resume_external_id'],
            'resume_json'         => $dataArray['resume_json'],
            'resume_date'         => $now,
            'hh_area'             => $dataArray['hh_area'],
            'hh_metro'            => $dataArray['hh_metro'],
            'hh_salary'           => $dataArray['hh_salary'],
            'hh_total_experience' => $dataArray['hh_total_experience'],
            'hh_education'        => $dataArray['hh_education'],
            'hh_citizenship'      => $dataArray['hh_citizenship'],
            'hh_gender'           => $dataArray['hh_gender'],
            'hh_age'              => $dataArray['hh_age'],
            'hh_negotiation_id'   => $dataArray['hh_negotiation_id'],
        ));

        if(count($dataArray['hh_specialization'])){
            foreach($dataArray['hh_specialization'] as $hhSpecialization){
                $candidateHHSpecializationService->insert(array(
                    'specialization_id' => $hhSpecialization->id,
                    'candidate_id'      => $candidate->candidate_id,
                ));
            }
        }
        
        $vacancyCandidatesBeforeInsert = $this->getService('RecruitVacancyAssign')->fetchAllDependence(array('Candidate', 'Vacancy'), array(
            'vacancy_id = ?' => $dataArray['vacancy_id']
        ));

        if ($providerId == HM_Recruit_Provider_ProviderModel::ID_EXCEL) return $candidate;
            
        $vacancyCandidate = $this->getService('RecruitVacancyAssign')->assign(
            $dataArray['vacancy_id'], $candidate->candidate_id, $status,
            array(

            )
        );

        /*
         * При включении в сессиию подбора теперь скрываем подругому
         *
         *
        // делаем невидимым
        $this->getService('RecruitVacancyResumeHhIgnore')->insert(array(
            'vacancy_id' => (int) $dataArray['vacancy_id'],
            'hh_resume_id' => (int) $dataArray['resume_id'],
            'date' => new Zend_Db_Expr('NOW()'),
            'create_user_id' => $user->MID,
        ));
        */

        if (!count($vacancyCandidatesBeforeInsert) && $vacancyCandidate) {
            $session = $this->getService('AtSession')->getOne($this->getService('AtSession')->find(intval($vacancy->session_id)));
            $this->getService('RecruitVacancy')->startSession($vacancy, $session);
        }

        $candidate->user = $user;
        return $candidate;
        
    }
    
    public function copyCandidate($candidateId){
        $sourceCandidate = $this->find($candidateId)->current();
        $sourceCandidateValues = $sourceCandidate->getValues();
        
        unset($sourceCandidateValues['candidate_id']);
        $newCandidate = $this->insert($sourceCandidateValues);
        
        return $newCandidate->candidate_id;
    }

    /*
     * Объединяем дублирующихся кандидатов от отдного юзера
     * Таким образом сохраняется хронологически последнее резюме
     */
    public function removeDuplicatedAssigns($userId)
    {
        $vacancies2Candidates = array();
        $candidates = $this->fetchAllDependence('VacancyAssign', array('user_id = ?' => $userId), 'candidate_id');
        foreach ($candidates as $candidate) {
            if (count($candidate->vacancies)) {
                foreach ($candidate->vacancies as $vacancyAssign) {
                    if (!isset($vacancies2Candidates[$vacancyAssign->vacancy_id])) $vacancies2Candidates[$vacancyAssign->vacancy_id] = array();
                    $vacancies2Candidates[$vacancyAssign->vacancy_id][] = $candidate->candidate_id;
                }
            } else {
                if (!isset($vacancies2Candidates[0])) $vacancies2Candidates[0] = array();
                $vacancies2Candidates[0][] = $candidate->candidate_id;
            }
        }

        // если есть одновременно и кандидаты на сессии, и кандидаты без сессии,
        // то кандидатов без сессии можно просто удалить
        if (isset($vacancies2Candidates[0]) && (count($vacancies2Candidates) > 1)) {
            $this->deleteBy(array('candidate_id IN (?)' => $vacancies2Candidates[0]));
            $this->getService('RecruitVacancyAssign')->deleteBy(array('candidate_id IN (?)' => $vacancies2Candidates[0]));
            unset($vacancies2Candidates[0]);
        }

        // если на одну сессию после merge оказалось назначено несколько кандидатов - лишние удаляем
        foreach ($vacancies2Candidates as $vacancyId => $candidateIds) {
            if (count($candidateIds) > 1) {
                $firstCandidate = array_shift($candidateIds);
                $this->deleteBy(array('candidate_id IN (?)' => $candidateIds));
                $vacancyAssigns = $this->getService('RecruitVacancyAssign')->fetchAll(array('candidate_id IN (?)' => $candidateIds));
                foreach ($vacancyAssigns as $vacancyAssign) {
                    $this->getService('RecruitVacancyAssign')->unassign($vacancyAssign->vacancy_candidate_id);
                }
            }
        }
        return true;
    }
}
?>