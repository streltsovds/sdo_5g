<?php
class HM_At_Evaluation_Method_Form_Finalize_ReserveModel extends HM_At_Evaluation_Method_Form_FinalizeModel implements HM_At_Evaluation_Method_Interface
{
    public function getRespondents($position, $user = null)
    {
        $return = array();

        // за finalize отвечают только hr'ы
        if ($this->reserve_id) {
            if (count($collection = Zend_Registry::get('serviceContainer')->getService('HrReserveAssignRecruiter')->fetchAllHybrid('Recruiter', 'Position', 'Recruiter', array('reserve_id = ?' => $this->reserve_id)))) {
                foreach ($collection as $recruiterAssign) {
                    if (count($recruiterAssign->position)) {
                        $position = $recruiterAssign->position->current(); // если он ещё и совместитель - не учитываем это
                        $return[] = $position;
                    } elseif (count($recruiterAssign->recruiter)) {
                        $recruiter = $recruiterAssign->recruiter->current();
                        $return[] = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getDummyPosition($recruiter->user_id);
                    }
                }
            }
        }
        
        return $return;
    }
        
    public function isAvailableForProgramm($programmType)
    {
        switch ($programmType) {
            case HM_Programm_ProgrammModel::TYPE_RESERVE:
                return true;
            break;
        }
        return false;
    }
    
    public function getQuestId()
    {
        return HM_Quest_QuestModel::BUILTIN_TYPE_FINALIZE_RESERVE;
    }          
}