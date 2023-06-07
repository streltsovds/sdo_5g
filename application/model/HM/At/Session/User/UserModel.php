<?php
class HM_At_Session_User_UserModel extends HM_Model_Abstract implements HM_Process_Model_Interface
{
    const STATUS_NOT_STARTED = 0;
    const STATUS_IN_PROGRESS = 1; // DEPRECATED! Теперь есть детализация средствами process
    const STATUS_COMPLETED = 2;
    
    protected $_primaryName = 'session_user_id';
    
    public function getServiceName()
    {
        return 'AtSessionUser';
    } 
        
    static public function getStatuses()
    {
        return array(
            self::STATUS_NOT_STARTED => _('Активный'),        
//             self::STATUS_IN_PROGRESS => _('В процессе'),        
            self::STATUS_COMPLETED => _('Завершивший'),        
        );
    }
    
    static public function getStatus($status)
    {
        $statuses = self::getStatuses();
        return $statuses[(int)$status];
    }
    
    public function getUserId()
    {
        return $this->user_id;
    }
    
    public function getName()
    {
        if (count($collection = Zend_Registry::get('serviceContainer')->getService('User')->find($this->user_id))) {
            return $collection->current()->getName();
        }
        return '';
    }
    

    // дублирован в HM_Recruit_Vacancy_Assign_AssignModel
    public function getProcessStateClass($state)
    {
        $return = '';
        $sessionEventsCompleted = true;
        $countSessionEvents = 0;
        $programmEventId = $state->getProgrammEventId();
        if (count($this->sessionEvents)) {
            $sessionEventExists = false;
            foreach ($this->sessionEvents as $sessionEvent) {
                if (count($sessionEvent->programmEvent)) {
                    if (isset($sessionEvent->programmEvent[$programmEventId])) {
                        $sessionEventsCompleted = $sessionEventsCompleted && ($sessionEvent->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED);
                        $countSessionEvents++;
                        $sessionEventExists = true;
                    }
                }
            }
            
// на случай если понадобятся разные стили на разные методики в bulbs 
//             if (!$sessionEventExists) {
//                 if ($programmEvent = Zend_Registry::get('serviceContainer')->getService('ProgrammEvent')->getOne(Zend_Registry::get('serviceContainer')->getService('ProgrammEvent')->findDependence('Evaluation', $programmEventId))) {
//                     if (count($programmEvent->evaluation)) {
//                         $return .= 'evaluationMethod' . ucfirst($programmEvent->evaluation->current()->method);
//                     }
//                 }
//             }
        } 
        if ($sessionEventsCompleted && $countSessionEvents) $return .= ' sessionEventsCompleted';
        
        return $return;
    }
    
    public function __get($key)
    {
        if ($key == 'progress') {
            $select = Zend_Registry::get('serviceContainer')->getService('AtSessionEvent')->getSelect();
            $select->from('at_session_events', array(
                'filled' => new Zend_Db_Expr('SUM(CASE WHEN status = ' . HM_At_Session_Event_EventModel::STATUS_COMPLETED . ' THEN 1 ELSE 0 END)'),
                'total' => new Zend_Db_Expr('COUNT(session_event_id)'),
                ))
            ->where('session_user_id = ?', $this->session_user_id)
            ->group('session_user_id');
                        
            if ($rowset = $select->query()->fetchAll()) {
                $row = array_shift($rowset);
                return $row['total'] ? round(100 * $row['filled'] / $row['total'], 2) : 0;
            }
        }

        return parent::__get($key);
    }

    public function getReportUrl()
    {
        $view = Zend_Registry::get('view');
        if (count($collection = Zend_Registry::get('serviceContainer')->getService('AtSession')->findDependence(array('Vacancy', 'Newcomer'), $this->session_id))) {
            $session = $collection->current();
            if (count($session->vacancy)) $vacancyId = $session->vacancy->current()->vacancy_id;
            if (count($session->newcomer)) $newcomerId = $session->newcomer->current()->newcomer_id;
            
            switch ($session->programm_type) {
                case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
                    return $view->url(array('baseUrl' => 'at', 'module' => 'session', 'controller' => 'report', 'action' => 'user', 'session_id' => $this->session_id, 'session_user_id' => $this->session_user_id));
                break;
                case HM_Programm_ProgrammModel::TYPE_RECRUIT:
                    return $view->url(array('baseUrl' => 'recruit', 'module' => 'vacancy', 'controller' => 'report', 'action' => 'user', 'vacancy_id' => $vacancyId, 'vacancy_candidate_id' => $this->vacancy_candidate_id));
                break;
                case HM_Programm_ProgrammModel::TYPE_ADAPTING:
                    return $view->url(array('baseUrl' => 'recruit', 'module' => 'newcomer', 'controller' => 'report', 'action' => 'user', 'newcomer_id' => $newcomerId));
                break;
            }     
        }     
    }    
}