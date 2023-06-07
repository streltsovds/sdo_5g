<?php
class HM_Controller_Action_Helper_QuestContextEvent extends Zend_Controller_Action_Helper_Abstract
{
    protected $_event;
    
    public function direct($event)
    {
        $this->_event = $event;
        return $this;
    }
    
    public function info()
    {
        $view = Zend_Registry::get('view');
        if (count($this->_event->user)) {
            $view->user = $this->_event->user->current(); 
        }
        if (count($this->_event->session)) {
            $view->session = $this->_event->session->current(); 
        }

        if ($view->session->programm_type == HM_Programm_ProgrammModel::TYPE_RECRUIT) {

            $sessionId = $view->session->session_id;
            $services = Zend_Registry::get('serviceContainer');
            $vacancy = $services->getService('RecruitVacancy')->fetchAll(
                array('session_id = ?' => $view->session->session_id)
            );

            $vacancy = $vacancy->current();

            $vacancyCandidate = $services->getService('RecruitVacancyAssign')->fetchAll(
                array('vacancy_id = ?' => $vacancy->vacancy_id, 'user_id = ?' => $view->user->MID)
            );
            $vacancyCandidate = $vacancyCandidate->current();

            $candidate = $services->getService('RecruitCandidate')->fetchAllDependence('User',
                array('candidate_id = ?' => $vacancyCandidate->candidate_id)
            );
            $candidate = $candidate->current();

            $view->candidate_id = $vacancyCandidate->candidate_id;
            $view->vacancy_id = $vacancy->vacancy_id;

            $url = $view->url(array(
                'baseUrl' => 'recruit',
                'module' => 'candidate',
                'controller' => 'index',
                'action' => 'resume',
                'candidate_id' => $vacancyCandidate->candidate_id,
            ));

            if (!in_array($candidate->source, array(
                HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER,
                HM_Recruit_Provider_ProviderModel::ID_SUPERJOB
            ))) {

                $path = $services->getService('User')->getPath(Zend_Registry::get('config')->path->upload->resume, $candidate->candidate_id);
                $filePath = $path. $candidate->candidate_id . '.docx';
                if (file_exists($filePath) && is_file($filePath)) {
                    $view->attachment = true;
                } else {
                    return '';
                }
            };

            $view->url = $url;
            $view->candidate = $candidate;
            if (count($candidate->user)) {
                $view->user = $candidate->user->current();
            } else {
                return '';
            }
            if ($this->isAjaxRequest()) {
                return array(
                    'resume_url' => $url,
                    'candidate' => $candidate,
                    'candidate_id' => $view->candidate_id,
                    'vacancy_id' => $view->vacancy_id,
                    'user' => $view->user,
                    'session' => $view->session
                );
            } else {
                return $view->render('context-event/recruit-info.tpl');
            }
        }
        else {
            if ($this->isAjaxRequest()) {
                return array(
                    'user' => $view->user,
                    'session' => $view->session
                );
            } else {
                return $view->render('context-event/info.tpl');
            }
        }

    }
    
    public function finalize($questAttempt)
    {
        $services = Zend_Registry::get('serviceContainer');
        $this->_event->saveQuestResults($questAttempt);
        $services->getService('AtSessionEvent')->updateStatus($this->_event->session_event_id, HM_At_Session_Event_EventModel::STATUS_COMPLETED);
    }
}