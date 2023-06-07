<div class="at-form-report">
    <?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');?>
    <div class="report-summary clearfix">
        <div class="left-block">
            <?php echo $this->reportList($this->cardFields);?>
        </div>
    </div>
</div>

<?php echo $this->form?>
