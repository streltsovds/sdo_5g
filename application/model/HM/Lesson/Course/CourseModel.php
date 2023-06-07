<?php
/*
 * Учебный модуль
 */
class HM_Lesson_Course_CourseModel extends HM_Lesson_LessonModel
{
    public function getType()
    {
        return HM_Event_EventModel::TYPE_COURSE;
    }

    public function isNewWindow()
    {
        $params=$this->getParams();
        $course = Zend_Registry::get('serviceContainer')->getService('Course')->getOne(Zend_Registry::get('serviceContainer')->getService('Course')->find($params['module_id']));

        return $course->new_window; /* && Zend_Registry::get('serviceContainer')->getService('CourseItem')->isDegeneratedTree($course->CID);*/
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/5g/course.svg";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
        if ($this->isfree == HM_Lesson_LessonModel::MODE_FREE) {
            $url = [
                'module' => 'subject',
                'controller' => 'material',
                'action' => 'index',
                'course_id' => $this->getModuleId(),
            ];
        } else {
            $url = [
                'module' => 'subject',
                'controller' => 'material',
                'action' => 'index',
                'subject_id' => $this->CID,
                'lesson_id' => $this->SHEID,
                'course_id' => $this->getModuleId()
            ];
        }


        return Zend_Registry::get('view')->baseUrl(
            Zend_Registry::get('view')->url($url, null, true)
        );
    }

    public function getResultsUrl($options = array())
    {
        // todo: scorm log по всему электронному курсу
        $params = array('module'     => 'lesson',
                        'controller' => 'result',
                        'action'     => 'index',
                        'lesson_id'  => $this->SHEID,
                        'subject_id' => $this->CID,);

        if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $params['userdetail'] = 'yes';
            $params['user_id'] = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
        }

        $params = (count($options))? array_merge($params,$options) : $params;
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params,null,true));
    }

    public function getFreeModeUrlParam()
    {
        return array(
                	'module' => 'lesson',
                	'controller' => 'execute',
                	'action' => 'index',
                	'lesson_id' => $this->SHEID
                );
    }

    public function getFreeModeAllUrlParam()
    {
        return array(
                	'module' => 'subject',
                	'controller' => 'index',
                	'action' => 'courses'
                );
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