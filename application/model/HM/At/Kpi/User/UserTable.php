<?php
class HM_At_Kpi_User_UserTable extends HM_Db_Table
{
    protected $_name = "at_user_kpis";
    protected $_primary = "user_kpi_id";
    protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'Kpi' => array(
            'columns'       => 'kpi_id',
            'refTableClass' => 'HM_At_Kpi_KpiTable',
            'refColumns'    => 'kpi_id',
            'propertyName'  => 'kpi'
        ), 
        'Cycle' => array(
            'columns'       => 'cycle_id',
            'refTableClass' => 'HM_Cycle_CycleTable',
            'refColumns'    => 'cycle_id',
            'propertyName'  => 'cycle'
        ),
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user'
        ),            
        'Result' => array(
            'columns'       => 'user_kpi_id',
            'refTableClass' => 'HM_At_Kpi_User_Result_ResultTable',
            'refColumns'    => 'user_kpi_id',
            'propertyName'  => 'results'
        ),
    );
}