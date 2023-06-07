<?php
class HM_Tc_ApplicationImpersonal_ApplicationImpersonalModel extends HM_Model_Abstract
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE   = 1;
    const STATUS_COMPLETE = 2;

    protected $_primaryName = 'application_impersonal_id';

    public function getServiceName()
    {
        return 'TcApplicationImpersonal';
    }
}