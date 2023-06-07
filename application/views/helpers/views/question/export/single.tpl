<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<h2><?php echo $this->question->question?></h2>
<table>
<?php foreach ($this->question->variants as $variant):?>
<?php if (!strlen(trim($variant->variant))) continue;?>
<tr class="<?= $this->cycle(array('odd', 'even'))->next() ?>">
    <td class="quest-item-value">
        <input type="radio" class="quest-variant" name="results[<?php echo $this->question->question_id;?>]"
            value="<?php echo $variant->question_variant_id;?>"
            <?php if ($variant->is_correct && $this->params['with_answer']) :?>checked<?php endif;?>
        >
    </td>
    <td class="title">
        <?php echo $variant->variant?>
    </td>
</tr>
<?php endforeach;?>
<?php if ($this->question->show_free_variant): ?>
<tr class="<?= $this->cycle(array('odd', 'even'))->next() ?>">
    <td class="quest-item-value">
        <input type="radio" class="quest-variant" name="results[<?php echo $this->question->question_id;?>]"
            value="<?php echo HM_Quest_Question_Variant_VariantModel::FREE_VARIANT;?>"
            <?php if ($this->free_variant) :?>checked<?php endif;?>
        >
    </td>
    <td class="quest-free-variant"><?php echo new Zend_Form_Element_Text(
            $id = "results_{$this->question->question_id}", 
            array(
                'Value' => $this->free_variant ? $this->free_variant : _('другое'), 
            ));?></td>
</tr>
<?php endif;?>
</table>
