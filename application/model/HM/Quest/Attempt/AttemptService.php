<?php
class HM_Quest_Attempt_AttemptService extends HM_Service_Abstract
{
    public function updateStatus($questAttemptIds, $status)
    {
        if (!is_array($questAttemptIds)) {
            $questAttemptIds = [$questAttemptIds];
        }
        foreach ($questAttemptIds as $questAttemptId) {
            if (empty($questAttemptId)) continue;
            
            $data = [
                'status' => $status,
                'attempt_id' => $questAttemptId,   
                'is_resultative' => 1,
            ];
            if ($status == HM_Quest_Attempt_AttemptModel::STATUS_COMPLETED) {
                $data['date_end'] = date('Y-m-d H:i:s');
                
                if (count($questionResults = $this->getService('QuestQuestionResult')->fetchAllDependence('Question', ['attempt_id = ?' => $questAttemptId]))) {

                    $questionResult = $this->getService('QuestQuestionResult')->getOne($questionResults);
                    $questionId = $questionResult->question_id;

                    $scoresRaw = 0;
                    $scoresWeighted = 0;
                    $scoresMin = 0;
                    $scoresMax = 0;
                    $scoresCount = 0;
                    foreach ($questionResults as $questionResult) {
                        if ($questionResult->question->current()->type != HM_Quest_Question_QuestionModel::TYPE_FREE) {
                            $scoresRaw += $questionResult->score_raw;
                            $scoresWeighted  += $questionResult->score_weighted;
                            $scoresMin  += $questionResult->score_min;
                            $scoresMax  += $questionResult->score_max;
                        }
                        $scoresCount++;
                    }
                    $data['score_raw'] = $scoresRaw;
                    $data['score_sum'] = $scoresWeighted;

                    if ($variantsCount = count($this->getService('QuestQuestionVariant')->fetchAll(array('question_id = ?' => $questionId)))) {
                        if ($denominator = $scoresMax - $scoresMin) {
                            $data['score_weighted'] = ($scoresWeighted - $scoresMin) / $denominator;
                        } else {
                            $data['score_weighted'] = $data['score_sum']/$scoresCount;
                        }
//                        $data['score_weighted'] = ($scoresWeighted-$scoresMin)/($scoresMax-$scoresMin);//array_sum($scoresWeighted)/count($scoresWeighted);
                    } else {
                        $data['score_weighted'] = $data['score_sum']/$scoresCount;
                    }
                 }
            }
            $questAttempt = $this->update($data);
            $questAttempt->updateByType();
            
            return $questAttempt;
        }
        return true;
    }
}