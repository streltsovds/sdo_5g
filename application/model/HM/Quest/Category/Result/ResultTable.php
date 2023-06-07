<?php
class HM_Quest_Category_Result_ResultTable extends HM_Db_Table
{
	protected $_name    = "quest_category_results";
	protected $_primary = "category_result_id";
	//protected $_sequence = 'S_100_1_QUEST';

    protected $_referenceMap = [
        'Category' => [
            'columns'       => 'category_id',
            'refTableClass' => 'HM_Quest_Category_CategoryTable',
            'refColumns'    => 'category_id',
            'propertyName'  => 'category'
        ],
        'Attempt' => [
            'columns'       => 'attempt_id',
            'refTableClass' => 'HM_Quest_Attempt_AttemptTable',
            'refColumns'    => 'attempt_id',
            'propertyName'  => 'attempt'
        ],
    ];
}