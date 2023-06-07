<?php

class HM_State_StateTable extends HM_Db_Table
{
    protected $_name = "state_of_process";
    protected $_primary = "state_of_process_id";
    protected $_sequence = "S_45_1_STATE_OF_PROCESS";

    protected $_dependentTables = array();

    protected $_referenceMap = array(
        'Process' => array(
            'columns'       => 'process_id',
            'refTableClass' => 'HM_Process_ProcessTable',
            'refColumns'    => 'process_id',
            'propertyName'  => 'process'
        ),
        'StateData' => array(
            'columns'       => 'state_of_process_id',
            'refTableClass' => 'HM_State_Data_StateDataTable',
            'refColumns'    => 'state_of_process_id',
            'propertyName'  => 'stateData'
        ),
    );
    
    public function getDefaultOrder()
    {
        return array('state_of_process.process_id ASC');
    }
}