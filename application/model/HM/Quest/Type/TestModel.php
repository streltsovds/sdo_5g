<?php
class HM_Quest_Type_TestModel extends HM_Quest_QuestModel
{
     public function getAvailableTypes()
    {
        $types = HM_Quest_Question_QuestionModel::getTypes();
        unset($types[HM_Quest_Question_QuestionModel::TYPE_FREE]);
        unset($types[HM_Quest_Question_QuestionModel::TYPE_FILE]);
        return $types;
    }
    
    public function getAttemptMode()
    {
        // в тестах каждая попытка начинается заново и попыток может быть много
        return HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_MULTIPLE;
    }

    public function getTestType()
    {
        return HM_Test_TestModel::TYPE_TEST;
    }

    /*
     * 5G
     * Implementing HM_Material_Interface
     */
    public function assignToSubject($subjectId)
    {
        return $this->getService()->createLesson($subjectId, $this->quest_id);
    }
}