<?php
class HM_At_Session_Respondent_RespondentModel extends HM_Model_Abstract
{
    public function __get($key)
    {
        if ($key == 'progress') {
            $select = Zend_Registry::get('serviceContainer')->getService('AtSessionEvent')->getSelect();
            $select->from('at_session_events', array(
                'filled' => new Zend_Db_Expr('SUM(CASE WHEN status = ' . HM_At_Session_Event_EventModel::STATUS_COMPLETED . ' THEN 1 ELSE 0 END)'),
                'total' => new Zend_Db_Expr('COUNT(session_event_id)'),
                ))
            ->where('session_respondent_id = ?', $this->session_respondent_id)
            ->group('session_respondent_id');
                        
            if ($rowset = $select->query()->fetchAll()) {
                $row = array_shift($rowset);
                return $row['total'] ? round(100 * $row['filled'] / $row['total'], 2) : 0;
            }
        }

        return parent::__get($key);
    }    
}