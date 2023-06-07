<hm-subject-wrapper :data-subject='<?php echo $this->subjects; ?>'/>
<?php if ($this->showEditLink):?>
    <hm-actions-edit :color="colors.textLight" url='<?php echo $this->baseUrl($this->url(array('module' => 'subject', 'controller' => 'slider', 'action' => 'index')))?>'/>
<?php endif; ?>