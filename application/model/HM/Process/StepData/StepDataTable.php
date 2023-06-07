<?php

class HM_Process_StepData_StepDataTable extends HM_Db_Table
{
    protected $_name = "process_steps_data";
    protected $_primary = "process_step_id";
    protected $_sequence = "S_100_2_PROCESSES";

    protected $_dependentTables = array();

    protected $_referenceMap = array(
    );

    public function getDefaultOrder()
    {
        return array('processes.process_id ASC');
    }
}