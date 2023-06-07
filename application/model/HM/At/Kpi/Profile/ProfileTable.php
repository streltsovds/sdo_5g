<?php
class HM_At_Kpi_Profile_ProfileTable extends HM_Db_Table
{
    protected $_name = "at_profile_kpis";
    protected $_primary = "profile_kpi_id";
    protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'Kpi' => array(
            'columns'       => 'kpi_id',
            'refTableClass' => 'HM_At_Kpi_KpiTable',
            'refColumns'    => 'kpi_id',
            'propertyName'  => 'kpi'
        ), 
        'Profile' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_At_Profile_ProfileTable',
            'refColumns'    => 'profile_id',
            'propertyName'  => 'profile'
        ),            
    );    
}