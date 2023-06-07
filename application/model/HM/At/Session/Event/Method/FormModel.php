<?php
/**
 * Психологические опросы
 * Все данные для формирования анкеты и populate прошлых результатов - в _attempt 
 */
class HM_At_Session_Event_Method_FormModel extends HM_At_Session_Event_Method_Quest_Abstract
{
    public $type = HM_At_Evaluation_EvaluationModel::TYPE_FORM;
    
    public function getIcon()
    {
        return 'images/events/4g/64x/poll.png';
    }
}