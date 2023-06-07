<?php $this->headLink()->appendStylesheet( $this->serverUrl('/css/infoblocks/schedule-accordion/schedule.css') ); ?>
<div class="schedule-accordion">
    <?php if ($this->lessons):?>
    <ul>
        <?php $i = 1; $all = count($this->lessons); ?>
        <?php foreach($this->lessons as $lesson):?>
        <li class="<?= $lesson['status']; ?><?php if ($i == 1): ?> first<?php endif; ?><?php if ($i == $all): ?> last<?php endif; ?>">

            <div class="lesson_bg_img_small">
                <?php if ($lesson['lesson']->getIcon()):?>
                <?php if ($lesson['lesson']->typeID != HM_Event_EventModel::TYPE_EMPTY):?>
                <a
                <?php if ($lesson['lesson']->typeID ==HM_Event_EventModel::TYPE_COURSE) echo 'target="'.$lesson['lesson']->isNewWindow().'"'?>
                href="<?php echo $this->url(array('action' => 'index', 'controller' => 'execute', 'module' => 'lesson', 'lesson_id' => $lesson['lesson']->SHEID, 'subject_id' => $lesson['lesson']->CID), false, true)?>"
                >
                <?php endif;?>
                <img src="<?php echo ($lesson['lesson']->getUserIcon()) ? $lesson['lesson']->getUserIcon() : $lesson['lesson']->getIcon(HM_Lesson_LessonModel::ICON_MEDIUM)?>" alt="<?php echo $this->escape($lesson['lesson']->title)?>" title="<?php echo $this->escape($lesson['lesson']->title)?> - <?php echo _('Выполнение занятия');?>"/>
                <?php if ($lesson['lesson']->typeID != HM_Event_EventModel::TYPE_EMPTY):?>
                </a>
                <?php endif;?>
                <?php endif;?>
            </div>

            <span class="title">
            <?php if ($lesson['lesson']->typeID == HM_Event_EventModel::TYPE_EMPTY): ?>
                <span><?php echo $this->escape($lesson['lesson']->title) ?></span>
            <?php else: ?>
                <?php $lessonAttribs = array(
                    'href' => $this->url(array('action' => 'index', 'controller' => 'execute', 'module' => 'lesson', 'lesson_id' => $lesson['lesson']->SHEID, 'subject_id' => $lesson['lesson']->CID), 'default', true),
                    'target' => ($lesson['lesson']->typeID == HM_Event_EventModel::TYPE_ECLASS) ? '_blank' : '_self'
                );
                if ($lesson['lesson']->typeID == HM_Event_EventModel::TYPE_COURSE) $lessonAttribs['target']=$lesson['lesson']->isNewWindow();?>
                <a <?php echo $this->HtmlAttribs($lessonAttribs) ?>><?php echo $this->escape($lesson['lesson']->title) ?></a>
            <?php endif ?>
            </span>

            <span class="pit">
                <span class="bg<?php if ($lesson['status'] == 'not-started'): ?> l-bgc<?php endif; ?>"></span><span class="text" title="<?=
                    $lesson['status'] == 'infinite' ? _('Время запуска не ограничено') : _('Время запуска') . ': ' . $this->escape($lesson['date']); ?>"><?=
                    $lesson['status'] == 'infinite' ? '&#x221E;' : $this->escape($lesson['date']); ?>
                </span>
            </span>

        </li>
        <?php $i++; ?>
        <?php endforeach;?>
    </ul>
    <?php else:?>
    <!--<p><?//= _('Данный курс не содержит занятий.')?></p>-->
    <?php endif;?>
    <?php if ($this->subject->period = HM_Subject_SubjectModel::PERIOD_FREE) $baseUrl = ''; ?>
    <div class="schedule-full lh-c"><a onclick="$('iframe').remove();" href="<?= $this->serverUrl($baseUrl.'/lesson/list/my/subject_id/'.$this->subject->subid);?>" class="l-bgc"><?= _('Все занятия');?></a></div>
</div>