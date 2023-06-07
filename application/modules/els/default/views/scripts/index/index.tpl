<div class="page-index__infoblocks">

    <?php if ($indexNavigation = $this->getIndexNavigation()):?>
        <div class="navigation__button">
            <?php echo $this->contextMenu($indexNavigation, false);?>
        </div>
    <?php endif;?>

    <?php echo $this->infoBlocks(); ?>
</div>
<?php if ($this->dataAgreement) : ?>
    <hm-data-agreement
            :content='<?= json_encode($this->dataAgreement["title"])?>'
            :confirm-text='<?= json_encode(_("Согласен"))?>'
            :cancel-text='<?= json_encode(_("Не согласен"))?>'
            :confirm-url='<?= json_encode($this->url(array('module' => 'default','controller' => 'index', 'action' => 'index', 'data_agreement' => '1')))?>'
            :cancel-url='<?= json_encode($this->url(array('module' => 'default','controller' => 'index', 'action' => 'logout')))?>'
    >
    </hm-data-agreement>
<?php endif; ?>