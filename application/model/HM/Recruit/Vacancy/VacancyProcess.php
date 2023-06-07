<?php

class HM_Recruit_Vacancy_VacancyProcess extends HM_Process_Type_Static
{
    public function onProcessStart(){}
    
    public function getType()
    {
        return HM_Process_ProcessModel::PROCESS_VACANCY;
    }

    static public function getStatuses()
    {
        return array(
            self::PROCESS_STATUS_INIT       => _('Создана'),
            self::PROCESS_STATUS_CONTINUING => _('В процессе'),
            self::PROCESS_STATUS_COMPLETE   => _('Закончена успешно'),
            self::PROCESS_STATUS_FAILED     => _('Отменена'),
        );
    }
    

    public function onProcessComplete()
    {
        $vacancy = $this->getModel();
        
        // всех кандидатов - goToComplete; это не означает, что кандидат провалил текущий этап, просто на этом этапе процесс прекращён
        $vacancyCandidates = Zend_Registry::get('serviceContainer')->getService('RecruitVacancyAssign')->fetchAllDependence(array('Candidate', 'Vacancy'), array('vacancy_id = ?' => $vacancy->vacancy_id));
        if (count($vacancyCandidates)) {
            foreach ($vacancyCandidates as $vacancyCandidate) {
                
                // важно здесь выставить result, т.к. в HM_Recruit_Vacancy_Assign_AssignProcess::onProcessComplete() 
                // есть логика автоматического завершения всего процесса при успешном завершении одним из кандидатов
                $vacancyCandidate->result = HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_DEFAULT;
                
                Zend_Registry::get('serviceContainer')->getService('Process')->goToComplete($vacancyCandidate);
            }
        }

        Zend_Registry::get('serviceContainer')->getService('RecruitVacancy')->update(
            array(
                'vacancy_id' => $vacancy->vacancy_id,
                'complete_date' => date('Y-m-d H:i:s'),
                'complete_year' => date('Y'),
                'complete_month' => date('m'),
            )
        );
        // архивировать вакансию HH
        if (Zend_Registry::get('config')->vacany->hh->enabled && !empty($vacancy->hh_vacancy_id)) {

            try {
                $factory = $this->getService('RecruitServiceFactory');
                $hhService = $factory->getRecruitingService(Zend_Registry::get('config')->vacancy->externalSource, Zend_Registry::get('config')->vacancy->api);

                $hhService->archiveVacancy($vacancy->hh_vacancy_id);
                $this->getService('RecruitVacancy')->updateWhere(array('hh_vacancy_id' => new Zend_Db_Expr('NULL')), $this->quoteInto('vacancy_id = ?', $vacancy->vacancy_id));

            } catch (Exception $e) {
            }
        }

        // Закрыть заявку на вакансияю
        $application = Zend_Registry::get('serviceContainer')->getService('RecruitApplication')->fetchOne(array(
            'vacancy_id = ?' => $vacancy->vacancy_id
        ));
        if ($application) {
            Zend_Registry::get('serviceContainer')->getService('RecruitApplication')->update(
                array(
                    'recruit_application_id' => $application->recruit_application_id,
                    'status' => HM_Recruit_Application_ApplicationModel::STATUS_COMPLETED,
                )
            );
        }

        // возможно, она уже была остановлена в HM_Recruit_Vacancy_Assign_AssignProcess::onProcessComplete()        
        Zend_Registry::get('serviceContainer')->getService('AtSession')->stopSession($vacancy->session_id);
    }

    public function getRedirectionUrl()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $resArray = array('action' => 'list', 'module' => 'recruit', 'controller' => 'vacancy');
        return Zend_Registry::get('view')->url($resArray, null, true);

    }

    public function getStateDatesMode()
    {
        return HM_Process_Abstract::MODE_STATE_DATES_HIDDEN;
    }
}