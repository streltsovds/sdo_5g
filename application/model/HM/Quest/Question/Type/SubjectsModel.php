<?php
/*
 * Специальный тип вопроса, для использования в итоговой оценочной форме
 * Позволяет назначить курсы на основании неудачно подтверждённых квалификаций 
 * 
 */
class HM_Quest_Question_Type_SubjectsModel extends HM_Quest_Question_QuestionModel implements HM_Quest_Question_Type_Interface
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
        $subjectIds = unserialize($questionResult->variant);
        
        if (is_array($subjectIds) && count($subjectIds)) {
            $subjects = Zend_Registry::get('serviceContainer')->getService('Subject')->fetchAll(array('subid IN (?)' => $subjectIds));
            foreach ($subjects as $subject) {
                $variant[] = sprintf('<a href="/subject/index/card/subject_id/%d">%s</a>', $subject->subid, $subject->name);
            }
        } 
        if (!empty($questionResult->free_variant)) {
            $variant[] = $questionResult->free_variant;
        }
    
        return implode($delimiter, $variant);
    }    
}