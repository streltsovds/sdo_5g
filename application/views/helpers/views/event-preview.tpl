<div class="event_min" id="<?php echo $this->event->session_event_id?>">
<a name="event_<?php echo $this->event->session_event_id?>"></a>
<div <?php if ($this->event->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED):?> class="event_wrapper_1_score" <?php else:?> class="event_wrapper_1" <?php endif;?>>
<div class="event_wrapper_2">
<div <?php if ($this->event->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED) :?> id="event_block_active" <?php else:?> class="event_block" <?php endif;?>>
<div class="event_table">
<table border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td width="88" align="center" valign="top" class="event_bg">
        <div class="event_bg_img">
            <?php if ($isExecutable = $this->event->isExecutable()): ?>
            <a href="<?php echo $this->url(array('action' => 'index', 'controller' => 'index', 'module' => 'event', 'session_event_id' => $this->event->session_event_id));?>"><img src="<?php echo $this->serverUrl();?>/<?php echo $this->event->getIcon()?>" alt="<?php echo $this->escape($this->event->title)?>" title="<?php echo $this->escape($this->event->name)?> - <?php echo _('Заполнение анкеты');?>" /></a>
            <?php else: ?>
                <img src="<?php echo $this->serverUrl();?>/<?php echo $this->event->getIcon()?>" alt="<?php echo $this->escape($this->event->title)?>" title="<?php echo $this->escape($this->event->name)?> - <?php echo _('Заполнение анкеты');?>" />
            <?php endif; ?>
        </div>
        <div id="event_type"><?php echo count($this->event->programmEvent) ? $this->event->programmEvent->current()->name : '';?></div>
    </td>
    <td width="415" class="event_options">
        <div id="event_title">
            <?php if ($isExecutable): ?>
            <a href="<?php echo $this->url(array('action' => 'index', 'controller' => 'index', 'module' => 'event', 'session_event_id' => $this->event->session_event_id));?>"><?php echo $this->escape($this->event->name) ?></a>
            <?php else: ?>
            <span><?php echo $this->escape($this->event->name) ?></span>
            <?php endif; ?>
        </div>
        <div id="event_go">
            <?php if (count($messages = $this->event->getMessages())): ?>
            <div id="event_begin" class="recomended">
                <?php foreach($messages as $message):?>
                <p><?php echo $message;?><p>
                <?php endforeach;?>
            </div>
            <?php endif; ?>
            <?php if ($this->event->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED): ?>
            <div class="event_ended"><?php echo _('Выполнено');?></div>
            <?php endif; ?>
        </div>
        <?php if (0 && $this->event->description):?>
        <div class="event_main_desc"><?php echo nl2br($this->event->description);?></div>
        <?php endif;?>
    </td>
    <td width="200" align="center" valign="top" class="showscore">
    <?php echo $this->score(array(
        'score' => ($this->event->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED),
        'scale_id' => HM_Scale_ScaleModel::TYPE_BINARY,
        'mode' => HM_View_Helper_Score::MODE_DEFAULT,
    ));?>

    <?php if (
         $this->event->isReportAvailable() &&
        ($this->event->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED) &&
         in_array($this->event->method, array(HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE, HM_At_Evaluation_EvaluationModel::TYPE_TEST))
    ): ?>
	<div class="student_comment">
        <p align="left"><a href="<?php echo $this->url(array(
            'module' => 'session',
            'controller' => 'report',
            'action' => 'event',
            'session_id' => $this->event->session_id,
            'session_event_id' => $this->event->session_event_id,
        ));?>"><?php echo _('Подробнее')?></a></p>
    </div>
    <?php endif; ?>
    </td>

    <td width="150" valign="top" class="event_descript_td">
        <div id="event_descript"></div>
    </td>
    </tr>
</table>
</div>
</div>
</div>
</div>

</div>
