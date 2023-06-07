<?php
class HM_At_Session_User_UserTable extends HM_Db_Table
{
    protected $_name    = "at_session_users";
    protected $_primary = "session_user_id";

    protected $_referenceMap = array(
        'Session' => array(
            'columns'       => 'session_id',
            'refTableClass' => 'HM_At_Session_SessionTable',
            'refColumns'    => 'session_id',
            'propertyName'  => 'session'
        ),
        'SessionEvents' => array(
            'columns'       => 'session_user_id',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns'    => 'session_user_id',
            'propertyName'  => 'sessionEvents'
        ),
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user'
        ),
        'Position' => array(
            'columns'       => 'position_id',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'soid',
            'propertyName'  => 'position'
        ),
        'EvaluationResults' => array(
            'columns'       => 'session_user_id',
            'refTableClass' => 'HM_At_Evaluation_Results_ResultsTable',
            'refColumns'    => 'session_user_id',
            'propertyName'  => 'evaluationResults'
        ),
        'EvaluationIndicators' => array(
            'columns'       => 'session_user_id',
            'refTableClass' => 'HM_At_Evaluation_Results_IndicatorTable',
            'refColumns'    => 'session_user_id',
            'propertyName'  => 'evaluationIndicators'
        ),
        'CriterionValue' => array(
            'columns'       => 'session_user_id',
            'refTableClass' => 'HM_At_Session_User_CriterionValue_CriterionValueTable',
            'refColumns'    => 'session_user_id',
            'propertyName'  => 'criterionValues'
        ),
        'Profile' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_At_Profile_ProfileTable',
            'refColumns'    => 'profile_id',
            'propertyName'  => 'profile'
        ),
        'Process' => array(
            'columns'       => 'process_id',
            'refTableClass' => 'HM_Process_ProcessTable',
            'refColumns'    => 'process_id',
            'propertyName'  => 'process'
        ),
        'VacancyAssign' => array(
            'columns'       => 'vacancy_candidate_id',
            'refTableClass' => 'HM_Recruit_Vacancy_Assign_AssignTable',
            'refColumns'    => 'vacancy_candidate_id',
            'propertyName'  => 'vacancyAssign'
        ),
        'Newcomer' => array(
            'columns'       => 'newcomer_id',
            'refTableClass' => 'HM_Recruit_Newcomer_NewcomerTable',
            'refColumns'    => 'newcomer_id',
            'propertyName'  => 'newcomer'
        ),
        'Reserve' => array(
            'columns'       => 'reserve_id',
            'refTableClass' => 'HM_Hr_Reserve_ReserveTable',
            'refColumns'    => 'reserve_id',
            'propertyName'  => 'reserve'
        ),
    );
}