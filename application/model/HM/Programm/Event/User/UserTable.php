<?php
class HM_Programm_Event_User_UserTable extends HM_Db_Table
{
	protected $_name = "programm_events_users";
    protected $_primary = array('programm_event_user_id');

    protected $_referenceMap = array(
        'Programm' => array(
            'columns'       => 'programm_id',
            'refTableClass' => 'HM_Programm_ProgrammTable',
            'refColumns'    => 'programm_id',
            'propertyName'  => 'programm'
        ),
        'ProgrammEvent' => array(
            'columns'       => 'programm_event_id',
            'refTableClass' => 'HM_Programm_Event_EventTable',
            'refColumns'    => 'programm_event_id',
            'propertyName'  => 'programmEvent'
        ),
        'SessionEvent' => array(
            'columns'       => 'programm_event_user_id',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns'    => 'programm_event_user_id',
            'propertyName'  => 'sessionEvent'
        ),
        'Student' => array(
            'columns'       => 'programm_event_user_id',
            'refTableClass' => 'HM_Role_StudentTable',
            'refColumns'    => 'programm_event_user_id',
            'propertyName'  => 'student'
        ),
    );
}