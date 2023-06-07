<div class="meeting_min" id="<?php echo $this->meeting->meeting_id ?>">
<a name="meeting_<?php echo $this->meeting->meeting_id?>"></a>
<div <?php if ($this->meeting->getParticipantScore($this->currentUserId) != -1 && $this->showScore):?> class="meeting_wrapper_1_score" <?php else:?> class="meeting_wrapper_1" <?php endif;?>>
<div class="meeting_wrapper_2">
<div <?php if ($this->meeting->getParticipantScore($this->currentUserId) != -1 && $this->showScore):
?> id="meeting_block_active" <?php else:?> class="meeting_block" <?php endif;?>>
<div class="meeting_table">
<table border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td width="88" align="center" valign="top" class="meeting_bg">
<div class="meeting_bg_img">
<?php if ($this->meeting->getIcon()):?>
    <a href="<?php echo $this->meeting->getResultsUrl(array('user_id' => $this->currentUserId))?>">
        <img src="<?php echo $this->meeting->getIcon(HM_Meeting_MeetingModel::ICON_MEDIUM)?>" alt="<?php echo $this->escape($this->meeting->title)?>" title="<?php echo $this->escape($this->meeting->title)?> - <?php echo ($this->isParticipantPageForModerator)? _('Просмотр результатов слушателя') : _('Просмотр общих результатов');?>"/>
	</a>
    <?php endif;?>
</div>
<div id="meeting_type"><?php echo $this->type;?></div>
</td>
<td width="415" class="meeting_options">
    <div id="meeting_title">
        <?php if ($this->meeting->typeID == HM_Event_EventModel::TYPE_EMPTY): ?>
            <span><?=$this->escape($this->meeting->title) ?></span>
        <?php else: ?>
            <?php $meetingAttribs = array('href' => $this->titleUrl) ?>
            <?php if(!empty($this->targetUrl)) $meetingAttribs['target'] = '_blank' ?>
                <a <?php if ($this->meeting->typeID ==HM_Event_EventModel::TYPE_COURSE) echo 'target="'.$this->meeting->isNewWindow().'"'?> href="<?php echo $this->meeting->getExecuteUrl(array('user_id' => $this->currentUserId))?>" title='<?php echo _('Предпросмотр занятия'); ?>'><?php echo $this->escape($this->meeting->title)?></a>
            <?php endif;?>

            <a class="edit" title="<?php echo _('Редактировать')?>" href="<?php echo $this->url(array(
	                'module'     => 'meeting',
	                'controller' => 'list',
	                'action'     => 'edit',
	                'subject_id' => $this->meeting->project_id,
	                'meeting_id'  => $this->meeting->meeting_id,
	                'fromlist'   => 'y',
	                'user_id'    => ($this->isParticipantPageForModerator)? $this->currentUserId : null
	         ), null, true)?>">&nbsp;&nbsp;&nbsp;&nbsp;</a>

            <a class="delete meeting_preview_moderator_delete_<?php echo $this->meeting->meeting_id; ?>" title="<?php echo _('Удалить')?>" href="<?php echo $this->url(array(
	                'module'     => 'meeting',
	                'controller' => 'list',
	                'action'     => 'delete',
	                'subject_id' => $this->meeting->project_id,
	                'meeting_id'  => $this->meeting->meeting_id,
	                'switcher'   => 'my',
	                'user_id'    => ($this->isParticipantPageForModerator)? $this->currentUserId : null
	         ), null, true)?>">&nbsp;&nbsp;&nbsp;&nbsp;</a>

	         <script type="text/javascript">
	           $('.meeting_preview_moderator_delete_<?php echo $this->meeting->meeting_id; ?>').bind('click', function(e) {
		         if (!confirm('<?php echo _('Вы действительно хотите удалить?'); ?>')) {
			         e.preventDefault();
		         }
	           });
	         </script>
    </div>

    <div id="meeting_go">
    <div id="meeting_begin" class="<?php if ($this->meeting->recommend):?>recomended<?php endif;?>">
    <?php if ($this->meeting->recommend):?>
        <?php echo _('Рекомендуемое время выполнения');?>:
    <?php else:?>
        <?php echo _('Время выполнения');?>:
    <?php endif;?>
	<?php echo $this->datetime?>
    </div>
    </div>

    <?php if ($this->meeting->cond_sheid):?>
    	<?php $title = $this->titles[$this->meeting->cond_sheid];?>
	    <div class="meeting_cond_sheid"><?php echo _('Условие: выполнение занятия ')?><a href="#meeting_<?php echo $this->meeting->cond_sheid;?>"><?php echo ($title) ? $title : _('не найдено')?></a></div>
	    <?php if ($this->meeting->cond_mark):?>
	    	<div class="meeting_cond_mark"><?php echo _(' на оценку ');?><b><?php echo $this->meeting->cond_mark;?></b></div>
	    <?php endif;?>
    <?php endif;?>
	<?php if($this->meeting->cond_progress):?>
		<div class="meeting_cond_sheid"><?php echo _('Условие: процент выполнения ').$this->meeting->cond_progress.'%'?></div>
	<?php endif;?>
	<?php if($this->meeting->cond_avgbal):?>
		<div class="meeting_cond_sheid"><?php echo _('Условие: средний балл ').$this->meeting->cond_avgbal?></div>
	<?php endif;?>
	<?php if($this->meeting->cond_sumbal):?>
		<div class="meeting_cond_sheid"><?php echo _('Условие: суммарный балл ').$this->meeting->cond_sumbal?></div>
	<?php endif;?>

    <?php if ($this->meeting->getFormulaPenaltyId()):?>
        <div class="meeting_penalty" style="color: red;"><?php echo _('Установлен штраф за несвоевременную сдачу')?></div>
    <?php endif;?>

    <?php if ($this->moderator):?>
    <div class="meeting_moderator"><?php echo _('Тьютор') . ': ' . $this->cardLink($this->url(array(
    																							'module' => 'user',
    																							'controller' => 'list',
    																							'action' => 'view',
    																							'user_id' => $this->moderator['user_id']))).$this->moderator['fio'];?></div>
    <?php endif;?>

    <?php if ($this->meeting->descript):?>
        <div class="meeting_main_desc"><?php echo $this->meeting->descript;?></div>
    <?php endif;?>
