<?php if($this->finish == true) :?>
<?php $this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/project.css'));?>
<div class="congratulations">
	<div class="congr_title">Завершен курс "<?php echo $this->project->name;?>"</div>
	<div class="congr_img">
		<img src="<?php echo $this->baseUrl('images/content-modules/marksheet/congratulations.png');?>"/>
	</div>
	<div class="congr_desc">
		<p>Уважаемый слушатель! Вы успешно прошли данный курс и Вам автоматически назначен статус «прошедший обучение» по курсу.<br />
При этом курс будет доступен в списке «Мои курсы» до окончания его срока актуальности и Вы можете продолжать пользоваться его материалами. Также Вы можете самостоятельно его удалить из списка «Мои курсы», после чего он будет доступен только через страницу «История обучения».</p>
	</div>
	<div class="congr_button">
		<button class="congr_sub" onClick=" window.location.reload();">Продолжить</button>
	</div>
</div>
<?php else :?>
<div class="wrapUtvPcard">
	<div class="ui-dialog pcard pcard_inline">
		<div class="ui-dialog-content-wrapper">
			<div class="lightdialog ui-dialog-content ui-widget-content" id="ui-lightdialog-2">
	            <?php echo $this->partial('list/card.tpl', null ,array('project' => $this->project));?>
			</div>
		</div>
	</div>
    <form action="" style="float: left;">
        <input type="submit" class="back_sub" onClick="window.location.href = '<?php echo $this->url(array('module'=> 'project', 'controller' => 'catalog', 'action' => 'index', 'classifier_id' => $this->clClassifierId, 'item' => $this->clItem, 'type' => $this->clType), null, true);?>'; return false;" value="<?php echo _('Назад');?>"/>
    </form>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Option')->getOption('regAllow') !== '0'): ?>    
	<div class="reg_button">
		<form action="">
			<input type="submit" class="reg_sub" onClick="window.location.href = '<?php echo $this->url(array('module'=> 'user', 'controller' => 'reg', 'action' => 'project', 'projid' => $this->project->projid));?>'; return false;" value="<?php echo $this->regText;?>"/>
		</form>
	</div>
	<?php endif;?>
</div>	
<?php endif; ?>
<?php if (strlen(strip_tags(trim($this->project->description)))) :?>
<br>
<br>
<h2><?php echo _('Описание курса');?></h2>
<hr>
<div class="text-content">
<?php echo $this->project->description?>
</div>
<?php endif; ?>


