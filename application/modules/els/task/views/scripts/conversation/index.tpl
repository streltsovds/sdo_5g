<?= $this->proctoringStudent($this->lesson_id); ?>
<hm-job-interface
        :lesson-id='<?php echo $this->lesson_id ?>'
        :user-id='<?php echo $this->user_id ?>'
        :lesson='<?php echo $this->lesson ?>'
        :task='<?php echo $this->task ?>'
        :variant='<?php echo $this->variant ?>'
        :is-end-user='<?php echo $this->isEnduser ?>'
/>
