<?php
class HM_At_Evaluation_MemoResult_MemoResultService extends HM_Service_Abstract
{
    public function saveMemoResults($sessionEvent, $memoResults)
    {
        $result = array();
        $memos = $this->getService('AtEvaluationMemo')->fetchAll(array('evaluation_type_id = ?' => $sessionEvent->evaluation_id))->getList('evaluation_memo_id');
        $this->getService('AtEvaluationMemoResult')->deleteBy(array('session_event_id = ?' => $sessionEvent->session_event_id));
        foreach ($memoResults as $evaluationMemoId => $value) {
            if (!strlen(trim($value))) continue;
            $result[] = $this->getService('AtEvaluationMemoResult')->insert(array(
                'evaluation_memo_id' => $evaluationMemoId,
                'session_event_id' => $sessionEvent->session_event_id,
                'value' => $value,
            ));
        }
        return (count($memos) == count($result));
    }
}