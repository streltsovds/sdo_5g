<?php
class HM_Controller_Action_Subject_ReportCustom extends HM_Controller_Action_ReportCustom
{
    protected $service = 'Subject';
    protected $idParamName  = 'subject_id';
    protected $idFieldName = 'subid';
    
    public function init()
    {
        if (!$this->isAjaxRequest() && !$this->_getParam('print', 0)) {
            
            $subjectId = (int) $this->_getParam($this->idParamName, 0);
            if ($subjectId) {
                $this->_subject = $this->getOne($this->getService($this->service)->find($subjectId));

                $this->view->setExtended(
                    array(
                        'subjectName' => $this->service,
                        'subjectId' => $this->_subject->subid,
                        'subjectIdParamName' => $this->idParamName,
                        'subjectIdFieldName' => $this->idFieldName,
                        'subject' => $this->_subject
                    )
                );
            }
        }
        return parent::init();
    }
    
    protected function _eventQuest($event)
    {
        $questAttempts = $this->getService('QuestAttempt')->fetchAll(array(
            'context_event_id = ?' => $event->session_event_id,        
            'context_type = ?' => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ASSESSMENT,        
            'is_resultative = ?' => 1,        
        ));
        if (count($questAttempts)) {
            $questAttempt = $questAttempts->current();
            $url = $this->view->url(array(
                'module' => 'quest', 
                'controller' => 'report', 
                'action' => 'attempt', 
                'session_id' => $event->session_id,        
                'attempt_id' => $questAttempt->attempt_id,        
                'baseUrl' => '',        
            ));
            $this->_redirector->gotoUrl($url, array('prependBase' => false));
        }
    }
}