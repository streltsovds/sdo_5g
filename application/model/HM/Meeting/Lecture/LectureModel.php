<?php
/*
 * Раздел учебного модуля
 */
class HM_Meeting_Lecture_LectureModel extends HM_Meeting_MeetingModel
{
    public function getType()
    {
        return HM_Event_EventModel::TYPE_LECTURE;
    }

    public function getIcon($size = HM_Meeting_MeetingModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}lecture.png";
    }

    public function isExternalExecuting()
    {
        return false;
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
        $params = array('module'     => 'meeting',
                        'controller' => 'result',
                        'action'     => 'index',
                        'meeting_id'  => $this->meeting_id,
                        'project_id' => $this->CID);
        
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