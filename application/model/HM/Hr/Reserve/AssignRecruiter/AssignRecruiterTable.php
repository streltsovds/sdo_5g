<?php 
class HM_Hr_Reserve_AssignRecruiter_AssignRecruiterTable extends HM_Db_Table
{
	protected $_name     = "hr_reserve_recruiters";
	protected $_primary  = array("reserve_recruiter_id");

    protected $_referenceMap = array(
    	'Recruiter' => array(
            'columns' => 'recruiter_id',
            'refTableClass' => 'HM_Role_RecruiterTable',
            'refColumns' => 'recruiter_id',
            'propertyName' => 'recruiter',
        ),
        'Reserve' => array(
            'columns' => 'reserve_id',
            'refTableClass' => 'HM_Hr_Reserve_ReserveTable',
            'refColumns' => 'reserve_id',
            'propertyName' => 'reserves',
        )
    );

    public function getDefaultOrder()
    {
        return array('hr_reserve_recruiters.reserve_recruiter_id ASC');
    }
	
	
}