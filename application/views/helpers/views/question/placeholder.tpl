<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<h2>
    <?php
        if (count($this->question->variants) > 0) {
            $pattern = array_fill(0, count($this->question->variants), HM_Quest_Question_Type_PlaceholderModel::PLACEHOLDER_PATTERN);
            $preparedPlaceholders = array_map(function ($item) {
                return "placeholder_{$item->question_variant_id}";
            }, $this->question->variants);
            $preparedQuestion = preg_replace($pattern, $preparedPlaceholders, $this->question->question, 1);
            foreach ($this->question->variants as $variantId => $variant) {
                $multiple = false;
                $varData = unserialize($variant->data);
                $mode_display = $varData['mode_display'];

                switch ($mode_display) {
                    case HM_Quest_Question_Type_PlaceholderModel::MODE_DISPLAY_MULTYSELECT:
                        $multiple = true;
                    case HM_Quest_Question_Type_PlaceholderModel::MODE_DISPLAY_SELECT:
                        $subVariants = explode(HM_Quest_Question_Type_PlaceholderModel::VARIANT_VARIANTS_DELIMETER, $variant->variant);
                        $options = array();
                        foreach ($subVariants as $key => $subVariant) {
                            $variantText = trim(str_replace(HM_Quest_Question_Type_PlaceholderModel::VARIANT_WRONG_MARKER, '', $subVariant));
                            if ($variantText) {
                                $selected = '';
                                if (is_array($this->result[$variantId])) {
                                    if (in_array($variantText, $this->result[$variantId])) {
                                        $selected = ' selected="selected" ';
                                    }
                                } elseif ($this->result[$variantId] == $variantText) {
                                    $selected = ' selected="selected" ';
                                }
                                $options[] = '<option value="' . $variantText . '"' . $selected . '>' . $variantText . '</option>';
                            }
                        }
                        shuffle($options);
                        if ($multiple) {
                            $replacer = '<select name="results[' . $variant->question_id . '][' . $variantId . '][]" multiple="multiple">' . implode('', $options) . '</select>';
                        } else {
                            $replacer = '<select name="results[' . $variant->question_id . '][' . $variantId . ']" ' . $multiple . '>' . implode('', $options) . '</select>';
                        }
                        break;
                    default:
                        $replacer = '<input type="text" name="results[' . $variant->question_id . '][' . $variantId . ']" value="' . $this->result[$variantId] . '">';
                        break;
                }

                $preparedQuestion = str_replace("placeholder_{$variantId}",
                    $replacer,
                    $preparedQuestion);

            }
        }
        echo $preparedQuestion;
    ?>
</h2>
