<div class="lesson_min" id="<?php echo $this->meeting->meeting_id ?>">
<a name="lesson_<?php echo $this->meeting->meeting_id?>"></a>
<div <?php if ($this->meeting->getParticipantScore($this->currentUserId) != -1 && $this->showScore):?> class="lesson_wrapper_1_score" <?php else:?> class="lesson_wrapper_1" <?php endif;?>>
<div class="lesson_wrapper_2">
<div <?php if ($this->meeting->getParticipantScore($this->currentUserId) != -1 && $this->showScore):
?> id="lesson_block_active" <?php else:?> class="lesson_block" <?php endif;?>>
<div class="lesson_table">
<table border="0" cellpadding="0" cellspacing="0" style="table-layout: fixed; width: 100%;">
    <col style="width: <?php echo $this->cols[0] ?>;">
    <col style="width: <?php echo $this->cols[1] ?>;">
    <col style="width: <?php echo $this->cols[2] ?>;">
    <col style="width: 150px;">
  <tr>
    <td align="center" valign="top" class="lesson_bg">
<div class="lesson_bg_img">
<?php if ($this->meeting->getIcon()):?>
	<?php if ($this->meeting->typeID != HM_Event_EventModel::TYPE_EMPTY):?>
    <a <?php if ($this->meeting->typeID ==HM_Event_EventModel::TYPE_COURSE) echo 'target="'.$this->meeting->isNewWindow().'"'?> href="<?php echo $this->url(array('action' => 'index', 'controller' => 'execute', 'module' => 'meeting', 'meeting_id' => $this->meeting->meeting_id, 'project_id' => $this->meeting->project_id), false, true)?>">
    <?php endif;?>
    <img src="<?php echo $this->meeting->getIcon(HM_Meeting_MeetingModel::ICON_MEDIUM)?>" alt="<?php echo $this->escape($this->meeting->title)?>" title="<?php echo $this->escape($this->meeting->title)?> - <?php echo _('Выполнение занятия');?>"/>
    <?php if ($this->meeting->typeID != HM_Event_EventModel::TYPE_EMPTY):?>
    </a>
    <?php endif;?>
    <?php endif;?>
</div>
<div id="lesson_type"><?php echo $this->type;?></div>
</td>
<td width="415" class="lesson_options">
    <div id="lesson_title">
        <?php if ($this->meeting->typeID == HM_Event_EventModel::TYPE_EMPTY): ?>
            <span><?php echo $this->escape($this->meeting->title) ?></span>
        <?php else: ?>
            <?php $meetingAttribs = array('href' => $this->titleUrl, 'target' => ($this->meeting->typeID == HM_Event_EventModel::TYPE_WEBINAR) ? '_blank' : '_self') ?>
            <?php if(!empty($this->targetUrl)) $meetingAttribs['target'] = '_blank' ?>

            <a <?php echo $this->HtmlAttribs($meetingAttribs) ?>><?php echo $this->escape($this->meeting->title) ?></a>
        <?php endif ?>
    </div>

    <div id="lesson_go">
    <div id="lesson_begin" class="<?php if ($this->meeting->recommend):?>recomended<?php endif;?>">
    <?php if ($this->meeting->recommend):?>
        <?php echo _('Рекомендуемое время выполнения');?>:
    <?php else:?>
        <?php echo _('Время выполнения');?>:
    <?php endif;?>
	<?php echo $this->datetime?>
    </div>
    </div>

    <?php if ($this->meeting->cond_project_id):?>
    	<?php $title = $this->titles[$this->meeting->cond_project_id];?>
	    <div class="lesson_cond_project_id"><?php echo _('Условие: выполнение занятия ')?><a href="#meeting_<?php echo $this->meeting->cond_project_id;?>"><?php echo ($title) ? $title : _('не найдено')?></a></div>
	        <?php endif;?>
	<?php if($this->meeting->cond_progress):?>
		<div class="lesson_cond_project_id"><?php echo _('Условие: процент выполнения ').$this->meeting->cond_progress.'%'?></div>
	<?php endif;?>
	<?php if($this->meeting->cond_avgbal):?>
		<div class="lesson_cond_project_id"><?php echo _('Условие: средний балл ').$this->meeting->cond_avgbal?></div>
	<?php endif;?>
	<?php if($this->meeting->cond_sumbal):?>
		<div class="lesson_cond_project_id"><?php echo _('Условие: суммарный балл ').$this->meeting->cond_sumbal?></div>
	<?php endif;?>

    <?php if ($this->meeting->getFormulaPenaltyId()):?>
        <div class="lesson_penalty" style="color: red;"><?php echo _('Установлен штраф за несвоевременную сдачу')?></div>
    <?php endif;?>

    <?php if ($this->moderator):?>
    <div class="lesson_teacher"><?php echo _('Модератор') . ': ' . $this->cardLink($this->url(array(
    																							'module' => 'user',
    																							'controller' => 'list',
    																							'action' => 'view',
    																							'user_id' => $this->teacher['user_id']))).$this->teacher['fio'];?></div>
    <?php endif;?>

    <?php if ($this->meeting->descript):?>
        <div class="lesson_main_desc"><?php echo nl2br($this->meeting->descript);?></div>
    <?php endif;?>
<!--<div id="lesson_edit">
<?php if ($this->allowEdit):?>
        <a href="<?php echo $this->url(array('action' => 'edit', 'meeting_id' => $this->meeting->meeting_id))?>"><?php echo $this->icon('edit')?></a>
    <?php endif;?>
    <?php if ($this->allowDelete):?>
        <a href="<?php echo $this->url(array('action' => 'delete', 'meeting_id' => $this->meeting->meeting_id))?>"><?php echo $this->icon('delete')?></a>
    <?php endif;?>
</div>
--></td>
<td width="200" align="center" valign="top" class="showscore">
<?php if ($this->showScore):?>
    <?php echo $this->score(array(
        'score' => $this->meeting->getParticipantScore($this->currentUserId),
        'user_id' => $this->currentUserId,
        'meeting_id' => $this->meeting->meeting_id,
        'scale_id' => $this->meeting->getScale(),
        'mode' => HM_View_Helper_Score::MODE_DEFAULT,
    ));?>
    <?//php echo $this->meeting->getParticipantScore($this->currentUserId)?>
	<div class="student_comment">
	    <?php if ($this->details && $this->meeting->getResultsUrl() && $this->meeting->isResultInTable()):?>
        <p align="left"><a href="<?php echo $this->meeting->getResultsUrl()?>"><?php echo _('Подробнее')?></a></p>
    <?php endif;?>
    <?php
    if (Zend_Registry::get('serviceContainer')->getService('MeetingAssignMarkHistory')->hasMarkHistory($this->meeting->project_id, $this->meeting->meeting_id, $this->currentUserId)):
    ?>
        <p align="left" style="padding-top:4px;"><?php /* @todo: переделать на lightDialogLink*/ echo $this->dialogLinkOld(_('История изменений'), $this->meeting->getParticipantAssign($this->currentUserId)->getScoreHistoryTable(),_('История изменений'),array('width'=>450))?></p>
    <?php endif;?>
    </div>
<?php endif;?>
</td>

<td width="150" valign="top" class="lesson_descript_td" <?php   if ($this->meeting->getParticipantComment($this->currentUserId)):?> <?php  endif;?>>
<div id="lesson_descript">
   <?php if ($this->meeting->getParticipantComment($this->currentUserId)):?>
        <div><?php echo $this->meeting->getParticipantComment($this->currentUserId);?></div>
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
