<?php
class HM_Quest_Question_Type_PlaceholderModel extends HM_Quest_Question_QuestionModel implements HM_Quest_Question_Type_Interface
{
    const PLACEHOLDER_PATTERN = '/(\[.*?\])/';
    const VARIANT_VARIANTS_DELIMETER = ';';
    const VARIANT_WRONG_MARKER = '^';

    const MODE_DISPLAY_INPUT = 0;
    const MODE_DISPLAY_SELECT = 1;
    const MODE_DISPLAY_MULTYSELECT = 2;

    protected function _serializeData(&$data)
    {
        $data['data'] = serialize(array(
            'mode_display' => $data['mode_display']
        ));

        unset($data['mode_display']);
    }

    public static function getDisplayModes() {
        return array(
            self::MODE_DISPLAY_INPUT       => _('Заполнение пропуска'),
            self::MODE_DISPLAY_SELECT      => _('Выбор одного значения из списка'),
            self::MODE_DISPLAY_MULTYSELECT => _('Множественный выбор из списка'),
        );
    }

    public function getResult($value)
    {
        $return = array('variant' => serialize($value), 'is_correct' => 0);
        if (is_object($this->variants)) {
            $variants = $this->variants->asArrayOfObjects();
        } else {
            return $return;
        }

        $rightAnswerCount = 0;
        if (count($this->quest)) {
            foreach ($variants as $variant) {
                //немножко наркомании
                $variantVariants = explode(self::VARIANT_VARIANTS_DELIMETER, $variant->variant);
                $this->clearWrongVariants($variantVariants);

                if (is_array($value[$variant->question_variant_id])) {
                    $multiCount = 0;
                    foreach ($value[$variant->question_variant_id] as $multiVariant) {
                        if (in_array(trim(strtolower($multiVariant)), $variantVariants)) {
                            $multiCount++;
                        }
                        if (count($variantVariants) == $multiCount) {
                            $rightAnswerCount++;
                        }
                    }
                }
                elseif (in_array(trim(strtolower($value[$variant->question_variant_id])), $variantVariants)) {
                    $rightAnswerCount++;
                }
            }
        }

        if($rightAnswerCount == count($variants) && (is_object($this->variants))) {
            $return['is_correct'] = 1;
        }

        $rightAnswerPercent = $rightAnswerCount / count($variants);

        $return['score_weighted'] = $this->score_min + (($this->score_max - $this->score_min) * $rightAnswerPercent);
        $return['score_raw'] = ($return['is_correct'] ? 1 : 0);
        return $return;
    }

    public function displayUserResult($questionResult, $delimiter = HM_Quest_Question_QuestionModel::RESULT_DEFAULT_DELIMITER, $context = HM_Quest_Question_QuestionModel::RESULT_CONTEXT_ATTEMPT)
    {
        $contextAttempt = $context === HM_Quest_Question_QuestionModel::RESULT_CONTEXT_ATTEMPT;

        if (count($this->variants)) {
            $variants = is_array($this->variants) ? $this->variants : $this->variants->asArrayOfObjects();
        }

        $variant = array();
        $pattern = array_fill(0, count($this->variants), HM_Quest_Question_Type_PlaceholderModel::PLACEHOLDER_PATTERN);
        $preparedPlaceholders = array_map(function($item) {
            return "placeholder_{$item->question_variant_id}";
        }, $variants);

       if ($contextAttempt) {
            $preparedQuestion = preg_replace($pattern, $preparedPlaceholders, $this->question, 1);
            $answer = $preparedQuestion;
       } else {
            $answer = implode('', $preparedPlaceholders);
       }
        
        if ($variant = unserialize($questionResult->variant)) {
            foreach($variants as $variantId => $item) {

	            $itemVariants = explode(self::VARIANT_VARIANTS_DELIMETER, $item->variant);

                if (is_array($variant[$variantId])) {
	                $this->clearWrongVariants($itemVariants);

	                $color = count(array_diff($itemVariants, $variant[$variantId]) === 0) ? '#4CAF50' : 'red';
                    $rq = implode(', ', $variant[$variantId]);

                } else {
                    $rq = $variant[$variantId];
	                $color = in_array($rq, $itemVariants) ? '#4CAF50' : 'red';
                }


	            $answer = str_replace("placeholder_{$variantId}",
                    "<span style='margin-left:1px; border:2px solid {$color}; padding: 0 3px;'>{$rq}{$delimiter}</span>",
                    $answer);
            }
        }
        else {
            foreach ($preparedPlaceholders as $ph) {
                $border = $contextAttempt ? 'border:2px solid blue; ' : '';
                
                $answer = str_replace($ph,
                    "<span style='margin-left:1px; {$border}padding: 0 3px;'>&nbsp;</span>",
                    $answer);
            }

        }

        return $answer;
    }

	/**
	 * @param $variantVariants
	 */
	private function clearWrongVariants(&$variantVariants)
	{
		array_walk($variantVariants, function(&$item) {
			$item = trim(strtolower($item));
		});

		foreach ($variantVariants as $k => $variantVariant) {
			if (strstr($variantVariant, self::VARIANT_WRONG_MARKER)) {
				unset($variantVariants[$k]);
			}
		}
	}

}