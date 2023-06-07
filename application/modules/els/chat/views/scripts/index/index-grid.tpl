<?php
if (!$this->gridAjaxRequest) {
    if (!$this->channel->lesson_id && !$this->isCallFromLesson) {
        if (!$this->channel->lesson_id) {
            echo $this->actions();
        } elseif($this->canCreate) {
            echo $this->actions();
        }
    }
    echo $this->headScript();
} ?>

<?php echo $this->grid; ?>