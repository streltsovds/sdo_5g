<?php
class HM_Tc_Session_SessionModel extends HM_Model_Abstract
{
    protected $_primaryName = 'session_id';

    const TYPE_TC = 0;
    const TYPE_SC = 1;

    //статусы сессии
    const FINISHED = 1;
    const GOING = 0;

    // статусы сессии в зависимости от состояния Бизнес-процесса
    // эти статусы почему-то проигнорированы; в БД - ГОИНГ
    const STATE_CANCELED = -1;
    const STATE_PENDING = 0;
    const STATE_ACTUAL = 1;
    const STATE_CLOSED = 2;

    public function getServiceName()
    {
        return 'TcSession';
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
            $this->cycle[0] = $this->getService()->getCycle();
        }
        return $this->cycle[0]->name;
    }

    public function getName()
    {
        return sprintf(_('Сессия годового планирования обучения &laquo;%s&raquo;'), $this->name);
    }

    public static function getApplicationsStateMessages()
    {
        return array(
            self::STATE_PENDING  => _('<p style="color:#FF0000">Формирование консолидированной заявки еще не началось</p>'),
            self::STATE_ACTUAL   => '',
            self::STATE_CLOSED   => _('<p style="color:#FF0000">Формирование консолидированной заявки завершено</p>'),
            self::STATE_CANCELED => _('<p style="color:#FF0000">Консолидированная заявка отменена</p>'),
        );
    }

    public static function getApplicationsStateMessage($state)
    {
        $messages = self::getApplicationsStateMessages();
        return isset($messages[$state]) ? $messages[$state] : '';
    }
}