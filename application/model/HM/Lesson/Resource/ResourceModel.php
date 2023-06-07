<?php
/*
 * Информационный ресурс
 */

class HM_Lesson_Resource_ResourceModel extends HM_Lesson_LessonModel
{
    public function getType()
    {
        return HM_Event_EventModel::TYPE_RESOURCE;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/5g/resource.svg";
    }

    public function isExternalExecuting()
    {
        return false;
    }

    public function getExecuteUrl()
    {
        $url = [
            'module' => 'subject',
            'controller' => 'material',
            'action' => 'index',
            'subject_id' => $this->CID,
            'lesson_id' => $this->SHEID,
            'resource_id' => $this->getModuleId(),
        ];
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($url, null, true));
    }

    public function getResultsUrl($options = array())
    {
        $params = array('module'     => 'subject',
                        'controller' => 'lesson',
                        'action'     => 'index',
                        'lesson_id'  => $this->SHEID,
                        'subject_id' => $this->CID);
        $params = (count($options))? array_merge($params,$options) : $params;
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params,null,true));

    }
    
    public function isResultInTable()
    {
        return false;
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
                	'module' => 'resource', 
                	'controller' => 'list', 
                	'action' => 'index'
                );
    }
    
    
    public function isFreeModeEnabled()
    {
        return true;
    }
    
    static public function getDefaultScale()
    {
        return HM_Scale_ScaleModel::TYPE_BINARY;
    }    
}