<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<h2><?php echo $this->question->question?></h2>
<?php
    $fileService = Zend_Registry::get('serviceContainer')->getService('Files');
    $file = $fileService->getOne($fileService->find($this->question->file_id));
    $fileUrl = $file->getUrl();
?>
<img src="<?php echo $fileUrl; ?>">
<?php if(count($this->question->variants) && $this->params['with_answer']) : ?>
<table>
    <?php foreach($this->question->variants as $variant) : ?>
    <tr class="<?= $this->cycle(array('odd', 'even'))->next() ?>">
        <td class="quest-item-value">
            <input type="checkbox" class="quest-variant" name="results[<?php echo $this->question->question_id;?>][]"
                   value="<?php echo $variant->question_variant_id;?>"
            <?php if ($variant->is_correct) :?>checked<?php endif;?>
            >
        </td>
        <td><?php echo $variant->variant?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
