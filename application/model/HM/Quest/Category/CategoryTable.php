<?php
class HM_Quest_Category_CategoryTable extends HM_Db_Table
{
	protected $_name    = "quest_categories";
	protected $_primary = "category_id";
	//protected $_sequence = 'S_100_1_QUEST';

    protected $_referenceMap = [
        'Quest' => [
            'columns'       => 'quest_id',
            'refTableClass' => 'HM_Quest_QuestTable',
            'refColumns'    => 'quest_id',
            'propertyName'  => 'quest'
        ],
		'QuestionResult' => [
            'columns'       => 'category_id',
            'refTableClass' => 'HM_Quest_Question_Result_ResultTable',
            'refColumns'    => 'category_id',
            'propertyName'  => 'questionResult'
        ],
		'CategoryResult' => [
            'columns'       => 'category_id',
            'refTableClass' => 'HM_Quest_Category_Result_ResultTable',
            'refColumns'    => 'category_id',
            'propertyName'  => 'categoryResult'
        ],
    ];
}