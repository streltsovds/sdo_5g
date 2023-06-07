<hm-chart-comparison-block
		date-picker-from-label="<?php echo _('За период c');?>"
		date-picker-to-label="<?php echo _('по');?>"
		:date-picker-from-value='<?php echo $this->from; ?>'
		:date-picker-to-value='<?php echo $this->to; ?>'
		:orgstructure-tree='<?php echo $this->orgstructureTree; ?>'
		url="<?php echo $this->url(array('module' => 'infoblock', 'controller' => 'hiring-dismissal', 'action' => 'stats-by-departments')); ?>"
		:selected-unit='<?php echo $this->unit; ?>'
></hm-chart-comparison-block>