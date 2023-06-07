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
            <img src="<?php echo $this->event->evaluation->current()->getIcon(HM_Lesson_LessonModel::ICON_MEDIUM)?>" alt="<?php echo $this->escape($this->event->name);?>" title="<?php echo $this->escape($this->event->name)?>"/>
        </div>
        <div id="event_type"><?php echo $this->event->evaluation->current()->getRelationTypeTitleForRespondent();?></div>
    </td>
    <td width="415" class="event_options">
        <div id="event_title">
            <span><?php echo $this->escape($this->event->name) ?></span>
            
        </div>
        <div id="event_go">
            <?php if (strtotime($this->event->date_end)) :?>
            <div id="event_begin" class="recomended">
                <?php echo _('Выполнить до') . ': ' . date('d.m.Y', strtotime($this->event->date_end));?>
            </div>
            <?php endif; ?>
            <?php if ($this->event->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED): ?>
            <div class="event_ended"><?php echo _('Выполнено');?></div>
            <?php endif; ?>
        </div>
        <div class="event_lessons">
            <?php if (count($this->event->lessons)): ?>        
            <?php foreach ($this->event->lessons as $lesson):?>        
                <div class="event_lesson">
                    <div class="event_lesson_title">
                        <?php if ($this->event->isExecutable()):?>
                        <a href="<?php echo $this->url(array('module' => 'lesson', 'controller' => 'execute', 'action' => 'index', 'lesson_id' => $lesson->SHEID, 'baseUrl' => ''))?>"><?php echo $this->escape($lesson->title) ?></a>
                        <?php else: ?>
                        <span><?php echo $this->escape($lesson->title) ?></span>
                        <?php endif;?>
                    </div>
                    <div class="event_lesson_description">
                        <?php if (!empty($lesson->descript)): ?>
                        <span><?php echo $lesson->descript ?></span>
                        <?php endif;?>
                    </div>
                </div>
                <div class="event_lesson_score">
                    <?php echo $this->score(array(
                        'score' => $this->results[$lesson->SHEID],
                        'user_id' => $this->currentUserId,
                        'lesson_id' => $lesson->SHEID,
                        'scale_id' => $this->event->evaluation->current()->scale_id,
                        'mode' => HM_View_Helper_Score::MODE_PLAIN,
                    ));?>
                    <?php if ($this->allowLog[$lesson->SHEID]): ?>
                	<div class="student_comment">
                        <p align="left"><a href="<?php echo $lesson->getResultsUrl()?>"><?php echo _('Подробнее')?></a></p>
                    </div>
                    <?php endif; ?>            
                </div>
                <div class="clearfix"></div>
            <?php endforeach;?>
            <?php else:?>
            <?php echo _('В данное мероприятие не включен ни один тест');?>
            <?php endif;?>            
        </div>
    </td>
    <td width="200" align="center" valign="top" class="showscore">
        <?php echo $this->score(array(
                           'score' => ($this->event->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED),
                           'scale_id' => 2,
                           'mode' => HM_View_Helper_Score::MODE_DEFAULT,
                   ));?>
    </td>
    <td width="150" valign="top" class="event_descript_td"></td>
  </tr>
</table>
</div>
</div>
</div>
</div>
</div>