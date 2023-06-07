<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');?>
<div class="at-form-report at-form-report-resume-small">
    <div class="report-summary clearfix resume-margin">
        <div>
            <?php if (!$this->isAjax): ?>
                <h1>
                    <?php echo $this->name;?>
                </h1>
            <?php endif; ?>
            <p>Резюме отсутствует...</p>
        </div>
    </div>
</div>