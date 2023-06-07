<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<h2><?php echo $this->question->question?></h2>
<?php $variants = array(); $data = array();?>
<?php foreach ($this->question->variants as $variant) {
        if (!in_array($variant->variant, $variants)){
            $variants[] = $variant->variant;
            $data[] = $variant->data;
        }
    }
    if (!$this->params['with_answer']) {
        shuffle($data);
    }
?>
<table style="text-align:center;">
    <?php foreach ($variants as $key => $variant) : ?>
    <?php if (!strlen(trim(variant))) continue;?>
    <tr class="<?= $this->cycle(array('odd', 'even'))->next() ?>">
        <td width="50%">
            <?php echo $data[$key] ?>
        </td>
        <td width="50%">
            <?php echo $variant ?>
        </td>
    </tr>
    <?php endforeach;?>
</table>