<?php
/*
 * Вебинар
 */

class HM_Lesson_Webinar_WebinarModel extends HM_Lesson_LessonModel
{
    public function getType()
    {
        return HM_Event_EventModel::TYPE_WEBINAR;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/5g/webinar.svg";
    }


    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url(
            [
                'module' => 'webinar',
                'controller' => 'index',
                'action' => 'index',
                'lesson_id' => $this->SHEID,
                'subject_id' => $this->CID
            ], null, true));
    }

    public function getResultsUrl($options = array())
    {
        $params = array(
            'module'     => 'webinar',
            'controller' => 'index',
            'action'     => 'index',
            'lesson_id'  => $this->SHEID,
            'subject_id' => $this->CID
        );

        $params = (count($options))? array_merge($params,$options) : $params;

        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params,null,true));
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