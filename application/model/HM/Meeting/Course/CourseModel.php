<?php
/*
 * Учебный модуль
 */
class HM_Meeting_Course_CourseModel extends HM_Meeting_MeetingModel
{
    public function getType()
    {
        return HM_Event_EventModel::TYPE_COURSE;
    }

    public function isNewWindow(){
        $params=$this->getParams();
        $course = Zend_Registry::get('serviceContainer')->getService('Course')->getOne(Zend_Registry::get('serviceContainer')->getService('Course')->find($params['module_id']));
        return ($course->new_window && Zend_Registry::get('serviceContainer')->getService('CourseItem')->isDegeneratedTree($course->CID)) ? '_blank' : '_self';
    }

    public function getIcon($size = HM_Meeting_MeetingModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}course.png";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
        return Zend_Registry::get('view')->baseUrl(
            Zend_Registry::get('view')->url(
                array(
                    'module' => 'project',
                    'controller' => 'index',
                    'action' => 'index',
                    'project_id' => $this->CID,
                    'meeting_id' => $this->meeting_id,
                    'course_id' => $this->getModuleId()
                )
            )
        );
    }

    public function getResultsUrl($options = array())
    {
        // todo: scorm log по всему электронному курсу
        $params = array('module'     => 'meeting',
                        'controller' => 'result',
                        'action'     => 'index',
                        'meeting_id'  => $this->meeting_id,
                        'project_id' => $this->CID,);

        if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $params['userdetail'] = 'yes' . Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
        }

        $params = (count($options))? array_merge($params,$options) : $params;
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params,null,true));
    }

    public function getFreeModeUrlParam()
    {
        return array(
                	'module' => 'meeting',
                	'controller' => 'execute',
                	'action' => 'index',
                	'meeting_id' => $this->meeting_id
                );
    }

    public function getFreeModeAllUrlParam()
    {
        return array(
                	'module' => 'project',
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