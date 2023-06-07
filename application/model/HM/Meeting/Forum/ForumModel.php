<?php

class HM_Meeting_Forum_ForumModel extends HM_Meeting_MeetingModel
{
    public function getType()
    {
        return HM_Activity_ActivityModel::ACTIVITY_FORUM;
    }

    public function getIcon($size = HM_Meeting_MeetingModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}forum.png";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl(array $options = array())
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $path = $options + array(
            'module'     => 'forum',
            'controller' => 'index',
            'action'     => 'view-lesson',
            'subject'    => 'project',
            'subject_id' => $this->project_id,
            'lesson_id'  => $this->meeting_id,
            'route'      => 'forum_subject'
        );
        
        return $router->assemble($path, 'default', true);
    }
    
    public function getResultsUrl($options = array())
    {
        return $this->getExecuteUrl($options);
    }

    public function isResultInTable()
    {
        return false;
    }
    
    public function isFreeModeEnabled()
    {
        return false;
    }
    
}