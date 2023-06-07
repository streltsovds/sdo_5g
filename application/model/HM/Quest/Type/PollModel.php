<?php
class HM_Quest_Type_PollModel extends HM_Quest_QuestModel
{
    const QUESTIONS_TYPE_MANUAL = 0;
    const QUESTIONS_TYPE_SCALE  = 1;


    const DISPLAYMODE_VERTICAL = 0;
    const DISPLAYMODE_HORIZONTAL = 1;

    const DISPLAYCOMMENT_NO = 0;
    const DISPLAYCOMMENT_YES = 1;

    public static function getPollQuestionsTypes()
    {
        return array(
            self::QUESTIONS_TYPE_MANUAL => _('Задать варианты ответа вручную'),
            self::QUESTIONS_TYPE_SCALE => _('Использовать шкалу оценивания'));
    }


    public static function getDisplayPositionModes()
    {
        return array(
            self::DISPLAYMODE_VERTICAL => _('Вертикально'),
            self::DISPLAYMODE_HORIZONTAL => _('Горизонтально'),
        );
    }

    public function getAvailableTypes()
    {
        $types = HM_Quest_Question_QuestionModel::getTypes();
        $return = array(
            HM_Quest_Question_QuestionModel::TYPE_SINGLE   => $types[HM_Quest_Question_QuestionModel::TYPE_SINGLE],
            HM_Quest_Question_QuestionModel::TYPE_MULTIPLE => $types[HM_Quest_Question_QuestionModel::TYPE_MULTIPLE],
            HM_Quest_Question_QuestionModel::TYPE_FREE => $types[HM_Quest_Question_QuestionModel::TYPE_FREE],
        );
        if ($this->scale_id) {
            unset($return[HM_Quest_Question_QuestionModel::TYPE_MULTIPLE]);
//            unset($return[HM_Quest_Question_QuestionModel::TYPE_FREE]);
        }
        return $return;
    }



    public function getAttemptMode()
    {
        return HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_SINGLE;
    }

    public function getQuestContext($contextEventType = HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_NONE)
    {
        return array(
            'context_type' => $contextEventType,
            'context_event_id' => $this->quest_id
        );
    }



}