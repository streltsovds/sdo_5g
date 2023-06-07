<?php if ($this->enabled): ?>
<div id="quizzes-question"><?php echo $this->question?></div>
<div id="quizzes-answers">
	<form id="quizzes-answers-form">
		<input type="hidden" id="quizUrl" name="quizUrl" value="<?php echo $this->url(array('module' => 'infoblock', 'controller' => 'quizzes', 'action' => 'answer'))?>">
		<input type="hidden" name="quiz_id" value="<?php echo $this->quizId; ?>">
		<input type="hidden" name="question_id" value="<?php echo $this->questionId; ?>">
		<input type="hidden" name="format" value="json">
		<?php foreach($this->answers as $key => $answer): ?>
		<div class="quizzes-answer"><input name="answer[]" type="<?php echo ($this->type == HM_Question_QuestionModel::TYPE_ONE) ? 'radio' : 'checkbox'?>" value="<?php echo $key?>" <?php echo (in_array($key, $this->userAnswers)) ? 'checked' : ''; ?>><label class="quizzes-answer-label" for="quizzes-answer-<?php echo $key?>"><?php echo $answer?></label></div>
		<?php endforeach; ?>
		<input id="quizzes-answers-submit" type="submit" value="<?php echo _('Ответить')?>" <?php echo $this->answersDisabled ? 'disabled' : ''; ?>>
		<div id="quizzes-results-allow" <?php echo !$this->resultsEnabled ? 'style="visibility: hidden"' : ''; ?>>
			<a href="javascript:void(0);"><?php echo _('Результаты опроса')?></a>
		</div>
		<div style="clear: both"></div>
	</form>
</div>
<div id="quizzes-chart-container" style="display: none"><?php echo $this->chart('quizzes', 'ampie', 100, 200);?></div>
<?php else: ?>
<div id="quizzes-empty"><p><?php echo _('Нет данных для отображения'); ?></p></div>
<?php endif; ?>
<?php if ($this->isModerator): ?>
<hr style="clear: both">
<div id="quizzes-moder">
	<a href="<?php echo $this->url(array(
		'module' 		=> 'infoblock',
		'controller'	=> 'quizzes',
		'action'		=> 'edit',
	));?>" id="quizzes-moder-edit">
		<?php echo _('Редактировать');?>
	</a>
</div>
<?php endif; ?>