<?php

class HM_Report_ReportModel extends HM_Model_Abstract
{
    const STATUS_PUBLISHED = 1;
    const STATUS_UNPUBLISHED = 0;

    protected $_errorMessage = '';

    static public function getStatuses()
    {
        return [
            self::STATUS_UNPUBLISHED => _('Не опубликован'),
            self::STATUS_PUBLISHED => _('Опубликован')
        ];
    }

    static function getStatus($status)
    {
        $statuses = self::getStatuses();
        return $statuses[$status];
    }

    static public function getReportRoles()
    {
        $roles =  HM_Role_Abstract_RoleModel::getBasicRoles(true, true); // без гостя, с объединением младших ролей в enduser'а
        unset($roles[HM_Role_Abstract_RoleModel::ROLE_GUEST]);

        return $roles;
    }

    public function isValid()
    {
        $fields = (strlen($this->fields)) ? unserialize($this->fields) : [];

        if (!count($fields)) {
            $this->_errorMessage = _('Не задано ни одного поля для построения отчета');

            return false;
        }

        return true;
    }

    public function getError()
    {
        return $this->_errorMessage;
    }
}
