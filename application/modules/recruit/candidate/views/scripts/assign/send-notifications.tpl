<table width="100%" cellpadding="0" cellspacing="0" border="0" class="main">
    <tr><th><?php echo _('Следующим пользователям будут отправлены уведомления:')?></th></tr>
    <?php if ($this->initiator): ?>
        <tr><td><?php echo sprintf("%s", $this->initiator->getName()); ?></td></tr>
    <?php else: ?>
        <tr><td>Список пуст</td></tr>
    <?php endif; ?>
</table>
<br>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="main">
    <tr><th><?php echo _('Список кандидатов:')?></th></tr>
    <?php if (count($this->listUsers)):?>
        <?php foreach($this->listUsers as $listUser):?>
            <tr><td><?php echo sprintf("%s", $listUser->getName())?></td></tr>
        <?php endforeach;?>
    <?php endif;?>
</table>
<br>
<?php echo $this->form?>