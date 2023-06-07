<?php if ($this->subjectId): ?>
    <?php echo $this->headSwitcher(array(
       'module' => 'course',
       'controller' => 'constructor',
       'action' => 'index',
       'switcher' => 'edit',
       'subject_id' => (int) $this->subjectId,
       'course_id' => (int) $this->courseId,
       'owner' => $this->course->chain
    ), 'course'); ?>
<?php endif; ?>
<div id="hm-constructor"></div>