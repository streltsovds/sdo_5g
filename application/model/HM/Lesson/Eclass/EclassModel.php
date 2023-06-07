<?php

class HM_Lesson_Eclass_EclassModel extends HM_Lesson_LessonModel
{
    public function getType()
    {
        return HM_Event_EventModel::TYPE_ECLASS;
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
                'module' => 'eclass',
                'controller' => 'index',
                'action' => 'index',
                'lesson_id' => $this->SHEID,
                'subject_id' => $this->CID
            ], null, true
        ));
    }

    public function getResultsUrl($options = array()) {
        return false;
    }
    
    public function isResultInTable()
    {
        return false;
    }
    
    public function isFreeModeEnabled()
    {
        return false;
    }
    
    public function isNewWindow()
    {
        return true;
    }

}