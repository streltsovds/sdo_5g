<?php
class HM_Quest_Question_Result_ResultTable extends HM_Db_Table
{
	protected $_name    = "quest_question_results";
	protected $_primary = "question_result_id";
	//protected $_sequence = 'S_100_1_QUEST';

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
            'propertyName'  => 'question'
        ),
        'Attempt' => array(
            'columns'       => 'attempt_id',
            'refTableClass' => 'HM_Quest_Attempt_AttemptTable',
            'refColumns'    => 'attempt_id',
            'propertyName'  => 'attempt'
        ),
        'Category' => array(
            'columns'       => 'category_id',
            'refTableClass' => 'HM_Quest_Category_CategoryTable',
            'refColumns'    => 'category_id',
            'propertyName'  => 'category'
        ),
    );
}