<?php
class HM_Quest_Cluster_ClusterTable extends HM_Db_Table
{
	protected $_name    = "quest_clusters";
	protected $_primary = "cluster_id";
	//protected $_sequence = 'S_100_1_QUEST';

    protected $_referenceMap = array(
        'Quest' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_Quest_QuestTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'quest'
        ),
        'QuestionQuest' => array(
            'columns'       => 'cluster_id',
            'refTableClass' => 'HM_Quest_Question_Quest_QuestTable',
            'refColumns'    => 'cluster_id',
            'propertyName'  => 'questionQuest'
        ),            
        'AttemptCluster' => array(
            'columns'       => 'cluster_id',
            'refTableClass' => 'HM_Quest_Attempt_Cluster_ClusterTable',
            'refColumns'    => 'cluster_id',
            'propertyName'  => 'attemptCluster'
        ),
    );
}