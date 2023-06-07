<?php
class HM_Programm_Event_User_UserModel extends HM_Model_Abstract
{
    const STATUS_NOT_STARTED = 0;
    const STATUS_CONTINUING = 1;
    const STATUS_PASSED = 2;
    const STATUS_FAILED = 3;

    protected $_primaryName = 'programm_event_user_id';
    
    public function getServiceName()
    {
        return 'ProgrammEventUser';
    }

    static public function getTitle($status)
    {
        $statuses = array(
            self::STATUS_NOT_STARTED => _('Не начат'),
            self::STATUS_CONTINUING => _('В процессе'),
            self::STATUS_PASSED => _('Завершен'),
            self::STATUS_FAILED => _('Прерван'),
        );

        return $statuses[$status] ? : '';
    }
}