<?php
class HM_Quest_Question_Type_FreeModel extends HM_Quest_Question_QuestionModel implements HM_Quest_Question_Type_Interface
{
    public function getResult($value)
    {
        return array('free_variant' => $value);
    }
    
    public function isCorrect($value)
    {
    }     
    

    public function displayUserResult($questionResult, $delimiter = HM_Quest_Question_QuestionModel::RESULT_DEFAULT_DELIMITER, $context = HM_Quest_Question_QuestionModel::RESULT_CONTEXT_ATTEMPT)
    {
        return $questionResult->free_variant;
    }    
}