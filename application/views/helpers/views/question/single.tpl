<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<h2><?php echo $this->question->question?></h2>
<?php if ($this->displaymode == HM_Quest_Type_PollModel::DISPLAYMODE_VERTICAL): ?>
<table>
<?php if($this->question->variants)/*бывает без вариантов*/ foreach ($this->question->variants as $variant):?>
<?php if (!strlen(trim($variant->variant))) continue;?>
<?php
    switch ($this->question->mode_scoring) {
        case HM_Quest_Question_QuestionModel::MODE_SCORING_CORRECT:
            $validityValue = $variant->is_correct ? 'green' : '#ff1a1a'; // это красный цвет
            break;
        case HM_Quest_Question_QuestionModel::MODE_SCORING_WEIGHT:
            $validityValue = $this->question->getSelfTestWeights($variant->question_variant_id);
            break;
    }
?>
<tr class="<?= $this->cycle(array('odd', 'even'))->next() ?>">
    <td class="quest-item-value">
        <input type="radio" class="quest-variant" data-color="<?php echo $validityValue; ?>" name="results[<?php echo $this->question->question_id;?>]" value="<?php echo $variant->question_variant_id;?>" <?php if ($this->result == $variant->question_variant_id) :?>checked<?php endif;?>>
    </td>
    <td class="title"><?php echo $variant->variant?></td>
</tr>
<?php endforeach;?>
<?php if ($this->question->show_free_variant): ?>
<tr class="<?= $this->cycle(array('odd', 'even'))->next() ?>">
    <td class="quest-item-value"><input type="radio" class="quest-variant" name="results[<?php echo $this->question->question_id;?>]" value="<?php echo HM_Quest_Question_Variant_VariantModel::FREE_VARIANT;?>" <?php if ($this->free_variant) :?>checked<?php endif;?>></td>
    <td class="quest-free-variant"><?php echo new Zend_Form_Element_Text(
            $id = "results_{$this->question->question_id}", 
            array(
                'Value' => $this->free_variant ? $this->free_variant : _('другое'), 
            ));?></td>
</tr>
<?php endif;?>
    <?php if ($this->displaycomment == HM_Quest_Type_PollModel::DISPLAYCOMMENT_YES): ?>
    <tr class="<?= $this->cycle(array('odd', 'even'))->next() ?>">
        <td class="quest-item-value"><?php echo _('комментарий'); ?></td>
        <td class="title"><textarea class="quest-variant" cols=30 rows=3 name="comment[<?php echo $this->question->question_id;?>]"><?php echo ($this->comment)?$this->comment:''; ?></textarea></td>
    </tr>
    <?php endif; ?>
</table>
<?php elseif ($this->displaymode == HM_Quest_Type_PollModel::DISPLAYMODE_HORIZONTAL): ?>


<table class="table--horizontal">
    <tr>
        <?php if($this->question->variants)/*бывает без вариантов*/ foreach ($this->question->variants as $variant):?>
            <?php if (!strlen(trim($variant->variant))) continue;?>
            <td class="quest-item-value">
                <input type="radio" class="quest-variant" name="results[<?php echo $this->question->question_id;?>]" value="<?php echo $variant->question_variant_id;?>" <?php if ($this->result == $variant->question_variant_id) :?>checked<?php endif;?>>
            </td>
        <?php endforeach;?>

        <?php if ($this->question->show_free_variant): ?>
            <td class="quest-item-value"><input type="radio" class="quest-variant" name="results[<?php echo $this->question->question_id;?>]" value="<?php echo HM_Quest_Question_Variant_VariantModel::FREE_VARIANT;?>" <?php if ($this->free_variant) :?>checked<?php endif;?>></td>
        <?php endif;?>

        <?php if ($this->displaycomment == HM_Quest_Type_PollModel::DISPLAYCOMMENT_YES): ?>
            <td class="quest-item-value quest-item-value--comment"><textarea class="quest-variant" cols=30 rows=3 name="comment[<?php echo $this->question->question_id;?>]"><?php echo ($this->comment)?$this->comment:''; ?></textarea></td>
        <?php endif; ?>
    </tr>
    <tr>
        <?php if($this->question->variants)/*бывает без вариантов*/ foreach ($this->question->variants as $variant):?>
        <?php if (!strlen(trim($variant->variant))) continue;?>

        <td class="title"><?php echo $variant->variant?></td>

    <?php endforeach;?>
    <?php if ($this->question->show_free_variant): ?>

        <td class="quest-free-variant"><?php echo new Zend_Form_Element_Text(
            $id = "results_{$this->question->question_id}",
            array(
                'Value' => $this->free_variant ? $this->free_variant : _('другое'),
            ));?>
        </td>

        <?php endif;?>


        <?php if ($this->displaycomment == HM_Quest_Type_PollModel::DISPLAYCOMMENT_YES): ?>
            <td class="title"><?php echo _('комментарий'); ?></td>
        <?php endif; ?>


    </tr>
</table>



<?php endif;?>
<?php // @todo: введенное значение не должно пропадать onFocus?>
<?php  $this->inlineScript()->captureStart(); ?>
    $("input<?php echo $id ? '#'.$id : '';?>").focus(function(){
        $(this).css('color', '#000').css('font-style', 'normal').val('');
    });

<?php $this->inlineScript()->captureEnd(); ?>