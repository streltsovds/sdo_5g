<div class="at-form-report">
    <h2><?php echo _('Общие сведения');?> <a class="edit" href="<?php echo $this->url(array('controller' => 'index', 'action' => 'edit'))?>"><svg-icon name="edit" width="18" height="18"></a></h2>
    <div class="report-summary clearfix">
        <div class="left-block">
            <?php echo $this->reportList($this->lists['generalLeft']);?>
        </div>
        <div class="right-block">
            <?php //echo $this->reportList($this->lists['generalRight']);?>
        </div>
    </div>
</div>
