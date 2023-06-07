<?php
class HM_Meeting_Empty_EmptyModel extends HM_Meeting_MeetingModel
{
    public function getType()
    {
        return HM_Event_EventModel::TYPE_EMPTY;
    }

    public function getIcon($size = HM_Meeting_MeetingModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}blank.png";
    }

    public function isExternalExecuting()
    {
        return false;
    }

    public function getExecuteUrl()
    {
        return false;
    }

    public function getResultsUrl($options = array())
    {
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
    
}