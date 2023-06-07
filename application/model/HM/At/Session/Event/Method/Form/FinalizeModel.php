<?php
/**
 * Итоговые мероприятия, специфичные для каждого вида программ
 */
class HM_At_Session_Event_Method_Form_FinalizeModel extends HM_At_Session_Event_Method_FormModel
{
    public $type = HM_At_Evaluation_EvaluationModel::TYPE_FINALIZE;

    public function finalize()
    {
        $questionResults = array();
        if ($collection = Zend_Registry::get('serviceContainer')->getService('QuestAttempt')->fetchAllDependence('QuestionResult', array('context_event_id = ?' => $this->session_event_id))) {
            $attempt = $collection->current();
            if (count($attempt->questionResults)) {
                foreach ($attempt->questionResults as $questionResult) {
                    if ($variantId = (int)$questionResult->variant) {
                        $method = "onQuestionVariant{$variantId}";
                    } else {
                        $method = "onQuestionFreeVariant{$questionResult->question_id}";
                    }
                    if (method_exists($this, $method)) {
                         $this->$method($attempt);
                    }
                    $questionResults[$questionResult->question_id] = $questionResult;
                }
            }
        }
        
        $this->assignSubjects($questionResults);
        $this->assignReservePositions($questionResults);
        
        return true;
    }
    
    public function assignSubjects($questionResults) 
    {
        return true;
    }

    public function assignReservePositions($questionResults)
    {
        return true;
    }
    
}