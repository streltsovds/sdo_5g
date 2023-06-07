<?php
/**
 * Психологические опросы
 * Все данные для формирования анкеты и populate прошлых результатов - в _attempt 
 */
class HM_At_Session_Event_Method_Quest_Abstract extends HM_At_Session_Event_EventModel
{
    protected $_attempt; 
    
    protected $_quest; 
    protected $_questAttempts; 

    public function init()
    {
    }
    
    public function initQuest()
    {
        if (!isset($this->_quest)) {
            if (is_a($this->quest, 'HM_Collection') && count($this->quest)) {
                $this->_quest = $this->quest->current();
                if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER))) {
                    $this->_questAttempts = Zend_Registry::get('serviceContainer')->getService('QuestAttempt')->fetchAll(array(
                        'quest_id = ?' => $this->_quest->quest_id,
                        'user_id = ?' => Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(),
                        'context_event_id = ?' => $this->session_event_id,
                        'context_type = ?' => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ASSESSMENT,
                    ));
                }           
            }           
        }           
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
        return $this; 
    }

    public function getResults()
    {
        return $return;
    }
    
    public function isReportAvailable()
    {
        return true;
    }        
}