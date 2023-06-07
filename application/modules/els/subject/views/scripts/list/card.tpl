<div class="tmc-subject-card-icon">
    <?php echo $this->subject->getIconHtml();?>
</div>
<?php
if ($this->subject->period == HM_Subject_SubjectModel::PERIOD_FREE && !$this->fromProgram) {
    $period = array($this->subject->getPeriod()  => _('Ограничение времени обучения'));
} else {
    if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),  HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
        $period = array($this->subject->getBeginForStudent($this->fromProgram)  => _('Дата начала обучения'), $this->subject->getEndForStudent($this->fromProgram) => _('Дата окончания обучения, не позднее'));
    } else {
        if ($this->subject->period == HM_Subject_SubjectModel::PERIOD_FIXED) {
            $period = array($this->subject->getLongtime()  => _('Ограничение времени обучения'));
        } else { // PERIOD_DATES
            $period = array($this->subject->getBegin()  => _('Дата начала'), $this->subject->getEnd()    => _('Дата окончания'));
        }
    }
}
?>
<?php
switch ($this->subject->state) {
    case HM_Subject_SubjectModel::STATE_PENDING:
        $tooltip = 'Обучение по курсу не начато. Никто из слушателей не имеет доступа к материалам курса.';
        break;
    case HM_Subject_SubjectModel::STATE_ACTUAL:
        $tooltip = 'Идёт обучение по курсу, материалы курса открыты для слушателей.';
        break;
    case HM_Subject_SubjectModel::STATE_CLOSED:
        $tooltip = 'Обучение по курсу закончено. Все слушатели переведены в прошедшие обученеи, никто из них не имеет доступа к материалам курса.';
        break;
}
?>
<div class="tmc-sub">

    <?php foreach($period as $k => $p): ?>
    <div class="tmc-sub-row">
        <div class="tmc-sub-com"><?php echo $p; ?>:</div>
        <div class="tmc-sub-text"><?php echo $k; ?></div>
    </div>
    <?php endforeach; ?>

    <?php if ($this->subject->getProvider()): ?>
    <div class="tmc-sub-row">
        <div class="tmc-sub-com"><?php echo _('Провайдер обучения'); ?>:</div>
        <div class="tmc-sub-text"><?php echo $this->subject->getProvider(); ?></div>
    </div>
    <?php endif; ?>

    <?php if ($this->subject->getRoom()): ?>
    <div class="tmc-sub-row">
        <div class="tmc-sub-com"><?php echo _('Место проведения'); ?>:</div>
        <div class="tmc-sub-text"><?php echo $this->subject->getRoom(); ?></div>
    </div>
    <?php endif; ?>

    <?php if ($this->subject->getType()): ?>
    <div class="tmc-sub-row">
        <div class="tmc-sub-com"><?php echo _('Тип'); ?>:</div>
        <div class="tmc-sub-text"><?php echo $this->subject->getType(); ?></div>
    </div>
    <?php endif; ?>

    <?php if ($this->subject->getPriceWithCurrency()): ?>
    <div class="tmc-sub-row">
        <div class="tmc-sub-com"><?php echo _('Цена'); ?>:</div>
        <div class="tmc-sub-text"><?php echo $this->subject->getPriceWithCurrency(); ?></div>
    </div>
    <?php endif; ?>

    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:subject:list:calendar')): ?>
    <div class="tmc-sub-row">
        <div class="tmc-sub-com"><?php echo _('Цвет в календаре'); ?>:</div>
        <div class="tmc-sub-text"><?php echo $this->subject->getColorField(); ?></div>
    </div>
    <?php endif; ?>

<?php if ($this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL ): ?>
<!--div class="tmc-sub-row">
    <div class="tmc-sub-com"><?php echo $this->subject->getStateSwitcher(); ?></div>
    <div class="tmc-sub-text"><?php echo $tooltip ?></div>
</div-->
<?php endif; ?>

</div>
<div class="tmc-sub-teacher-block">
    <?php if ($this->teachers): ?>
        <?php foreach($this->teachers as $teacher): ?>
            <div class="tmc-lesson_teacher">
                <div class="tmc-lesson_teacher_photo">
                        <?php echo $this->cardLink($this->url(array(
                        'module' => 'user',
                        'controller' => 'list',
                        'action' => 'view',
                        'user_id' => $teacher->MID)),
                        '<div class="tmc-lesson_teacher_photo-image" style="background-image: url('.$this->baseUrl($teacher->getPhoto()).');"></div>',
                        'html'
                        ); ?>
                    <?php echo _('Тьютор'); ?>:
                    <span><?php echo $teacher->getName(); ?></span>
                </div>
            </div>

        <?php endforeach; ?>
    <?php endif; ?>
</div>
<div class="tmc-sub-graduated-block">
<?php if ($this->graduated): ?>
        <?php echo $this->subject->getGraduatedMsg(); ?>
<?php endif; ?>
</div>

