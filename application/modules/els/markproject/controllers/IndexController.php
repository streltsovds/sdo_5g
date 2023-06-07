<?php
class Markproject_IndexController extends HM_Controller_Action
{
    protected $service     = 'Project';
    protected $idParamName = 'project_id';
    protected $idFieldName = 'projid';
    protected $id          = 0;

    public function indexAction()
    {

        $fromDate = $this->_getParam('from', '');
        $toDate   = $this->_getParam('to', '');


        $this->_setParam('to', null);
        $this->_setParam('from', null);
        $this->_setParam('groupname', null);

        $fromDate = str_replace('_', '.', $fromDate);
        $toDate = str_replace('_', '.', $toDate);


       $courseId = $this->id = (int) $this->_getParam('project_id', 0);
       if ( $this->_request->isPost() ) {
           if (($fromDate == '' || $toDate == '')) {
               $fromDate = null;
               $toDate   = null;
           }

           if (!$fromDate && !$toDate) {
               Zend_Registry::get('session_namespace_default')->markprojectFilter = null;
           } else {
               Zend_Registry::get('session_namespace_default')->markprojectFilter[$courseId] = array( 'to'    => $toDate,
                                                                                                    'from'  => $fromDate,
                                                                                                    'group' => $group);
           }

       } else {
           $dates = Zend_Registry::get('session_namespace_default')->markprojectFilter[$courseId];
           $fromDate = $dates['from'];
           $toDate   = $dates['to'];
       }

        $this->view->dates = $dates;

        $project = $this->getOne($this->getService('Project')->find($courseId));
        $this->view->dates = array('from' => $fromDate, 'to' => $toDate);

        $this->view->setExtended(
            array(
                'subjectName' => $this->service,
                'subjectId' => $this->id,
                'subjectIdParamName' => $this->idParamName,
                'subjectIdFieldName' => $this->idFieldName,
                'subject' => $project
            )
        );
        
        $scores = $this->getService('Meeting')->getUsersScore($courseId, $fromDate, $toDate);
        $this->view->score = $scores;
        $this->view->projectId = $courseId;

    }

    public function setScoreAction()
    {
        $scores = $this->_getParam('score');
        $courseId = $this->_getParam('project_id', 0);

        if ($scores && !empty($scores) && is_array($scores))
        {
            foreach ($scores as $id => $score)
            {
                list($user_id, $meeting_id) = explode("_", $id);
                
                if (null === $score || '' === $score)
                    $score = -1;
                $this->_setScore($id, $score, $courseId);
            }
        }
        else
        {
            $id = $this->_getParam('id');
            $score = $this->_getParam('score', -1);
            $this->_setScore($id, $score, $courseId);
        }
        
        echo count($scores);
        exit;
    }

    private function _setScore($id, $score, $courseId)
    {
        $score = iconv('UTF-8', Zend_Registry::get('config')->charset, $score);
        list($pkey, $skey) = explode("_", $id);

        $this->getService('MeetingAssign')->setUserScore($pkey, $skey, $score, $courseId);
    }
    
