<?php
class HM_Tc_SessionQuarter_SessionQuarterModel extends HM_Model_Abstract
{
    protected $_primaryName = 'session_quarter_id';

    const TYPE_TC = 0;
    const TYPE_SC = 1;

    //статусы сессии
    const FINISHED = 1;
    const GOING = 0;
    //статусы сессии в зависимости от состояния Бизнес-процесса
    const STATE_CANCELED = -1;
    const STATE_PENDING = 0;
    const STATE_ACTUAL = 1;
    const STATE_CLOSED = 2;

    const WHOLE_YEAR = 0;
    const QUARTER_1  = 1;
    const QUARTER_2  = 2;
    const QUARTER_3  = 3;
    const QUARTER_4  = 4;



    public function getServiceName()
    {
        return 'TcSessionQuarter';
    }

    static function getStatus($status)
    {
        $states = self::getStatuses();
        return ($states[$status]) ? $states[$status] : '';
    }

    static function getStatuses()
    {
        return array(
            self::GOING => _('Идет'),
            self::FINISHED => _('Окончена'),
        );
    }

    public function getCardFields()
    {
        $dateBegin = new HM_Date($this->date_begin);
        $dateEnd = new HM_Date($this->date_end);
        $fields = array(
            _('Название') => $this->name,
            _('Дата начала')  => $dateBegin->get(HM_Date::DATE_SHORT),
            _('Дата окончания')  => $dateEnd->get(HM_Date::DATE_SHORT),
            _('Статус') => self::getStatus($this->status),
            _('Период планирования') => $this->getCycleName(),

        );
        return $fields;
    }

    public function getCycleName()
    {
        if (!isset($this->cycle[0])) {
            return Zend_Registry::get('serviceContainer')->getService('User')->getService('Cycle')->fetchOne(array('cycle_id = ?' => $this->cycle_id))->name;
        }
        return $this->cycle[0]->name;
    }

    public function getName()
    {
        return sprintf(_('Сессия квартального планирования обучения &laquo;%s&raquo;'), $this->name);
    }

    public static function getApplicationsStateMessages()
    {
        return array(
            self::STATE_PENDING  => _('<p style="color:#FF0000">Редактирование квартального плана подразделения еще не началось</p>'),
            self::STATE_ACTUAL   => '',
            self::STATE_CLOSED   => _('<p style="color:#FF0000">Редактирование квартального плана подразделения завершено</p>'),
            self::STATE_CANCELED => _('<p style="color:#FF0000">Обучение отменено</p>'),
        );
    }

    public static function getApplicationsStateMessage($state)
    {
        $messages = self::getApplicationsStateMessages();
        return isset($messages[$state]) ? $messages[$state] : '';
    }
}