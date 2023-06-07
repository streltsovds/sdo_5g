<?= $this->proctoringStudent($this->lessonId); ?>
<?php

$iframeUrl = $this->url(
    [
        'module' => 'course',
        'controller' => 'index',
        'action' => 'index',
        'course_id' => $this->courseId
    ]
);

echo $this->materialResponsive(
        null,
        null,
        null,
        [
          'fullHeight' => true,
          'title' => $this->getHeader(),
          'showTitleBelow' => false,
          'iframe' => $iframeUrl
        ]
    );

?>
<?php ?>