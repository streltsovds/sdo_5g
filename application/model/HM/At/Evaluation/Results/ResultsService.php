<?php
class HM_At_Evaluation_Results_ResultsService extends HM_Service_Abstract
{
    public function saveResults($event, $criterionIds, $results)
    {
        if (is_array($criterionIds) && count($criterionIds)) {
            
            $toUpdate = $this->fetchAll(array(
                'session_event_id = ?' => $event->session_event_id,
                'criterion_id IN (?)' => $criterionIds
            ))->getList('criterion_id', 'result_id');
            
            foreach ($results as $criterionId => $valueId) {
                $result = array(
                    'session_event_id' => $event->session_event_id,
                    'session_user_id' => $event->session_user_id,     
                    'relation_type' => $event->evaluation->current()->relation_type,                   
                    'criterion_id' => $criterionId,
                    'value_id' => $valueId,
                    'indicators_status' => HM_At_Evaluation_Results_ResultsModel::INDICATORS_STATUS_FINISHED,
                );
                if (array_key_exists($criterionId, $toUpdate)) {
                    $result['result_id'] = $toUpdate[$criterionId];
                    $this->update($result);
                    unset($toUpdate[$criterionId]);
                } else {
                    $this->insert($result);
                }
            }
            
            if (count($toUpdate)) {
                $this->deleteBy(array('result_id IN (?)' => $toUpdate));
            }
            
            return (count($criterionIds) == count($results));
        }
        return false;
    }
    
    public function saveResultsByLessons($sessionEvent, $method)
    {
        $toUpdate = $this->fetchAll(array(
            'session_event_id = ?' => $sessionEvent->session_event_id,
        ))->getList('criterion_id', 'result_id');
                    
        $sessionEventLessons = $this->getService('AtSessionEventLesson')->fetchAll(array(
            'session_event_id = ?' => $sessionEvent->session_event_id,        
        )); 
        if (count($sessionEventLessons)) {
            $lessonCriteria = $sessionEventLessons->getList('lesson_id', 'criteria');
            $lessonAssigns = $this->getService('LessonAssign')->fetchAll(array(
                'SHEID IN (?)' => array_keys($lessonCriteria),        
                'MID = ?' => $sessionEvent->user_id,        
            ));
            foreach ($lessonAssigns as $lessonAssign) {
                if ($lessonAssign->V_STATUS != HM_Scale_Value_ValueModel::VALUE_NA) {
                    if (is_array($criteria = unserialize($lessonCriteria[$lessonAssign->SHEID]))) {
                        foreach ($criteria as $criterionId) {
                            $result = array(
                                'session_event_id' => $sessionEvent->session_event_id,
                                'session_user_id' => $sessionEvent->session_user_id,     
                                'criterion_id' => $criterionId,
                                'value_id' => (int)$lessonAssign->V_STATUS,
                            ); 
                            
                            if (array_key_exists($criterionId, $toUpdate)) {
                                $this->update($result);
                            } else {
                                $this->insert($result);
                            }                            
                        }
                    }
                }
            }
        }
    }
    
    public function getSameResults($event, $scaleValueId)
    {
        $return = array();
        if (count($event->position)) {
            
            $positions = $this->getService('Orgstructure')->getDescendants($event->position->current()->owner_soid, true);
            if (count($positions)) {
                
                $events = $this->getService('AtSessionEvent')->fetchAllDependence(array('SessionEventUser', 'EvaluationResult'), array(
                    'position_id IN (?)' => $positions,        
                    'evaluation_id = ?' => $event->evaluation_id,        
                    'session_id = ?' => $event->session_id,        
                ));
                
                if ($scaleValueId && ($resultsTotal = count($events))) {
                    foreach ($events as $event) {
                        if (count($event->evaluationResults) && count($event->user)) {
                            $result = $event->evaluationResults->current();
                            if ($result->value_id == $scaleValueId) {
                                $resultsWitValue[$result->criterion_id][] = $event->user->current();                        
                            }
                        }
                    }                    
                }
            }
            
        }        
    }
}