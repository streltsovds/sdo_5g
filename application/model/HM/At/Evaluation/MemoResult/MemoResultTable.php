<?php
class HM_At_Evaluation_MemoResult_MemoResultTable extends HM_Db_Table
{
    protected $_name = "at_evaluation_memo_results";
    protected $_primary = "evaluation_memo_result_id";

    protected $_referenceMap = array(
        'EvaluationMemo' => array(
            'columns'       => 'evaluation_memo_id',
            'refTableClass' => 'HM_At_Evaluation_Memo_MemoTable',
            'refColumns'    => 'evaluation_memo_id',
            'propertyName'  => 'memo'
        ),
        'SessionEvent' => array(
            'columns'       => 'session_event_id',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns'    => 'session_event_id',
            'propertyName'  => 'sessionEvents'
        ),
    );
}