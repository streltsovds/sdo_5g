<?php

class HM_Role_RecruiterTable extends HM_Db_Table
{
    protected $_name = "recruiters";
    protected $_primary = "recruiter_id";
    //protected $_sequence = "S_77_1_ADMINS";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user',
        ),
        'Position' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'mid',
            'propertyName'  => 'position',
        ),
        'Responsibility' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_Responsibility_ResponsibilityTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'soid', // нужно еще отфильтровать по item_type
        ),
        'VacancyAssign' => array(
            'columns'       => 'recruiter_id',
            'refTableClass' => 'HM_Recruit_Vacancy_AssignRecruiter_AssignRecruiterTable',
            'refColumns'    => 'recruiter_id',
            'propertyName'  => 'vacancyAssign',
        ),
        'NewcomerAssign' => array(
            'columns'       => 'recruiter_id',
            'refTableClass' => 'HM_Recruit_Newcomer_AssignRecruiter_AssignRecruiterTable',
            'refColumns'    => 'recruiter_id',
            'propertyName'  => 'newcomerAssign',
        ),
        'ReserveAssign' => array(
            'columns'       => 'recruiter_id',
            'refTableClass' => 'HM_Hr_Reserve_AssignRecruiter_AssignRecruiterTable',
            'refColumns'    => 'recruiter_id',
            'propertyName'  => 'recruiterAssign',
        ),
        'RotationAssign' => array(
            'columns'       => 'recruiter_id',
            'refTableClass' => 'HM_Hr_Rotation_AssignRecruiter_AssignRecruiterTable',
            'refColumns'    => 'recruiter_id',
            'propertyName'  => 'rotationAssign',
        ),
    );

    public function getDefaultOrder()
    {
        return array('recruiters.user_id');
    }
}