    public function setTotalScoreAction() {
        $persons = $this->_getParam('person', 0);
        $projectId = $this->_getParam('project_id', 0);
        
        $meetingAssignService = $this->getService('MeetingAssign');
        $userService = $this->getService('User');
        
        
        $notMarked = array();
        foreach($persons as $userId => $value){
            if(!$meetingAssignService->onMeetingScoreChanged($projectId, $userId)){
                $notMarked[] = $userId;
            }
        }
        
        $this->_flashMessenger->addMessage(_('Оценки выставлены успешно!'));
        
        if(count($notMarked)){
            $users = $userService->fetchAll(array('MID IN (?)' => $notMarked));
            if (count($users)) {
                foreach($users as $user) {
                    $userName = $user->getName();
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _("Оценки не были выставлены: ") . $userName
                    ));
                }
            }
        }
        
    }
    
    public function setCommentAction()
    {
        $comment = $this->_getParam('comment');
        $score = $this->_getParam('score');
        $projectId = $this->_getParam('project_id',0);
        $comment = iconv('UTF-8', Zend_Registry::get('config')->charset, $comment);
        foreach($score as $key => $value){
            list($pkey, $skey) = explode("_", $key);
            $this->getService('MeetingAssign')->setUserComments($pkey, $skey, $comment, $projectId);
        }
        echo 'Ok';
        exit;
    }
    
    // from markproject
    public function graduateParticipantsAction()
    {
        $person = $this->_getParam('person', 0);
        $courseId = $this->_getParam('project_id', 0);
        foreach($person as $key => $value) {
            if (!$this->getService('Project')->assignGraduated($courseId, $key)) {
                echo 'Fail';
                exit;
            }
       }
       echo 'Ok';
       exit;
    }
    
    public function graduateParticipantsGridAction()
    {
        $count = 0;
        $mids = array();
        $projectId = $this->_getParam('project_id', 0);
    
        $gridId = ($projectId) ? "grid{$projectId}" : 'grid';
        $postMassIds = $this->_getParam('postMassIds_' . $gridId, ''); // from participants
        $mids = explode(',', $postMassIds);
    
        foreach($mids as $mid) {
            if ($this->getService('Project')->assignGraduated($projectId, $mid)) {
                $count++;
            }
        }
         
        $this->_flashMessenger->addMessage(array(
                'type'        => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message'    => sprintf(_('Успешно переведены в прошедшие обучение %s пользователя (-ей)'), $count)
        ));
        $this->_redirector->gotoSimple('index', 'graduated', 'assign', array('project_id' => $projectId));
    
    }
    

    public function clearScheduleAction(){
        $schedule = $this->_getParam('schedule', 0);
        foreach($schedule as $key => $value){
            $this->getService('MeetingAssign')->updateWhere(array('V_STATUS' => -1), array('SHEID = ?' => $key));
        }
        echo 'Ok';
        exit;
    }
    
    public function printAction(){
    	
		$this->_helper->layout()->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
    	$courseId = $this->_getParam('project_id', 0);
        $project = $this->getOne($this->getService('Project')->find($courseId));
        
        $dates = Zend_Registry::get('session_namespace_default')->markprojectFilter[$courseId];
        $score = $this->getService('Meeting')->getUsersScore($courseId,$dates['from'],$dates['to']);
        
        $this->view->score = $score;
        $this->view->projectId = $courseId;
    	
    }
    
    public function wordAction(){

		$this->_helper->layout()->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
    	$projectId = $this->id = (int) $this->_getParam('project_id', 0);
        $project = $this->getOne($this->getService('Project')->find($projectId));
        
        $dates = Zend_Registry::get('session_namespace_default')->markprojectFilter[$projectId];
        $score = $this->getService('Meeting')->getUsersScore($projectId,$dates['from'],$dates['to']);
        
        $this->view->score = $score;
        $this->view->projectId = $projectId;
        $data =  $this->view->render('index/export.tpl');
        
        $doc = fopen(Zend_Registry::get('config')->path->upload->markprojects.'/'.$projectId, 'w');
        fwrite($doc, $data);
        fclose($doc);
        
		$this->sendFile($projectId, 'doc', $project->name);
    	
    }
    
    public function excelAction(){
    	
		$this->_helper->layout()->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
    	$projectId = $this->id = (int) $this->_getParam('project_id', 0);
        $project = $this->getOne($this->getService('Project')->find($projectId));

        $dates = Zend_Registry::get('session_namespace_default')->markprojectFilter[$projectId];
        $score = $this->getService('Meeting')->getUsersScore($projectId,$dates['from'],$dates['to']);
        
        $this->view->score = $score;
        $this->view->projectId = $projectId;
        $data =  $this->view->render('index/export.tpl');
        
        $xls = fopen(Zend_Registry::get('config')->path->upload->markprojects.'/'.$projectId, 'w');
        fwrite($xls, $data);
        fclose($xls);
        
		$this->sendFile($projectId, 'xls', $project->name);
    	
    }
    
    public function sendFile($projectId, $ext = 'doc', $name = null){
    	
        if ($projectId) {
        	$name = $name ? $name : $projectId;
            $options = array('filename' => $name.'.'.$ext);
            
            switch(true){
            	case $ext == 'doc':
            		$contentType = 'application/word';
            		break;
            	case $ext == 'xls':
            		$contentType = 'application/excel';
            		break;
            	case strpos($this->getRequest()->getHeader('user_agent'), 'opera'):
            		$contentType = 'application/x-download';
            		break;
            	default:
            		$contentType = 'application/unknown';
            }
            
            $this->_helper->SendFile(
				Zend_Registry::get('config')->path->upload->markprojects.'/'.$projectId,
				$contentType,
				$options
	        );
            die();
        }
        $this->_flashMessenger->addMessage(_('Файл не найден'));
		$this->_redirector->gotoSimple('index', 'index', 'default');
    	
    }
    
}

