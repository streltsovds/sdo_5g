<div class="at-form-report">
    <?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');?>
    <div class="report-summary clearfix">
        <div class="left-block">
            <h2><?php echo _('Общие свойства');?></h2>
            <?php echo $this->reportList($this->data['session']);?>
        </div>
    </div>
    <div class="report-summary clearfix">
        <div class="left-block ">
          <?php if ($this->data['departments']) : ?>
            <h2><?php echo _('Участвующие подразделения');?></h2>
            <?php echo $this->reportList($this->data['departments'], HM_View_Helper_ReportList::CLASS_WITHOUT_KEYS);?>
          <?php endif;?>
        </div>
    </div>
</div>