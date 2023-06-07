<?php
    $this->score();
?>
<div class="lesson_min" id="<?php echo $this->lesson->SHEID ?>">
<a name="lesson_<?php echo $this->lesson->SHEID?>"></a>
<div <?php if ($this->lesson->getStudentScore($this->currentUserId) != -1 && $this->showScore):?> class="lesson_wrapper_1_score" <?php else:?> class="lesson_wrapper_1" <?php endif;?>>
<div class="lesson_wrapper_2">
<div <?php if ($this->lesson->getStudentScore($this->currentUserId) != -1 && $this->showScore):
?> id="lesson_block_active" <?php else:?> class="lesson_block" <?php endif;?>>
<div class="lesson_table" style="overflow-x: auto; overflow-y: visible;">
<table border="0" cellpadding="0" cellspacing="0" style="table-layout: fixed; width: 100%;">
    <col style="width: <?php echo $this->cols[0] ?>;">
    <col style="width: <?php echo $this->cols[1] ?>;">
    <col style="width: <?php echo $this->cols[2] ?>;">
    <col style="width: 150px;">
  <tr>
    <td align="center" valign="top" class="lesson_bg">
<div class="lesson_bg_img">
<?php if ($this->lesson->getIcon()):?>
	<?php if ($this->lesson->typeID != HM_Event_EventModel::TYPE_EMPTY):?>
    <a
        <?php if ($this->lesson->typeID ==HM_Event_EventModel::TYPE_COURSE) echo 'target="'.$this->lesson->isNewWindow().'"'?>
        href="<?php echo $this->url(array('action' => 'index', 'controller' => 'execute', 'module' => 'lesson', 'lesson_id' => $this->lesson->SHEID, 'subject_id' => $this->lesson->CID), false, true)?>"
    >
    <?php endif;?>
    <img src="<?php echo ($this->lesson->getUserIcon()) ? $this->lesson->getUserIcon() : $this->lesson->getIcon(HM_Lesson_LessonModel::ICON_MEDIUM)?>" alt="<?php echo $this->escape($this->lesson->title)?>" title="<?php echo $this->escape($this->lesson->title)?> - <?php echo _('Выполнение занятия');?>"/>
    <?php if ($this->lesson->typeID != HM_Event_EventModel::TYPE_EMPTY):?>
    </a>
    <?php endif;?>
    <?php endif;?>
</div>
<div id="lesson_type"><?php echo $this->type;?></div>
</td>
<td class="lesson_options" style="overflow: hidden">
    <div id="lesson_title">
        <?php if ($this->lesson->typeID == HM_Event_EventModel::TYPE_EMPTY): ?>
            <span><?php echo $this->escape($this->lesson->title) ?></span>
        <?php else: ?>
            <?php $lessonAttribs = array('href' => $this->titleUrl, 'target' => ($this->lesson->typeID == HM_Event_EventModel::TYPE_ECLASS) ? '_blank' : '_self') ?>
            <?php if(!empty($this->targetUrl)) $lessonAttribs['target'] = '_blank' ?>

            <a <?php echo $this->HtmlAttribs($lessonAttribs) ?>><?php echo $this->escape($this->lesson->title) ?></a>
        <?php endif ?>
    </div>

    <div id="lesson_go">
    <div id="lesson_begin" class="<?php if ($this->lesson->recommend):?>recomended<?php endif;?>">
    <?php if ($this->lesson->recommend):?>
        <?php echo _('Рекомендуемое время выполнения');?>:
    <?php else:?>
        <?php echo _('Время выполнения');?>:
    <?php endif;?>
	<?php echo $this->datetime?>
    </div>
    </div>

    <?php if ($this->lesson->cond_sheid):?>
    	<?php $title = $this->titles[$this->lesson->cond_sheid];?>
	    <div class="lesson_cond_sheid"><?php echo _('Условие: выполнение занятия ')?><a href="#lesson_<?php echo $this->lesson->cond_sheid;?>"><?php echo ($title) ? $title : _('не найдено')?></a></div>
	    <?php if ($this->lesson->cond_mark):?>
	    	<div class="lesson_cond_mark"><?php echo _(' на оценку ');?><b><?php echo $this->lesson->cond_mark;?></b></div>
	    <?php endif;?>
    <?php endif;?>
	<?php if($this->lesson->cond_progress):?>
		<div class="lesson_cond_sheid"><?php echo _('Условие: процент выполнения ').$this->lesson->cond_progress.'%'?></div>
	<?php endif;?>
	<?php if($this->lesson->cond_avgbal):?>
		<div class="lesson_cond_sheid"><?php echo _('Условие: средний балл ').$this->lesson->cond_avgbal?></div>
	<?php endif;?>
	<?php if($this->lesson->cond_sumbal):?>
		<div class="lesson_cond_sheid"><?php echo _('Условие: суммарный балл ').$this->lesson->cond_sumbal?></div>
	<?php endif;?>

    <?php if ($this->lesson->getFormulaPenaltyId()):?>
        <div class="lesson_penalty" style="color: red;"><?php echo _('Установлен штраф за несвоевременную сдачу')?></div>
    <?php endif;?>

    <?php if ($this->teacher):?>
    <div class="lesson_teacher"><?php echo _('Тьютор') . ': ' . $this->cardLink($this->url(array(
    																							'module' => 'user',
    																							'controller' => 'list',
    																							'action' => 'view',
    																							'user_id' => $this->teacher['user_id']))).' <span class="tmc-blue-teacher">'.$this->teacher['fio'];?></span></div>
    <?php endif;?>

    <?php if ($this->lesson->descript):?>
        <div class="lesson_main_desc"><?php echo nl2br($this->lesson->descript);?></div>
    <?php endif;?>
