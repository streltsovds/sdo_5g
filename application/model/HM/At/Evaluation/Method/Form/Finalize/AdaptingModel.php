<?php
class HM_At_Evaluation_Method_Form_Finalize_AdaptingModel extends HM_At_Evaluation_Method_Form_FinalizeModel implements HM_At_Evaluation_Method_Interface
{
    public function getRespondents($position, $user = null)
    {
        $return = array();
        
//         if (!$position->is_manager) {
//             $return = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->fetchAll(Zend_Registry::get('serviceContainer')->getService('Orgstructure')->quoteInto(array(
//                 'owner_soid = ? AND ', 
//                 'type = ? AND ',
//                 'is_manager = ?',
//             ), array( 
//                 $position->owner_soid,
//                 HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
//                 HM_Orgstructure_Position_PositionModel::ROLE_MANAGER,
//             )))->asArrayOfObjects();
//         } else {
//             // ищем руководителей родительского подразделения
//             $collection = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->fetchAllDependenceJoinInner('Sibling', Zend_Registry::get('serviceContainer')->getService('Orgstructure')->quoteInto(array(
//                 'self.soid = ? AND ', 
//                 'Sibling.type = ? AND ', // не работает 
//                 'Sibling.is_manager = ?', // не работает
//             ), array( 
//                 $position->owner_soid,
//                 HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
//                 HM_Orgstructure_Position_PositionModel::ROLE_MANAGER,
//             )));
//             if (count($collection)) {
//                 $department = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getOne($collection);
//                 // workaround 
//                 foreach ($department->siblings as $sibling) {
//                     if (($sibling->type != HM_Orgstructure_OrgstructureModel::TYPE_POSITION) || ($sibling->is_manager != HM_Orgstructure_Position_PositionModel::ROLE_MANAGER)) continue;
//                     $return[] = $sibling;
//                 }
//             }
//         }
        
        // за finalize отвечают только рекрутёры
        if ($this->newcomer_id) {
            if (count($collection = Zend_Registry::get('serviceContainer')->getService('RecruitNewcomerRecruiterAssign')->fetchAllHybrid('Recruiter', 'Position', 'Recruiter', array('newcomer_id = ?' => $this->newcomer_id)))) {
                foreach ($collection as $recruiterAssign) {
                    if (count($recruiterAssign->position)) {
                        $position = $recruiterAssign->position->current(); // если он ещё и совместитель - не учитываем это
                        $return[] = $position;
                    } elseif (count($recruiterAssign->recruiters)) {
                        $recruiter = $recruiterAssign->recruiters->current();
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
            case HM_Programm_ProgrammModel::TYPE_ADAPTING:
                return true;
            break;
        }
        return false;
    }
    
    public function getQuestId()
    {
        return HM_Quest_QuestModel::BUILTIN_TYPE_FINALIZE_ADAPTING;
    }          
}