<style>
    .ui-datepicker {
        z-index: 100 !important;
    }
</style>
<?php if (!$this->isAjaxRequest && Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:recruit:actual-costs:new')):?>
    <?php echo $this->Actions('actual-costs');?>
<?php endif;?>
<?php echo $this->grid;