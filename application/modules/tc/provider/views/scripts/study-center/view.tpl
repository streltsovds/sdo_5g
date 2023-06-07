<div class="at-form-report">
    <?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');?>
    <div class="report-summary clearfix">
        <div class="left-block">
            <h2><?php echo _('Общая информация');?><?php if($this->canEdit):?><a class="edit" href="<?php echo $this->url(array('module' => 'provider', 'controller' => 'list', 'action' => 'edit'))?>" class="edit">&nbsp;</a><?php endif; ?></h2>
            <?php echo $this->reportList($this->provider->getStudyCenterCardFields());?>
        </div>
        <div class="right-block ">
            <h2><?php echo  _('Город');?><?php if($this->canEdit):?><a class="edit" href="<?php echo $this->url(array('module' => 'provider', 'controller' => 'list', 'action' => 'edit'))?>" class="edit">&nbsp;</a><?php endif; ?></h2>
            <?php echo $this->reportList($this->details['cities'], HM_View_Helper_ReportList::CLASS_WITHOUT_KEYS);?>
        </div>
    </div>
    <h2><?php echo  _('Контактные лица');?><?php if($this->canEdit):?><a class="edit" href="<?php echo $this->url(array('module' => 'provider', 'controller' => 'list', 'action' => 'edit'))?>" class="edit">&nbsp;</a><?php endif; ?></h2>
    <div class="clearfix">
        <?php echo $this->reportTable($this->details['contacts']);?>
    </div>
    <!--h2><?php echo  _('Очные курсы');?><?php if($this->canEdit):?><a class="edit" href="<?php echo $this->url(array('module' => 'subject', 'controller' => 'fulltime', 'action' => 'index', 'nobase' => 1))?>" class="edit">&nbsp;</a><?php endif; ?></h2>
    <div class="clearfix">
        <?php echo $this->reportTable($this->details['subjects']);?>
    </div>
    <h2><?php echo  _('Тьюторы');?><?php if($this->canEdit):?><a class="edit" href="<?php echo $this->url(array('module' => 'teacher', 'controller' => 'list', 'action' => 'index'))?>" class="edit">&nbsp;</a><?php endif; ?></h2>
    <div class="clearfix">
        <?php echo $this->reportTable($this->details['teachers']);?>
    </div-->
</div>
