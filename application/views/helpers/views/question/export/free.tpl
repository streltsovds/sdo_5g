<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<input type="hidden" name="results[<?php echo $this->question->question_id;?>]" value="<?php echo HM_Quest_Question_Variant_VariantModel::FREE_VARIANT;?>">
<h2><?php echo $this->question->question?></h2>
<div class="free">
<?php echo new HM_Form_Element_JqTinyMce("results_{$this->question->question_id}", array('Value' => $this->result));?>
</div>