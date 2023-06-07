<?php
class HM_At_Evaluation_Results_IndicatorService extends HM_Service_Abstract
{
    /**
     * Сохранение непосредственно результатов по индикаторам
     * 
     */
    public function saveResults($event, $criterionIndicatorIds, $results)
    {
        $indicatorIds = $indicator2criterion = array();
        foreach ($criterionIndicatorIds as $criterionId => $indicators) {
            $indicatorIds = array_merge($indicatorIds, $indicators);
            foreach ($indicators as $indicatorId) {
                $indicator2criterion[$indicatorId] = $criterionId;
            }
        }
        if (is_array($indicatorIds) && count($indicatorIds)) {
            
            $toUpdate = $this->fetchAll(array(
                'session_event_id = ?' => $event->session_event_id,
                'indicator_id IN (?)' => $indicatorIds
            ))->getList('indicator_id', 'indicator_result_id');
            
            foreach ($results as $indicatorId => $valueId) {
                $result = array(
                    'session_event_id' => $event->session_event_id,
                    'session_user_id' => $event->session_user_id,
                    'relation_type' => $event->evaluation->current()->relation_type,
                    'criterion_id' => $indicator2criterion[$indicatorId],
                    'indicator_id' => $indicatorId,
                    'value_id' => $valueId
                );
                if (array_key_exists($indicatorId, $toUpdate)) {
                    $result['indicator_result_id'] = $toUpdate[$indicatorId];
                    $this->update($result);
                    unset($toUpdate[$indicatorId]);
                } else {
                    $this->insert($result);
                }
            }
            
            if (count($toUpdate)) {
                $this->deleteBy(array('indicator_result_id IN (?)' => $toUpdate));
            }
            return (count($indicatorIds) == count($results));
        }
        return false;
    }

     /**
     * Пересчет результатов по индикаторам в результаты по компетенциям
     * 
     */
    public function saveTotalResults($event, $scaleId, $criterionIndicatorIds, $results)
    {
        $return = array();
        $toUpdate = $this->getService('AtEvaluationResults')->fetchAll(array('session_event_id = ?' => $event->session_event_id))->getList('criterion_id', 'result_id');

        foreach ($criterionIndicatorIds as $clusterId => $criteria) {
            foreach ($criteria as $criterionId => $indicators) {
                
                $result = array(
                    'session_event_id' => $event->session_event_id,
                    'session_user_id' => $event->session_user_id,  
                    'relation_type' => $event->evaluation->current()->relation_type,                      
                    'criterion_id' => $criterionId,
                );
                                
                $total = $count = $countFilled = $percent = $value = 0;
                foreach ($indicators as $indicatorId) {
                    if (isset($results[$clusterId][$indicatorId])) {
                        $value = HM_Scale_Converter::getInstance()->id2value($results[$clusterId][$indicatorId], $scaleId);
                        if ($value != HM_Scale_Value_ValueModel::VALUE_NA) {
                            $total += $value;
                            $count++;                            
                        }
                        $countFilled++;
                    }
                }
                $value = $count ? round($total/$count) : HM_Scale_Value_ValueModel::VALUE_NA; // и мильоны пользователей говорят спасибо что не floor.) 
                $progress = count($indicators) ? $countFilled/count($indicators) : 0;
                
                $result['value_id'] = HM_Scale_Converter::getInstance()->value2id($value, $scaleId);
                
                // кэшируем кол-во отмеченных индикаторов чтоб каждый раз не вычислять 
                switch ($progress) {
                    case 0:
                        $result['indicators_status'] = HM_At_Evaluation_Results_ResultsModel::INDICATORS_STATUS_NOT_STARTED;
                    break;
                    case 1:
                        $result['indicators_status'] = HM_At_Evaluation_Results_ResultsModel::INDICATORS_STATUS_FINISHED;
                    break;
                    default:
                        $result['indicators_status'] = HM_At_Evaluation_Results_ResultsModel::INDICATORS_STATUS_IN_PROGRESS;
                    break;
                }
                
                $return[$criterionId] = array(
                    'value' => ($result['indicators_status'] == HM_At_Evaluation_Results_ResultsModel::INDICATORS_STATUS_NOT_STARTED) ? '-' : $value,
                    'indicators_status' => $result['indicators_status'],
                );
                
                if (array_key_exists($criterionId, $toUpdate)) {
                    $result['result_id'] = $toUpdate[$criterionId];
                    $this->getService('AtEvaluationResults')->update($result);
                    unset($toUpdate[$criterionId]);
                } else {
                    $this->getService('AtEvaluationResults')->insert($result);
                }
            }        
        }
        return $return;        
    }
}