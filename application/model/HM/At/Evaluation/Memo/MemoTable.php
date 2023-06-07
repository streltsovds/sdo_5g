<?php
class HM_At_Evaluation_Memo_MemoTable extends HM_Db_Table
{
    protected $_name = "at_evaluation_memos";
    protected $_primary = "evaluation_memo_id";
    //protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'Evaluation' => array(
            'columns'       => 'evaluation_type_id',
            'refTableClass' => 'HM_At_Evaluation_EvaluationTable',
            'refColumns'    => 'evaluation_type_id',
            'propertyName'  => 'evaluation'
        ), 
        'MemoResult' => array(
            'columns'       => 'evaluation_memo_id',
            'refTableClass' => 'HM_At_Evaluation_MemoResult_MemoResultTable',
            'refColumns'    => 'evaluation_memo_id',
            'propertyName'  => 'memoResults'
        ), 
    );
}