<!--<div id="meeting_edit">
<?php if ($this->allowEdit):?>
        <a href="<?php echo $this->url(array('action' => 'edit', 'meeting_id' => $this->meeting->meeting_id))?>"><?php echo $this->icon('edit')?></a>
    <?php endif;?>
    <?php if ($this->allowDelete):?>
        <a href="<?php echo $this->url(array('action' => 'delete', 'meeting_id' => $this->meeting->meeting_id))?>"><?php echo $this->icon('delete')?></a>
    <?php endif;?>
</div>
--></td>
<td align="center" valign="top" class="showscore">
<?php if ($this->showScore):?>
    <?php echo $this->score(array(
        'score' => $this->meeting->getParticipantScore($this->currentUserId),
        'user_id' => $this->currentUserId,
        'meeting_id' => $this->meeting->meeting_id,
        'mode' => HM_View_Helper_Score::MODE_FORSTUDENT,
        'scale_id' => $this->meeting->getScale(),
    ));?>
    <?//php echo $this->meeting->getParticipantScore($this->currentUserId)?>
	<div class="student_comment">
    <?php
    $studentAssign = $this->meeting->getParticipantAssign($this->currentUserId);
    if ($studentAssign && count($studentAssign->getScoreHistory()) > 1):
    ?>
        <p align="left" style="padding-top:10px;"><?php echo $this->dialogLinkOld(_('История изменений'), $this->meeting->getParticipantAssign($this->currentUserId)->getScoreHistoryTable(),_('История изменений'),array('width'=>450))?></p>
    <?php endif;?>
    </div>
<?php endif;?>
</td>

<td width="150" valign="top" class="meeting_descript_td" <?php   if ($this->isParticipantPageForModerator && $this->meeting->getParticipantComment($this->currentUserId)):?> <?php  endif;?>>
<div class="meeting_descript">
    <?php if ($this->isParticipantPageForModerator):?>
    <textarea name="score[<?php echo $this->currentUserId; ?>_<?php echo $this->meeting->meeting_id; ?>]" class="tComment" id="clId_<?php echo $this->currentUserId; ?>_<?php echo $this->meeting->meeting_id; ?>" placeholder="<?php echo _('добавить комментарий к оценке');?>">
<?php
if ( $this->meeting->getParticipantComment($this->currentUserId)):
                    echo $this->meeting->getParticipantComment($this->currentUserId);
                endif;
?></textarea>
    <?php endif;?>
</div>
</td>
</tr>
</table>
</div>
</div>
</div>
</div>
</div>