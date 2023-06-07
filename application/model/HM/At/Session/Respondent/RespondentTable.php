<?php
class HM_At_Session_Respondent_RespondentTable extends HM_Db_Table
{
    protected $_name    = "at_session_respondents";
    protected $_primary = "session_respondent_id";

    protected $_referenceMap = array(
        'Session' => array(
            'columns'       => 'session_id',
            'refTableClass' => 'HM_At_Session_SessionTable',
            'refColumns'    => 'session_id',
            'propertyName'  => 'session'
        ),
        'SessionEvents' => array(
            'columns'       => 'session_respondent_id',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns'    => 'session_respondent_id',
            'propertyName'  => 'sessionEvents'
        ),
    );
}