<?php 
echo $this->form;
?>

<?php $this->inlineScript()->captureStart(); ?>

$('#base_sum').on('focusout', function(){
        if(!$.isNumeric($('#corrected_sum').val())){
                $('#corrected_sum').val($('#base_sum').val());
        }
});

<?php $this->inlineScript()->captureEnd(); ?>

<?php
