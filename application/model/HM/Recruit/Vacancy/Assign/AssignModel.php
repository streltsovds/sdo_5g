<?php 
class HM_Recruit_Vacancy_Assign_AssignModel extends HM_Model_Abstract implements HM_Process_Model_Interface
{
    const STATUS_ACTIVE = 0;
    const STATUS_HOLD_ON = 1; // отклик
    const STATUS_PASSED = 2; // прошел
    
    const RESULT_FAIL_DEFAULT = -1;
    const RESULT_FAIL_RESERVE = -2;
    const RESULT_FAIL_BLACKLIST = -3;
    const RESULT_SUCCESS = 1;

    protected $_primaryName = 'vacancy_candidate_id';

    const SALT = 's0meSEkrETc0de';

    public function getServiceName()
    {
        return 'RecruitVacancyAssign';
    }     

    public function getUserId()
    {
        return $this->user_id; 
    }
    
    public function getName()
    {
        if (count($collection = Zend_Registry::get('serviceContainer')->getService('RecruitCandidate')->findDependence('User', $this->candidate_id))) {
            if (count($collection = $collection->current()->user)) {
                return $collection->current()->getName();
            }
        }
        return sprintf(_('Кандидат #%s'), $this->candidate_id);
    }
    
    static public function getFullStatuses()
    {
        $statuses = array(
            self::STATUS_HOLD_ON => _('Отклик'),
            self::STATUS_ACTIVE => _('Активный'),
            implode('_', array(self::STATUS_PASSED, self::RESULT_FAIL_DEFAULT)) => _('Отклонён'),
            implode('_', array(self::STATUS_PASSED, self::RESULT_FAIL_BLACKLIST)) => _('Чёрный список'),
            implode('_', array(self::STATUS_PASSED, self::RESULT_FAIL_RESERVE)) => _('Кадровый резерв'),
            implode('_', array(self::STATUS_PASSED, self::RESULT_SUCCESS)) => _('Рекомендован'),
        );
        
        return $statuses;
    }

    static public function extractFullStatus($fullstatus)
    {
        list($status, $result) = explode('_', $fullstatus);
        if (empty($result)) $result = 0;
        return array($status, $result);
    }

    static public function getResultStatuses($onlyFail = false)
    {
        $statuses = array(
            self::RESULT_FAIL_DEFAULT => _('Отклонён'),
            self::RESULT_FAIL_BLACKLIST => _('Чёрный список'),
            self::RESULT_FAIL_RESERVE => _('Кадровый резерв'),
            self::RESULT_SUCCESS => _('Рекомендован'),
        );

        if ($onlyFail) unset($statuses[self::RESULT_SUCCESS]);

        return $statuses;
    }

    static public function getResultStatus($status)
    {
        $statuses = self::getResultStatuses();
        return $statuses[$status];
    }

    static public function getStatuses()
    {
        $statuses = array(
            self::STATUS_HOLD_ON => _('Отклик'),
            self::STATUS_ACTIVE => _('Активный'),
            self::STATUS_PASSED => _('Завершивший'),
        );
        return $statuses;
    }

    static public function getStatus($status)
    {
        $statuses = self::getStatuses();
        return $statuses[$status];
    }
    
    public function getProcessStateClass($state)
    {
        $sessionEventsCompleted = true;
        $countSessionEvents = 0;
        $programmEventId = $state->getProgrammEventId();
        if (count($this->sessionEvents)) {
            foreach ($this->sessionEvents as $sessionEvent) {
                if (count($sessionEvent->programmEvent)) {
                    if (isset($sessionEvent->programmEvent[$programmEventId])) {
                        $sessionEventsCompleted = $sessionEventsCompleted && ($sessionEvent->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED);
                        $countSessionEvents++;
                    }
                }
            }
        }



        $result = '';
        if ($sessionEventsCompleted && $countSessionEvents) {
            if (null !== $this->result) {

                $result = ($this->result == static::RESULT_SUCCESS ? 'complete' : 'failed');

            } else {
                $result = 'sessionEventsCompleted';
            }
        }

        return $result;

    }
}