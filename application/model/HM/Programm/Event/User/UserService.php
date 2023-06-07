<?php
class HM_Programm_Event_User_UserService extends HM_Service_Abstract
{
    public function assign($userId, $event)
    {
        $this->insert(
            array(
                'user_id' => $userId,
                'programm_id' => $event->programm_id,
                'programm_event_id' => $event->programm_event_id,
            )
        );
    }
    
    public function isEvaluationPassed($programmId, $userId, $method)
    {
        if ($programmEvent = $this->getOne($this->getService('ProgrammEvent')->fetchAllDependenceJoinInner('Evaluation', $this->quoteInto(array(
            'self.programm_id = ? AND ',
            'Evaluation.method = ?'
        ), array(
            $programmId,
            $method,
        ))))) {
            if ($programmEventUser = $this->getOne($this->getService('ProgrammEventUser')->fetchAll(array(
                'programm_event_id = ?' => $programmEvent->programm_event_id,
                'user_id = ?' => $userId,
            )))) {
                return $programmEventUser->status == HM_Programm_Event_User_UserModel::STATUS_PASSED;
            }
        }
        return false;
    }
}