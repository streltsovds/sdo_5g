<div class="at-form-comment">
    <?php echo _('В случае отсутствия у пользователя электронной почты на момент назначения на welcome-тренинг это уведомление высылается его непосредственному руководителю.');?>
</div>
<br>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="main">
    <tr><th><?php echo _('Следующим пользователям будут отправлены уведомления:')?></th></tr>
    <?php if (count($this->users)):?>
        <?php foreach($this->users as $user):?>
            <tr><td><?php echo sprintf("%s", $user->getName())?></td></tr>
        <?php endforeach;?>
    <?php else: ?>
        <tr><td><?php echo _('У всех отмеченных пользователей отсутствует e-mail.');?></td></tr>
    <?php endif;?>
</table>
<br>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="main">
    <tr><th><?php echo _('Следующим руководителям будут отправлены уведомления:')?></th></tr>
    <?php if (count($this->managers)):?>
        <?php foreach($this->managers as $manager):?>
            <tr><td><?php echo sprintf("%s", $manager->getName())?></td></tr>
        <?php endforeach;?>
    <?php else: ?>
        <tr><td><?php echo _('У всех руководителей отмеченных пользователей отсутствует e-mail.');?></td></tr>
    <?php endif;?>
</table>
<br>
<?php echo $this->form;
