<?php
/*
 * Задание
 */
class HM_Meeting_Task_TaskModel extends HM_Meeting_MeetingModel
{

    const TASK_EXECUTE_URL = 'test_start.php?mode=start&tid=%d&sheid=%d';

    const ASSIGN_TYPE_RANDOM = 0;
    const ASSIGN_TYPE_MANUAL = 1;

    public function getType()
    {
        return HM_Event_EventModel::TYPE_TASK;
    }

    public function getIcon($size = HM_Meeting_MeetingModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}task.png";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
    	$url = array(
            'module' => 'interview',
            'controller' => 'index',
            'action' => 'index',
            'meeting_id' => $this->meeting_id,
        );
    	
//    	if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),HM_Role_Abstract_RoleModel::ROLE_MODERATOR)) {
//    		$url['task-preview'] = 1;
//    	}
    	
        return Zend_Registry::get('view')->url($url);
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
        return false; //Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),HM_Role_Abstract_RoleModel::ROLE_MODERATOR);
    }

    /**
     * Возвращает массив "тип=>название" режимов назначения вариантов задания
     * @static
     * @return array
     */
    public static function getAssignTypes()
    {
        return array(
            self::ASSIGN_TYPE_RANDOM => _('Случайным образом'),
            self::ASSIGN_TYPE_MANUAL => _('Ручной режим')
        );
    }

    public function isFreeModeEnabled()
    {
        return false;
    }
}