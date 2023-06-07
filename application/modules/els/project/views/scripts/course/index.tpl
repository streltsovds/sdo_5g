<div style="padding-right: 10px;">
    <?php if ($this->course->format == HM_Course_CourseModel::FORMAT_FREE):?>
        <?php echo $this->headSwitcher(array(
                                           'module' => 'project',
                                           'controller' => 'course',
                                           'action' => 'index',
                                           'switcher' => 'index',
                                           'project_id' => (int) $this->projectId,
                                           'course_id' => (int) $this->courseId,
                                           'owner' => $this->course->chain
        ), 'course');?>
    <?php endif;?>
    <?php echo $this->action('index', 'index', 'course', array('project_id' => $this->projectId, 'course_id' => $this->courseId, 'withoutContextMenu' => true));?>
</div>