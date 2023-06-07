<?php $actions = json_encode($this->actionsData(), JSON_PRETTY_PRINT | JSON_HEX_APOS); ?>
<hm-forum :forum="view.forum" :actions='<?= $actions?>'/>