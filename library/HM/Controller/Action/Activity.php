<?php
class HM_Controller_Action_Activity extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Context;
    use HM_Controller_Action_Trait_Grid;

    const ERR_NORIGHTS = 'Не хватает прав. Вы не можете использовать данный вид сервиса взаимодействия.';
    
    const PARAM_CONTEXT_TYPE    = 'context_type';
    const PARAM_CONTEXT_ID = 'context_id';
    const PARAM_LESSON_ID  = 'lesson_id';

    const CONEXT_TYPE_SUBJECT = 'subject';
    const CONEXT_TYPE_PROJECT = 'project';
    const CONEXT_TYPE_COURSE = 'course';
    const CONEXT_TYPE_RESOURSE = 'resourse';

    const PARAM_SUBJECT_ID = 'subject_id';

    protected $_activitySubjectName;
    protected $_activitySubjectId;
    protected $_activityResourceId;
    protected $_showInFrame;
    
    /**
     * @var HM_Subject_SubjectModel
     */
    private $_activitySubject;

    /**
     * @var HM_Lesson_LessonModel
     */
    private $_activityLesson;
    
    public function init()
    {
        $this->_activitySubjectName = (string) $this->_getParam(self::PARAM_CONTEXT_TYPE, '');
        $this->_activitySubjectId   = (int) $this->_getParam(self::PARAM_CONTEXT_ID);

        if($this->_hasParam(self::PARAM_SUBJECT_ID)) {
            $this->_activitySubjectName = self::CONEXT_TYPE_SUBJECT;
            $this->_activitySubjectId   = (int) $this->_getParam(self::PARAM_SUBJECT_ID);
        }

        $this->_activityResourceId   = (int) $this->_getParam('activity_resource_id');
        $this->_showInFrame   = (int) $this->_getParam('frame', 0);

        $this->view->activitySubjectName = $this->_activitySubjectName;
        $this->view->activitySubjectId = $this->_activitySubjectId;

        if(
            $this->_activitySubjectName and
            $this->_activitySubjectId and
            $service = $this->getService(ucfirst($this->_activitySubjectName))
        ) {
            $this->_activitySubject = $this->getOne($service->find($this->_activitySubjectId));
            if(!$this->isAjaxRequest() && !$this->_activityResourceId && !$this->_showInFrame) {
                $this->initContext($this->_activitySubject);
            }
        }

        parent::init();
    }

    /**
     * @return HM_Subject_SubjectModel
     */
    protected function getActivitySubject()
    {
        return $this->_activitySubject;
    }
    
    /**
     * @return HM_Lesson_LessonModel
     */
    protected function getActivityLesson()
    {
        if($this->_activityLesson === null){
            $lessonId = (int) $this->_getParam('lesson_id');
            if(!$lessonId) return;
            
            $this->_activityLesson = $this->getService('Lesson')->getLesson($lessonId);
            
        }
        
        return $this->_activityLesson;
    }

    protected function getActivityMeeting()
    {
        if($this->_activityLesson === null){
            $meetingId = (int) $this->_getParam('lesson_id');
            if(!$meetingId) return;

            $this->_activityLesson = $this->getService('Meeting')->getMeeting($meetingId);

        }

        return $this->_activityLesson;
    }

    public function preDispatch()
    {
        $activitySubjectName = $this->_getParam(self::PARAM_CONTEXT_TYPE, '');
        $activitySubjectId = $this->_getParam(self::PARAM_CONTEXT_ID, 0);

        if ($this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_USER
            && !$activitySubjectName
            && !$activitySubjectId) {
            return true;
        }
        
        if ($this->_activityResourceId) {
            return true; // доступ к сервису через базу знаний - всем разрешаем на чтение; на изменение должно быть запрещено средствами acl (в каждом сервисе)
        }

        if (!$this->getService('Activity')->isActivityUser($this->getService('User')->getCurrentUserId(), $this->getService('User')->getCurrentUserRole(), $activitySubjectName, $activitySubjectId)) {
            
            $throwMessage = _(self::ERR_NORIGHTS);
            
            if ($activitySubjectName === 'project' && $activitySubjectId) {
                // конкурс может быть публичным
                if (!$this->getService('Project')->find($activitySubjectId)->current()->is_public) {
                    throw new HM_Permission_Exception($throwMessage);
                }
            } else {
                throw new HM_Permission_Exception($throwMessage);
            }
        }
    }
}