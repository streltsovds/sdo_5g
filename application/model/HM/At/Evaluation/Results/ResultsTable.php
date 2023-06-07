<?php
class HM_At_Evaluation_Results_ResultsTable extends HM_Db_Table
{
    protected $_name = "at_evaluation_results";
    protected $_primary = "result_id";

    protected $_referenceMap = array(
        'SessionEvent' => array(
            'columns'       => 'session_event_id',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns'    => 'session_event_id',
            'propertyName'  => 'sessionEvents'
        ),
        'SessionUser' => array(
            'columns'       => 'session_user_id',
            'refTableClass' => 'HM_At_Session_User_UserTable',
            'refColumns'    => 'session_user_id',
            'propertyName'  => 'sessionUser'
        ),
        'Criterion' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_CriterionTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criterion'
        ), 
        'CriterionKpi' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_Kpi_KpiTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criterionKpi'
        ), 
        'CriterionTest' => array(
            'columns'       => 'criterion_id',
            'refTableClass' => 'HM_At_Criterion_Test_TestTable',
            'refColumns'    => 'criterion_id',
            'propertyName'  => 'criterionTest'
        ), 
        'ScaleValue' => array(
            'columns'       => 'value_id',
            'refTableClass' => 'HM_Scale_Value_ValueTable',
            'refColumns'    => 'value_id',
            'propertyName'  => 'scale_value'
        ), 
    );
}