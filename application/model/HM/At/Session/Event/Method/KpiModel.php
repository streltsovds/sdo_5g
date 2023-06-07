<?php
/**
 * Анкета KPI
 * Все данные для формирования анкеты и populate прошлых результатов - в _attempt 
 */
class HM_At_Session_Event_Method_KpiModel extends HM_At_Session_Event_EventModel
{
    public $type = HM_At_Evaluation_EvaluationModel::TYPE_KPI;

    protected $_attempt; 

    public function init()
    {
    }
    
    public function isValid()
    {
        return true;
    }
    
    public function getAttempt()
    {
        return $this->_attempt;    
    }
    
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
                $return[$result->criterion_id] = array(
                    'value' => $result->scale_value->current()->value,        
                    'criterion' => $result->criterion->current()->name,        
                    'indicators_status' => $result->indicators_status,        
                );
            }
        }
        return $return;
    }
    
    public function isReportAvailable()
    {
        return true; //false; // просто пока не реализована отчётная форма
    }    
}