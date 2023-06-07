<?php require_once APPLICATION_PATH .  '/views/helpers/Score.php';?>
<div class="lesson">
<a name="lesson_<?php echo $this->subject->subid?>"></a>
<div class="lesson_wrapper_1">
<div class="lesson_wrapper_2">
<div  <?php if (strtotime($this->studentCourseData['end']) && $this->showScore): // если обучение закончено - выделить цветом
?> id="lesson_block_active" <?php else: ?> class="lesson_block" <?php endif;?>>
<div class="lesson_table">
<table border="0" cellpadding="0" cellspacing="0" >
  <tr>
    <td width="220" align="center" valign="top" class="lesson_bg">

        <?php $isStudent = Zend_Registry::get('serviceContainer')->getService('Subject')->isStudent($this->subject->subid, $this->currentUserId); ?>

<div class="subject_icon_container">
<?php if ($this->subject->getIcon()):?>
    <?php if (!$this->subject->isAccessible($this->fromProgram) && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) || (!$this->graduated && !$isStudent && !$this->isTeacher)): ?>
        <?php echo $this->subject->getIconHtml()?>
    <?php else: ?>
    <a href="<?php echo $this->subject->getDefaultUri();?>">
        <?php echo $this->subject->getIconHtml()?>
    </a>
    <?php endif;?>
<?php endif;?>
</div>
<div id="lesson_type"><?php echo $this->subject->isBase() ? _('Базовый курс') : $this->subject->getType();?></div>

</td>
<td width="350" class="lesson_options">
    <div id="lesson_title">
    <?php if (!$this->subject->isAccessible($this->fromProgram) && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) || (!$this->graduated && !$isStudent && !$this->isTeacher)): ?>
        <p><?php echo $this->escape($this->subject->name)?></p>
    <?php else: ?>
        <a href="<?php echo $this->subject->getDefaultUri();?>">
            <?php echo $this->escape($this->subject->name)?>
        </a>
    <?php endif;?>
    </div>

    <?php if ($this->switcher != 'programm') { ?>
    <?php $programms = Zend_Registry::get('serviceContainer')->getService('Programm')->getProgrammsBySubjectId($this->subject->subid, $this->currentUserId);?>
    <?php if (count($programms)) { ?>
    <div id="lesson_go" class="tmc-lesson-programm">
        <p><?php echo count($programms)==1 ? _('Программа'):('Программы'); $i=0; foreach ($programms as $programm) { $i++; if ($i == 1) {echo ': ' . $programm->name;} else {echo ', ' . $programm->name;}  }?></p>
    </div>
    <?php } } ?>


    <div id="lesson_go">
        <div id="lesson_begin" class="<?php if ((!$this->subject->begin) || $this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT):?>recomended<?php endif;?>">
        <?php if (strtotime($this->studentCourseData['end'])): ?>
            <p><?php echo _('Дата окончания обучения');?>: <?php $end = new Zend_Date($this->studentCourseData['end']); echo $end->toString(Zend_Date::DATES);?></p>
        <?php else:?>
            <?php if (($this->subject->period == HM_Subject_SubjectModel::PERIOD_FREE) && !$this->fromProgram):?>
                <?php echo _('Время обучения не ограничено');?>
            <?php else:?>
                <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, HM_Role_Abstract_RoleModel::ROLE_TEACHER))) :?>

                    <?php if ($this->fromProgram || in_array($this->subject->period, array(HM_Subject_SubjectModel::PERIOD_FREE, HM_Subject_SubjectModel::PERIOD_FIXED))): // когда зачислен - тогда и начало?>

                        <?php if ($this->studentCourseData['begin']):?>
                            <p><?php echo _('Дата начала обучения');?>:
                            <?php $begin = new Zend_Date($this->studentCourseData['begin']); echo $begin->toString(Zend_Date::DATES);?></p>
                        <?php elseif ($this->subject->longtime):?>
                            <p><?php echo sprintf(_('Время обучения, дней: %s'), $this->subject->longtime);?></p>
                        <?php endif;?>

                    <?php else: // PERIOD_DATES?>

                        <?php if ($this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT): ?>
                            <p>
                            <?php if (strtotime($this->subject->begin) > time()): ?>
                            <?php echo _('Дата начала обучения, не ранее');?>:
                            <?php else: ?>
                            <?php echo _('Дата начала обучения');?>:
                            <?php endif; ?>
                            <?php $begin = new Zend_Date($this->subject->begin); echo $begin->toString(Zend_Date::DATES);?>
                            </p>
                        <?php elseif ($this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT): ?>
                            <p>
                            <?php if (strtotime($this->subject->begin) > time()): ?>
                            <?php echo _('Рекомендуемая дата начала обучения');?>:
                            <?php else: ?>
                            <?php echo _('Дата начала обучения');?>:
                            <?php endif; ?>
                            <?php $begin = new Zend_Date($this->subject->begin); echo $begin->toString(Zend_Date::DATES);?>
                            </p>
                        <?php elseif ($this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL): // ручной режим?>
                            <?php if (!empty($this->subject->begin)): // уже стартовал ?>
                                <p><?php echo _('Дата начала обучения');?>: <?php $begin = new Zend_Date($this->subject->begin); echo $begin->toString(Zend_Date::DATES);?></p>
                            <?php else: // еще не стартовал?>
                                <?php $begin = new Zend_Date($this->subject->begin_planned); $begin = $begin->toString(Zend_Date::DATES);?>
                                <p><?php echo sprintf(_('Дата начала обучения определяется тьютором (ориентировочно: %s)'), $begin);?></p>
                            <?php endif; ?>
                        <?php endif; ?>

                    <?php endif; // end date begin?>

                    <?php if ($this->fromProgram || ($this->subject->period == HM_Subject_SubjectModel::PERIOD_FIXED) || ($this->subject->period == HM_Subject_SubjectModel::PERIOD_DATES && in_array($this->subject->period_restriction_type, array(HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL)))): ?>

                        <?php if (strtotime($this->studentCourseData['end'])): ?>
                        <p>
                            <?php if ($this->subject->base != HM_Subject_SubjectModel::BASETYPE_SESSION): ?>
                                <?php echo _('Дата окончания обучения, не позднее');?>:
                                    <?php
                                        $end = new Zend_Date($this->studentCourseData['end_planned']);
                                        echo $end->toString(Zend_Date::DATES);
                                    ?>
                            <?php else: ?>
                                <?php echo _('Дата окончания обучения, не позднее');?>:
                                <?php
                                    $end = new Zend_Date($this->subject->end);
                                    echo $end->toString(Zend_Date::DATES);
                                ?>
                            <?php endif; ?>
                        </p>
                            <?php endif;?>

                    <?php elseif ($this->subject->period == HM_Subject_SubjectModel::PERIOD_DATES && $this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT): // нестрого?>

                        <?php if (strtotime($this->studentCourseData['end'])): ?>
                        <p><?php echo _('Рекомендуемая дата окончания обучения');?>: <?php $end = new Zend_Date($this->studentCourseData['end_planned']); echo $end->toString(Zend_Date::DATES);?></p>
                        <?php endif;?>

                    <?php elseif ($this->subject->period == HM_Subject_SubjectModel::PERIOD_DATES && $this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL): // вручную?>

                        <!-- nothing -->

                    <?php endif; // end date end?>

                <?php endif;?>
            <?php endif;?>
        <?php endif;?>
        </div>
    </div>
    <?php if (0 && count($this->subject->teachers) > 0):?>
    <?php $teacher = $this->subject->teachers->current(); ?>
    <div class="lesson_teacher">
        <div class="tmc-lesson_teacher_photo">
            <div class="tmc-lesson_teacher_photo-image" style="background-image: url(&quot;<?php echo $this->baseUrl($teacher->getPhoto());?>&quot;);">

                <?php echo $this->cardLink($this->url(array(
                'module' => 'user',
                'controller' => 'list',
                'action' => 'view',
                'user_id' => $teacher->MID))); ?>

            </div>
        </div>
    </div>
    <?php endif;?>
    <?php if ($this->graduated) : ?>
        <div class="lesson_ended"><?php echo _('Курс завершён');?></div>
    <?php endif;?>

    <?php if (!$this->graduated && !$this->subject->isAccessible($this->fromProgram) && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) : ?>
        <div class="lesson_ended"><?php echo _('Курс не доступен ввиду ограничения по времени');?></div>
    <?php endif;?>
