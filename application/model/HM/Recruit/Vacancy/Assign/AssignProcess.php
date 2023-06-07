<?php

class HM_Recruit_Vacancy_Assign_AssignProcess extends HM_Process_Type_Programm
{
    public function onProcessStart()
    {
        $vacancyCandidate = $this->getModel();
        
        // @todo: кэшировать
        if (!count($vacancyCandidate->candidates) || !count($vacancyCandidate->vacancies)) {
            $candidate = Zend_Registry::get('serviceContainer')->getService('RecruitCandidate')->getOne(Zend_Registry::get('serviceContainer')->getService('RecruitCandidate')->findDependence('User', $vacancyCandidate->candidate_id));
            $vacancy = Zend_Registry::get('serviceContainer')->getService('RecruitVacancy')->getOne(Zend_Registry::get('serviceContainer')->getService('RecruitVacancy')->find($vacancyCandidate->vacancy_id));
        } else {
            $candidate = $vacancyCandidate->candidates->current();
            $vacancy = $vacancyCandidate->vacancies->current();
        }

        $candidate->setAutoBlocked(HM_User_UserModel::BLOCKED_OFF);
        
        if (!count($candidate->user)) {
            $user = Zend_Registry::get('serviceContainer')->getService('User')->getOne(Zend_Registry::get('serviceContainer')->getService('User')->find($candidate->user_id));
        } else {
            $user = $candidate->user->current();
        }
        
        if (empty($vacancy) || empty($candidate) || empty($user) || !$candidate->isAllowed()) {
            return false;
        }
        
        // уведомление о старте подбора
        $replacements = array(
            'vacancy' => $vacancy->name,
            'name' => implode(' ', array($user->FirstName, $user->Patronymic)),
        );  
        if ($candidate->source == HM_Recruit_Provider_ProviderModel::ID_PERSONAL) {
            $template = HM_Messenger::TEMPLATE_RECRUIT_START_INTERNAL;
        } else {
            $template = HM_Messenger::TEMPLATE_RECRUIT_START_EXTERNAL;
            
            // новому кандидату каждый раз сбрасываем пароль при назначении на вакансию
            // одновременно двух вакансий быть не могет
            $password = Zend_Registry::get('serviceContainer')->getService('User')->getRandomString();
            $user->Password = new Zend_Db_Expr("PASSWORD(" . Zend_Registry::get('serviceContainer')->getService('User')->getSelect()->getAdapter()->quote($password) . ")");
            Zend_Registry::get('serviceContainer')->getService('User')->update($user->getValues());
            $replacements = $replacements + array(
                'login' => $user->Login,        
                'new_password' => $password,        
            ); 
        }

        Zend_Registry::get('serviceContainer')->getService('Messenger')->setOptions($template, $replacements, '', 0);
        if (($template != HM_Messenger::TEMPLATE_RECRUIT_START_INTERNAL) || ($template != HM_Messenger::TEMPLATE_RECRUIT_START_INTERNAL)) {
            try {
                Zend_Registry::get('serviceContainer')->getService('Messenger')->send(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(), $candidate->user_id);
            } catch (Exception $e) {
            }
        }
        
        return true;
    }
    
    public function getType()
    {
        return HM_Process_ProcessModel::PROCESS_PROGRAMM_RECRUIT;
    }

