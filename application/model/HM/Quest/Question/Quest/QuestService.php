<?php
class HM_Quest_Question_Quest_QuestService extends HM_Service_Abstract
{
    public function copy($questions, $toQuestId, $toSubjectId)
    {
        foreach ($questions as $question) {

			if(!$question) continue;

            $questionData = $question->getValues(null, array('question_id', 'variants'));
            $questionData['subject_id'] = $toSubjectId;
            $newQuestion = $this->getService('QuestQuestion')->insert($questionData);
            foreach ($question->variants as $variant) {
                unset($variant->question_variant_id);
                $variant->question_id = $newQuestion->question_id;
                $newVariant = $this->getService('QuestQuestionVariant')->insert($variant->getValues());
            }
            $this->insert(array(
                'question_id' => $newQuestion->question_id,
                'quest_id' => $toQuestId,
                'cluster_id' => $question->cluster_id //$newQuestion->cluster_id
            ));
        }
    }
}