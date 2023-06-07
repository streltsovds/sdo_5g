<?php

class HM_Log_LogService extends HM_Service_Abstract
{
    protected $_subjects = array();

    public function log($userId, $action, $status, $priority, $slaveType = '', $slaveId = 0, $message = '', $editResult = '')
    {
        $enable = $this->getService('Option')->getOption('security_logger');

        if(!$enable)
            return false;

        if (strlen($userId) == 0) {
            $userId = 'Guest';
        }

        $remoteAddr = empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'];

        // Классический подробный лог в файл
        if ($slaveId) {
            $message = sprintf(
                " User Id: %s| Action: %s| Status: %s| Class Name: %s| Item Id: %s| Ip: %s",
                $userId,
                $action,
                $status,
                $slaveType,
                $slaveId,
                $remoteAddr
            );
        } elseif ($slaveType != '') {
            $message = sprintf(
                " User Id: %s| Action: %s| Status: %s| Description: %s| Ip: %s",
                $userId,
                $action,
                $status,
                $slaveType,
                $remoteAddr
            );
        } else {
            $message = sprintf(
                " User Id: %s| Action: %s| Status: %s| Ip: %s",
                $userId,
                $action,
                $status,
                $remoteAddr
            );

        }

        Zend_Registry::get('log_security')->log($message, $priority);

        $types = array_merge(
            HM_Log_LogService::getLogClassesNames(),
            HM_Role_Abstract_RoleModel::getBasicRoles(false,true)
        );

        if (!$slaveId || !in_array($slaveType, array_keys($types))) {
            return false;
        }

        if ($userId !== 'Guest') {
            // Лог для журнала на спец. странице
            $this->getService('LogSecurity')->insert(
                array(
                    'timestamp' => date('Y-m-d\TH:i:s'), // Странно, но у формата 'c' в хвосте добавляется '+03:00', чего не требуется
                    'user_id' => $userId,
                    'action' => $action,
                    'status' => $status,
                    'type' => $slaveType,
                    'id' => $slaveId,
                    'user_ip' => $remoteAddr,
                    'edit_result' => $editResult
                )
            );
        }
    }

    public static function getActions()
    {
        return array(
            'UPDATE' => _('Запись обновлена'),
            'DELETE' => _('Запись удалена'),
            'INSERT' => _('Запись добавлена'),
            'ASSIGN_ROLE' => _('Роль назначена'),
            'UNASSIGN_ROLE' => _('Роль удалена'),
        );
    }

    public static function getLogClassesNames()
    {
        return array(
            'HM_User_UserService' => _('Учетная запись'),
            'HM_Subject_SubjectService' => _('Курс'),
            'HM_Quest_QuestService' => _('Тест'),
        );
    }

    public static function getLogClasses()
    {
        return array_keys(self::getLogClassesNames());
    }

}