<?php if (!$this->gridAjaxRequest):?>
    <?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css'); ?>
<?php endif;?>
<?php echo $this->grid?>
<?php if (!$this->gridAjaxRequest):?>
<?php echo $this->footnote();?>
<?php endif;?>
<?php $this->inlineScript()->captureStart(); ?>
    jQuery(document).ready(function(){
        jQuery('#_fdiv [multiple]').attr('size','1');
        jQuery('#_fdiv [multiple]').removeAttr('multiple');
    });
<?php $this->inlineScript()->captureEnd(); ?>
