<?php class HM_Recruit_Vacancy_AssignRecruiter_AssignRecruiterService extends HM_Service_Abstract
{
    public function assign($vacancyId, $recruiterId)
    {
        $recruiter = $this->getService('Recruiter')->getOne($this->getService('Recruiter')->find($recruiterId));
        $vacancy = $this->getService('RecruitVacancy')->getOne($collection = $this->getService('RecruitVacancy')->fetchAllHybrid(array('Session'), 'Recruiter', 'RecruiterAssign', array('vacancy_id = ?' => $vacancyId)));
        
        if ($vacancy && $recruiter) {
            $recruiterIds = array();
            if (count($vacancy->recruiters)) {
                $existingRecruiter = $vacancy->recruiters->current(); // первый попавшийся
                $recruiterIds = $vacancy->recruiters->getList('recruiter_id');
                if (in_array($recruiterId, $recruiterIds)) return; // уже назначен
            }
            
            $this->insert(array(
                'vacancy_id' => $vacancyId,
                'recruiter_id' => $recruiterId
            ));

            if (
                $existingRecruiter &&
                count($vacancy->session) && 
                ($vacancy->session->current()->state == HM_At_Session_SessionModel::STATE_ACTUAL)
            ) {            
                $session = $vacancy->session->current();
                $this->getService('AtSessionRespondent')->duplicate($existingRecruiter->user_id, $recruiter->user_id, $session->session_id);
            }
        }
    }
    
    public function unassign($vacancyId, $recruiterId)
    {
        $recruiter = $this->getService('Recruiter')->getOne($this->getService('Recruiter')->find($recruiterId));
        $vacancy = $this->getService('RecruitVacancy')->getOne($collection = $this->getService('RecruitVacancy')->fetchAllHybrid(array('Session'), 'Recruiter', 'RecruiterAssign', array('vacancy_id = ?' => $vacancyId)));
        
        if ($vacancy && $recruiter) {
            
            $this->deleteBy(array(
                'vacancy_id = ?' => $vacancyId,        
                'recruiter_id = ?' => $recruiterId,        
            ));
            
            $session = $vacancy->session->current();
            if (count($collection = $this->getService('AtSessionRespondent')->fetchAll(array(
                'user_id = ?' => $recruiter->user_id, 
                'session_id = ?' => $session->session_id, 
            )))) {
                $respondent = $collection->current(); // он может быть только 1
                $this->getService('AtSessionEvent')->deleteBy(array(
                    'session_respondent_id = ?' => $respondent->session_respondent_id,
                    'status != ?' => HM_At_Session_Event_EventModel::STATUS_COMPLETED,
                ));
                $this->getService('AtSessionRespondent')->delete($respondent->session_respondent_id);
            }
        }
    }
}