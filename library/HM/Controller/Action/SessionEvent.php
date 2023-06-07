<?php
/**
 * Для сложных видов оценки, запускаемых через отдельный контроллер (не через event/index/run) 
 */
class HM_Controller_Action_SessionEvent extends HM_Controller_Action
{
    protected $_event; 

    public function init()
    {
        $sessionEventId = $this->_getParam('session_event_id');
        
        if ($event = $this->getService('AtSessionEvent')->getOne(
            $this->getService('AtSessionEvent')->findDependence(array('User', 'Position', 'Session', 'SessionUser', 'SessionEventUser', 'SessionEventCouple', 'SessionEventRespondent', 'Evaluation', 'EvaluationResult', 'EvaluationIndicator', 'EvaluationMemoResult'), $sessionEventId))
        ) {
            $this->_event = $event;
            // important!!
            $this->_event->setAttempt();  

        } else {
            $this->_flashMessenger->addMessage(array('message' => _('Не найдено мероприятие'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirect('/session/event/my/session_id/' . $event->session_id);
            return true;
        }
        parent::init();    
    }
}