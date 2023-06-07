<?php echo $this->grid?>
<?php $this->inlineScript()->captureStart(); ?>
    jQuery(document).ready(function(){
        jQuery('#_fdiv [multiple]').attr('size','1');
        jQuery('#_fdiv [multiple]').removeAttr('multiple');
    });
<?php $this->inlineScript()->captureEnd(); ?>
