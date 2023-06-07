<?php
class HM_At_Evaluation_Method_Form_Finalize_RecruitModel extends HM_At_Evaluation_Method_Form_FinalizeModel implements HM_At_Evaluation_Method_Interface
{
    // дублирован в HM_At_Evaluation_Method_Competence_RecruiterModel 
    public function getRespondents($position, $user = null)
    {
        $positions = array();
        if ($this->vacancy_id) {
            if (count($collection = Zend_Registry::get('serviceContainer')->getService('RecruitVacancyRecruiterAssign')->fetchAllHybrid('Recruiter', 'Position', 'Recruiter', array('vacancy_id = ?' => $this->vacancy_id)))) {
                foreach ($collection as $recruiterAssign) {
                    if (count($recruiterAssign->position)) {
                        $position = $recruiterAssign->position->current(); // если он ещё и совместитель - не учитываем это
                        $positions[] = $position;
                    } elseif (count($recruiterAssign->recruiters)) {
                        $recruiter = $recruiterAssign->recruiters->current();
                        $positions[] = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getDummyPosition($recruiter->user_id);
                    }
                }
            }
        } 

        return $positions;
    }
        
    public function isAvailableForProgramm($programmType)
    {
        switch ($programmType) {
            case HM_Programm_ProgrammModel::TYPE_RECRUIT:
                return true;
            break;
        }
        return false;
    }
    
    public function getQuestId()
    {
        return HM_Quest_QuestModel::BUILTIN_TYPE_FINALIZE_RECRUIT;
    }          
}