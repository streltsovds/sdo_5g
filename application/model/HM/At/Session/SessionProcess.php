<?php

class HM_At_Session_SessionProcess extends HM_Process_Type_Static
{
    public function onProcessStart(){}
    
    public function getType()
    {
        return HM_Process_ProcessModel::PROCESS_SESSION;
    }

    static public function getStatuses()
    {
        return array(
            self::PROCESS_STATUS_INIT       => _('Сессия создана'),
            self::PROCESS_STATUS_CONTINUING => _('Доступ к оценочным мероприятиям открыт'),
            self::PROCESS_STATUS_COMPLETE   => _('Сессия завершена'),
            self::PROCESS_STATUS_FAILED     => _('Сессия отменена'), // ?
        );
    }
    
    public function onProcessComplete()
    {
        $session = $this->getModel();
        
        if (count($sessionUsers = Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->fetchAll(array('session_id = ?' => $session->session_id)))) {

            foreach ($sessionUsers as $sessionUser) {
                Zend_Registry::get('serviceContainer')->getService('Process')->goToComplete($sessionUser);
            }
        }
        
    }     

    public function getRedirectionUrl()
    {
        return Zend_Registry::get('view')->url(array('module' => 'session', 'controller' => 'list', 'action' => 'index'), null, true);
    }

    public function getStateDatesMode()
    {
        return HM_Process_Abstract::MODE_STATE_DATES_HIDDEN;
    }
}