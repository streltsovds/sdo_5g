<div class="at-form-comment">
    <?php echo _('В случае отсутствия у пользователя электронной почты на момент назначения на welcome-тренинг это уведомление высылается его непосредственному руководителю.');?>
</div>
<br>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="main">
    <tr><th><?php echo _('Список (пользователь/руководитель):')?></th></tr>
    <?php if (count($this->users)):?>
        <?php foreach($this->users as $user):?>
            <?php $serviceContainer = Zend_Registry::get('serviceContainer')->getService('Orgstructure'); ?>
            <?php $position = $serviceContainer->getOne($serviceContainer->fetchAll(array('mid = ?' => $user->MID))); ?>
            <?php $manager  = $serviceContainer->getManager($position->soid); ?>
            <?php $serviceContainer = Zend_Registry::get('serviceContainer')->getService('User'); ?>
            <?php $manager      = $serviceContainer->find($manager->mid)->current(); ?>
            <?php $userEmail = $user ? $user->getName() . ' [' . ($user->EMail ? : _('нет email')) . ']' : ''; ?>
            <?php $managerEmail = $manager ? $manager->getName() . ' [' . ($manager->EMail ? : _('нет email')) . ']' : ''; ?>
            <tr><td><?php echo implode(' / ', array($userEmail, $managerEmail)); ?></td></tr>
        <?php endforeach;?>
    <?php endif;?>
</table>
<br>
<?php echo $this->form;