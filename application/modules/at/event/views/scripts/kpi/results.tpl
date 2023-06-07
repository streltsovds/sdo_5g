<?php
    Zend_Registry::get('serviceContainer')->getService('Unmanaged')->getController()->setView('DocumentBlank');
    // можем для минимизации .css все виды оценки держать в field-training.css; audit уже там
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/field-training.css'), 'screen');    
    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/quest.css'), 'screen');
?>
<div style="float: right">
    <input type="button" id="button-print" value="<?php echo _('Распечатать отчет')?>">
</div>
<h1><?php echo _('Результаты заполнения анкеты');?></h1>
<?php echo $this->partial(HM_At_Session_Event_EventModel::FORM_REPORT . '.tpl', array(
    'totalResults' => $this->totalResults, 
    'event' => $this->event
));?>
<input type="button" id="button-continue" value="<?php echo _('Вернуться к заполнению')?>">&nbsp;
<input type="button" id="button-stop" value="<?php echo _('Выйти')?>">
<input type="button" id="button-finalize" value="<?php echo _('Закончить заполнение')?>">
<?php $this->inlineScript()->captureStart(); ?>

$(document).ready(function(){
	
	$('#button-continue').click(function(){
	    top.location.href = '<?php echo $this->url(array('module' => 'event', 'controller' => 'competence', 'action' => 'index', 'session_event_id' => $this->event->session_event_id));?>';    
	});
	
	$('#button-stop').click(function(){
	    <?php if ($this->finalizeable) :?> 
	    if (confirm('<?php echo _('Вы закончили заполнение анкеты и можете зафиксировать результат. Действительно хотите сейчас выйти без фиксации результата?');?>')) {
	        top.location.href = '<?php echo $this->url(array('module' => 'session', 'controller' => 'event', 'action' => 'my', 'session_id' => $this->event->session_id));?>';
        }    
        <?php else: ?>
            top.location.href = '<?php echo $this->url(array('module' => 'session', 'controller' => 'event', 'action' => 'my', 'session_id' => $this->event->session_id));?>';        
        <?php endif; ?>
	});
	
	$('#button-finalize').click(function(){
	    top.location.href = '<?php echo $this->url(array('module' => 'event', 'controller' => 'competence', 'action' => 'finalize', 'session_event_id' => $this->event->session_event_id));?>';    
	});		
	
	$('#button-print').click(function(){
	    
	    var url = '<?php echo $this->url(array('module' => 'event', 'controller' => 'index', 'action' => 'print', 'session_event_id' => $this->event->session_event_id));?>';
	    var name = 'print-results';
	    var options = [ 'location=no', 'menubar=no', 'status=no', 'resizable=yes', 'scrollbars=yes', 'directories=no', 'toolbar=no' ].join(',');
	    
	    window.open(url, name, options);
	});

});
<?php $this->inlineScript()->captureEnd(); ?>