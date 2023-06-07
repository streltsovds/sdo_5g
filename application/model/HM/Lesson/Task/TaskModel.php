<?php
/*
 * Задание
 */
class HM_Lesson_Task_TaskModel extends HM_Lesson_LessonModel
{

    const TASK_EXECUTE_URL = 'test_start.php?mode=start&tid=%d&sheid=%d';

    const ASSIGN_TYPE_RANDOM = 0;
    const ASSIGN_TYPE_MANUAL = 1;
    
    protected $_primaryName = 'SHEID';

    public function getType()
    {
        return HM_Event_EventModel::TYPE_TASK;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/5g/task.svg";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
        $url = [
            'module' => 'task',
            'controller' => 'conversation',
            'action' => 'index',
            'subject_id' => $this->CID,
            'lesson_id' => $this->SHEID,
            'task_id' => $this->getModuleId()
        ];

        $services = Zend_Registry::get('serviceContainer');


        if($services->getService('Acl')->checkRoles(HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $url['user_id'] = $services->getService('User')->getCurrentUserId();
        }

        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($url, null, true));
    }

    public function getResultsUrl($options = [])
    {
        $endUser = Zend_Registry::get('serviceContainer')->getService('Acl')->checkRoles([HM_Role_Abstract_RoleModel::ROLE_ENDUSER]);

        $params = $endUser
            ? []
            : [
                'module' => 'task',
                'controller' => 'index',
                'action' => 'preview',
                'task_id' => $this->material_id,
                'subject_id' => $this->CID
            ];

        $params = (count($options)) ? array_merge($params, $options) : $params;

        return count($params)
            ? Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params, null, true))
            : '';
    }

    public function isResultInTable()
    {
        return Zend_Registry::get('serviceContainer')->getService('Acl')->checkRoles([HM_Role_Abstract_RoleModel::ROLE_TEACHER, HM_Role_Abstract_RoleModel::ROLE_DEAN]);
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

    public function getAssignType()
    {
        $params = $this->getParams();
        return (isset($params['assign_type'])) ? (int) $params['assign_type'] : self::ASSIGN_TYPE_RANDOM;
    }
}