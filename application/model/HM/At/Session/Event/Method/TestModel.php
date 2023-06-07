<?php
/**
 * Профтестирование
 * Все данные для формирования анкеты и populate прошлых результатов - в _attempt 
 */
class HM_At_Session_Event_Method_TestModel extends HM_At_Session_Event_Method_Quest_Abstract
{
    public $type = HM_At_Evaluation_EvaluationModel::TYPE_TEST;
    
    protected $_questAttempts;
    
    public function setAttempt()
    {
        $this->_attempt = Zend_Registry::get('serviceContainer')->getService('AtSessionEventAttempt')->insert(array(
            'method' => $this->method,
            'session_event_id' => $this->session_event_id,
            'user_id' => Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(),  
            'date_begin' => HM_Date::now()->toString('yyyy-MM-dd HH:mm:ss'),  
        ));
        if ($this->status != HM_At_Session_Event_EventModel::STATUS_IN_PROGRESS) {
            Zend_Registry::get('serviceContainer')->getService('AtSessionEvent')->update(array(
                'status' => HM_At_Session_Event_EventModel::STATUS_IN_PROGRESS,
                'session_event_id' => $this->session_event_id,                          
            ));
        }    
        $this->_attempt->setupModel($this);
        return $this; 
    }

    public function getResults()
    {
        $return = array();
        $results = Zend_Registry::get('serviceContainer')->getService('AtEvaluationResults')->fetchAllDependence(array('ScaleValue', 'Criterion'), array('session_event_id = ?' => $this->session_event_id));
        if (count($results)) {
            foreach ($results as $result) {
                if(is_object($result->scale_value) && is_object($result->criterion)){
                    $return[$result->criterion_id] = array(
                        'value' => $result->scale_value->current()->value,        
                        'criterion' => $result->criterion->current()->name,        
                        'indicators_status' => $result->indicators_status,        
                    );
                }
            }
        }
        return $return;
    }
    
    public function saveQuestResults($questAttempt)
    {
        if ($this->criterion_id) {
            Zend_Registry::get('serviceContainer')->getService('AtSessionUserCriterionValue')->deleteBy(array(
                'criterion_id = ?' => $this->criterion_id,
                'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL,
                'session_user_id = ?' => $this->session_user_id,
            ));
            
            $result = Zend_Registry::get('serviceContainer')->getService('AtSessionUserCriterionValue')->insert(array(
                'criterion_id' => $this->criterion_id,
                'criterion_type' => HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL,
                'session_user_id' => $this->session_user_id,
                'value' => round(100 * $questAttempt->score_weighted),
            ));
        }
    }
    
    public function isExecutable()
    {
        $return = true;
        $this->initQuest();
        if ($this->_quest->limit_attempts) {
            if ($this->_quest->limit_attempts <= count($this->_questAttempts)) {
                $return = false;
            }
        }
        return $return && parent::isExecutable();
    }    
    
    public function isReportAvailable()
    {    
        $this->initQuest();
        if (!$this->_quest->show_log) return false;
        return parent::isReportAvailable();
    }
    
    public function getMessages()
    {
        $return = array();
        $this->initQuest();
        if ($this->_quest->limit_time) {
            $return[] = sprintf(_('Ограничение времени выполнения: %s мин.'), $this->_quest->limit_time);
        }
        if ($this->_quest->limit_attempts) {
            $return[] = sprintf(_('Ограничение количества попыток: %s; израсходовано: %s'), $this->_quest->limit_attempts, count($this->_questAttempts));
        }
        return array_merge($return, parent::getMessages());
    }
    
    public function getIcon()
    {
        return 'images/events/4g/64x/test.png';
    }
    
}