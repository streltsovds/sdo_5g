<?php echo $this->markSheetTable('page');?>

<?php ?>


<?php $this->inlineScript()->captureStart(); ?>


var oldACstate  = localStorage.getItem("accordion-column-state")
$(function() {
    if (oldACstate == '"expanded"') {
        $('.container-ear').click();
        setTimeout(function() {
            localStorage.setItem("accordion-column-state", '"expanded"');
        }, 1000);
    }
});


<?php $this->inlineScript()->captureEnd(); ?>
