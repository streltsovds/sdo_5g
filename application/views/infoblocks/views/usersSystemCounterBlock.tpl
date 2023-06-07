<hm-users-system-counter
		date-picker-from-label="<?php echo _('За период c');?>"
		date-picker-to-label="<?php echo _('по');?>"
		:date-picker-from-value='<?php echo HM_Json::encodeErrorSkip($this->from); ?>'
		:date-picker-to-value='<?php echo HM_Json::encodeErrorSkip($this->to); ?>'
		:items='<?php echo HM_Json::encodeErrorSkip($this->items); ?>'
		url="<?php echo $this->url(array('module' => 'infoblock', 'controller' => 'user-counter', 'action' => 'get-stats')); ?>"
></hm-users-system-counter>