<button style="margin:10px 0;" onClick="javascript: history.go(-1);"><?php echo _('Назад');?></button>
<div id="hm-training-modules-viewer">
</div>
<?php
$url = $this->url(array(
    'module' => 'resource',
    'controller' => 'index',
    'action' => 'view',
    'resource_id' => $this->resourceId,
    'revision_id' => $this->revisionId
));
$this->HM()->create('hm.core.ui.trainingModulesViewer.Viewer', array(
    'renderTo' => '#hm-training-modules-viewer',
    'loadUrl' => $url
));
?>
<?= $this->proctoringStudent($this->lessonId); ?>