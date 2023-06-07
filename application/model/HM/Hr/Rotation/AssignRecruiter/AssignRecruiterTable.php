<?php 
class HM_Hr_Rotation_AssignRecruiter_AssignRecruiterTable extends HM_Db_Table
{
	protected $_name     = "hr_rotation_recruiters";
	protected $_primary  = array("rotation_recruiter_id");

    protected $_referenceMap = array(
    	'Recruiter' => array(
            'columns' => 'recruiter_id',
            'refTableClass' => 'HM_Role_RecruiterTable',
            'refColumns' => 'recruiter_id',
            'propertyName' => 'recruiter',
        ),
        'Rotation' => array(
            'columns' => 'rotation_id',
            'refTableClass' => 'HM_Hr_Rotation_RotationTable',
            'refColumns' => 'rotation_id',
            'propertyName' => 'rotations',
        )
    );

    public function getDefaultOrder()
    {
        return array('hr_rotation_recruiters.rotation_recruiter_id ASC');
    }
	
	
}