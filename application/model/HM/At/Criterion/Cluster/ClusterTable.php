<?php
class HM_At_Criterion_Cluster_ClusterTable extends HM_Db_Table
{
    protected $_name = "at_criteria_clusters";
    protected $_primary = "cluster_id";
    protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'Criterion' => array(
            'columns'       => 'cluster_id',
            'refTableClass' => 'HM_At_Criterion_CriterionTable',
            'refColumns'    => 'cluster_id',
            'propertyName'  => 'criteria'
        ), 
    );    
}