<!--<div id="lesson_edit">
<?php if ($this->allowEdit):?>
        <a href="<?php echo $this->url(array('action' => 'edit', 'lesson_id' => $this->lesson->SHEID))?>"><?php echo $this->icon('edit')?></a>
    <?php endif;?>
    <?php if ($this->allowDelete):?>
        <a href="<?php echo $this->url(array('action' => 'delete', 'lesson_id' => $this->lesson->SHEID))?>"><?php echo $this->icon('delete')?></a>
    <?php endif;?>
</div>
-->
<?php foreach ($this->eventCollection as $event): ?>
    <div class="lesson_notify">
        <?php
            if ($event->getEventTypeStr() == 'courseAttachLesson' && $event->subjectId() == $this->lesson->SHEID) {
                echo 'Новое занятие';
                break;
            }
            if ($event->getEventTypeStr() == 'courseTaskScoreTriggered' && $event->getParam('lesson_id') == $this->lesson->SHEID) {
                echo 'Выставлена оценка';
                break;
            }
        ?>
    </div>
<?php endforeach; ?>
</td>
<td align="center" valign="top" class="showscore">
<?php if ($this->showScore):?>
    <?php echo $this->score(array(
        'score' => $this->lesson->getStudentScore($this->currentUserId),
        'user_id' => $this->currentUserId,
        'lesson_id' => $this->lesson->SHEID,
        'scale_id' => $this->lesson->getScale(),
        'mode' => HM_View_Helper_Score::MODE_DEFAULT,
    ));?>
    <?php if (false): ?>
    <p style="margin-top: 10px;">из 20 баллов</p>
    <div class="hm-clear"></div>
    <?php
        //прогрессбар
        $id = $this->id('progressbar');
        echo '<div id="'.$id.'"></div>';
        $HM = $this->HM();
        $HM->create('hm.core.ui.progressbar.Progressbar', array(
            'renderTo' => '#'.$id,
            'value' => 3, //балл
            'maxValue' => 5, //максимальный балл
            'label' => 'Процент успешности'
        ));
    ?>
    <?php endif;?>

    <?//php echo $this->lesson->getStudentScore($this->currentUserId)?>
	<div class="student_comment">
	    <?php if ($this->details && $this->lesson->getResultsUrl() && $this->lesson->isResultInTable()):?>
        <a href="<?php echo $this->lesson->getResultsUrl()?>"><?php echo _('Подробнее')?></a>
    <?php endif;?>
    <?php
        if (Zend_Registry::get('serviceContainer')->getService('LessonAssignMarkHistory')->hasMarkHistory($this->lesson->CID, $this->lesson->SHEID, $this->currentUserId)):
    ?>
        <?php /* @todo: переделать на lightDialogLink*/ echo $this->dialogLinkOld(_('История изменений'), $this->lesson->getStudentAssign($this->currentUserId)->getScoreHistoryTable(),_('История изменений'),array('width'=>450))?>
    <?php endif;?>
    </div>
<?php endif;?>
</td>
      <td valign="top" class="lesson_descript_td" <?php   if ($this->lesson->getStudentComment($this->currentUserId)):?> <?php  endif;?>>
      <div id="lesson_descript">
          <?php if ($this->lesson->getStudentComment($this->currentUserId)):?>
          <div><?php echo $this->lesson->getStudentComment($this->currentUserId);?></div>
          <?php endif;?>
      </div>
      </td>
  </tr>

<!-- <tr>
<td colspan="1" width="109" height="30" align="center" class="lesson_bg_type"></td>
</tr>  -->
</table>



</div>
</div>
</div>
</div>

</div>
