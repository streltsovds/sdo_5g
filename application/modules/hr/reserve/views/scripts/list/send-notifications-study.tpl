<table width="100%" cellpadding="0" cellspacing="0" border="0" class="main">
    <tr><th><?php echo _('Следующим пользователям будут отправлены уведомления:')?></th></tr>
    <?php if (count($this->users)):?>
    <?php foreach($this->users as $user):?>
    <tr><td><?php echo sprintf("%s", $user->getName())?></td></tr>
    <?php endforeach;?>
    <?php endif;?>
</table>
<br>
<?php echo $this->form;