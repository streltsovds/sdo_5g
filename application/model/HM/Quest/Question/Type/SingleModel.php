<?php
class HM_Quest_Question_Type_SingleModel extends HM_Quest_Question_QuestionModel implements HM_Quest_Question_Type_Interface
{
    public function getResult($value)
    {
        $value = $value == 0 ? "" : $value;
        $return = array('variant' => $value, 'free_variant' => '');
        if (!empty($this->variants)) {
            $variants = $this->variants->asArrayOfObjects();
            if (count($this->quest)) {
                if (isset($variants[$value])) {
                    if ($variants[$value]->category_id) $return['category_id'] = $variants[$value]->category_id;

                    if ($this->mode_scoring == HM_Quest_Question_QuestionModel::MODE_SCORING_WEIGHT) {
                        $return['score_raw'] = $variants[$value]->weight;

                        if (!$this->show_free_variant) $return['score_weighted'] = $this->getScoreWeighted($value);
                    } elseif ($this->mode_scoring == HM_Quest_Question_QuestionModel::MODE_SCORING_CORRECT) {
                        $return['is_correct'] = $variants[$value]->is_correct;

                        // несмотря на то, что MODE_SCORING_CORRECT, записываем score_raw и score_weighted т.к. именно они используются дальше в расчётах
                        $return['score_weighted'] = ($return['is_correct'] ? $this->score_max : $this->score_min);
                        $return['score_raw'] = ($return['is_correct'] ? 1 : 0); 
                    }
                } elseif ($this->show_free_variant) {
                    $return['free_variant'] = trim($value);
                } else {

                    $return['score_raw'] = 0;
                    $return['score_weighted'] = $this->score_min;
                    $return['is_correct'] = 0;
                }
            }
        } else {
            $quest = $this->quest->current();
            if ($quest->scale_id) {
                $scaleValues = Zend_Registry::get('serviceContainer')->getService('ScaleValue')
                    ->fetchAll(array('scale_id = ?' => $quest->scale_id));

                $min = null;
                $max = null;
                $return['score_raw'] = 0;
                foreach ($scaleValues as $scaleValue) {
                    if ($scaleValue->value < $min || $min === null) {
                        $min = $scaleValue->value;
                    }
                    if ($scaleValue->value > $max || $max === null) {
                        $max = $scaleValue->value;
                    }
                    if ($scaleValue->value_id == $value) {
                        $return['score_raw'] = $scaleValue->value;
                    }
                }
                $return['score_weighted'] = ($return['score_raw'] - $min) / ($max - $min)  ;
                $return['is_correct'] = 1;
                ;
            }
        }
        return $return;
    }

    public function getScoreWeighted($variantId)
    {
        if (is_a($this->variants, 'HM_Collection') && count($this->variants)) {

            $variants = $this->variants->asArrayOfObjects();
            $weights = $this->variants->getList('question_variant_id', 'weight');
            sort($weights);

            $multiplier = ($this->score_max - $this->score_min) / (count($weights) - 1);

            $weightMin = $weights[0];
            $weightMax = $weights[count($weights) - 1];
            $weightCur = $variants[$variantId]->weight;

            if ($weightMax != $weightMin) {
                $weightPosition = array_search($weightCur, $weights);

                if ($weightPosition !== false) {
                    $score = $weightPosition === 0
                        ? $this->score_min
                        : $multiplier * $weightPosition + $this->score_min;

                    return round($score, 2);
                }
            }
        }

        return $this->score_min;
    }
    
    public function getAsTxt(){
        $rusult = $this->question."\r\n";
        $variantService = Zend_Registry::get('serviceContainer')->getService('QuestQuestionVariant');
        $variants = $variantService->fetchAll(array('question_id = ?' => $this->question_id));
        
        foreach($variants as $variant){
            $prefix = '';
            if($this->mode_scoring == HM_Quest_Question_QuestionModel::MODE_SCORING_WEIGHT){
                $prefix = '(!) ' . '(' . $variant->weight . ')';
            } elseif($this->mode_scoring == HM_Quest_Question_QuestionModel::MODE_SCORING_CORRECT) {
                $prefix = '(' . ($variant->is_correct ? '!' : '?') . ')';
            }
            $rusult .= $prefix . ' ' . $variant->variant . "\r\n";
        }
        return $rusult;
    }
}