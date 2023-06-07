<?php
class HM_Controller_Action_Helper_QuestContextFeedback extends Zend_Controller_Action_Helper_Abstract
{
    protected $_event;
    
    public function direct($event)
    {
        $this->_event = $event;
        return $this;
    }
    
    public function info()
    {
        $view = Zend_Registry::get('view');

        $controller = $this->getActionController();
        $model = $controller->getControllerModel();
        $course = $controller->getService('Subject')->getOne($controller->getService('Subject')->find($this->_event->subject_id));
        $attempts = '';    

        if ($this->isAjaxRequest()) {
            return array(
                'titleCourse' => $course->name,
                'attempts' => $attempts,
                'questionsCount' => count($model['questions'])
            );
        } else {
            $view->titleCourse = $course->name;
            $view->questionsCount = count($model['questions']);
            $view->attempts = $attempts;
            return $view->render('context-subject/info.tpl');
        }

    }
    
    public function finalize($questAttempt)
    {
        $service = Zend_Registry::get('serviceContainer')->getService('SubjectFeedback');

        $service->update(array(
            'feedback_id'   => $questAttempt->context_event_id,
            'date_finished' => (string) $service->getDateTime(),
            'status'        => HM_Subject_Feedback_FeedbackModel::STATUS_FINISHED
        ));
    }
}