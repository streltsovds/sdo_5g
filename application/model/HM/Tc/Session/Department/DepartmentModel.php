<?php
class HM_Tc_Session_Department_DepartmentModel extends HM_Model_Abstract
{
    protected $_primaryName = 'session_department_id';

    const DEPARMENT_ADDITIONAL_APPLICATIONS_LIMIT = 0.3; //30%

    //статусы Бизнес-процесса
    const STATE_PENDING = 0;
    const STATE_ACTUAL = 1;
    const STATE_CLOSED = 2;

    public function getServiceName()
    {
        return 'TcSessionDepartment';
    }

    public function getName()
    {

        return sprintf(_('Консолидированная заявка &laquo;%s&raquo;'), $this->getDepartmentName());
    }

    public function getDepartmentName() {
        return $this->getService()->getDepartmentName($this->department_id);
    }
}