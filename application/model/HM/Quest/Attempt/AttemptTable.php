<?php
class HM_Quest_Attempt_AttemptTable extends HM_Db_Table
{
	protected $_name    = "quest_attempts";
	protected $_primary = "attempt_id";
	//protected $_sequence = 'S_100_1_QUEST';

    protected $_referenceMap = array(
        'Quest' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_Quest_QuestTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'quest'
        ),
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user'
        ),
        'QuestionResult' => array(
            'columns'       => 'attempt_id',
            'refTableClass' => 'HM_Quest_Question_Result_ResultTable',
            'refColumns'    => 'attempt_id',
            'propertyName'  => 'questionResults'
        ),
        'SessionEvent' => array(
            'columns'       => 'context_event_id',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns'    => 'session_event_id', // нужно еще отфильтровать по context_type
            'propertyName'  => 'sessionEvent'
        ),
        'Lesson' => array(
            'columns'       => 'context_event_id',
            'refTableClass' => 'HM_Lesson_LessonTable',
            'refColumns'    => 'SHEID', // нужно еще отфильтровать по context_type
            'propertyName'  => 'lesson'
        ),
        'AttemptCluster' => array(
            'columns'       => 'attempt_id',
            'refTableClass' => 'HM_Quest_Attempt_Cluster_ClusterTable',
            'refColumns'    => 'attempt_id',
            'propertyName'  => 'attemptCluster'
        ),
    );
}