<?php
class HM_Quest_Type_PsychoModel extends HM_Quest_QuestModel
{
    const FORM_ID_KETTELL   = 60;
    const FORM_ID_CPI       = 65;
    const FORM_ID_MMIL      = 66;

    public static function getTypes()
    {
        return array(
            HM_Quest_Type_PsychoModel::FORM_ID_KETTELL => _('Тест Кеттела'),
            HM_Quest_Type_PsychoModel::FORM_ID_CPI => _('Калифорнийский психологический опросник (CPI)'),
            HM_Quest_Type_PsychoModel::FORM_ID_MMIL => _('Методика многостороннего исследования личности (ММИЛ)'),
        );
    }

    public function getAvailableTypes()
    {
        $types = HM_Quest_Question_QuestionModel::getTypes();
        //#21072 - Необходимо чтобы было 3 типа: одиночный выбор, множественный выбор и прикрепление файла
        // поэтому ансет остального
        unset($types[HM_Quest_Question_QuestionModel::TYPE_TEXT]);
        unset($types[HM_Quest_Question_QuestionModel::TYPE_FREE]);
        unset($types[HM_Quest_Question_QuestionModel::TYPE_IMAGEMAP]);
        // unset($types[HM_Quest_Question_QuestionModel::TYPE_MAPPING]);
        // unset($types[HM_Quest_Question_QuestionModel::TYPE_CLASSIFICATION]);
        // unset($types[HM_Quest_Question_QuestionModel::TYPE_SORTING]);

        return $types;
    }
    
    public function getAttemptMode()
    {
        // в псих.опросах одна длинная попытка (при повторном запуске подставляются значения из предыдущего)
        return HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_SINGLE;
    }    
}