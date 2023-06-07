<?php
/**
 * Психологические опросы
 * Все данные для формирования анкеты и populate прошлых результатов - в _attempt 
 */
class HM_At_Session_Event_Method_PsychoModel extends HM_At_Session_Event_Method_Quest_Abstract
{
    public $type = HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO;
    
    public function getIcon()
    {
        return 'images/events/4g/64x/test.png'; // @todo: нарисовать отдельную иконку
    }    
}