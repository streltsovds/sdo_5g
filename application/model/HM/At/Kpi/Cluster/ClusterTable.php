<?php
class HM_At_Kpi_Cluster_ClusterTable extends HM_Db_Table
{
    protected $_name = "at_kpi_clusters";
    protected $_primary = "kpi_cluster_id";
    protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'Kpi' => array(
            'columns'       => 'kpi_cluster_id',
            'refTableClass' => 'HM_At_Kpi_KpiTable',
            'refColumns'    => 'kpi_cluster_id',
            'propertyName'  => 'kpis'
        ), 
    );    
}