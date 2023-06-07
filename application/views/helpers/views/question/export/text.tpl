<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<h2><?php echo $this->question->question?></h2>
<?php
    $answers = '';
    if(count($this->question->variants) && $this->params['with_answer']) {
        $answerArray = array();
        foreach($this->question->variants as $variant) {
            $answerArray[] = $variant->variant;
        }
        $answers = implode(', ', $answerArray);
    }
?>
<?php // этот hidden нужно для обработки free_variant?>
<input type="hidden" name="results[<?php echo $this->question->question_id;?>]" value="<?php echo HM_Quest_Question_Variant_VariantModel::FREE_VARIANT;?>">
<table>
<tr class="<?= $this->cycle(array('odd', 'even'))->next() ?>">
    <td class="quest-text-variant">
        <input type="text" class="quest-variant"
            name="results[<?php echo $this->question->question_id;?>]"
            value="<?php echo $answers; ?>">
    </td>
</tr>
</table>