<?php
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
?>
<style>
@media print {
.pagebreak {
    page-break-after: auto !important;
}
</style>
<div class="at-form-report">
<?php if ($this->print):?>
    <h1><?php echo _('Индивидуальный отчет');?></h1>
<?php endif;?>
<?php if ($this->sessionUser->status != HM_At_Session_User_UserModel::STATUS_COMPLETED): ?>
    <div class="attention"><?php echo _('ВНИМАНИЕ! Пользователь ещё не прошел оценку, результаты предварительные');?></div>
<?php endif;?>

<h2><?php echo _('Общая информация');?></h2>
<div class="report-summary clearfix">
    <div class="left-block">
        <?php echo $this->reportList($this->lists['general']);?>
    </div>
    <div class="right-block">
        <?php echo $this->reportList($this->lists['session']);?>
    </div>
</div>
<?php Zend_Controller_Front::getInstance()->addModuleDirectory(APPLICATION_PATH . '/modules/at');?>
<?php foreach ($this->methods as $params):?>
    <?= $this->action($params['evaluation']->method, 'report-methods', 'session', $params); ?>
<?php endforeach;?>

</div>
<?php if (!$this->print):?>
    <div>
        <input type="button" id="button-print" class="hm-report-button-print" value="<?php echo _('Печать')?>">
        <!-- <input type="button" class="hm-report-button-download" value="<?php //echo _('Скачать как PDF')?>"> -->
    </div>
<?php endif;?>
<?php echo $this->partial('_report-custom.tpl', array(
    'print' => $this->print,
    'url' => $this->url(array('module' => 'reserve', 'controller' => 'report', 'action' => 'user', 'print' => 1)),
)); ?>
<?php $this->inlineScript()->captureStart(); ?>
$(document).ready(function(){
	$('.hm-report-button-download').click(function() {
	    var url = '/merge_pdfs/generate.php?session_id=<?php echo $this->sessionUser->session_id;?>&session_user_id=<?php echo $this->sessionUser->session_user_id;?>';
	    var name = 'dl';
	    var options = [ 'location=no', 'menubar=yes', 'status=no', 'resizable=yes', 'scrollbars=yes', 'directories=no', 'toolbar=no' ].join(',');
	    window.open(url, name, options);
	});
});
<?php $this->inlineScript()->captureEnd(); ?>
