<?php
class Quest_IndexController extends HM_Controller_Action_Quest
{
    public function cardAction()
    {
        $this->view->quest = $this->_quest;
    }
    
    public function indexAction()
    {
        if ($quest = $this->_quest) {
            $params = array('quest_id' => $quest->quest_id);
            if ($lessonId = $this->getRequest()->getParam('lesson_id')) {
                $params['lesson_id'] = $lessonId;
            }
            if ($subjectId = $this->getRequest()->getParam('subject_id')) {
                $params['subject_id'] = $subjectId;
            }

            $redirectUrl = parse_url($_SERVER['HTTP_REFERER']);
            $params['redirect_url'] = urlencode($redirectUrl['path']);

            $this->_redirector->gotoSimple('start', 'preview', 'quest', $params);
            return;
        } else {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Тест не найден')
            ));
            $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
        }
    }
}
