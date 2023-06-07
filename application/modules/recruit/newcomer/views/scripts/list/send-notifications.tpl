<table width="100%" cellpadding="0" cellspacing="0" border="0" class="main">
    <tr><th><?php echo _('Следующим пользователям будут отправлены уведомления:')?></th></tr>
    <?php if (count($this->users)):?>
    <?php foreach($this->users as $user):?>
    <tr><td><?php echo sprintf("%s", $user->getName())?></td></tr>
    <?php endforeach;?>
    <?php endif;?>
</table>
<br>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="main">
    <tr><th><?php echo _('Список пользователей, проходящих адаптацию:')?></th></tr>
    <?php if (count($this->listUsers)):?>
        <?php foreach($this->listUsers as $receiverId => $listUser):?>
            <?php $receiver = Zend_Registry::get('serviceContainer')->getService('User')->find($receiverId)->current(); ?>
            <tr><td><?php echo sprintf("%s", $receiver->getName()) . ':'?></td></tr>
            <?php foreach($listUser as $user):?>
                <tr><td><?php echo "&emsp;&emsp;&emsp;" . sprintf("%s", $user->getName())?></td></tr>
            <?php endforeach;?>
        <?php endforeach;?>
    <?php endif;?>
</table>
<br>
<?php echo $this->form?>