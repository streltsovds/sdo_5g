<?php

class HM_Tc_Session_Department_DepartmentTable extends HM_Db_Table
{
    protected $_name = "tc_session_departments";
    protected $_primary = "session_department_id";
    protected $_sequence = "S_106_1_TC_SESSION_DEPARTMENTS";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Session' => array(
            'columns'       => 'session_id',
            'refTableClass' => 'HM_Tc_Session_SessionTable',
            'refColumns'    => 'session_id',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'sessions' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'SessionQuarter' => array(
            'columns'       => 'session_quarter_id',
            'refTableClass' => 'HM_Tc_SessionQuarter_SessionQuarterTable',
            'refColumns'    => 'session_quarter_id',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'sessionsQuarter' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Orgstructure' => array(
            'columns'       => 'department_id',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'soid',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'departments' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
    );

    public function getDefaultOrder()
    {
        return array('tc_session_departments.session_department_id ASC');
    }
}