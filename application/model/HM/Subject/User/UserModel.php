<?php
class HM_Subject_User_UserModel extends HM_Model_Abstract implements HM_Rest_Interface
{    
    const SUBJECT_USER_CLAIMANT = 0;
    const SUBJECT_USER_STUDENT = 1;
    const SUBJECT_USER_GRADUATED = 2;
    const SUBJECT_USER_TEACHER = 3;
    const SUBJECT_USER_REJECTED = 4;

    protected $_subject;

    protected $_assignment;

    public static function getLearningStatuses()
    {
        return [
            self::SUBJECT_USER_CLAIMANT => _('Заявка подана'),
            self::SUBJECT_USER_STUDENT => _('В процессе'),
            self::SUBJECT_USER_GRADUATED => _('Пройден'),
            // Видимо пока что не нужно
            // self::SUBJECT_USER_TEACHER => _('Преподаватель'),
            self::SUBJECT_USER_REJECTED => _('Заявка отклонена'),
        ];
    }

    public function init()
    {
        if (!isset($this->subject) || !count($this->subject)) return null;

        $this->_subject = $subject = $this->subject->current();
        $sameSubjectClosure = function($item) use ($subject){
            return $item->CID == $subject->subid;
        };

        if (isset($this->status)) {
            switch($this->status) {
                case self::SUBJECT_USER_CLAIMANT:
                    $collection = $this->claimant;
                    break;
                case self::SUBJECT_USER_STUDENT:
                    $collection = $this->student;
                    break;
                case self::SUBJECT_USER_GRADUATED:
                    $collection = $this->graduated;
                    break;
            }

            if (!$collection || !count($collection)) return null;

            $collection->filter($sameSubjectClosure);
            if (count($collection)) {
                $this->setAssignment($collection->current());
            } else {
                return null;
            }
        }
        return $this;
    }

    /**
     */
    public function getAssignment()
    {
        return $this->_assignment;
    }

    /**
     */
    public function setAssignment($assignment)
    {
        $this->_assignment = $assignment;
        return $this;
    }

    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     *  Пробрасываем ниже, т.к. там тоже нужен cache
     * @param null $collection
     */
    public function setCollection(HM_Collection_Abstract $collection)
    {
        $this->_collection = $collection;
        $this->_assignment->setCollection($collection);
        $this->_subject->setCollection($collection);
        return $this;
    }

    public function __call($method, $args)
    {
        $subject = $this->subject->current();
        $serviceName = $this->_assignment->getServiceName();
        $service = Zend_Registry::get('serviceContainer')->getService($serviceName);
        if (method_exists($service, $method)) {
            return $service->$method($this->_assignment, $subject);
        }
    }

    public function getBegin($time = false)
    {
        if (!strtotime($this->begin))
            return '';

        $return = $this->date($this->begin);
        if ($time)
            $return .= ' ' . $this->time($this->begin);

        return $return;
    }

    public function getEnd($time = false)
    {
        if (!strtotime($this->end))
            return '';

        $return = $this->date($this->end);
        if ($time)
            $return .= ' ' . $this->time($this->end);

        return $return;
    }

    public function getRestDefinition()
    {
        return [
            'subjectId' => (int)$this->subject_id,
            'userId' => (int)$this->user_id,
            'date_begin' => (string)$this->getBegin(true),
            'date_end' => (string)$this->getEnd(true),
            'status' => (string)$this->getRestStatus(),
            'result' => '__todo__'
        ];
    }

    private function getRestStatus($status = null)
    {
        $periods = self::getRestStatuses();

        if ($status == NULL) {
            $status = $this->status;
        }

        return $periods[$status];
    }

    private function getRestStatuses()
    {
        return [
            self::SUBJECT_USER_CLAIMANT => 'application_active',
            self::SUBJECT_USER_CLAIMANT => 'application_confirmed',
            self::SUBJECT_USER_CLAIMANT => 'application_declined',
            self::SUBJECT_USER_STUDENT => 'active',
            self::SUBJECT_USER_GRADUATED => 'passed',
        ];
    }


}