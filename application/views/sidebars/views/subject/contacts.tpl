<?php

$id = $this->model->subid; //subject_id
$title = $this->model->name;
$icon = $this->model->getIcon();
$iconCourse = $this->model->getUserIcon();

?>
<hm-contacts-sidebar
        <?php if( !is_null($iconCourse) ) :?>
        :url-icon-course="`<?php echo $iconCourse ?>`"
        <?php endif; ?>
        :subject-id="<?php echo $id ?>"
        :subject-title="`<?php echo htmlspecialchars($title) ?>`" />
