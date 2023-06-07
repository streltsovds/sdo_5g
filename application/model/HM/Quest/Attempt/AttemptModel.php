<?php
class HM_Quest_Attempt_AttemptModel extends HM_Model_Abstract
{
    const STATUS_NOT_STARTED = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_COMPLETED = 2;
    
    const MODE_ATTEMPT_OFF = 0; // режим preview, результаты не сохраняются
    const MODE_ATTEMPT_SINGLE = 1; // режим одной попытки, при повторном запуске - populate из прошлой попытки
    const MODE_ATTEMPT_MULTIPLE = 2; // много попыток, как в тесте

    const CONTEXT_TYPE_NONE = 0;       // само по себе (напр., preview)
    const CONTEXT_TYPE_ELEARNING = 1;  // занятие в курсе
    const CONTEXT_TYPE_ASSESSMENT = 2; // мероприятие в сессии оценки или подбора
    const CONTEXT_TYPE_FEEDBACK = 3;   // сбор обратной связи
    const CONTEXT_TYPE_WIDGET = 4;   // виджет опрос на главной
    const CONTEXT_TYPE_PROJECT = 5;  // мероприятие в проекте


    protected $_quest;

    // а нужен ли factory? пока никаких отличий в моделях нет..
    static public function factory($data, $default = 'HM_Quest_Attempt_Type_TestModel')
    {
        if (isset($data['type']))
        {
            switch($data['type']) {
                case HM_Quest_QuestModel::TYPE_TEST:
                    return parent::factory($data, 'HM_Quest_Attempt_Type_TestModel');
                    break;
                case HM_Quest_QuestModel::TYPE_POLL:
                    return parent::factory($data, 'HM_Quest_Attempt_Type_PollModel');
                    break;
                case HM_Quest_QuestModel::TYPE_PSYCHO:
                    return parent::factory($data, 'HM_Quest_Attempt_Type_PsychoModel');
                    break;
                case HM_Quest_QuestModel::TYPE_FORM:
                    return parent::factory($data, 'HM_Quest_Attempt_Type_FormModel');
                    break;
            }
        }
        return parent::factory($data, $default);        
    }

    public static function getContextTypes()
    {
        return [
            self::CONTEXT_TYPE_NONE => _('Без типа'),
            self::CONTEXT_TYPE_ELEARNING => _('Занятие в курсе'),
            self::CONTEXT_TYPE_ASSESSMENT => _('Мероприятие в сессии оценки или подбора'),
            self::CONTEXT_TYPE_FEEDBACK => _('Обратная связь'),
            self::CONTEXT_TYPE_WIDGET => _('Виджет опроса на главной'),
            self::CONTEXT_TYPE_PROJECT => _('Мероприятие в проекте'),
        ];
    }

    public static function getContextTypeName($name)
    {
        $types = self::getContextTypes();
        if(isset($types[$name])) {
            return $types[$name];
        } else {
            return '';
        }
    }
    
    static public function getStatuses()
    {
        return array(
            self::STATUS_NOT_STARTED   => _('Не начато'),
            self::STATUS_IN_PROGRESS => _('В процессе'),
            self::STATUS_COMPLETED => _('Закончено'),
        );
    }

    /**
     * Возвращает наименование статуса по его ключу
     * Если ключ некорректный - возвращает пустую строку
     * @static
     * @param $statusKey
     * @return string
     */
    static public function getStatusName($statusKey)
    {
        $statuses = self::getStatuses();
        if (!array_key_exists($statusKey,$statuses)) return '';
        return $statuses[$statusKey];
    }

    public function updateByType()
    {
        return true;
    }
}