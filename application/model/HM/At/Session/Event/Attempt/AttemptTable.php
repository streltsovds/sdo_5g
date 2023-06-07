<?php
class HM_At_Session_Event_Attempt_AttemptTable extends HM_Db_Table
{
    protected $_name    = "at_session_event_attempts";
    protected $_primary = "attempt_id";

    protected $_referenceMap = array(
        'SessionEvent' => array(
            'columns'       => 'session_event_id',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns'    => 'session_event_id',
            'propertyName'  => 'event'
        ),
    );

}