<?php
class HM_Task_Conversation_ConversationModel extends HM_Model_Abstract
{
    protected $_primaryName = "conversation_id";

    const MESSAGE_TYPE_TASK      = 'task'; //Task
    const MESSAGE_TYPE_QUESTION  = 'question'; //Question
    const MESSAGE_TYPE_TO_PROVE  = 'to_prove'; // to prove
    const MESSAGE_TYPE_ANSWER    = 'answer'; // answer
    const MESSAGE_TYPE_REQUIREMENTS = 'requirements'; //some conditions
    const MESSAGE_TYPE_ASSESSMENT   = 'assessment'; // total ball
    const MESSAGE_TYPE_MESSAGE   = 'message';
    /**
     * Добавлен тип - не выполнено задание, для разграничения Interview без записей
     * @author Artem Smirnov
     * @date 19.02.2013
     */
    const MESSAGE_TYPE_EMPTY     = 'empty'; //Empty

    public function getType(){
        $types = self::getTypes();
        return $types[$this->type];
    }


    public static function getTypes()
    {
        return array(
            self::MESSAGE_TYPE_TASK => _('Выдано задание'),
            self::MESSAGE_TYPE_MESSAGE => _('Простое сообщение'),
            self::MESSAGE_TYPE_QUESTION => _('Вопрос тьютору'),
            self::MESSAGE_TYPE_ANSWER => _('Ответ тьютора'),
            self::MESSAGE_TYPE_TO_PROVE => _('Решение на проверку'),
            self::MESSAGE_TYPE_REQUIREMENTS => _('Требования на доработку'),
            self::MESSAGE_TYPE_ASSESSMENT => _('Выставлена оценка'),
            self::MESSAGE_TYPE_EMPTY => _('Не выполнено')
        );
    }

    public static function getStudentTypes()
    {
        return array(
            //self::MESSAGE_TYPE_TASK => _('Задание'),
            self::MESSAGE_TYPE_MESSAGE => _('Простое сообщение'),
            self::MESSAGE_TYPE_QUESTION => _('Вопрос тьютору'),
            self::MESSAGE_TYPE_TO_PROVE => _('Решение на проверку'),
            //self::MESSAGE_TYPE_ANSWER => _('Ответ'),
            //self::MESSAGE_TYPE_CONDITION => _('Требования на доработку'),
            //self::MESSAGE_TYPE_BALL => _('Комментарии к оценке')
        );
    }

    public static function getTeacherTypes()
    {
        return array(
            //self::MESSAGE_TYPE_TASK => _('Задание'),
            //self::MESSAGE_TYPE_QUESTION => _('Вопрос'),
            //self::MESSAGE_TYPE_TO_PROVE => _('На проверку'),
            self::MESSAGE_TYPE_ANSWER => _('Ответ тьютора'),
            self::MESSAGE_TYPE_REQUIREMENTS => _('Требования на доработку'),
            self::MESSAGE_TYPE_ASSESSMENT => _('Выставлена оценка')
        );
    }

    public function getStyleClass()
    {

    }

    public function getIcon()
    {
        return 'images/content-modules/interview/' . $this->type . '.png';
    }

    public function getDate()
    {
        $date = new Zend_Date();
        $date->set($this->date);

        return $date->toString();

    }
}
