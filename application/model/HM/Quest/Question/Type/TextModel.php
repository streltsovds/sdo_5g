<?php
class HM_Quest_Question_Type_TextModel extends HM_Quest_Question_QuestionModel implements HM_Quest_Question_Type_Interface
{
    public function getResult($value)
    {
        $return = array('variant' => $value, 'free_variant' => '', 'is_correct' => 0, 'score_weighted' => $this->score_min);
        if(!isset($this->variants)){
            if(!trim($value)) {
                $return['score_raw'] = 1;
                $return['score_weighted'] = $this->score_max; 
                $return['is_correct'] = 1;
            }
        } else {

            $variants = $this->variants->asArrayOfObjects();
            if (count($this->quest)) {
                foreach ($variants as $variant) {
                    if (trim(strtolower($value)) == trim(strtolower($variant->variant))) {
                        $return['score_raw'] = 1;
                        $return['score_weighted'] = $this->score_max; 
                        $return['is_correct'] = 1;
                    }
                }
            } 
    }
        return $return;
    }

    public function displayUserResult($questionResult, $delimiter = HM_Quest_Question_QuestionModel::RESULT_DEFAULT_DELIMITER, $context = HM_Quest_Question_QuestionModel::RESULT_CONTEXT_ATTEMPT)
    {
        return $questionResult->variant;
    }    

}