<?php 
class HM_Hr_Reserve_ReserveTable extends HM_Db_Table
{
    protected $_name     = "hr_reserves";
    protected $_primary  = "reserve_id";
    
    protected $_referenceMap = array(
        'User' => array(
            'columns' => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns' => 'MID',
            'onDelete' => self::CASCADE,
            'propertyName' => 'user',
        ),
//        'ManagerUser' => array(
//            'columns' => 'manager_id',
//            'refTableClass' => 'HM_User_UserTable',
//            'refColumns' => 'MID',
//            'onDelete' => self::CASCADE,
//            'propertyName' => 'managerUser',
//        ),
//        'CuratorUser' => array(
//            'columns' => 'evaluation_user_id',
//            'refTableClass' => 'HM_User_UserTable',
//            'refColumns' => 'MID',
//            'onDelete' => self::CASCADE,
//            'propertyName' => 'curatorUser',
//        ),
        'Profile' => array(
            'columns' => 'profile_id',
            'refTableClass' => 'HM_At_Profile_ProfileTable',
            'refColumns' => 'profile_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'profile',
        ),
        'Position' => array(
            'columns' => 'position_id',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns' => 'soid',
            'onDelete' => self::CASCADE,
            'propertyName' => 'position',
        ),
        'VacancyAssign' => array(
            'columns' => 'vacancy_candidate_id',
            'refTableClass' => 'HM_Recruit_Vacancy_Assign_AssignTable',
            'refColumns' => 'vacancy_candidate_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'vacancyCandidate',
        ),
        'RecruiterAssign' => array(
            'columns' => 'reserve_id',
            'refTableClass' => 'HM_Hr_Reserve_AssignRecruiter_AssignRecruiterTable',
            'refColumns' => 'reserve_id',
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
            'columns' => 'reserve_id',
            'refTableClass' => 'HM_At_Session_User_UserTable',
            'refColumns' => 'reserve_id',
            'propertyName' => 'sessionUser',
        ),
        'SessionEvent' => array(
            'columns' => 'session_id',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns' => 'session_id',
            'propertyName' => 'sessionEvents',
        ),
        'Evaluation' => array(
            'columns' => 'reserve_id',
            'refTableClass' => 'HM_At_Evaluation_EvaluationTable',
            'refColumns' => 'reserve_id',
            'propertyName' => 'evaluations'
        ),
        'Cycle' => array(
            'columns' => 'cycle_id',
            'refTableClass' => 'HM_Cycle_CycleTable',
            'refColumns' => 'cycle_id',
            'propertyName' => 'cycle'
        ),
        'Programm' => array(
            'columns'       => 'reserve_id',
            'refTableClass' => 'HM_Programm_ProgrammTable',
            'refColumns'    => 'item_id', // нужно еще отфильтровать по type
            'propertyName'  => 'programm' 
        ), 
        'Student' => array(
            'columns'       => 'reserve_id',
            'refTableClass' => 'HM_Role_StudentTable',
            'refColumns'    => 'reserve_id',
            'propertyName'  => 'student'
        ),
        'ReserveRequest' => array(
            'columns'       => 'reserve_id',
            'refTableClass' => 'HM_Hr_Reserve_Request_RequestTable',
            'refColumns'    => 'reserve_id',
            'propertyName'  => 'reserveRequest'
        ),
        'ReservePosition' => array(
            'columns'       => 'reserve_position_id',
            'refTableClass' => 'HM_Hr_Reserve_Position_PositionTable',
            'refColumns'    => 'reserve_position_id',
            'propertyName'  => 'reservePosition'
        ),
    );
}