<!--<script src="/js/lib/amcharts_3.21.12/amcharts/amcharts.js" type="text/javascript"></script>-->
<!--<script src="/js/lib/amcharts_3.21.12/amcharts/serial.js" type="text/javascript"></script>-->
<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print'); ?>
<div class="at-form-report">

    <?php if ($this->status != HM_At_Session_User_UserModel::STATUS_COMPLETED): ?>
    <div class="attention"><?php echo _('ВНИМАНИЕ! Пользователь ещё не прошел оценку, результаты предварительные');?></div>
    <?php endif;?>

    <div>
        <?php echo $this->chartJS(
            $this->analyticsChartData['data'],
            $this->analyticsChartData['graphs'],
            array(
                'id' => 'analytics',
                'colors' => ['#003F7E','#C759D2'],
                'type' => 'apexbar',
                'width'  => 1100,
                'height' => 400,
            )
        );?>
    </div>

    <div><?php echo $this->form;?></div>

</div>
