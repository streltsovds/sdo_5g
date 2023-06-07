<?php
class HM_Meeting_Wiki_WikiModel extends HM_Meeting_MeetingModel
{
    public function getType()
    {
        return HM_Activity_ActivityModel::ACTIVITY_WIKI;
    }

    public function getIcon($size = HM_Meeting_MeetingModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}wiki.png";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url(array(
            'module' => 'wiki',
            'controller' => 'index',
            'action' => 'view',
            'id' => $this->getModuleId()
        )));
    }

    public function getResultsUrl($options = array())
    {
        return $this->getExecuteUrl();
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