<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<h2><?php echo $this->question->question?></h2>
<table>
    <?php $variants = $this->question->variants;
        if (!$this->params['with_answer']) {
            shuffle($variants);
        }
        $counter = 1;
    ?>
    <?php foreach ($variants as $key => $variant):?>
    <?php if (!strlen(trim($variant))) continue;?>
    <tr class="<?= $this->cycle(array('odd', 'even'))->next() ?>">
        <td class="quest-item-value">
            <input style="width:2em;height:2em;" type="text" class="quest-variant"
               name="results[<?php echo $this->question->question_id;?>]"
               value=" <?php echo ($this->params['with_answer']) ? $counter++ : ''; ?>"
            >
        </td>
        <td class="title">
            <?php echo $variant->variant?>
        </td>
    </tr>
    <?php endforeach;?>
</table>