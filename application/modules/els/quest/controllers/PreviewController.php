<?php
class Quest_PreviewController extends HM_Controller_Action_Multipage_Quest
{
    const NAMESPACE_MULTIPAGE = 'preview-multipage';
    
    public function init()
    {
        $this->setNamespaceMultipage(self::NAMESPACE_MULTIPAGE);   
        parent::init();
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
            $this->_redirector->gotoSimple('view', 'preview', 'quest', array('quest_id' => $this->_getMultipageId()));
        }
    }
    
    public function _redirectToIndex($msg = '', $type = HM_Notification_NotificationModel::TYPE_SUCCESS, $redirectUrl = false)
    {
        if (!empty($msg)) {
            $this->_flashMessenger->addMessage(array(
                'type'    => $type,
                'message' => $msg
            ));
        }
        if (!$this->isAjaxRequest()) {

            if($redirectUrl) {
                $this->_redirector->gotoUrl($redirectUrl);
            }  else {
                $this->_redirector->gotoUrl($this->_persistentModel->getRedirectUrl());
                //$this->_redirector->gotoSimple('card', 'index', 'quest', array('quest_id' => $this->_getMultipageId()));
            }
        }
    }

    public function _getBaseUrl()
    {
        return array('module' => 'quest', 'controller' => 'preview');
    }    
    
    public function _getPersistentModel(
        $mode = HM_Quest_Attempt_AttemptModel::MODE_ATTEMPT_OFF,
        $contextEventId = null,
        $contextEventType = null
    ) {
        return parent::_getPersistentModel($mode, $contextEventId, $contextEventType);
    }

    public function _isFinalizeable($totalResults)
    {
        return false; // это preview, нечего финализировать
    } 
}