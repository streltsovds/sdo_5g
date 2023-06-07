<div class="at-form-info">
    <p><?php echo _('Название мероприятия: '); echo $this->titleMeeting; ?></p>
    <p><?php echo _('Название проекта: '); echo $this->titleProject; ?></p>
    <p><?php echo _('Всего вопросов: '); echo $this->questionsCount; ?></p>
	<?php if($this->attempts): ?>
    <p><?php echo _('Попыток (осталось/всего): '); echo $this->attempts; ?></p>
	<?php endif;?>
	<?php if (is_a($this->user, 'HM_User_UserModel')): ?>
	<ul>
		<li><?php echo $this->cardLink($this->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $this->user->MID)));?><?php echo $this->escape($this->user->getName())?></li>
	</ul>
	<?php endif;?>
</div>
<div class="at-form-scale">
</div>    