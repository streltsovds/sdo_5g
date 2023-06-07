<?php
/**
 * Общая реализация оценочного мероприятия на основе Quest 
 * Вся специфика в helper'е QuestContextEvent 
 */
class Quest_EventController extends HM_Controller_Action_Multipage_Quest
{
    const NAMESPACE_MULTIPAGE = 'event-multipage';

    public function init()
    {
        $this->setNamespaceMultipage(self::NAMESPACE_MULTIPAGE);
        parent::init();
    }

    public function getControllerModel()
    {
        return $this->_persistentModel->getModel();
    }

    public function _redirectToIndex($msg = '', $type = HM_Notification_NotificationModel::TYPE_SUCCESS, $redirectUrl = false)
    {
        if (!empty($msg)) {
            $this->_flashMessenger->addMessage(array(
                'type'    => $type,
                'message' => $msg
            ));
        }
        if (empty($this->_persistentModel)) {
            $this->_persistentModel = $this->_getPersistentModel();
        }

        $contextModel = $this->_persistentModel ? $this->_persistentModel->getContextModel() : 0;
        if (!$this->isAjaxRequest()) {
            // $action = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) ? 'my' : 'list';
            // $this->view->url(array('module' => 'session', 'controller' => 'event', 'action' => $action, 'baseUrl' => 'at', 'session_id' => $contextModel->session_id))
            if($redirectUrl) {
                $this->_redirector->gotoUrl($redirectUrl);
            } elseif($contextModel) {
                $url = $contextModel->getRedirectUrl();
                $this->_redirector->gotoUrl($url);
            }
        }
    }

    public function getIndexUrl()
    {
        $action = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) ? 'my' : 'list';
        $params = array(
            'module' => 'session',
            'controller' => 'event',
            'action' => $action,
            'baseUrl' => 'at',
            'session_id' => $this->_getParam('session_id', NULL)
        );
        return $this->view->url($params);
    }

    public function _redirectToMultipage($msg = '')
    {
        if (!empty($msg)) {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_CRIT,
                'message' => $msg
            ));
        }
        if (!$this->isAjaxRequest()) {
            $this->_redirector->gotoSimple('view', 'event', 'quest', array('quest_id' => $this->_getMultipageId()));
        }
    }

    public function _getBaseUrl()
    {
        return array('module' => 'quest', 'controller' => 'event');
    }

    // сохранять парамы метода при оверрайде для слабых >_<
    public function _getPersistentModel($mode = null, $contextEventId = null, $contextEventType = null)
    {
        $sessionEventId = $this->_getParam('session_event_id');
        $model = parent::_getPersistentModel($mode, $sessionEventId);

        if ($sessionEventId) {
            $event = $this->getService('AtSessionEvent')->getOne(
                $this->getService('AtSessionEvent')->fetchAllHybrid(array('Session', 'SessionUser'), 'User', 'SessionUser', array('session_event_id = ?' => $sessionEventId))
            );
            $model->setContextModel($event);
        }
        if ($event->status != HM_At_Session_Event_EventModel::STATUS_IN_PROGRESS) {
            $this->getService('AtSessionEvent')->updateStatus($event->session_event_id, HM_At_Session_Event_EventModel::STATUS_IN_PROGRESS);
        }

        return $model;
    }

    protected function _isSuspendable()
    {
        $model = $this->_persistentModel->getModel();
        return ($model['quest']->type == HM_Quest_QuestModel::TYPE_TEST) ? false : true;
    }
}