<?php
class HM_Quest_Question_QuestionTable extends HM_Db_Table
{
	protected $_name    = "quest_questions";
	protected $_primary = "question_id";
	//protected $_sequence = 'S_100_1_QUEST';

    protected $_referenceMap = array(
        'QuestionQuest' => array(
            'columns'       => 'question_id',
            'refTableClass' => 'HM_Quest_Question_Quest_QuestTable',
            'refColumns'    => 'question_id',
            'propertyName'  => 'questionQuest'
        ),
        'Variant' => array(
            'columns'       => 'question_id',
            'refTableClass' => 'HM_Quest_Question_Variant_VariantTable',
            'refColumns'    => 'question_id',
            'propertyName'  => 'variants'
        ),		
        'Result' => array(
            'columns'       => 'question_id',
            'refTableClass' => 'HM_Quest_Question_Result_ResultTable',
            'refColumns'    => 'question_id',
            'propertyName'  => 'results'
        ),		
    );
}