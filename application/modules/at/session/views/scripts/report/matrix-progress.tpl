<?php
    $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/report_matrix.css'), 'screen');
?>

<hm-matrix-progress
    :departments='view.departments'
    :users='view.usersByDepartments'
>
</hm-matrix-progress>