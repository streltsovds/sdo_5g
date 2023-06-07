<?php
class HM_Quest_QuestTable extends HM_Db_Table
{
	protected $_name    = "questionnaires";
	protected $_primary = "quest_id";
	//protected $_sequence = 'S_100_1_QUEST';

    protected $_referenceMap = array(
        'QuestionQuest' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_Quest_Question_Quest_QuestTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'questionQuest'
        ),
        'QuestionResult' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_Quest_Question_Result_ResultTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'questionResults'
        ),
        'Category' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_Quest_Category_CategoryTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'categories'
        ),
        'Cluster' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_Quest_Cluster_ClusterTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'clusters'
        ),
        'Settings' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_Quest_Settings_SettingsTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'settings'
        ),
        'Attempt' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_Quest_Attempt_AttemptTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'attempts'
        ),
        'CriterionTest' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_At_Criterion_Test_TestTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'criterionTest'
        ),
        'CriterionPersonal' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_At_Criterion_Personal_PersonalTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'criterionPersonal'
        ),
        'SessionEvent' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'sessionEvent'
        ),
        'EvaluationCriterion' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_At_Evaluation_Criterion_CriterionTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'evaluationCriterion'
        ),
        'SubjectAssign' => array(
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_Subject_Quest_QuestTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'subjects'
        ),
        'Feedback' => array(
            'columns' => 'quest_id',
            'refTableClass' => 'HM_Feedback_FeedbackTable',
            'refColumns' => 'quest_id',
            'propertyName' => 'feedbacks'
        ),
    );
}