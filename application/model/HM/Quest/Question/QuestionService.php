<?php
class HM_Quest_Question_QuestionService extends HM_Service_Abstract
{
    public function insert($data, $unsetNull = true)
    {
        unset($data['quest_id']);
        unset($data['question_id']);
        unset($data['cluster_id']);

        if (isset($data['variants'])) {
            $variants = $data['variants'];
            unset($data['variants']);
        }
        
        if (empty($data['shorttext'])) {
            $text = strip_tags($data['question']);
            $data['shorttext'] = (strlen($text) > 32) ? mb_substr($text, 0, 32) . '...' : $text;
        }
                
        // т.к. не удаось настроить в форме валидацию, там русский float с запятой
        $data['score_min'] = (float)$data['score_min'];
        $data['score_max'] = (float)$data['score_max'];

        $question = parent::insert($data);
        
        if (!empty($variants)) {
            $this->_updateVariants($question->question_id, $variants);
        }
        return $question;
    }
    
    public function update($data, $unsetNull = true)
    {
        unset($data['quest_id']);
        unset($data['cluster_id']);

        if (isset($data['variants'])) {
            $variants = $data['variants'];
            unset($data['variants']);
        }
        
        if (empty($data['shorttext'])) {
            $text = strip_tags($data['question']);
            $data['shorttext'] = (strlen($text) > 32) ? substr($text, 0, 32) . '...' : $text;
        }

        // т.к. не удаось настроить в форме валидацию, там русский float с запятой
        $data['score_min'] = (float)$data['score_min'];
        $data['score_max'] = (float)$data['score_max'];

        $question = parent::update($data);
        
        if (!empty($variants)) {
            $this->_updateVariants($question->question_id, $variants);
        }
        return $question;
    }
    
    protected function _updateVariants($questionId, $variants)
    {
        /** @var HM_Quest_Question_QuestionModel $question */
        $question = $this->getService('QuestQuestion')->getOne(
            $this->getService('QuestQuestion')->findDependence(['Variant'], $questionId)
        );

        $allowEmptyVariants = $question->emptyVariantsAllowed();

        $currentVariants = [];
        if (count($question->variants)) {
            $currentVariants = $question->variants->getList('question_variant_id', 'question_variant_id');    
        }
        $dataCounter = 1;

        /** @var HM_Quest_Question_Variant_VariantService $variantService */
        $variantService = $this->getService('QuestQuestionVariant');

        foreach ($variants as $variantId => $data) {
            if ($variantId != HM_Form_Element_MultiSet::ITEMS_NEW) {
                $dataValue = $dataCounter++;
                if (in_array($question->type, [
                    HM_Quest_Question_QuestionModel::TYPE_MAPPING,
                    HM_Quest_Question_QuestionModel::TYPE_CLASSIFICATION,
                    HM_Quest_Question_QuestionModel::TYPE_IMAGEMAP,
                    HM_Quest_Question_QuestionModel::TYPE_PLACEHOLDER
                ])) {
                    $dataValue = $data['data'];
                }
                $variant = [
                    'question_variant_id' => $variantId,
                    'question_id' => $question->question_id,
                    'is_correct' => ($question->mode_scoring == HM_Quest_Question_QuestionModel::MODE_SCORING_CORRECT) ? $data['is_correct'] : 0,
                    'data' => $dataValue,
                    'weight' => ($question->mode_scoring == HM_Quest_Question_QuestionModel::MODE_SCORING_WEIGHT) ? $data['weight'] : 0,
                    'variant' => $data['variant'],
                    'category_id' => $data['category_id'],
                ];

                $variantService->update($variant);
                unset($currentVariants[$variantId]);
            } else { // новые варианты
                foreach ($data['variant'] as $key => $value) {
                    if (!strlen(trim($value)) && !$allowEmptyVariants) continue;
                    $dataValue = $dataCounter++;

                    if (in_array($question->type, [
                        HM_Quest_Question_QuestionModel::TYPE_MAPPING,
                        HM_Quest_Question_QuestionModel::TYPE_CLASSIFICATION,
                        HM_Quest_Question_QuestionModel::TYPE_IMAGEMAP,
                        HM_Quest_Question_QuestionModel::TYPE_PLACEHOLDER
                    ])) {
                        $dataValue = $data['data'][$key];
                    }
                    $variant = [
                        'question_id' => $question->question_id,
                        'is_correct'  => isset($data['is_correct'][$key]) ? $data['is_correct'][$key] : 0,
                        'data'        => $dataValue,
                        'weight'      => isset($data['weight'][$key]) ? $data['weight'][$key] : 0,
                        'variant'     => $value,
                        'category_id' => isset($data['category_id'][$key]) ? $data['category_id'][$key] : 0,
                    ];

                    $variantService->insert($variant);
                }
            }
        }

        if (is_array($currentVariants) && count($currentVariants)) {
            $variantService->deleteBy(array('question_variant_id IN (?)' => $currentVariants));
        }
        return true;
    }    
    
    public function copy($questionId, $questId)
    {
        if ($question = $this->getOne($this->findDependence(array('Variant'), $questionId))) {
            unset($question->question_id);

            $ref = $this->getService('QuestQuestionQuest')->fetchAll(array(
                'quest_id = ?' => $questId,
                'question_id = ?' => $questionId
            ))->current();

            $clusterId = $ref ? $ref->cluster_id : 0;

            $newQuestion = $this->insert($question->getValues());
            foreach ($question->variants as $variant) {
                unset($variant->question_variant_id);            
                $variant->question_id = $newQuestion->question_id;            
                $newVariant = $this->getService('QuestQuestionVariant')->insert($variant->getValues());
            }
            $this->getService('QuestQuestionQuest')->insert(array(
                'question_id' => $newQuestion->question_id,
                'quest_id' => $questId,
                'cluster_id' => $clusterId
            ));
            return $newQuestion;
        }
        return false;
    }

    /* 
     * Этот метод, как ему и положено, удаляет _насовсем_ 
     */
    public function delete($questionId)
    {
        $this->getService('QuestQuestionQuest')->deleteBy(['question_id = ?' => $questionId]);
        $this->getService('QuestQuestionVariant')->deleteBy(['question_id = ?' => $questionId]);

        parent::delete($questionId);
    }
    
    public function getTxt($ids){
        $result = '';
        
        $questions = $this->fetchAll(array(
            'question_id IN (?)' => $ids,
            'type IN (?)' => array(
                HM_Quest_Question_QuestionModel::TYPE_SINGLE,
                HM_Quest_Question_QuestionModel::TYPE_MULTIPLE
            ),
        ));
        $counter = 1;
        foreach($questions as $question){
            $result .= $counter . '. ' . $question->getAsTxt() . "\r\n";
            $counter++;
        }
        
        return $result;
    }

    public function isDeletable($questionId)
    {
        if (in_array($questionId, HM_Quest_Question_QuestionModel::getHardcodeDeleteIds())) {
            return false;
        }
        return true;
    }

    public function isEditable($questionId)
    {
        if (in_array($questionId, HM_Quest_Question_QuestionModel::getHardcodeEditIds())) {
            return false;
        }
        return true;
    }


}