<?php
class Feedback_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Context;
    use HM_Controller_Action_Trait_Grid;

    protected $_subjectId = null;
    protected $_subject   = null;
    
    public function init() {

        $this->_subjectId = $subjectId = $this->_getParam('subject_id', $this->_getParam('subject_id', 0));
        $this->_subject = $this->getOne($this->getService('Subject')->find($subjectId));

        $isEnduser = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_Abstract_RoleModel::ROLE_ENDUSER);

        if (!$this->isAjaxRequest()) {
            if ($this->_subject) {

                $this->initContext($this->_subject);

                $this->view->addSidebar('subject', [
                    'model' => $this->_subject,
                ]);

                $this->view->setBackUrl($this->view->url([
                    'module' => 'subject',
                    'controller' => 'list',
                    'action' => 'index',
                    'base' => $this->_subject->base,
                ], null, true));

                if ($isEnduser) {
                    $switcherData = $this->getService('Subject')->getContextSwitcherData($this->_subject);
                    $this->view->setSwitchContextUrls($switcherData);
                }
            }
        }

        parent::init();

        // лезет сюда из главного меню
        // $this->view->setSubHeader(false);
    }

    public function indexAction()
    {
        $feedbackService = $this->getService('Feedback');
        
        $select = $feedbackService->getSelect();
        $select->from(
            array('sf' => 'feedback'),
            array(
                'sf.feedback_id',
                'sf.name',
                'quest_name' => 'q.name',
                'quest_id' => 'q.quest_id',
                'sf.respondent_type',
                'sf.assign_type',
                'sf.assign_anonymous',
                'sf.assign_anonymous_hash',
            )
        );
        $select->joinLeft(array('q' => 'questionnaires'), 'q.quest_id = sf.quest_id', array());
        
        $select->where('sf.subject_id = ?', $this->_subjectId);
        
        $columnsOptions = array(
            'feedback_id' => array('hidden' => true),
            'quest_id' => array('hidden' => true),
            'name'        => array('title' => _('Название')),
            'quest_name'  => array('title' => _('Опрос')),
            
            'respondent_type' => array(
                'title' => _('Респондент'),
                'callback' => array(
                    'function' => array($this, 'updateRespondentType'),
                    'params' => array('{{respondent_type}}')
                ),
                'hidden' => $this->_subjectId?false:true,
            ),
            'assign_type' => array(
                'title' => _('Способ назначения'),
                'callback' => array(
                    'function' => array($this, 'updateAssignType'),
                    'params' => array('{{assign_type}}')
                ),
                'hidden' => $this->_subjectId?false:true,
            ),
            'assign_anonymous' => array(
                'title' => _('Анонимный доступ'),
                'callback' => array(
                    'function' => array($this, 'updateAssignAnonymous'),
                    'params' => array('{{assign_anonymous}}', '{{assign_anonymous_hash}}')
                ),
                'hidden' => $this->_subjectId?true:false,
            ),
            'assign_anonymous_hash' => array('hidden' => true),

        );
        $filters = array(
            'name' => null,
            'quest_name' => null,
            'respondent_type' => array('values' => HM_Feedback_FeedbackModel::getRespondentTypes()),
            'assign_type' => array('values' => HM_Feedback_FeedbackModel::getAssignTypes()),
        );
        
        $gridId = 'grid'; //'grid_'.$this->_subjectId;
        
        $grid = $this->getGrid($select, $columnsOptions, $filters, $gridId);
        
        $grid->addAction(array(
            'module' => 'feedback',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('feedback_id'),
            $this->view->svgIcon('edit', _('Редактировать'))
        );

        $grid->addAction(array(
            'module' => 'feedback',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('feedback_id'),
            $this->view->svgIcon('delete', _('Удалить'))
        );

        $grid->addAction(array(
                'module' => 'quest',
                'controller' => 'report',
                'action' => 'index'
            ),
            array('quest_id', 'feedback_id'),
            $this->view->svgIcon('bar-chart', _('Статистика ответов'))
        );
        
        
        $this->view->subjectId = $this->_subjectId;

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;

        $urlParams = array(
            'module' => 'feedback',
            'controller' => 'list',
            'action' => 'new',
        );

        if ($this->_subjectId) {
            $urlParams['subject_id'] = $this->_subjectId;
        }

        $this->view->createUrl = $this->view->url($urlParams
            , null, true);

    }
    
    public function updateRespondentType($type)
    {
       $types = HM_Feedback_FeedbackModel::getRespondentTypes();
       return $types[$type];
    }
    
    public function updateAssignType($type)
    {
        $types =  HM_Feedback_FeedbackModel::getAssignTypes();
        return $types[$type];
    }

    public function updateAssignAnonymous($anonymous, $assign_anonymous_hash)
    {
        if ($anonymous == HM_Feedback_FeedbackModel::ASSIGN_ANONYMOUS_ALLOW) {
            return $this->view->serverUrl($this->view->url(
                array(
                    'module' => 'quest',
                    'controller' => 'feedback',
                    'action' => 'external',
                    'hash' => $assign_anonymous_hash), null, true
            ));
        } else {
            return _("Не разрешен");
        }
    }

    
    protected function _redirectToIndex()
    {
        $subjectId = $this->_getParam('subject_id', 0);
        if (isset($subjectId)) {
            $this->_redirector->gotoSimple('index', 'list', 'feedback', array('subject_id' => $subjectId), null, true);

        } else {
            $this->_redirector->gotoSimple('index', 'list', 'feedback');

        }
    }
    
    
    public function newAction()
    {
        $this->view->setSubHeader(_('Создание мероприятия'));

        $feedbackUsersService = $this->getService('FeedbackUsers');
        $feedbackService      = $this->getService('Feedback');

        $form = new HM_Form_Feedback();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $values = $form->getValues();

                if(empty($this->_subjectId) and !isset($values['assign_type'])) {
                    $values['assign_type'] = HM_Feedback_FeedbackModel::ASSIGN_NOW;
                }

                $userIds = $values['students'];
                unset($values['students']);
                unset($values['feedback_id']);
                $values['subject_id'] = $this->_subjectId;
                if (is_array($values['quest_id'])) {
                    $values['quest_id'] = $values['quest_id'][0];
                }

                if ($values['assign_anonymous']) {
                    $values['assign_anonymous_hash'] = md5(microtime().'feedbacksolt');
                }
                $feedback = $feedbackService->insert($values);
                
                if ($feedback->feedback_id){
                    $feedbackUsersService->assignUsers($userIds, $feedback->feedback_id, $values['respondent_type']);
                    if ($feedback->subject_id) {
                        $feedbackService->assingNewPoll($feedback->subject_id, $feedback->quest_id);
                    }
                }
                
                $this->_flashMessenger->addMessage(_('Мероприятие успешно создано'));
                $this->_redirectToIndex();
            }
        }
        $this->view->form = $form;
    }
    
    public function editAction()
    {
        $this->view->setSubHeader(_('Редактирование мероприятия'));

        /** @var HM_Feedback_Users_UsersService $feedbackUsersService */
        $feedbackUsersService = $this->getService('FeedbackUsers');
        $feedbackService      = $this->getService('Feedback');
        $questService         = $this->getService('Quest');
        
        $feedbackId = $this->_getParam('feedback_id');
        $feedback = $feedbackService->getOne($feedbackService->find($feedbackId));
        if (!$feedback) {
            $this->_redirectToIndex();
        }

        $form = new HM_Form_Feedback();
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $values = $form->getValues();

                // если меняют эту галочку, надо полностью перестраивать список назначенных
                if ($feedback->respondent_type != $values['respondent_type']) {
                    $feedbackUsersService->deleteBy(array(
                        'feedback_id = ?' => $feedbackId,
                    ));
                }

                $userIds = $values['students'];
                unset($values['students']);
                if (is_array($values['quest_id'])) {
                    $values['quest_id'] = $values['quest_id'][0];
                }

                if ($values['assign_anonymous'] && !$feedback->assign_anonymous_hash) {
                    $values['assign_anonymous_hash'] = md5(microtime().'feedbacksolt');
                }

                $feedback = $feedbackService->update($values);

                $feedbackUsersService->assignUsers($userIds, $values['feedback_id'], $values['respondent_type']);
                if ($feedback->subject_id) {
                    $feedbackService->assingNewPoll($feedback->subject_id, $feedback->quest_id);
                }

                $this->_flashMessenger->addMessage(_('Мероприятие успешно отредактировано'));
                $this->_redirectToIndex();
            }
        } else {
            $feedback = $feedbackService->fetchAll(array(
                'feedback_id = (?)' => $feedbackId
            ))->current();

            $quest = array();
            if($feedback->quest_id){
                $quest = $questService->find($feedback->quest_id)->getList('quest_id', 'name');
            }
            if($feedback->feedback_id){
                $values = $feedback->getValues();
                if (!$feedback->subject_id) {
                    $values['quest_id'] = $quest;
                }
                $form->setDefaults($values);
            }
        }
        $this->view->form = $form;
    }
    
    public function deleteAction() {
        $feedbackId = $this->_getParam('feedback_id');
        $feedbackService = $this->getService('Feedback');
        $feedbackService->delete($feedbackId);
        $this->_flashMessenger->addMessage('Мероприятие успешно удалено');
        $this->_redirectToIndex();
    }
    
    public function getPollAjaxAction()
    {
        $tagName = $this->_getParam('tag');
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new HM_Permission_Exception(_('Не хватает прав доступа.'));
        }
        
        $questService = $this->getService('Quest');
        
        $polls = $questService->fetchAll(
            $questService->quoteInto(
                array(
                    ' (subject_id = ? OR subject_id = 0) ',
                    ' AND type = ?', 
                    ' AND status = ?',
                    ' AND name like ?'
                ),
                array(
                    $this->_subjectId,
                    HM_Quest_QuestModel::TYPE_POLL,
                    HM_Quest_QuestModel::STATUS_RESTRICTED,
                    "%$tagName%"
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
        $feedbackService = $this->getService('Feedback');
        $feedbackUsersService = $this->getService('FeedbackUsers');

        $students = array();


        if ($subjectId) {
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
        } else {
            $select = $studentService->getSelect();
            $select->from(
                array('p' => 'People'),
                array(
                    'user_id' => 'p.MID',
                    'name' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                )
            );


            $select->where(
                $studentService->quoteInto(
                    array(
                        'p.blocked = ?',
                    ),
                    array(
                        0,
                    )
                )
            );
            $students = $select->query()->fetchAll();
        }


        $feedback = $feedbackService->find($feedbackId)->current();

        $key = 'user_id';
        if ($feedback->respondent_type == HM_Feedback_FeedbackModel::RESPONDENT_TYPE_MANAGER) {
            $key = 'subordinate_id';
        }

        $assignedStudents = $feedbackUsersService->fetchAll(
            array('feedback_id = ?' => $feedbackId)
        )->getList($key);
        
        $result = [];
        $uniqueUserIds = array();
        if (is_array($students) && count($students)) {
            $position = 0;
            foreach($students as $student) {
                $userId = $student['user_id'];
                if (!in_array($userId, $uniqueUserIds)) {
                    $uniqueUserIds[] = $userId;
                } else {
                    continue;
                }

                $result[] = [
                    'id' => $userId,
                    'name' => $student['name'],
                    'selected' => in_array($userId, $assignedStudents),
                    'level' => 1,
                    'lft' => ++$position,
                    'rgt' => ++$position,
                ];
            }
        }
        $this->_helper->json($result);
    }
    
    
}

