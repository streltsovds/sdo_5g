<?php if ($this->quest && $this->quest->quest_id) : ?>
<?php $this->inlineScript()->captureStart(); ?>
//disable radiogroup
$(document).ready(function() {
    var pollMode = <?=$this->quest->scale_id > 0 ? 1 : 0?>;
    if (pollMode == 0) {
        $('#poll_mode-1').attr('disabled','disabled');
    } else {
        $('#poll_mode-0').attr('disabled','disabled');
    }
})
<?php $this->inlineScript()->captureEnd(); ?>
<?php endif; ?>
<?php echo $this->form?>