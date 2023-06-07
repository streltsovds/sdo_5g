<?php
class Lesson_ResultController extends HM_Controller_Action_Subject
{
    use HM_Controller_Action_Trait_Grid;

    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;

    private $_studentId = 0;
    //private $_subject_id; nobody is need it
    private $_lesson = null;

    private $_maxScoreCache = null;

    public function init()
    {
        parent::init();
        if (
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
        ) {
            $this->_studentId = $this->getService('User')->getCurrentUserId();
        } else {
            $this->_studentId = $this->_getParam('user_id', 0);
        }
        $this->view->setSubSubHeader(_('Результаты занятия'));
    }

    /**
     * @deprecated
     * @see Subject_ResultsController::indexAction()
     */
    public function indexAction()
    {
        $this->_forward('index', 'results', 'subject');
    }

    /**
     * @deprecated ?
     * данные проведения опроса для конкретного юзера (ссылка с названия опроса в сборе обратной связи)
     */
    public function pollByUserAction(){

        $subjectId = $this->_getParam('subject_id', 0);
    	$userId = $this->_getParam('user_id', 0);
        $lessonId = $this->_getParam('lesson_id', 0);
    	//$test = Zend_Registry::get('serviceContainer')->getService('Test')->getOne(Zend_Registry::get('serviceContainer')->getService('Test')->find($lesson->getModuleId()));
		//$quizId = $test->test_id;
        $lesson = $this->getOne($this->getService('Lesson')->findDependence('Teacher', $lessonId));
        if ($lesson) {
            $this->view->setSubHeader($lesson->title);
        }

        $claimant = $this->getOne($this->getService('Claimant')->fetchAllDependence(array('Teacher', 'Provider'), $this->getService('Claimant')->quoteInto('CID = ? AND MID = ?', $subjectId, $userId)));
        if($claimant) {
        	$this->view->date = $claimant->begin;
        	$this->view->place = $claimant->place;
        	$this->view->provider = $claimant->provider;
        	$this->view->teacher = $lesson->teacher[0]->LastName.' '.$lesson->teacher[0]->FirstName.' '.$lesson->teacher[0]->Patronymic;
        }
        elseif($lesson){
        	$this->view->date = new Zend_Date($lesson->begin, Zend_Locale_Format::getDateFormat()); // $lesson->begin;
        	$this->view->place = _('дистанционно');
        	$this->view->provider = '—';
        	$this->view->teacher = $lesson->teacher[0]->LastName.' '.$lesson->teacher[0]->FirstName.' '.$lesson->teacher[0]->Patronymic;
        }

        $log = $this->getService('TestResult')->fetchAll($this->getService('TestResult')->quoteInto(array('SHEID = ?', ' AND MID = ?'), array($lessonId, $userId)))->asArray();

       	$content = '';

        if(is_array($log) && count($log)){

	        $s = Zend_Registry::get('session_namespace_unmanaged')->s;
	        $params = $this->_getAllParams();
	        if (is_array($params) && count($params)) {
	            foreach($params as $key => $value) {
	                $$key = $value;
	            }
	        }
	        $c = $_GET['c'] = 'mini';
	        $paths = get_include_path();
	        set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/")));
	        $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');
	        $currentDir = getcwd();

	        foreach ($log as $attempt){

				$stid = $attempt['stid'];
		        ob_start();
		        chdir(APPLICATION_PATH.'/../public/unmanaged/');
		        include(APPLICATION_PATH.'/../public/unmanaged/test_log.php');
		        $content .= ob_get_contents();
		        ob_end_clean();
		        set_include_path(implode(PATH_SEPARATOR, array($paths)));

			}

	        chdir($currentDir);


        }

        $this->view->content = $content;

    }

    public function skillsoftAction()
    {
        $this->_lesson = $this->getOne($this->getService('Lesson')->find((int) $this->_getParam('lesson_id', 0)));
        $userId = $this->_getParam('user_id', $this->getService('User')->getCurrentUserId());

        if ($this->_lesson) {
            if ($user = $this->getService('User')->find($userId)->current()) {
                $this->view->setSubHeader($this->_lesson->title . ' - ' . $user->getName());
            }
        }

        if(
            !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
            //$this->getService('User')->getCurrentUserRole() != HM_Role_Abstract_RoleModel::ROLE_STUDENT
        ){
            $userId =  $this->_getParam('user_id', 0);
            $subjectId = $this->_getParam('subject_id', 0);
            $students = $this->getService('Subject')->getAssignedUsers($subjectId);

            $resStudents = array(0 => _('Выберите слушателя'));

            foreach($students as $student){
                $resStudents[$student->MID] = $student->getName();
            }
            $this->view->students = $resStudents;

            if($userId == 0){
                return;
            }
        }
        $this->view->userId = $userId;
        $this->view->lessonId = $this->_lesson->SHEID;

    }

    public function reportAction()
    {

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

		$reports = $this->getService('ScormReport')->fetchAll(array(
            'mid = ?' => $this->_getParam('user_id', 0),
            'lesson_id = ?' => $this->_getParam('lesson_id', 0),
        ));

        exit($reports[0]->report_data);
    }


}