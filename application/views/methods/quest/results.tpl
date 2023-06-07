<?php
    
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/competencies.css'), 'screen,print');
?>
<div class="at-competence at-form at-form-results">
    <h1><?php echo _('Результаты');?></h1>
    <div class="at-form-wrapper"><div class="at-form-body">
        <?php if (in_array($this->context, array(HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING, HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_PROJECT))):?>
        <div style="overflow: hidden; margin-bottom: 23px;">
            <?php echo $this->action('context-helper', $this->controller, $this->module, array('context-helper-action' => 'info')); ?>
        </div>
        <?php endif;?>
        <?php echo $this->partial('report.tpl', array(
            'totalResults' => $this->totalResults, 
            'attempt' => $this->model['attempt']
        ));?>

        <div class="at-form-navpanel">
            <a href="<?= $this->continueUrl ?>" target="_top" title="<?= $this->escape(_('Вернуться к заполнению')) ?>" class="at-form-button at-form-return"><?= _('Вернуться') ?></a>
<?php if ($this->suspendable) :?>            
            <a href="<?= $this->stopUrl ?>" target="_top" title="<?= $this->escape(_('Выйти без подтверждения заполнения')) ?>" class="at-form-button at-form-stop"><?= _('Выйти') ?></a>
<?php endif;?> 
<?php if ($this->finalizeable) :?>            
            <a href="<?= $this->finalizeUrl ?>" target="_top" title="<?= $this->escape(_('Подтвердить окончание заполнения и выйти')) ?>" class="at-form-button at-form-finalize"><?= _('Готово') ?></a>
<?php endif;?>            
        </div>
    </div></div>

<?php $this->inlineScript()->captureStart(); ?>

$(document).delegate('.at-form a.at-form-finalize', 'mousedown', function (event) {
	var target;
	if (target = $(this).attr('target')) {
		$(this)
			.data('target', target)
			.removeAttr('target');
	}
});
$(document).delegate('.at-form a.at-form-finalize', 'click', function (event) {
    var $target = $(this)
      , message;

    event.preventDefault();

    //elsHelpers.alert(<?= HM_Json::encodeErrorSkip(_('Остались незаполненные поля. Выполняя данную операцию, Вы подтвержаете что анкета заполнена корректно и дальнейшему изменению не подлежит. Необходимо заполнить оставшиеся поля, либо прервать заполнение анкеты.')) ?>).done(function () {
    top.location.href = $target.attr('href');
});

$(document).delegate('.at-form a.at-form-stop', 'mousedown', function (event) {
	var target;
	if (target = $(this).attr('target')) {
		$(this)
			.data('target', target)
			.removeAttr('target');
	}
});
$(document).delegate('.at-form a.at-form-stop', 'click', function (event) {
    var $target = $(this)
      , message;

    event.preventDefault();

    elsHelpers.confirm(<?= HM_Json::encodeErrorSkip(_('Выполняя данную операцию, Вы НЕ заканчиваете заполнение анкеты и оставляете возможность для её дальнейшего изменения. Продолжить?')) ?>).done(function () {
        top.location.href = $target.attr('href');
    })
});

$(document).ready(function(){
	$('#button-print').click(function(){
	    
	    var url = '<?php echo $this->url(array('module' => 'event', 'controller' => 'index', 'action' => 'print', 'session_event_id' => $this->questId));?>';
	    var name = 'print-results';
	    var options = [ 'location=no', 'menubar=no', 'status=no', 'resizable=yes', 'scrollbars=yes', 'directories=no', 'toolbar=no' ].join(',');

	    window.open(url, name, options);
	});
});
<?php $this->inlineScript()->captureEnd(); ?>
</div>