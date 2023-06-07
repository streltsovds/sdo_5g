<?php

class HM_Tc_Session_SessionTable extends HM_Db_Table
{
    protected $_name = "tc_sessions";
    protected $_primary = "session_id";
    protected $_sequence = "S_106_1_TC_SESSIONS";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Cycle' => array(
            'columns'       => 'cycle_id',
            'refTableClass' => 'HM_Cycle_CycleTable',
            'refColumns'    => 'cycle_id',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'cycle' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Department' => array(
            'columns'       => 'session_id',
            'refTableClass' => 'HM_Tc_Session_Department_DepartmentTable',
            'refColumns'    => 'session_id',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'departments' // имя свойства текущей модели куда будут записываться модели зависимости
        ),

    );

    public function getDefaultOrder()
    {
        return array('tc_sessions.session_id ASC');
    }
}