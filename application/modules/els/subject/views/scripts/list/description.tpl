<?php if($this->finish == true) :?>
<?php $this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/subject.css'));?>
<div class="congratulations">
	<div class="congr_title">Завершен курс "<?php echo $this->subject->name;?>"</div>
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

	<div class="tmc-subject">
		<div class="tmc-subject-leftside">
			<?php echo $this->partial('list/card.tpl', null, array('subject' => $this->subject, 'graduated' => $this->graduated, 'teachers' => $this->teachers));?>
<br>
            <input
                    type="button"
                    class="back_sub"
                    style="border-radius: 2px;background-color: #ffffff;color: #E0A126;padding: 5px 12px;font-size: 12px;border: 1px solid #E0A126;margin-right: 5px;"
                    onClick="javascript: history.go(-1)"
                    value="<?php echo _('Назад');?>"/>

			<?php if ((!$this->isStudent) && (!$this->isLimitReached) ): ?>
			<div class="reg_button" style="display: inline-block;">
				<form action="">
					<input
                            type="submit"
                            class="reg_sub"
                            style="border-radius: 2px;background-color: #E0A126;color: #ffffff;padding: 5px 14px;font-size: 12px;border: 1px solid #E0A126;margin-right: 5px;"
                            onClick="window.location.href = '<?php
                            echo $this->url(array('module'=> 'user', 'controller' => 'reg', 'action' => 'subject', 'subid' => $this->subjectId), null, true);
                        ?>'; return false;" value="<?php echo $this->regText;?>"/>
				</form>
			</div>
			<?php endif;?>

		</div><br>
		<div class="tmc-subject-rightside">
			<?php if (strlen(strip_tags(trim($this->subject->description)))) :?>
			<h2><?php echo _('Описание курса');?></h2>
			<hr>
			<div class="text-content">
				<?php echo $this->subject->description?>
			</div>
			<?php endif; ?>
		</div>
	</div>

<?php endif; ?>


