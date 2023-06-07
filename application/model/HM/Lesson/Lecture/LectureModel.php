<?php
/*
 * Раздел учебного модуля
 */
class HM_Lesson_Lecture_LectureModel extends HM_Lesson_LessonModel
{
    public function getType()
    {
        return HM_Event_EventModel::TYPE_LECTURE;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/5g/lecture.svg";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
        $params = $this->getParams();
        $courseId = $params['course_id'];

        return Zend_Registry::get('view')->baseUrl(
            Zend_Registry::get('view')->url(
                [
                    'module' => 'subject',
                    'controller' => 'index',
                    'action' => 'index',
                    'subject_id' => $this->CID,
                    'lesson_id' => $this->SHEID,
                    'course_id' => $courseId
                ], null, true
            )
        );
    }

    public function getResultsUrl($options = array())
    {
        $params = array('module'     => 'lesson',
                        'controller' => 'result',
                        'action'     => 'index',
                        'lesson_id'  => $this->SHEID,
                        'subject_id' => $this->CID);
        
        if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $params['userdetail'] = 'yes' . Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
        }        
        
        $params = (count($options))? array_merge($params,$options) : $params;
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params,null,true));
    }
    
    public function getCourseId() 
    {
        
    }

    public function isResultInTable()
    {
        return true;
    }
    
    
    public function isFreeModeEnabled()
    {
        return true;
    }

    static public function getDefaultScale()
    {
        return HM_Scale_ScaleModel::TYPE_TERNARY;
    }
}