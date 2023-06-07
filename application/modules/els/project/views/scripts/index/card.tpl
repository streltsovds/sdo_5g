<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');?>
<div class="tmc-project at-form-report">
    <div class="report-summary clearfix">
        <div class="left-block">
            <?php echo $this->partial('list/card.tpl', null, array('project' => $this->project, 'graduated' => $this->graduated, 'teachers' => $this->teachers));?>
        </div>
        <div class="right-block clearfix">
            <?php if ($this->showProtocol):?>
                <a class="report-link download-report-link " href="<?php echo $this->url(array('action' => 'download-protocol', 'project_id' => $this->project->projid));?>">
                    <?php echo _('Сводный итоговый протокол');?>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="report-summary clearfix">
        <p><?php echo $this->project->description?></p>
    </div>
</div>
