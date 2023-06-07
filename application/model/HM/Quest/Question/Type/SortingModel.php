<?php
class HM_Quest_Question_Type_SortingModel extends HM_Quest_Question_QuestionModel implements HM_Quest_Question_Type_Interface
{
    //нужна для кодирования айдишников ответов
    const SALT = 'CPWqNDMUrv';

    public function getResult($values)
    {
        $variants = is_array($this->variants) ? $this->variants : $this->variants->asArrayOfObjects();

        if (is_array($values)) {
            // TODO: солить options перед отправкой на фронтенд
            // for test begin
            $values2 = array();
            foreach ($values as $key => $value) {
                $values2[md5($key . self::SALT)] = $value;
            }
            $values = $values2;
            // for test end

            $valuesHashes = array();
            foreach ($variants as $key => $value) {
                $valuesHashes[md5($key . self::SALT)] = $key;
            }

            $decodedValues = array();
            foreach ($values as $key => $value) {
                if ($value !== '') {
                    $decodedValues[$valuesHashes[$key]] = $value;
                }
            }
            $values = $decodedValues;
        }

        $return = array('variant' => serialize($values), 'free_variant' => '');
        if (is_array($values) && (count($values) > 0)) {
            //        $values_results = array();
            if (count($this->quest)) {
                $return['is_correct'] = true;
                foreach ($values as $key => $value) {
                    if (!isset($variants[$key])) continue;
                    $return['is_correct'] = $return['is_correct'] && ($variants[$key]->data === $value);
                    //                    $values_results[] = array(
                    //                        'data'    => $variants[$key]->data,
                    //                        'variant' => $variants[$value]->variant,
                    //                    );
                }
                // несмотря на то, что MODE_SCORING_CORRECT, записываем score_raw и score_weightedб т.к. именно они используются дальше в расчётах
                //            }
            }

            if ($this->show_free_variant) {
                foreach ($values as $value) {
                    if (!isset($variants[$value])) {
                        $return['free_variant'] = trim($value);
                    }
                }
            }
        } else {
            $return['is_correct'] = false;
        }

        $return['score_weighted'] = ($return['is_correct'] ? $this->score_max : $this->score_min);
        $return['score_raw'] = ($return['is_correct'] ? 1 : 0);

        return $return;
    }

    public function getScoreWeighted($variantIds)
    {
        return 0;
    }

    public function displayUserResult($questionResult, $delimiter = HM_Quest_Question_QuestionModel::RESULT_DEFAULT_DELIMITER, $context = HM_Quest_Question_QuestionModel::RESULT_CONTEXT_ATTEMPT)
    {
        if (count($this->variants)) {
            $variants = $this->variants->asArrayOfObjects();
        }

        $answer = array();
        if ($arr = unserialize($questionResult->variant)) {
            foreach ($arr as $key => $value) {
                $answer[$value] = "{$value} = {$variants[$key]->variant}";
            }
        } elseif (!empty($questionResult->free_variant)) {
            $answer[] = $questionResult->free_variant;
        }

        ksort($answer);

        return implode($delimiter, $answer);
    }
}
