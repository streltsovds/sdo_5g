<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<h2><?php echo $this->question->question?></h2>
<?php $variants = array(); $data = array();?>
<?php foreach ($this->question->variants as $variant) {
        if (!in_array($variant->variant, $variants) || $this->params['with_answer']){
            $variants[] = $variant->variant;
        }
        if (!in_array($variant->data, $data) ){
            $data[] = $variant->data;
        }
    }

    if (!$this->params['with_answer']){
        shuffle($data);
        shuffle($variants);
    }
?>
<table style="text-align:center;">
    <?php foreach ($data as $key => $data) : ?>
    <?php if (!strlen(trim($variant))) continue;?>
    <tr class="<?= $this->cycle(array('odd', 'even'))->next() ?>">
        <td width="50%">
            <?php echo $data ?>
        </td>
        <td width="50%">
            <?php echo $variants[$key] ?>
        </td>
    </tr>
    <?php endforeach;?>
</table>