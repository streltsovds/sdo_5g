<?php 
class HM_Recruit_Newcomer_NewcomerTable extends HM_Db_Table
{
    protected $_name     = "recruit_newcomers";
    protected $_primary  = "newcomer_id";
    
    protected $_referenceMap = array(
        'User' => array(
            'columns' => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns' => 'MID',
            'onDelete' => self::CASCADE,
            'propertyName' => 'user',
        ),
        'ManagerUser' => array(
            'columns' => 'manager_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns' => 'MID',
            'onDelete' => self::CASCADE,
            'propertyName' => 'managerUser',
        ),
        'CuratorUser' => array(
            'columns' => 'evaluation_user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns' => 'MID',
            'onDelete' => self::CASCADE,
            'propertyName' => 'curatorUser',
        ),
        'Position' => array(
            'columns' => 'position_id',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns' => 'soid',
            'onDelete' => self::CASCADE,
            'propertyName' => 'position',
        ),
        'Profile' => array(
            'columns' => 'profile_id',
            'refTableClass' => 'HM_At_Profile_ProfileTable',
            'refColumns' => 'profile_id',
            'propertyName' => 'profile',
        ),
        'VacancyAssign' => array(
            'columns' => 'vacancy_candidate_id',
            'refTableClass' => 'HM_Recruit_Vacancy_Assign_AssignTable',
            'refColumns' => 'vacancy_candidate_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'vacancyCandidate',
        ),
        'RecruiterAssign' => array(
            'columns' => 'newcomer_id',
            'refTableClass' => 'HM_Recruit_Newcomer_AssignRecruiter_AssignRecruiterTable',
            'refColumns' => 'newcomer_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'recruiterAssign',
        ),
        'Session' => array(
            'columns' => 'session_id',
            'refTableClass' => 'HM_At_Session_SessionTable',
            'refColumns' => 'session_id',
            'propertyName' => 'session',
        ),
        'SessionUser' => array(
            'columns' => 'newcomer_id',
            'refTableClass' => 'HM_At_Session_User_UserTable',
            'refColumns' => 'newcomer_id',
            'propertyName' => 'sessionUser',
        ),
        'Evaluation' => array(
            'columns' => 'newcomer_id',
            'refTableClass' => 'HM_At_Evaluation_EvaluationTable',
            'refColumns' => 'newcomer_id',
            'propertyName' => 'evaluations'
        ),
        'Cycle' => array(
            'columns' => 'newcomer_id',
            'refTableClass' => 'HM_Cycle_CycleTable',
            'refColumns' => 'newcomer_id',
            'propertyName' => 'cycle'
        ),
        'Programm' => array(
            'columns'       => 'newcomer_id',
            'refTableClass' => 'HM_Programm_ProgrammTable',
            'refColumns'    => 'item_id', // нужно еще отфильтровать по type
            'propertyName'  => 'programm' 
        ), 
        'Student' => array(
            'columns'       => 'newcomer_id',
            'refTableClass' => 'HM_Role_StudentTable',
            'refColumns'    => 'newcomer_id',
            'propertyName'  => 'student'
        ),
    );
}