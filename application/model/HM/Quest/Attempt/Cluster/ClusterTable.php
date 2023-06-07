<?php
class HM_Quest_Attempt_Cluster_ClusterTable extends HM_Db_Table
{
	protected $_name    = "quest_attempt_clusters";
	protected $_primary = "quest_attempt_cluster_id";

    protected $_referenceMap = array(
        'Attempt' => array(
            'columns'       => 'attempt_id',
            'refTableClass' => 'HM_Quest_Attempt_AttemptTable',
            'refColumns'    => 'attempt_id',
            'propertyName'  => 'attempt'
        ),
        'Cluster' => array(
            'columns'       => 'cluster_id',
            'refTableClass' => 'HM_Quest_Cluster_ClusterTable',
            'refColumns'    => 'cluster_id',
            'propertyName'  => 'cluster'
        ),
    );
}