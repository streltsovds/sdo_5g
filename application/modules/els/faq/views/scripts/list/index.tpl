<?php if ($this->viewType != 'table'):
    echo $this->actions();
?>

    <hm-faq
        :items='<?= HM_Json::encodeErrorSkip($this->items) ?>'
        :page-number="<?= $this->currentPage ?>"
        :pages="<?= $this->pageCount ?>"
        :url="`/faq/list/index/`"
    ></hm-faq>
<?php else: ?>
    <?php echo $this->grid;?>
<?php endif; ?>
