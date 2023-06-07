<?php
    //Zend_Registry::get('serviceContainer')->getService('Unmanaged')->getController()->setView('DocumentBlank');
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/competencies.css'), 'screen,print');
    $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/forms.css'), 'screen,print');
    $containerId = $this->id('at-form');
?>
<div class="at-competence at-form hm-at-competence-form-quest-<?php $this->model['quest']->type;?>">
    <div class="at-form-body" style="margin:20px !important;">
        <div id="<?= $containerId ?>" class="at-form-container">
            <?php echo $this->action('load', $this->controller, $this->module) ?>
        </div>
    </div>
</div>

<script>

</script>
