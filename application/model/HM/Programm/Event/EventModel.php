<?php
class HM_Programm_Event_EventModel extends HM_Model_Abstract
{
    const EVENT_TYPE_AT = 0; // виды оценки (Evaluation)
    const EVENT_TYPE_SUBJECT = 1; // учебные курсы и сессии
    const EVENT_TYPE_AGREEMENT = 2; // согласующие лица
    const EVENT_TYPE_PROJECT = 11; // Прожекты

    const EVENT_HIDDEN_NO = 0;
    const EVENT_HIDDEN_YES = 1;

    const SPECIAL_EVENT_FINALIZE = 'finalize';

    protected $_primaryName = 'programm_event_id';
    
    public function getServiceName()
    {
        return 'ProgrammEvent';
    } 
        
    static public function factory($data, $default = 'HM_Programm_Event_EventModel')
    {
        if (isset($data['type']))
        {
            switch($data['type']) {
                case self::EVENT_TYPE_AT:
                    return parent::factory($data, 'HM_Programm_Event_Type_AtModel');
                    break;
                case self::EVENT_TYPE_SUBJECT:
                    return parent::factory($data, 'HM_Programm_Event_Type_SubjectModel');
                    break;
                case self::EVENT_TYPE_AGREEMENT:
                    return parent::factory($data, 'HM_Programm_Event_Type_AgreementModel');
                    break;
            }
            return parent::factory($data, $default);

        }
    }


    static $_programmCacahe = array();



}