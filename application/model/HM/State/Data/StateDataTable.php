<?php

class HM_State_Data_StateDataTable extends HM_Db_Table
{
    protected $_name = 'state_of_process_data';
    protected $_primary = array('state_of_process_data_id');
    protected $_sequence = "S_45_1_STATE_OF_PROCESS_DATA";

    protected $_dependentTables = array();

    protected $_referenceMap = array(
        'State' => array(
            'columns'       => 'state_of_process_id',
            'refTableClass' => 'HM_State_StateTable',
            'refColumns'    => 'state_of_process_id',
            'propertyName'  => 'state'
        ),
        'SessionEvent' => array(
            'columns'       => 'programm_event_user_id',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns'    => 'programm_event_user_id',
            'propertyName'  => 'sessionEvent'
        ),
    );


}