<?php
class HM_At_Evaluation_Memo_MemoService extends HM_Service_Abstract
{
    public function assignMemos($evaluationId, $memos)
    {
        $this->deleteBy(array('evaluation_type_id = ?' => $evaluationId));
        foreach ($memos as $memo) {
            if (empty($memo)) continue;
            $memo = $this->insert(array(
                'evaluation_type_id' => $evaluationId,
                'name' => $memo
            ));
        }           
    }
}