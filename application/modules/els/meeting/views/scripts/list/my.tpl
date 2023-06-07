<?php
require_once APPLICATION_PATH .  '/views/helpers/Score.php';
?>
<div class="tmc-my-lessons"><?php echo _('План мероприятий'); ?></div>
<div class="tmc-mark-table">

<form id="marksheetmoderator">
<?php echo $this->headSwitcher(array('module' => 'meeting', 'controller' => 'list', 'action' => 'index', 'switcher' => 'my'));?>

<?php if (count($this->meetings)):?>
    <?php foreach($this->meetings as $meeting):?>
        <?php if ($meeting instanceof HM_Meeting_MeetingModel):?>
        <?php echo $this->meetingPreview($meeting,
                                        $this->titles,
                                        (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_MODERATOR))? 'meeting-preview-moderator' : 'meeting-preview',
                                        $this->forParticipant)?>
        <?php endif;?>
    <?php endforeach;?>
<?php else:?>
    <?php echo _('Отсутствуют данные для отображения')?>
<?php endif;?>

<?php if(!$this->forParticipant && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_MODERATOR)):?>
<?php $this->inlineScript()->captureStart(); ?>
    $(function(){
    	$(".lesson_bg_img").prepend('<span class="field-cell drag-handler"></span>')
    	$('#marksheetmoderator').sortable({
    		tolerance: 'pointer',
    		appendTo: 'body',
    		handle: 'span.drag-handler',
    		helper: 'clone',
    		revert: true,
    		update: function (event, ui) {
    			var cItemSort = $.map($('#marksheetmoderator').sortable("toArray"),function(item){
    				if(item.length>0) return item
    			})
    			$.getJSON('<?php echo $this->url(array('module' => 'meeting', 'controller' => 'list', 'action' => 'save-order'));?>', {
    				posById: cItemSort
    			});
    		}
    	});
    })

<?php $this->inlineScript()->captureEnd(); ?>
<?php endif;?>

<?php if(Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_MODERATOR)):?>
<?php $this->inlineScript()->captureStart(); ?>
if(typeof initMarksheet=="function"){
    initMarksheet({
        url: {
            comments: "<?php echo $this->url(array('module' =>'marksheet', 'controller' => 'index', 'action' => 'set-comment'));?>",
            score: "<?php echo $this->url(array('module' =>'marksheet', 'controller' => 'index', 'action' => 'set-score'));?>"
        },
        l10n: {
            save: "<?php echo _("Сохранить"); ?>",
            noParticipantActionSelected: "<?php echo _("Не выбрано ни одного действия со слушателем"); ?>",
            noParticipantSelected: "<?php echo _("Не выбрано ни одного слушателя"); ?>",
            noMeetingActionSelected: "<?php echo _("Не выбрано ни одного действия с занятием"); ?>",
            noMeetingSelected: "<?php echo _("Не выбрано ни одного занятия"); ?>",
            formError: "<?php echo _("Ошибка формы") ?>",
            ok: "<?php echo _("Хорошо"); ?>",
            confirm: "<?php echo _("Подтверждение"); ?>",
            areUShure: "<?php echo _("Данное действие может быть необратимым. Вы действительно хотите продолжить?"); ?>",
            yes: "<?php echo _("Да"); ?>",
            no: "<?php echo _("Нет"); ?>"
        }
    });
}
<?php $this->inlineScript()->captureEnd(); ?>

<?php endif;?>
</div>
</form>
