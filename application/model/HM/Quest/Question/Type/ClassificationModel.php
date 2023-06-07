<?php
class HM_Quest_Question_Type_ClassificationModel extends HM_Quest_Question_QuestionModel implements HM_Quest_Question_Type_Interface
{
    //нужна для кодирования айдишников ответов
    const SALT = 'X82DiEigcg';
    
    public function getResult($values)
    {
        
        $valuesHashes = array();
        if (is_array($values)) {
            // TODO: солить options перед отправкой на фронтенд
            // for test begin
            foreach ($values as $key => $value) {
                $values[$key] = md5($value.self::SALT);
            }
            //for test end

            foreach ($values as $key => $value) {
                $valuesHashes[md5($key.self::SALT)] = $key;
            }
            
            foreach ($values as $key => &$value) {
                $value = $valuesHashes[$value];
            }
            unset($value);
        }
        $variants = $this->variants->asArrayOfObjects();
        
        $valuesResult = array();
        $return = array();
        if (count($this->quest)) {
//            if ($this->mode_scoring == HM_Quest_Question_QuestionModel::MODE_SCORING_WEIGHT) {
//                
//                $return['score_raw'] = 0;
//                foreach ($values as $value) {
//                    if (!isset($variants[$value])) continue;
//                    $return['score_raw'] += $variants[$value]->weight;    
//                }
//                if (!$this->show_free_variant) $return['score_weighted'] = $this->getScoreWeighted($values);    
//                
//            } else {
                // @todo: может быть более хитрая формула
                // сейчас нужно полное совпадение отмеченных и неотмеченных вариантов
                if (is_array($values)) {
                    $return['is_correct'] = true;
                    foreach ($values as $key => $value) {
                        // if (!isset($variants[$value])) continue; // иначе при всех невыбранных засчитывает 100%
                        $return['is_correct'] = $return['is_correct'] && isset($variants[$value]) && ($variants[$key]->variant === $variants[$value]->variant);
                        $valuesResult[] = array(
                            'data'    => $variants[$key]->data,
                            'variant' => $variants[$value]->variant,
                        );
                    }
                } else {
                    $return['is_correct'] = false;
                }
                // несмотря на то, что MODE_SCORING_CORRECT, записываем score_raw и score_weightedб т.к. именно они используются дальше в расчётах
                $return['score_raw'] = ($return['is_correct'] ? 1 : 0); 
                $return['score_weighted'] = ($return['is_correct'] ? $this->score_max : $this->score_min); 
//            }
        }
        $return['variant'] = serialize($valuesResult);
        $return['free_variant'] = '';
        
        if ($this->show_free_variant) {
            foreach ($values as $value) {
                if (!isset($variants[$value])) {
                    $return['free_variant'] = trim($value);                        
                }   
            }            
        }
        
        return $return;
    }
    
    public function getScoreWeighted($variantIds)
    {
//        if (count($this->variants)) {
//            
//            $variants = $this->variants->asArrayOfObjects();
//            $weights = $this->variants->getList('question_variant_id', 'weight');
//
//            $weightCur = $weightMin = $weightMax = 0;
//            foreach ($weights as $weight) {
//                $maxOrMin = ($weight > 0) ? 'weightMax' : 'weightMin';
//                $$maxOrMin += $weight;
//            }
//            foreach ($variantIds as $value) {
//                $weightCur += $variants[$value]->weight;
//            }
//            
//            if ($weightMax != $weightMin) {
//                return ($weightCur - $weightMin)/($weightMax - $weightMin); 
//            }
//        }
        return 0;
    }

    public function displayUserResult($questionResult, $delimiter = HM_Quest_Question_QuestionModel::RESULT_DEFAULT_DELIMITER, $context = HM_Quest_Question_QuestionModel::RESULT_CONTEXT_ATTEMPT)
    {
//        if (count($this->variants)) {
//            $variants = $this->variants->asArrayOfObjects();
//        }
        
        $variant = array();
        if ($arr = unserialize($questionResult->variant)) {
            foreach ($arr as $key => $value) {
                $variant[] = "{$value['data']} = {$value['variant']}";
            }
        } elseif (!empty($questionResult->free_variant)) {
            $variant[] = $questionResult->free_variant;
        }
        
        return implode($delimiter, $variant);   
    }

    public function isEmptyResult($result)
    {
        foreach ($result as $answer) {
            if ($answer) {
                return false;
            }
        }

        return true;
    }
    
}