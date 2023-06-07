<?= $this->proctoringStudent($this->lessonId); ?>
<?php
    
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/competencies.css'), 'screen,print');
    $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/forms.css'), 'screen,print');

    $this->headScript()->appendFile($this->serverUrl('/js/lib/fileupload/jquery.iframe-transport.min.js'));
    $this->headScript()->appendFile($this->serverUrl('/js/lib/fileupload/jquery.fileupload.min.js'));
    $this->headScript()->appendFile($this->serverUrl('/js/lib/fileupload/jquery.fileupload-ui.min.js'));
    $this->headScript()->appendFile($this->serverUrl('/js/lib/fileupload/jquery.fileupload-fileremove.js'));

    $containerId = $this->id('at-form');

    $settings = $this->model['quest']->getSettings();
?>
<hm-test
        context-helper="<?php echo $this->url(array('module' => $this->module, 'controller' => $this->controller, 'action' => 'context-helper', 'context-helper-action' => 'info'));?>"
        load="<?php echo $this->url(array('module' => $this->module, 'controller' => $this->controller, 'action' => 'load'));?>"
></hm-test>
