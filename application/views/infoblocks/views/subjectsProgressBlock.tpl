<hm-chart-subject-progress-block
		:orgstructure-tree='<?php echo $this->orgstructureTree; ?>'
		:job-profiles-data='<?php echo $this->jobProfiles; ?>'
		url="<?php echo $this->url(array('module' => 'infoblock', 'controller' => 'subject', 'action' => 'progress')); ?>"
		:selected-unit='<?php echo $this->unit; ?>'
		:selected-profile='<?php echo $this->profileId; ?>'
></hm-chart-subject-progress-block>