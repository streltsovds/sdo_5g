<?php echo $this->headSwitcher(array('module' => 'meeting', 'controller' => 'result', 'action' => 'skillsoft', 'switcher' => 'skillsoft'), 'result', $this->disabledMods);?>
<?php // выглядит плохо, работает неверно, не факт что это вообще здесь нужно?>
<?php if (0 && !Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT)):?>


<?php echo $this->formSelect('user_id', $this->userId, array('data-url' => $this->url(array('user_id' => ''))), $this->participants); ?>



<?php $this->inlineScript()->captureStart(); ?>
$(document).delegate('#user_id', 'change', function (event) {
    document.location.href = $(event.target).data('url') + event.target.value;
});
<?php $this->inlineScript()->captureEnd(); ?>


<?php endif;?>
<?php if ($this->userId) :?>
<div class="content-container content-container-expandable">
    <div class="content-here">
        <div class="course-iframe-box">

<iframe frameborder="0" src="<?php echo $this->url(array('module' => 'meeting', 'controller' => 'result', 'action' => 'report', 'user_id' => $this->userId, 'meeting_id' => $this->meetingId, 'gridmod' => null))?>" width="100%" height="550px"></iframe>
</div></div></div>
<?php endif;?>

