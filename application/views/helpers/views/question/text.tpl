<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<h2><?php echo $this->question->question?></h2>
<?php // этот hidden нужно для обработки free_variant?>
<input type="hidden" name="results[<?php echo $this->question->question_id;?>]" value="<?php echo HM_Quest_Question_Variant_VariantModel::FREE_VARIANT;?>">
<table>
<tr class="<?= $this->cycle(array('odd', 'even'))->next() ?>">
    <td class="quest-text-variant"><?php echo new Zend_Form_Element_Text(
            $id = "results_{$this->question->question_id}", 
            array(
                'Value' => $this->free_variant ? $this->free_variant : '', 
            ));?></td>
</tr>
</table>