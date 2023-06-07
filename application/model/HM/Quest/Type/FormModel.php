<?php
class HM_Quest_Type_FormModel extends HM_Quest_QuestModel
{
     public function getAvailableTypes()
    {
        $types = HM_Quest_Question_QuestionModel::getTypes();
        unset($types[HM_Quest_Question_QuestionModel::TYPE_TEXT]); // для любого ввода надо испльзовать TYPE_FREE; иначе не в тему варианты ответов задаются
        return $types;
    }
        
    public function getAttemptMode()
    {
        // в формах одна длинная попытка (при повторном запуске подставляются значения из предыдущего)
        return HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_SINGLE;
    }    
}