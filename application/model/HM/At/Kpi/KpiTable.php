<?php
class HM_At_Kpi_KpiTable extends HM_Db_Table
{
    protected $_name = "at_kpis";
    protected $_primary = "kpi_id";
    protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'KpiUnit' => array(
            'columns'       => 'kpi_unit_id',
            'refTableClass' => 'HM_At_Kpi_Unit_UnitTable',
            'refColumns'    => 'kpi_unit_id',
            'propertyName'  => 'unit'
        ), 
        'KpiCluster' => array(
            'columns'       => 'kpi_cluster_id',
            'refTableClass' => 'HM_At_Kpi_Cluster_ClusterTable',
            'refColumns'    => 'kpi_cluster_id',
            'propertyName'  => 'cluster'
        ), 
        'UserKpi' => array(
            'columns'       => 'kpi_id',
            'refTableClass' => 'HM_At_Kpi_User_UserTable',
            'refColumns'    => 'kpi_id',
            'propertyName'  => 'user_kpi'
        ), 
        'ProfileKpi' => array(
            'columns'       => 'kpi_id',
            'refTableClass' => 'HM_At_Kpi_Profile_ProfileTable',
            'refColumns'    => 'kpi_id',
            'propertyName'  => 'profile_kpi'
        ), 
    );
}