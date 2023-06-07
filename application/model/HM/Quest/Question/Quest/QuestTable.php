<?php
class HM_Quest_Question_Quest_QuestTable extends HM_Db_Table
{
	protected $_name    = 'quest_question_quests';
	protected $_primary = array('question_id', "quest_id");
	
    protected $_referenceMap = array(
        'Quest' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_Quest_QuestTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'quest'
        ),
        'Question' => array(
            'columns'       => 'question_id',
            'refTableClass' => 'HM_Quest_Question_QuestionTable',
            'refColumns'    => 'question_id',
            'propertyName'  => 'questions'
        ),
        'Cluster' => array(
            'columns'       => 'cluster_id',
            'refTableClass' => 'HM_Quest_Cluster_ClusterTable',
            'refColumns'    => 'cluster_id',
            'propertyName'  => 'cluster'
        ),
    );
}