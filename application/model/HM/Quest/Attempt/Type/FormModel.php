<?php
class HM_Quest_Attempt_Type_FormModel extends HM_Quest_Attempt_Type_Abstract
{
    public function getReportContext()
    {
        $contextList = array();
        $context = Zend_Registry::get('serviceContainer')->getService('AtSessionEvent')->getEventContext($this->context_event_id);
        if (count($context)) {
            $contextList = array(
                _('Оценочная сессия') => $context['session']->name,
                _('Методика оценки') => $context['evaluation']->name,
            );
        }
        return $contextList;
    }  
}