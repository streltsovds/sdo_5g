<?php
class HM_Quest_Question_Variant_VariantTable extends HM_Db_Table
{
	protected $_name    = 'quest_question_variants';
	protected $_primary = 'question_variant_id';
	
    protected $_referenceMap = array(
        'Question' => array(
            'columns'       => 'question_id',
            'refTableClass' => 'HM_Quest_Question_QuestionTable',
            'refColumns'    => 'question_id',
            'propertyName'  => 'question'
        ),
    );
}