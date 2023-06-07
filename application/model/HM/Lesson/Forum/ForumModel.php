<?php

class HM_Lesson_Forum_ForumModel extends HM_Lesson_LessonModel
{
    public function getType()
    {
        //return HM_Activity_ActivityModel::ACTIVITY_FORUM;
        return HM_Event_EventModel::TYPE_FORUM;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/5g/forum.svg";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl(array $options = [])
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $path = $options + [
                'module' => 'forum',
                'controller' => 'index',
                'action' => 'index',
                'context_type' => 'subject',
                'context_id' => $this->CID,
                'lesson_id' => $this->SHEID,
                'route' => 'forum_subject'
            ];

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