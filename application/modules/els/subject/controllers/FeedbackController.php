<?php
class Subject_FeedbackController extends HM_Controller_Action
{
    protected $_subjectId = null;
    protected $_subject   = null;
    
    public function init() {
        
        parent::init();

        $this->_helper->ContextSwitch()
            ->setAutoJsonSerialization(true)
            ->addActionContext('get-poll-ajax', 'json')
            ->setAutoJsonSerialization(true)
            ->initContext('json');
        
        $this->_subjectId = (int) $this->_getParam('subject_id', 0);
        
        if ($this->_subjectId > 0) {
            $this->_subject = $this->getOne(
                $this->getService('Subject')->find($this->_subjectId)
            );
            if($this->getRequest()->getActionName() != 'description'){
                $this->view->setExtended(
                    array(
                        'subjectName' => 'Subject',
                        'subjectId' => $this->_subjectId,
                        'subjectIdParamName' => 'subject_id',
                        'subjectIdFieldName' => 'subid',
                        'subject' => $this->_subject
                    )
                );
            }
            
//            $this->_setParam('subid', $this->_subjectId);
        }
    }

    public function listAction(){
        
        $feedbackService = $this->getService('SubjectFeedback');
        
        $select = $feedbackService->getSelect();
        $select->from(
            array('sf' => 'subjects_feedback'),
            array(
                'sf.feedback_id',
                'sf.name',
                'quest_name' => 'q.name',
                'sf.respondent_type',
                'sf.assign_type',
            )
        );
        $select->joinLeft(array('q' => 'questionnaires'), 'q.quest_id = sf.quest_id', array());
        
        $select->where('sf.subject_id = ?', $this->_subjectId);
        
        $columnsOptions = array(
            'feedback_id' => array('hidden' => true),
            'name'        => array('title' => _('Название')),
            'quest_name'  => array('title' => _('Опрос')),
            
            'respondent_type' => array(
                'title' => _('Респондент'),
                'callback' => array(
                    'function' => array($this, 'updateRespondentType'),
                    'params' => array('{{respondent_type}}')
                )
            ),
            'assign_type' => array(
                'title' => _('Способ назначения'),
                'callback' => array(
                    'function' => array($this, 'updateAssignType'),
                    'params' => array('{{assign_type}}')
                )
            ),
        );
        $filters = array(
            'name' => null,
            'quest_name' => null,
            'respondent_type' => array('values' => HM_Subject_Feedback_FeedbackModel::getRespondentTypes()),
            'assign_type' => array('values' => HM_Subject_Feedback_FeedbackModel::getAssignTypes()),
        );
        
        $gridId = 'grid_'.$this->_subjectId;
        
        $grid = $this->getGrid($select, $columnsOptions, $filters, $gridId);
        
        $grid->addAction(array(
            'module' => 'subject',
            'controller' => 'feedback',
            'action' => 'edit'
        ),
            array('feedback_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'subject',
            'controller' => 'feedback',
            'action' => 'delete'
        ),
            array('feedback_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );
        
        
        $this->view->subjectId = $this->_subjectId;
        $this->view->grid      = $grid;
    }
    
    public function updateRespondentType($type)
    {
       $types = HM_Subject_Feedback_FeedbackModel::getRespondentTypes();
       return $types[$type];
    }
    
    public function updateAssignType($type)
    {
        $types =  HM_Subject_Feedback_FeedbackModel::getAssignTypes();
        return $types[$type];
    }
    
    protected function _redirectToIndex()
    {
        $subjectId = $this->_getParam('subject_id');
        $this->_redirector->gotoSimple('list', 'feedback', 'subject', array('subject_id' => $subjectId), null, true);
    }
    
    
    public function newAction(){
        
        $feedbackUsersService = $this->getService('SubjectFeedbackUsers');
        $feedbackService      = $this->getService('SubjectFeedback');
        $questService         = $this->getService('Quest');
        
        $feedbackId = $this->_getParam('feedback_id');
        
        $form = new HM_Form_Feedback();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $values = $form->getValues();
                
                $students = $values['students'];
                unset($values['students']);
                unset($values['feedback_id']);
                $values['subject_id'] = $this->_subjectId;
                $values['quest_id'] = $values['quest_id'][0];
                $feedback = $feedbackService->insert($values);
                
                if($feedback->feedback_id){
                    $feedbackUsersService->assign($students, $feedback->feedback_id, $values['respondent_type']);
                    $feedbackService->assingNewPoll($feedback->subject_id, $feedback->quest_id);
                }
                
                $this->_flashMessenger->addMessage(_('Мероприятие успешно создано'));
                $this->_redirectToIndex();
            }
        }
        $this->view->form = $form;
    }
    
