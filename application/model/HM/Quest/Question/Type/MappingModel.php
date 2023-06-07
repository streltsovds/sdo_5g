<?php
class HM_Quest_Question_Type_MappingModel extends HM_Quest_Question_QuestionModel implements HM_Quest_Question_Type_Interface
{
    //нужна для кодирования айдишников ответов
    const SALT = 'v0LP2o59Ee';

    public function getResult($values)
    {
        if (is_array($values)) {
            // TODO: солить options перед отправкой на фронтенд
            // for test begin
            foreach ($values as $key => $value) {
                $values[$key] = md5($value . self::SALT);
            }
            //for test end

            $valuesHashes = array();
            foreach ($values as $key => $value) {
                $valuesHashes[md5($key . self::SALT)] = $key;
            }

            foreach ($values as $key => $value) {
                $values[$key] = $valuesHashes[$value];
            }
            unset($value);
        }

        $return = array('variant' => serialize($values), 'free_variant' => '');
        if (empty($values)) {
            $return['score_raw'] = 0;
            $return['score_weighted'] = $this->score_min;
            $return['is_correct'] = 0;
            return $return;
        }
        $variants = $this->variants->asArrayOfObjects();
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
                    if (!isset($variants[$value])) {
                        continue;
                    }
                    $return['is_correct'] = $return['is_correct'] && ($key == $value);
                }
            } else {
                $return['is_correct'] = false;
            }
            // несмотря на то, что MODE_SCORING_CORRECT, записываем score_raw и score_weightedб т.к. именно они используются дальше в расчётах
            $return['score_weighted'] = ($return['is_correct'] ? $this->score_max : $this->score_min);
            $return['score_raw'] = ($return['is_correct'] ? 1 : 0);
            //            }
        }

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
        $delimiter = str_replace(' ', '&nbsp;', $delimiter);

        if (count($this->variants)) {
            $variants = $this->variants->asArrayOfObjects();
        }

        $answer = '';
        if ($arr = unserialize($questionResult->variant)) {
            foreach ($arr as $key => $i) {

                if ($i) {
                    if ($i !== $key) {
                        $style = 'border: 2px red solid;';
                    } else {
                        $style = '';
                    }

                    $tagValue = isset($variants[$i]) ? "{$variants[$key]->data} = {$variants[$i]->variant}" : $i;

                    $answer .= "<span style='margin-left:1px; padding: 0 3px; {$style}'>{$tagValue}{$delimiter}</span>";
                }
            }
        } elseif (!empty($questionResult->free_variant)) {
            $answer .= "{$questionResult->free_variant}{$delimiter}";
        }

        return $answer;
    }
}
