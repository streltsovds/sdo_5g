<?php
class HM_Hr_Rotation_RotationModel extends HM_Model_Abstract implements HM_Process_Model_Interface
{
    protected $_primaryName = 'rotation_id';

    // Агрегатные состояния сессии ротации
    const STATE_PENDING = 0;
    const STATE_ACTUAL = 1;
    const STATE_CLOSED = 2;

    const RESULT_FAIL_DEFAULT = -1;
    const RESULT_FAIL_SELF = -2;
    const RESULT_FAIL_MANAGER = -3;
    const RESULT_SUCCESS = 1;
    const RESULT_EXTENDED = 2;

    const PROCESS_STATE_OPEN = 1;
    const PROCESS_STATE_PLAN = 2;
    const PROCESS_STATE_PUBLISH = 3;
    const PROCESS_STATE_RESULT = 4;
    const PROCESS_STATE_COMPLETE = 5;

    const REPORT_NOTIFICATION_NOT_SENT_YET = 0;
    const REPORT_NOTIFICATION_SENT = 1;

    const DEBT_NO = 0;
    const DEBT_YES = 1;

    public function getUserId()
    {
        return $this->user_id; 
    }
        
    public function getName()
    {
        $collection = Zend_Registry::get('serviceContainer')->getService('HrRotation')->findDependence(array('User'), $this->rotation_id);
        if (count($collection)) {
            if (count($collection = $collection->current()->user)) {
                return $collection->current()->getName();
            }
        }
        return sprintf(_('Участник #%s'), $this->user_id);
    }

    static public function getResultStatuses($onlyFail = false)
    {
        $statuses = array(
            self::RESULT_FAIL_DEFAULT => _('Не пройдена'),
            self::RESULT_FAIL_SELF => _('Не пройдена по инициативе сотрудника'),
            self::RESULT_FAIL_MANAGER => _('Не пройдена по инициативе руководителя'),
            self::RESULT_SUCCESS => _('Пройдена'),
            self::RESULT_EXTENDED => _('Продлена'),
        );

        if ($onlyFail) unset($statuses[self::RESULT_SUCCESS]);

        return $statuses;
    }

    static public function getStatuses()
    {
        $statuses = array(
            self::STATE_PENDING => _('Не начата'),
            self::STATE_ACTUAL => _('Идёт'),
            self::STATE_CLOSED => _('Закончена'),
        );
        return $statuses;
    }

    static public function getStatus($status)
    {
        $statuses = self::getStatuses();
        return $statuses[$status];
    }

    static public function getResultStatus($status)
    {
        $statuses = self::getResultStatuses();
        return $statuses[$status];
    }



    static public function getStates()
    {
        $states = array(
            self::PROCESS_STATE_OPEN => _('1'),
            self::PROCESS_STATE_PLAN => _('2'),
            self::PROCESS_STATE_PUBLISH => _('3'),
            self::PROCESS_STATE_RESULT => _('4'),
            self::PROCESS_STATE_COMPLETE => _('Окончена'),
        );
        return $states;
    }

    static public function getState($state)
    {
        $states = self::getStates();
        return $states[$state];
    }


    static public function getDebts()
    {
        $states = array(
            self::DEBT_NO => _('Нет'),
            self::DEBT_YES => _('Есть'),
        );
        return $states;
    }

    static public function getDebt($debt)
    {
        $debts = self::getDebts();
        return $debts[$debt];
    }

    static public function getStateColor($state)
    {
        $colors = array(
            'HM_Hr_Rotation_State_Open' => 'blue',
            'HM_Hr_Rotation_State_Plan' => 'gray',
            'HM_Hr_Rotation_State_Publish' => 'red',
            'HM_Hr_Rotation_State_Result' => 'green'
        );
        return $colors[$state];
    }

}
