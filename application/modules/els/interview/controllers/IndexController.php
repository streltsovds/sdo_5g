<?php
class Interview_IndexController extends HM_Controller_Action_Subject
{
    public function indexAction()
    {
        $lessonId = $this->_getParam('lesson_id', 0);
        $currentId = $this->getService('User')->getCurrentUserId();
        $userId = $this->_getParam('user_id', $currentId);
       
        $condition = array();
        if ($lessonId) {
            $condition['lesson_id = ?'] = $lessonId;
        } elseif ($taskId = $this->_getParam('task_id')) {
            $task = $this->getService('Task')->getOne($this->getService('Task')->find($taskId));
            if ($task && !empty($task->questions)) {
                if (count($questions = explode(HM_Question_QuestionModel::SEPARATOR, $task->data))) {
                    $condition["question_id IN ('?')"] = new Zend_Db_Expr(implode("','", $questions));
                }
            }
        }
        
        if($this->_getParam('task-preview') && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_TEACHER))) {
        	$this->view->taskPreview = true;
        	$condition['type = ?'] = HM_Interview_InterviewModel::MESSAGE_TYPE_TASK;
        
        } else {
        	
        	$user = $this->getService('User')->getOne($this->getService('User')->fetchAll(array('MID = ?' => $userId)));
        	$this->getService('Unmanaged')->setSubHeader($user->getName());

            $interviewForm = new HM_Form_Interview();
            if ($this->isAjaxRequest()) {
                $req = $this->getRequest();
                $interviewForm->setAction(
                    $this->view->url(
                        array(
                            'module' => $req->getModuleName(),
                            'controller' => $req->getControllerName(),
                            'action' => $req->getActionName(),
                            'referer_redirect' => 1
                        )
                    )
                );
            }
        	$this->view->form = $interviewForm;
        	$this->formProccess();

            if ($currentId == $userId) {
                /**
                 * исключаем попадание в выборку своих ответов из других заданий
                 * оставляем только ответы самому себе
                 */
                $condition[] = '(user_id = ' . $userId .' OR to_whom = ' . $userId . ') AND (user_id != ' . $currentId . ' OR user_id = to_whom OR to_whom = 0)';
            } else {
                $condition[] = '(user_id = ' . $userId .' OR to_whom = ' . $userId . ')';
            }
        }
       
        $messages = $this->getService('Interview')->fetchAllHybrid('User', 'Files', 'File', $condition, array('question_id DESC', 'interview_id ASC'));

        if ($lesson = $this->getOne($this->getService('Lesson')->find($lessonId))) {
    		$mark = $this->getService('LessonAssign')->getOne($this->getService('LessonAssign')->fetchAll(array("MID = ?"=>$userId, "SHEID = ?"=>$lesson->SHEID)));
    
    		if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER))) {
    			$mark = $this->getService('LessonAssign')->getOne($this->getService('LessonAssign')->fetchAll(array('MID = ?' => $currentId, "SHEID = ?"=>$lesson->SHEID)));
    		}
    		
    		if ($mark->V_STATUS != HM_Scale_Value_ValueModel::VALUE_NA) {
    			$this->view->mark = $mark->V_STATUS;
    		} else {
    			$this->view->mark = "";
    		}
    		
    		if ($lesson->teacher) {
                $this->view->teacher = $this->getOne($this->getService('User')->find($lesson->teacher));
            }
            
            $this->view->lesson = $lesson;
            $this->view->lessonId = $lesson->SHEID;
            $this->view->scale_id = $this->getService('Event')->getOne($this->getService('Event')->find(abs($lesson->typeID)))->scale_id;
        }
        
        $this->view->messages = $messages;	
    }
       
    protected function formProccess()
    {
        $lessonId = $this->_getParam('lesson_id', 0);
        $userId = (int)$this->_getParam('user_id', 0);
        $request = $this->getRequest();
        $currentId = $this->getService('User')->getCurrentUserId();
        if ($request->isPost())
        {
            if ($this->view->form->isValid($request->getParams()))
            {
                $date = new HM_Date();
                $form = $this->view->form;         
                $fileIds = array();
                if($userId > 0){
                    $lessonUserId = $userId;
                }else{
                    $lessonUserId = $currentId;
                }  
                $message = $this->getService('Interview')->getOne($this->getService('Interview')->fetchAllHybrid('User', 'Files', 'File',array('lesson_id = ?' => $lessonId, '( user_id = ' . $lessonUserId .' OR to_whom = ' . $lessonUserId . ')'), array('question_id DESC', 'interview_id ASC')));
                $interview = $this->getService('Interview')->insert(
                    array(
                        'user_id' => $this->getService('User')->getCurrentUserId(),
                        'to_whom' => $userId,
                        'lesson_id' => $lessonId,
                        'title' => '',
                        'question_id' => '',
                    	'type' => $this->view->form->getValue('type'),
                        'message' => $this->view->form->getValue('message'),
                        'date' => $date->toString(),
                        'interview_hash' => $message->interview_hash
						//'mark' => $this->view->form->getValue('ball'),
                    )
                );
                if($form->files->isUploaded() && $form->files->receive() && $form->files->isReceived()){
                    $files = $form->files->getFileName();
                    if(count($files) > 1){
                        foreach($files as $file){
                            
                            $fileInfo = pathinfo($file);
                            $file = $this->getService('Files')->addFile($file, $fileInfo['basename']);
                            $this->getService('InterviewFile')->insert(array('interview_id' => $interview->interview_id, 'file_id' => $file->file_id));
                        }
                    }else{
                        $fileInfo = pathinfo($files);
                        $file = $this->getService('Files')->addFile($files, $fileInfo['basename']);
                        $this->getService('InterviewFile')->insert(array('interview_id' => $interview->interview_id, 'file_id' => $file->file_id));
                    }
                }
                $this->_flashMessenger->addMessage(_('Сообщение успешно добавлено.'));    
                if(/*$this->view->form->getValue('type') == HM_Interview_InterviewModel::MESSAGE_TYPE_BALL &&*/ $this->view->form->getValue('ball') != "") {
                    $this->getService('LessonAssign')->setUserScore($userId, $lessonId, $this->view->form->getValue('ball'));
                }
                if ($this->_getParam('referer_redirect')){
                    $request = new Zend_Controller_Request_Http($_SERVER['HTTP_REFERER']);
                    Zend_Controller_Front::getInstance()->getRouter()->route($request);
                    
                    $params = $request->getParams();
                    
                    $this->_redirector->gotoSimple(
                        $request->getActionName(),
                        $request->getControllerName(),
                        $request->getModuleName(),
                        array(
                            'user_id'    => $userId,
                            'subject_id' => $params['subject_id'],
                            'lesson_id'  => $params['lesson_id'],
                        )
                    );
                }
                $this->_redirector->gotoSimple('index', 'index', 'interview',
                    array(
                    	'lesson_id' => $lessonId,
                    	'subject_id' => $this->_getParam('subject_id', 0),
                    	'user_id' => ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_TEACHER))) ? $userId : null,
                    )
                );    
            }else{    
            }
        }else{ 
        }    
    }  
}