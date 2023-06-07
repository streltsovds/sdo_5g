<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<h2><?php echo $this->question->question?></h2>
<table>
<?php foreach ($this->question->variants as $variant):?>
<?php if (!strlen(trim($variant->variant))) continue;?>
<tr class="<?= $this->cycle(array('odd', 'even'))->next() ?>">
    <td class="quest-item-value-mapping">
        <?php
            echo $variant->data;
        ?>
    </td>
    <td class="quest-item-value-mapping-right">
        <select name="results[<?=$this->question->question_id;?>][<?=$variant->question_variant_id;?>]">
            <?php
                $variants = $this->question->variants;
                shuffle($variants);
                foreach ($variants as $k => $option) {
                    $val1 = md5($option->question_variant_id.HM_Quest_Question_Type_MappingModel::SALT);
                    $val2 = md5($variant->question_variant_id.HM_Quest_Question_Type_MappingModel::SALT);
                    $selected = '';
                    if (is_array($this->result) && isset($this->result[$variant->question_variant_id])) {
                        if ($val1 == $this->result[$variant->question_variant_id]) {
                            $selected = ' selected="selected" ';
                        }
                    }
                    ?><option value="<?= $val1 ?>" <?= $selected; ?>><?=$option->variant;?></option><?php
                }
            ?>
        </select>
    </td>
</tr>
<?php endforeach;?>
<?php if ($this->question->show_free_variant): ?>
<tr class="<?= $this->cycle(array('odd', 'even'))->next() ?>">
    <td class="quest-item-value-mapping"><input type="checkbox" class="quest-variant" name="results[<?php echo $this->question->question_id;?>]" value="<?php echo HM_Quest_Question_Variant_VariantModel::FREE_VARIANT;?>" <?php if ($this->free_variant) :?>checked<?php endif;?>></td>
    <td class="quest-free-variant"><?php echo new Zend_Form_Element_Text(
            $id = "results_{$this->question->question_id}", 
            array(
                'Value' => $this->free_variant ? $this->free_variant : _('другое'), 
            ));?></td>
</tr>
<?php endif;?>
</table>