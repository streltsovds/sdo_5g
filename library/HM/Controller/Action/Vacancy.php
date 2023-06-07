<?php
class HM_Controller_Action_Vacancy extends HM_Controller_Action implements HM_Controller_Action_Interface_Context
{
    use HM_Controller_Action_Trait_Context;

    protected $_vacancyId = null;
    protected $_vacancy = null;
    protected $_vacancyState = null;

    protected $_session = null;
    protected $_application = null;

    public function init()
    {
        parent::init();

        $vacancyId = $this->_vacancyId = $this->_getParam('vacancy_id', 0);
        $applicationId = $this->_getParam('application_id', 0);

        $vacancyCandidateId = $this->_getParam('vacancy_candidate_id', 0);
        if($vacancyCandidateId && $vacancyCandidate = $this->getService('RecruitVacancyAssign')->find($vacancyCandidateId)){
            $vacancyId = $vacancyCandidate->current()->vacancy_id;
        }

        if ($vacancyId) {
            $vacancies = $this->getService('RecruitVacancy')->fetchAllHybrid(array('Session', 'DataFields', 'Evaluation'), 'Recruiter', 'RecruiterAssign', array('vacancy_id = ?' => $vacancyId));
            $this->_vacancy = $vacancies->current();
            if ($this->_vacancy->session) {
                $this->_session = $this->_vacancy->session->current();
            }
        } else if($applicationId){
            $applications = $this->getService('RecruitApplication')->find($applicationId);
            $this->_application = $applications->current();
        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не существует сессия подбора')));
            $this->_redirector->gotoSimple('list', 'list', 'vacancy');
        }

        if ($this->_vacancy) {
            $this->getService('Process')->initProcess($this->_vacancy);
            $this->initContext($this->_vacancy);
            $this->_vacancyState = $this->getService('Process')->getCurrentState($this->_vacancy);

            $this->view->addSidebar('vacancy', [
                'model' => $this->_vacancy,
            ]);
        }
    }
}