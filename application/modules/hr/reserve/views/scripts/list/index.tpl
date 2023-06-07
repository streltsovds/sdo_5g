<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:reserve:list:new')):?>
    <?php if (!$this->gridAjaxRequest):?>
        <?php echo $this->Actions('reserve');?>
    <?php endif; ?>
<?php endif;?>

<?php echo $this->grid;?>
<?php if (!$this->gridAjaxRequest):?>
    <?php echo $this->footnote();?>
<?php endif;?>

<?php if (!$this->isAjaxRequest): ?>
<style>
    .hm-newcomer-dolg {
        color: #ffffff;
        background-color: #cc0000;
        font-weight: bold;
        font-size: 14px;
        padding: 1px 6px;
        border-radius: 3px;
    }
</style>
<?php endif; ?>