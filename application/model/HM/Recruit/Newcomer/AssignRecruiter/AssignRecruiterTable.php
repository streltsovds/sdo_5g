<?php 
class HM_Recruit_Newcomer_AssignRecruiter_AssignRecruiterTable extends HM_Db_Table
{
	protected $_name     = "recruit_newcomer_recruiters";
	protected $_primary  = array("newcomer_recruiter_id");

    protected $_referenceMap = array(
    	'Recruiter' => array(
            'columns' => 'recruiter_id',
            'refTableClass' => 'HM_Role_RecruiterTable',
            'refColumns' => 'recruiter_id',
            'propertyName' => 'recruiters',
        ),
        'Newcomer' => array(
            'columns' => 'newcomer_id',
            'refTableClass' => 'HM_Recruit_Newcomer_NewcomerTable',
            'refColumns' => 'newcomer_id',
            'propertyName' => 'newcomers',
        )
    );

    public function getDefaultOrder()
    {
        return array('recruit_newcomer_recruiters.newcomer_recruiter_id ASC');
    }
	
	
}