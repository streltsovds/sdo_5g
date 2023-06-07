<?php
class HM_Feedback_Users_UsersModel extends HM_Model_Abstract
{
    protected $_primaryName = 'feedback_user_id';

    public function getQuestContext()
    {
        return array(
            'context_type'     => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_FEEDBACK,
            'context_event_id' => $this->feedback_user_id
        );
    }

    public function isExecutable() {

        $feedback = Zend_Registry::get('serviceContainer')->getService('Feedback')->find($this->feedback_id)->current();
        if ($feedback->assign_anonymous != HM_Feedback_FeedbackModel::ASSIGN_ANONYMOUS_ALLOW) {
            $userId = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();

            $assignedStudents = Zend_Registry::get('serviceContainer')->getService('FeedbackUsers')->fetchAll(
                array('feedback_id = ?' => $this->feedback_id)
            )->getList('user_id');

            if (!in_array($userId, $assignedStudents)) {
                //throw new HM_Exception(_('Ошибка запуска сбора обратной связи'));
                return false;
            }
        }

        $feedback = Zend_Registry::get('serviceContainer')->getService('Feedback')->find($this->feedback_id)->current();

        if ($feedback->status == HM_Feedback_FeedbackModel::STATUS_FINISHED) {
            //throw new HM_Exception(_('Cбора обратной связи завершен'));
            return false;
        }

        return true;
    }
}