    public function editAction(){
        
        $feedbackUsersService = $this->getService('SubjectFeedbackUsers');
        $feedbackService      = $this->getService('SubjectFeedback');
        $questService         = $this->getService('Quest');
        
        $feedbackId = $this->_getParam('feedback_id');
        
        $form = new HM_Form_Feedback();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $values = $form->getValues();

                $feedbackUsersService->assign($values['students'], $values['feedback_id'], $values['respondent_type']);

                unset($values['students']);
                $values['quest_id'] = $values['quest_id'][0];
                $feedback = $feedbackService->update($values);
                
                $feedbackService->assingNewPoll($feedback->subject_id, $feedback->quest_id);
                
                $this->_flashMessenger->addMessage(_('Анкета успешно отредактирована!'));
                $this->_redirectToIndex();
            }
        } else {
            $feedback = $feedbackService->fetchAll(array(
                'feedback_id = (?)' => $feedbackId
            ))->current();
            
            if($feedback->quest_id){
                $quest = $questService->find($feedback->quest_id)->getList('quest_id', 'name');
            }
            if($feedback->feedback_id){
                $values = $feedback->getValues();
                $values['quest_id'] = $quest;
                $form->setDefaults($values);
            }
        }
        $this->view->form = $form;
    }
    
    public function deleteAction(){
        $feedbackId = $this->_getParam('feedback_id');
        $feedbackService = $this->getService('SubjectFeedback');
        $feedbackService->delete($feedbackId);
        $this->_flashMessenger->addMessage('Анкета успешно удалена!');
        $this->_redirectToIndex();
    }
    
    public function getPollAjaxAction(){
        
        $tagName = $this->_getParam('tag');
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new HM_Permission_Exception(_('Не хватает прав доступа.'));
        }
        
        $questService = $this->getService('Quest');
        
        $polls = $questService->fetchAll(
            $questService->quoteInto(
                array(
                    'subject_id = ? OR subject_id = 0',
                    ' AND type = ?', 
                    ' AND status = ?' 
                ),
                array(
                    $this->_subjectId,
                    HM_Quest_QuestModel::TYPE_POLL,
                    HM_Quest_QuestModel::STATUS_RESTRICTED
                )
            )
        );
        
        $res = array();
        foreach($polls as $poll) {
            $o = new stdClass();
            $o->key = $poll->name;
            $o->value = $poll->quest_id;
            $res [] = $o;
        }
        
        $this->view->clearVars();
        $this->view->assign($res);
    }
    
    
    public function getStudentsAjaxAction()
    {   
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset='.Zend_Registry::get('config')->charset);
        
        $subjectId  = (int) $this->_getParam('subject_id', 0);
        $feedbackId = (int) $this->_getParam('feedback_id', 0);

        $q = urldecode($this->_getParam('q', ''));

        $studentService = $this->getService('Student');
        $feedbackService = $this->getService('SubjectFeedback');
        $feedbackUsersService = $this->getService('SubjectFeedbackUsers');

        $students = array();
        $tables = array(
            0 => array('s' => 'Students'),
            1 => array('s' => 'graduated')
        );

        foreach ($tables as $from) {
            $select = $studentService->getSelect();
            $select->from(
                $from,
                array(
                    'user_id' => 'p.MID',
                    'name' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                )
            );

            $select->joinInner(array('p' => 'People'), 'p.MID = s.MID', array());

            $select->where(
                $studentService->quoteInto(
                    array(
                        'p.blocked = ?',
                        ' AND s.CID = ?',
                    ),
                    array(
                        0,
                        $subjectId
                    )
                )
            );
            $return = $select->query()->fetchAll();
            $students = array_merge($students, $return);
        }



        $feedback = $feedbackService->find($feedbackId)->current();

        $key = 'user_id';
        if ($feedback->respondent_type == HM_Subject_Feedback_FeedbackModel::RESPONDENT_TYPE_MANAGER) {
            $key = 'subordinate_id';
        }

        $assignedStudents = $feedbackUsersService->fetchAll(
            array('feedback_id = ?' => $feedbackId)
        )->getList($key);
        
        $result = '';
        if (is_array($students) && count($students)) {
            $count = 0;
            foreach($students as $student) {
                $userId = $student['user_id'];
                if (in_array($userId, $assignedStudents)) {
                    $userId .= '+';
                }
                $result .= sprintf("%s=%s\r\n", $userId, $student['name']);
                $count++;
            }
        }
        echo $result;
    }
    
    
}

