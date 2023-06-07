<?php
    
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/competencies.css'), 'screen,print');
?>
<div class="at-competence at-form">
    <h1><?php echo _('Результаты заполнения анкеты');?></h1>
    <div class="at-form-wrapper"><div class="at-form-body">
        <?php echo $this->partial(HM_At_Session_Event_EventModel::FORM_REPORT . '.tpl', array(
            'totalResults' => $this->totalResults, 
            'attempt' => $this->model['attempt']
        ));?>
        <div class="at-form-navpanel">
            <form id=final_form method=post action="<?= $this->finalizeUrl ?>">
                <input type=hidden id='session_event_id' name='session_event_id' value='<?=$this->model['attempt']['session_event_id']?>'>
                <?php if ($this->finalizeable && $this->model['evaluationType'] && ($this->model['evaluationType']->relation_type != HM_At_Evaluation_EvaluationModel::RELATION_TYPE_SELF)) :?>
                    <table width="100%">
                        <tr><td align=left>Каковы, с Вашей точки зрения, сильные стороны этого человека?
                        <tr><td><textarea id="strengths" name=strengths style='width:100%;height:150px;'><?= $this->memoResults[0]; ?></textarea>
                        <tr><td align=left>Каковы, с Вашей точки зрения, области, требующие развития?<br>
                        <tr><td><textarea id="need2progress" name=need2progress style='width:100%;height:150px;'><?= $this->memoResults[1]; ?></textarea>
                    </table>
                <?php endif;?>
        <a href="<?= $this->continueUrl ?>" target="_top" title="<?= $this->escape(_('Вернуться к заполнению анкеты')) ?>" class="at-form-button at-form-return"><?= _('Вернуться') ?></a>
        <a onclick='saveComments()' href="<?= $this->stopUrl ?>" target="_top" title="<?= $this->escape(_('Выйти без подтверждения заполнения')) ?>" class="at-form-button at-form-stop"><?= _('Выйти') ?></a>
        <?php if ($this->finalizeable) :?>            
            <a onclick='document.getElementById("final_form").submit()' href="<?= $this->finalizeUrl ?>" target="_top" title="<?= $this->escape(_('Подтвердить окончание заполнения анкеты и выйти')) ?>" class="at-form-button at-form-finalize"><?= _('Готово') ?></a>
            </form>
        <?php endif;?>            
        </div>
    </div></div>

<?php $this->inlineScript()->captureStart(); ?>
/*
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
*/

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