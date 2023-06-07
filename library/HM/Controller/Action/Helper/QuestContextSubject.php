<?php
class HM_Controller_Action_Helper_QuestContextSubject extends Zend_Controller_Action_Helper_Abstract
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
/*@D
        if (count($this->_event->user)) {
            $view->user = $this->_event->user->current(); 
        }
        if (count($this->_event->session)) {
            $view->session = $this->_event->session->current(); 
        }
*/
        $controller = $this->getActionController();
        $model = $controller->getControllerModel();
        $course = $controller->getService('Subject')->getOne($controller->getService('Subject')->find($this->_event->CID));
        $attempts = '';    
        if($model['quest']->limit_attempts)
        {
            $a = $controller->getService('QuestAttempt')->fetchAll(array(
                'user_id = ?' => $controller->getService('User')->getCurrentUserId(),
                'context_event_id  = ?' => $this->_event->SHEID,
                'quest_id  = ?' => $model['quest']->quest_id)
                );
            $attemptsLimit = (int)$model['quest']->limit_attempts;

            $attemptsLeft = $attemptsLimit - count($a);
            if($attemptsLeft<0 && $model['quest']->limit_clean) {
                $attemptsLeft = count($a)%$attemptsLimit ? ($attemptsLimit - count($a)%$attemptsLimit) : 0;
            }

            $attempts = $attemptsLeft.' / '.$attemptsLimit;
        }

        if ($this->isAjaxRequest()) {
            return array(
                'titleLesson' => $this->_event->title,
                'titleCourse' => $course->name,
                'questionsCount' => count($model['questions']),
                'attempts' => $attempts
            );
        } else {
            $view->titleLesson = $this->_event->title;
            $view->titleCourse = $course->name;
            $view->questionsCount = count($model['questions']);
            $view->attempts = $attempts;
            return $view->render('context-subject/info.tpl');
        }
    }
    
    public function finalize($questAttempt)
    {
        $services = Zend_Registry::get('serviceContainer');

        $lesson = $services->getService('Lesson')->getOne(
            $services->getService('Lesson')->fetchAll('SHEID = ' . $questAttempt->context_event_id));

        $services->getService('LessonAssign')->onLessonFinish(
            $lesson,
            array(
                'score' => intval(round($questAttempt->score_weighted * 100)),
//                'attemptId' => $questAttempt->attempt_id, // Рубцов В.В. - добавил для фиксации оценки за текущую попытку
        ));

//@D        $this->_event->saveQuestResults($questAttempt);
//@D        $services->getService('AtSessionSubject')->updateStatus($this->_event->session_event_id, HM_At_Session_Subject_SubjectModel::STATUS_COMPLETED);
    }
}