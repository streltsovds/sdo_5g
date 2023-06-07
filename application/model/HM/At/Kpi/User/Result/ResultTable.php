<?php
class HM_At_Kpi_User_Result_ResultTable extends HM_Db_Table
{
    protected $_name = "at_user_kpi_results";
    protected $_primary = "user_kpi_result_id";
    protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'UserKpi' => array(
            'columns'       => 'user_kpi_id',
            'refTableClass' => 'HM_At_Kpi_User_UserTable',
            'refColumns'    => 'user_kpi_id',
            'propertyName'  => 'userKpi'
        ),            
    );
}