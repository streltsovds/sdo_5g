<?php
class HM_Tc_Session_Department_Application_ApplicationModel extends HM_Model_Abstract
{
    protected $_primaryName = 'department_application_id';

    // выездное
    const OFFSITE = 1;
    const NOT_OFFSITE = 0;

    public function getServiceName()
    {
        return 'TcSessionDepartmentApplication';
    }

    static public function getOffsiteTypes()
    {
        return array(
            self::NOT_OFFSITE => _('Нет'),
            self::OFFSITE => _('Да'),
        );
    }

    public function getStudyMonth()
    {
        return $this->getService()->getStudyMonth($this->study_month);
    }

}