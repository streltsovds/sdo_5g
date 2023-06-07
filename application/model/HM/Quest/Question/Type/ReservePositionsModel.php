<?php
/*
 * Специальный тип вопроса, для использования в итоговой оценочной форме
 * Позволяет назначить должности кадрового резерва
 * 
 */
class HM_Quest_Question_Type_ReservePositionsModel extends HM_Quest_Question_QuestionModel implements HM_Quest_Question_Type_Interface
{
    public function getResult($values)
    {
        $return = array('variant' => array(), 'free_variant' => '');
        foreach ($values as $value) {
            if (!intval($value) && strlen($value)) {
                $return['free_variant'] = trim($value);
            } else {
                $return['variant'][] = $value;
            }
        }
        
        $return['variant'] = serialize(array_unique($return['variant']));
        return $return;
    }
    
    public function displayUserResult($questionResult, $delimiter = HM_Quest_Question_QuestionModel::RESULT_DEFAULT_DELIMITER, $context = HM_Quest_Question_QuestionModel::RESULT_CONTEXT_ATTEMPT)
    {
        $variant = array();
        $reservePositionIds = unserialize($questionResult->variant);
        
        if (is_array($reservePositionIds) && count($reservePositionIds)) {
            $reservePositions = Zend_Registry::get('serviceContainer')->getService('HrReservePosition')->fetchAll(array('reserve_position_id IN (?)' => $reservePositionIds));
            foreach ($reservePositions as $reservePosition) {

                $variant[] = sprintf('<a href="/hr/reserve/position/description/position_id/%d">%s</a>', $reservePosition->reserve_position_id, $reservePosition->name);
            }
        } 
        if (!empty($questionResult->free_variant)) {
            $variant[] = $questionResult->free_variant;
        }
    
        return implode($delimiter, $variant);
    }    
}