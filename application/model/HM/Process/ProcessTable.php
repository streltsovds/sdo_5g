<?php

class HM_Process_ProcessTable extends HM_Db_Table
{
    protected $_name = "processes";
    protected $_primary = "process_id";
    protected $_sequence = "S_100_1_PROCESSES";

    protected $_dependentTables = array();

    protected $_referenceMap = array(
        'Programm' => array(
            'columns'       => 'programm_id',
            'refTableClass' => 'HM_Programm_ProgrammTable',
            'refColumns'    => 'programm_id',
            'propertyName'  => 'programm'
        ),
        'State' => array(
            'columns'       => 'process_id',
            'refTableClass' => 'HM_State_StateTable',
            'refColumns'    => 'process_id',
            'propertyName'  => 'states'
        ),
        'SessionUser' => array(
            'columns'       => 'process_id',
            'refTableClass' => 'HM_At_Session_User_UserTable',
            'refColumns'    => 'process_id',
            'propertyName'  => 'sessionUsers'
        ),
    );

    public function getDefaultOrder()
    {
        return array('processes.process_id ASC');
    }
}