    public function onProcessComplete()
    {
        $vacancyCandidate = $this->getModel();
        
        if (!count($vacancyCandidate->vacancies)) {
            $vacancy = Zend_Registry::get('serviceContainer')->getService('RecruitVacancy')->findDependence('RecruiterAssign', $vacancyCandidate->vacancy_id)->current();
        } else {
            $vacancy = $vacancyCandidate->vacancies->current();
        }
        
        if (count($vacancyCandidate->candidates)) {
            $candidate = $vacancyCandidate->candidates->current();
        } else {
            $candidate = Zend_Registry::get('serviceContainer')->getService('RecruitCandidate')->getOne(Zend_Registry::get('serviceContainer')->getService('RecruitCandidate')->find($vacancyCandidate->candidate_id));
        }
        
        if (!count($candidate->user)) {
            $user = Zend_Registry::get('serviceContainer')->getService('User')->getOne(Zend_Registry::get('serviceContainer')->getService('User')->find($candidate->user_id));
        } else {
            $user = $candidate->user->current();
        }
        
        if (count($vacancyCandidate->sessionUser)) {
            $sessionUser = $vacancyCandidate->sessionUser->current();
        } else {
            $sessionUser = Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->getOne(Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->fetchAll(array('vacancy_candidate_id = ?' => $vacancyCandidate->vacancy_candidate_id)));
        }
        
        
        // если кто-то один прошёл всю программу до complete - значит этот кандидат отобран, а все остальные не отобраны
        // а если у кандидата уже установлен result - значит он не сам прошёл, а его переводят сюда принудительно
        // если переводят принудительно в рекомендованные - тоже все правильно, закрываем вакансию
        if (empty($vacancyCandidate->result) || ($vacancyCandidate->result == HM_Recruit_Vacancy_Assign_AssignModel::RESULT_SUCCESS)) {
            
            $vacancyCandidate->status = HM_Recruit_Vacancy_Assign_AssignModel::STATUS_PASSED;
            $vacancyCandidate->result = HM_Recruit_Vacancy_Assign_AssignModel::RESULT_SUCCESS;
            
            $otherCandidates = Zend_Registry::get('serviceContainer')->getService('RecruitVacancyAssign')->fetchAllDependence(array('User', 'Candidate'), array(
                'vacancy_id = ?' => $vacancyCandidate->vacancy_id,        
                'vacancy_candidate_id != ?' => $vacancyCandidate->vacancy_candidate_id,        
            ));  
            if (count($otherCandidates)) {
                foreach ($otherCandidates as $otherCandidate) {
                    if($otherCandidate->status!=HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE) continue; //#19867
                    
                    if($otherCandidate->user == null) continue;
                    
                    $otherCandidate->status = HM_Recruit_Vacancy_Assign_AssignModel::STATUS_PASSED;
                    $otherCandidate->result = HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_DEFAULT;
                    
                    Zend_Registry::get('serviceContainer')->getService('Process')->goToComplete($otherCandidate);
                }
            }

            // остановить сессию оценки, прекратить дальнейшее заполнение форм
            Zend_Registry::get('serviceContainer')->getService('AtSession')->stopSession($vacancy->session_id);
            
            // дело идёт к трудоустройству
            Zend_Registry::get('serviceContainer')->getService('Process')->goToNextState($vacancy);
        }    

        if ($sessionUser) {
            Zend_Registry::get('serviceContainer')->getService('Process')->goToComplete($sessionUser);
        } // в этот момент генерится pdf
        
        Zend_Registry::get('serviceContainer')->getService('RecruitVacancyAssign')->update($vacancyCandidate->getValues());
        
        // заблокировать внешнего кандидата;
        // если он победил, разблокируется при назначении в должность
        if ($candidate) {
            $candidate->setAutoBlocked();
        } 
        
        // уведомление об успешном либо неуспешном завершении
        $replacements = array(
            'vacancy' => $vacancy->name,
            'name' => implode(' ', array($user->FirstName, $user->Patronymic)),
        );  
        if ($vacancyCandidate->result == HM_Recruit_Vacancy_Assign_AssignModel::RESULT_SUCCESS) {
            $template = HM_Messenger::TEMPLATE_RECRUIT_SUCCESS;
        } else {
            $template = HM_Messenger::TEMPLATE_RECRUIT_FAIL;
        }
        Zend_Registry::get('serviceContainer')->getService('Messenger')->setOptions($template, $replacements, '', 0);
        if (($template != HM_Messenger::TEMPLATE_RECRUIT_SUCCESS) || ($template != HM_Messenger::TEMPLATE_RECRUIT_FAIL)) {
            try {
                Zend_Registry::get('serviceContainer')->getService('Messenger')->send(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(), $candidate->user_id);
            } catch (Exception $e) {
            }
        }
        
    }    

    static public function getStatuses()
    {
        return array(
            self::PROCESS_STATUS_INIT => _('Не начат'),
            self::PROCESS_STATUS_CONTINUING => _('В процессе'),
            self::PROCESS_STATUS_COMPLETE => _('Завершен'),
            self::PROCESS_STATUS_FAILED => _('Отменен'), // ?
        );
    }    
}