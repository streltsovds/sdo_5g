<?php
/**
 * Общая реализация оценочного мероприятия на основе Quest 
 * Вся специфика в helper'е QuestContextFeedback
 */
class Quest_FeedbackController extends HM_Controller_Action_Multipage_Quest
{
    const NAMESPACE_MULTIPAGE = 'feedback-multipage';
    
    public function init()
    {
        $this->setNamespaceMultipage(self::NAMESPACE_MULTIPAGE);   
        parent::init();
    }
    

    public function externalAction() {
        $hash = $this->_getParam('hash',null);
        if ($hash) {
            $feedback = $this->getService('Feedback')->getOne(
                $this->getService('Feedback')->fetchAll
                    (
                        array(
                            'assign_anonymous_hash = ?' => $hash,
                            'assign_anonymous = ?' => HM_Feedback_FeedbackModel::ASSIGN_ANONYMOUS_ALLOW
                        )
                    ));

            if ($feedback) {

                $feedbackUser = $this->getService('FeedbackUsers')->insert(array(
                        'feedback_id'    => $feedback->feedback_id,
                        'user_id'        => $this->getService('User')->getCurrentUserId(),
                        'subordinate_id' => 0,
                    ));

                $this->_redirector->gotoSimple('start', 'feedback', 'quest',
                    array(
                        'quest_id' => $feedback->quest_id,
                        'feedback_user_id' => $feedbackUser->feedback_user_id

                    ));
            }
        }
        $msg = _('Опрос завершен');
        $this->_redirectToError($msg, HM_Notification_NotificationModel::TYPE_CRIT);
    }

    public function _initModel()
    {
        if (empty($this->_persistentModel)) {
            $this->_persistentModel = $this->_getPersistentModel();
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
            } else {
                $this->_redirector->gotoUrl($this->_persistentModel->getRedirectUrl());
            }
        }
    }

    public function startAction()
    {
        $quest_id = $this->_getParam('quest_id',null);
        $feedback_id = $this->_getParam('feedback_id',null);
        if ($feedback_id) {
            $feedback = $this->getService('Feedback')->getOne($this->getService('Feedback')->find($feedback_id));
        } else {
            $feedbackUserId = $this->_getParam('feedback_user_id',null);
            $feedbackUser = $this->getService('FeedbackUsers')->getOne($this->getService('FeedbackUsers')->find($feedbackUserId));
            $feedback = $this->getService('Feedback')->getOne($this->getService('Feedback')->find($feedbackUser->feedback_id));
        }
         if ($feedback->quest_id == $quest_id) {
            parent::startAction();
        } else {
            $msg = _('Ошибка');
            $this->_redirectToError($msg, HM_Notification_NotificationModel::TYPE_CRIT);
        }


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
            $this->_redirector->gotoSimple('view', 'lesson', 'quest', array('quest_id' => $this->_getMultipageId()));
        }
    }

    public function _getBaseUrl()
    {
        return array('module' => 'quest', 'controller' => 'feedback');
    }  
        
    public function     _getPersistentModel($mode = null, $contextEventId = null, $contextEventType = null)
    {
        $feedbackUserId = $this->_getParam('feedback_user_id');
        $model = parent::_getPersistentModel($mode, $feedbackUserId, HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_FEEDBACK);// Возвращает модель тек. попытки тестирования

        if ($feedbackUserId) {
            $feedback = $this->getService('FeedbackUsers')->getOne(
                $this->getService('FeedbackUsers')->fetchAll(array('feedback_user_id = ?' => $feedbackUserId)));
            $model->setContextModel($feedback);
        }
        
        return $model;
    }       
    
    public function getControllerModel()
    {
        return $this->_persistentModel->getModel();
    }

    public function _isExecutable()
    {
        //todo
        $contextModel = $this->_persistentModel->getContextModel();
        if($contextModel){
            if (!$contextModel->isExecutable(true)) {
                return _('Это занятие вам более не доступно');
            }
        }
        return true;
    }

}