</td>
<td width="100" align="center" valign="top" class="showscore">
<?php if ($this->showScore):?>
    <?php echo $this->score(array(
        'score' => isset($this->marks[$this->subject->subid]) ? $this->marks[$this->subject->subid] : -1,
        'lesson_id' => 'total',
        'scale_id' => $this->subject->getScale(),
        'mode' => HM_View_Helper_Score::MODE_DEFAULT,
    ));?>
<?php endif;?>
<?php if($this->disperse == true && $this->isElectiv===false || $this->isElectiv): ?>
<div class="score_desc">
    <?php if ($isStudent) { ?>
        <?php  if ($this->subject->isUnsubscribleSubject) { ?>
            <a href="<?php echo $this->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'disperse', 'subject_id' => $this->subject->subid));?>" onClick="javascript: return confirm('<?php echo _('Вы действительно хотите отказаться от обучения на этом курсе? Это не исключает возможности в будущем заново подать заявку и пройти обучение.');?>');"><?php echo _('Отказаться от обучения');?></a>
        <?php } ?>
    <?php } else { ?>
        <a href="<?php echo $this->url(array('module' => 'user', 'controller' => 'reg', 'action' => 'subject', 'gridmod' => '', 'subid' => $this->subject->subid, 'programm_id' => 1)); ?>" onClick="javascript: return confirm('<?php echo _('Вы действительно хотите подать заявку на обучение на этом курсе?'); ?>');"><?php echo _('Подать заявку');?></a>
    <?php } ?>
</div>
<?php endif; ?>
</td>
      <td width="250" valign="top" class="lesson_descript_td" id="<?php echo $this->descriptionId ?>">
          <?if (!empty($this->studentCourseData['feedback'])) : ?>
          <?php foreach ($this->studentCourseData['feedback'] as $feedback) :?>
          <div class="lesson_ended"><a href="<?php echo $this->url(array('module' => 'quest', 'controller' => 'feedback', 'action' => 'start', 'quest_id' => $feedback['quest_id'], 'feedback_id' => $feedback['feedback_id']), null, true)?>">
                  <?php echo $feedback['poll']?></a></div><br>
          <?php endforeach; ?>
          <?php endif;?>
      </td>

</tr>
</table>
</div>
</div>
</div>
</div>

</div>
