<?php
class HM_Quest_Question_Type_MultipleModel extends HM_Quest_Question_QuestionModel implements HM_Quest_Question_Type_Interface
{
    const RESULT_DEFAULT_DELIMITER = '<br>';

    public function getResult($values)
    {
        if (!$values) {
            $values = array();
        }

        $return = array(
            'variant' => serialize($values),
            'free_variant' => '',
        );

        if (!empty($this->variants)) {

            $variants = $this->variants->asArrayOfObjects();

            if (count($this->quest)) {

                if ($this->mode_scoring == static::MODE_SCORING_WEIGHT) {

                    $return['score_raw'] = 0;

                    if (is_array($values)) {

                        foreach ($values as $value) {

                            if (!isset($variants[$value])) {
                                continue;
                            }

                            $return['score_raw'] += $variants[$value]->weight;
                        }

                    }

                    if (!$this->show_free_variant) {
                        $return['score_weighted'] = $this->getScoreWeighted($values);
                    }

                } elseif ($this->mode_scoring == static::MODE_SCORING_CORRECT) {
                    // @todo: может быть более хитрая формула
                    // сейчас нужно полное совпадение отмеченных и неотмеченных вариантов
                    $return['is_correct'] = $this->_answerIsCorrect($values, $variants);
                    // несмотря на то, что MODE_SCORING_CORRECT, записываем score_raw и score_weightedб т.к. именно они используются дальше в расчётах
                    $return['score_weighted'] = ($return['is_correct'] ? $this->score_max : $this->score_min);
                    $return['score_raw'] = ($return['is_correct'] ? 1 : 0); 
                }
            }

            if ($this->show_free_variant) {
                foreach ($values as $value) {
                    if (!isset($variants[$value])) {
                        $return['free_variant'] = trim($value);
                    }
                }
            }
        }
        return $return;
    }

    public function getScoreWeighted($variantIds)
    {
        if (is_a($this->variants, 'HM_Collection') && count($this->variants)) {

            $variants = $this->variants->asArrayOfObjects();
            $weights = $this->variants->getList('question_variant_id', 'weight');

            $allPossibleSums = $this->_getPossibleWeightsSumsScale($weights);
            $multiplier = ($this->score_max - $this->score_min) / (count($allPossibleSums) - 1);

            $weightCur = $weightMin = $weightMax = 0;
            foreach ($weights as $weight) {
                $maxOrMin = ($weight > 0) ? 'weightMax' : 'weightMin';
                $$maxOrMin += $weight;
            }

            foreach ($variantIds as $value) {
                $weightCur += $variants[$value]->weight;
            }

            if ($weightMax != $weightMin) {
                $weightPosition = array_search($weightCur, $allPossibleSums);

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

    /**
     * @method Построение шкалы из всех возможных сумм весов вариантов ответов
     * @return array Упорядоченный массив всевозможных сумм
     */
    protected function _getPossibleWeightsSumsScale($weights)
    {
        sort($weights);

        $scale = [];
        foreach ($weights as $i => $weight) {
            if (!array_key_exists($weight, $scale)) {
                $scale[$weight] = $weight;
            }

            foreach ($weights as $j => $w) {
                if ($i === $j) {
                    continue;
                }

                $sum = $weight + $w;

                if (!array_key_exists($weight, $scale)) {
                    $scale[$sum] = $sum;
                }
            }
        }

        sort($scale);

        return $scale;
    }

    protected function _answerIsCorrect($values, $variants)
    {
        $result = true;

        if (is_array($values)) {

            $correctVariants = array();

            foreach ($variants as $variantId => $variant) {

                if ($variant->is_correct) {
                    $correctVariants[$variantId] = true;
                }

            }

            foreach ($values as $value) {

                if (!isset($variants[$value])) {
                    continue;
                }

                if (!isset($correctVariants[$value])) {
                    $result = false;
                }

                unset($correctVariants[$value]);

            }

            if (count($correctVariants)) {
                $result = false;
            }

        } else {
            $result = false; // если даже не пытались ответить
        }

        return $result;

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

    public function displayUserResult($questionResult, $delimiter = self::RESULT_DEFAULT_DELIMITER, $context = HM_Quest_Question_QuestionModel::RESULT_CONTEXT_ATTEMPT)
    {
        $delimiter = str_replace(' ', '&nbsp;', $delimiter);

        $contextAttempt = $context === HM_Quest_Question_QuestionModel::RESULT_CONTEXT_ATTEMPT;
        $contextDiagram = $context === HM_Quest_Question_QuestionModel::RESULT_CONTEXT_DIAGRAM;

        // Варианты ответа по данному вопросу ( с указанимем правильных и неправильных)
        $variants = array();
        foreach ($this->variants as $variant) {
            $variants[$variant->question_variant_id] = array('text' => $variant->variant, 'isCorrect' => ($variant->is_correct || ($variant->weight !== null && $variant->weight > 0)) ? 1 : 0);
        }

        // Ответы пользователя (с анализом правильных и неправильных)
        $result = array();
        $answers = unserialize($questionResult->variant);
        foreach ($answers as $answer) {
            if (isset($variants[$answer])) $result[$answer] = $variants[$answer];
        }

        if ($contextAttempt) {
            // Правильные варианты, которые не выбрал пользователь
            foreach ($variants as $key => $variant) {
                if ($variant['isCorrect'] && !isset($result[$key])) {
                    $variant['isCorrect'] = -1;
                    $variant['text'] = "{$variant['text']} (Не выбран)";
                    $result[$key] = $variant;
                }
            }
        }

        // Текстовое представление
        $answer = '';
        foreach ($result as $item) {
            $style = '';
            
            if ($item['isCorrect'] == 0) {
                $style = 'border: 2px red solid;';
            } elseif ($item['isCorrect'] == -1) {
                $style = 'font-style: italic; color: #509465;';
            }
            
            if ($contextDiagram) {
                $answer .= sprintf('%s%s', $item['text'], $delimiter);
            } else {
                $answer .= sprintf('<span style="margin-left:1px; padding: 3px; %s">%s%s</span>', $style, $item['text'], $delimiter);
            }
        }

        return $answer;
    }
}