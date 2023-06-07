<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<h2><?php echo $this->question->question?></h2>
<table>
<?php foreach ($this->question->variants as $subjectId => $variant):?>
<tr class="<?= $this->cycle(array('odd', 'even'))->next() ?>">
    <td class="quest-item-value"><input type="checkbox" class="quest-variant" name="results[<?php echo $this->question->question_id;?>][]" value="<?php echo $subjectId;?>" <?php if (is_array($this->result) && in_array($subjectId, $this->result)) :?>checked<?php endif;?>></td>
    <td><?php echo $variant?></td>
</tr>
<?php endforeach;?>
<?php if ($this->question->show_free_variant): ?>
<tr class="<?= $this->cycle(array('odd', 'even'))->next() ?>">
    <td class="quest-item-value"><input type="checkbox" class="quest-variant" name="results[<?php echo $this->question->question_id;?>][]" value="<?php echo HM_Quest_Question_Variant_VariantModel::FREE_VARIANT;?>" <?php if ($this->free_variant) :?>checked<?php endif;?>></td>
    <td class="quest-free-variant"><?php echo new Zend_Form_Element_Text(
            $id = "results_{$this->question->question_id}", 
            array(
                'Value' => $this->free_variant ? $this->free_variant : _('другое'), 
            ));?></td>
</tr>
<?php endif;?>
</table>
<?php // @todo: введенное значение не должно пропадать onFocus?>
<?php  $this->inlineScript()->captureStart(); ?>
    $("input<?php echo $id ? '#'.$id : '';?>").focus(function(){
        $(this).css('color', '#000').css('font-style', 'normal').val('');
    });
<?php $this->inlineScript()->captureEnd(); ?>