<?php
class HM_Responsibility_ResponsibilityTable extends HM_Db_Table
{
    protected $_name = "responsibilities";
    protected $_primary = "responsibility_id";
    protected $_sequence = "????"; // @todo

    
    protected $_referenceMap = array(
        'Atmanager' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_Role_AtManagerTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'atmanager',
        ),
        'Recruiter' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_Role_RecruiterTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'recruiter',
        ),
        'Hr' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_Role_HrTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'hr',
        ),
        'LaborSafety' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_Role_LaborSafetyTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'laborSafety',
        ),
        'Dean' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_Role_DeanTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'dean',
        ),
        'Department' => array(
            'columns'       => 'item_id',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'soid',
            'propertyName'  => 'department', // нужно еще отфильтровать по type
        ),
    );
    
}