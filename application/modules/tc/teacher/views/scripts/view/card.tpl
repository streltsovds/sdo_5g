<div class="at-form-report">
    <?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');?>
    <div class="report-summary clearfix">
        <div class="left-block">
            <h2><?php echo _('Общая информация');?><?php if($this->canEdit):?><a class="edit" href="<?php echo $this->url(array('module' => 'teacher', 'controller' => 'edit', 'action' => 'edit'))?>" class="edit">&nbsp;</a><?php endif; ?></h2>
            <?php echo $this->reportList($this->teacher->getCardFields());?>
        </div>
    </div>
    <div class="report-summary clearfix">
        <div class="left-block">
            <h2><?php echo  _('Дополнительная информация');?><?php if($this->canEdit):?><a class="edit" href="<?php echo $this->url(array('module' => 'teacher', 'controller' => 'edit', 'action' => 'edit'))?>" class="edit">&nbsp;</a><?php endif; ?></h2>
            <?php echo $this->reportList($this->details);?>
        </div>
    </div>
</div>
