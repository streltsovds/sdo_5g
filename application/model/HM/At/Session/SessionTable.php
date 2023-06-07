<?php
class HM_At_Session_SessionTable extends HM_Db_Table
{
    protected $_name    = "at_sessions";
    protected $_primary = "session_id";

    protected $_referenceMap = array(
        'SessionEvent' => array(
            'columns'       => 'session_id',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns'    => 'session_id',
            'propertyName'  => 'events'
        ),
        'SessionUser' => array(
            'columns'       => 'session_id',
            'refTableClass' => 'HM_At_Session_User_UserTable',
            'refColumns'    => 'session_id',
            'propertyName'  => 'users'
        ),
        'SessionRespondent' => array(
            'columns'       => 'session_id',
            'refTableClass' => 'HM_At_Session_Respondent_RespondentTable',
            'refColumns'    => 'session_id',
            'propertyName'  => 'respondents'
        ),
        'Cycle' => array(
            'columns'       => 'cycle_id',
            'refTableClass' => 'HM_Cycle_CycleTable',
            'refColumns'    => 'cycle_id',
            'propertyName'  => 'cycle'
        ),
        'Vacancy' => array(
            'columns'       => 'session_id',
            'refTableClass' => 'HM_Recruit_Vacancy_VacancyTable',
            'refColumns'    => 'session_id',
            'propertyName'  => 'vacancy'
        ),
        'Newcomer' => array(
            'columns'       => 'session_id',
            'refTableClass' => 'HM_Recruit_Newcomer_NewcomerTable',
            'refColumns'    => 'session_id',
            'propertyName'  => 'newcomer'
        ),
        'Reserve' => array(
            'columns'       => 'session_id',
            'refTableClass' => 'HM_Hr_Reserve_ReserveTable',
            'refColumns'    => 'session_id',
            'propertyName'  => 'reserve'
        ),
    );
}