<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<h2><?php echo $this->question->question?></h2>
<?php
echo $this->imageMap('results', $this->question->variants, array(
    'readOnly' => true,
    'value' => $this->result,
    'imageFileId' => $this->question->file_id,
    'showVariants' => (bool) (int) $this->question->show_variants
));
$this->inlineScript()->captureStart();
?>
$("input").focus(function(){
$(this).css('color', '#000').css('font-style', 'normal').val('');
});
<?php $this->inlineScript()->captureEnd(); ?>