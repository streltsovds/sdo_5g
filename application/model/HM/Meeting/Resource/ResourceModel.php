<?php
/*
 * Информационный ресурс
 */

class HM_Meeting_Resource_ResourceModel extends HM_Meeting_MeetingModel
{
    public function getType()
    {
        return HM_Event_EventModel::TYPE_RESOURCE;
    }

    public function getIcon($size = HM_Meeting_MeetingModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}resource.png";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
        parse_str(str_replace(';','&',$this->params),  $module);

        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url(array(
        																			'module' => 'resource',// 'file',
        																			'controller' => 'index',// 'get',
        																			'action' => 'index',// 'resource',
        																			'resource_id' => array(
        																				'resource_id' => $module['module_id']
        																			)
       )));

/*
        return Zend_Registry::get('config')->url->base.sprintf(
            'webinar/index/index/pointId/',
            $this->getModuleId(),
            $this->meeting_id
        );*/
    }

    public function getResultsUrl($options = array())
    {
        $params = array('module'     => 'meeting',
                        'controller' => 'result',
                        'action'     => 'index',
                        'meeting_id'  => $this->meeting_id,
                        'project_id' => $this->CID);
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
                	'module' => 'meeting',
                	'controller' => 'execute', 
                	'action' => 'index', 
                	'meeting_id' => $this->meeting_id
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