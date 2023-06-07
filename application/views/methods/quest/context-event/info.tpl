<div class="at-form-info">
	<?php if (is_a($this->user, 'HM_User_UserModel')): ?>
	<ul>
		<li><?php echo $this->cardLink($this->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $this->user->MID)));?><?php echo $this->escape($this->user->getName())?></li>
		<li><?php echo _('Сессия')?>: <?php echo $this->session->name;?></li>
	</ul>
	<?php endif;?>
</div>
<div class="at-form-scale">
</div>    