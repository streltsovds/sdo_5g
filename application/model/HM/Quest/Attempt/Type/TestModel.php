<?php
class HM_Quest_Attempt_Type_TestModel extends HM_Quest_Attempt_Type_Abstract
{
    public function updateByType()
    {
        // для тестов в зачёт идёт максимальная попытка
        $siblingAttempts = Zend_Registry::get('serviceContainer')->getService('QuestAttempt')->fetchAll(array(
            'user_id = ?' => $this->user_id,
            'quest_id = ?' => $this->quest_id,
            'context_event_id = ?' => $this->context_event_id,
            'context_type = ?' => $this->context_type,
        ));
        if (count($siblingAttempts) > 1) {
            $scoresWeighted = $siblingAttempts->getList('attempt_id', 'score_weighted');
            asort($scoresWeighted);
            $attemptIds = array_keys($scoresWeighted);
            $resultativeAttemptId = array_pop($attemptIds);
            
            Zend_Registry::get('serviceContainer')->getService('QuestAttempt')->updateWhere(array('is_resultative' => 1), array('attempt_id = ?' => $resultativeAttemptId));
            Zend_Registry::get('serviceContainer')->getService('QuestAttempt')->updateWhere(array('is_resultative' => 0), array('attempt_id IN (?)' => $attemptIds));
        }
    }  

    public function getReportContext()
    {
        switch ($this->context_type) {
            case HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ASSESSMENT:
                return $this->_getReportContextEvent();
                break;
            case HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING:
                return $this->_getReportContextLesson();
                break;
            case HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_PROJECT:
                return $this->_getReportContextMeeting();
                break;
        }
        return $contextList;
    }

    protected function _getReportContextLesson()
    {
        $contextList = array();
        $context = Zend_Registry::get('serviceContainer')->getService('Lesson')->getOne(Zend_Registry::get('serviceContainer')->getService('Lesson')->findDependence('Subject', $this->context_event_id));

        if ((is_array($context) or $context instanceof Countable) and count($context)) {
            $contextList = array(
                _('Курс') => count($context->subject) ? $context->subject->current()->name : '',
                _('Занятие') => $context->title,
            );
        }
        
        return $contextList;
    }

    protected function _getReportContextMeeting()
    {
        $contextList = array();
        $context = Zend_Registry::get('serviceContainer')->getService('Meeting')->getOne(Zend_Registry::get('serviceContainer')->getService('Meeting')->findDependence('Project', $this->context_event_id));
        if (count($context)) {
            $contextList = array(
                _('Проект') => count($context->project) ? $context->project->current()->name : '',
                _('Мероприятие') => $context->title,
            );
        }

        return $contextList;
    }

    protected function _getReportContextEvent()
    {
        $contextList = array();
        $context = Zend_Registry::get('serviceContainer')->getService('AtSessionEvent')->getEventContext($this->context_event_id);
        if (count($context)) {
            if (count($context['event']->criterionTest)) {
                $criterionTest = $context['event']->criterionTest->current();
                if (count($context['profileCriteria'])) {
                    $plannedResults = $context['profileCriteria']->getList('criterion_id', 'value');
                    $plannedResult = $plannedResults[$criterionTest->criterion_id]; 
                }
            }
            
            $contextList = array(
                _('Оценочная сессия') => $context['session']->name,
                _('Методика оценки') => $context['evaluation']->name,
                _('Критерий оценки') => $criterionTest->name, 
                _('Уровень успешности') => $plannedResult . '%',
            );
        }
        
        return $contextList;
    }
}