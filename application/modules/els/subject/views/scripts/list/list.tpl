<?php
$this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css');
$this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/courses_table.css');
?>
<?php echo $this->headSwitcher(
        array('module' => 'subject', 'controller' => 'list', 'action' => 'index', 'switcher' => 'list'), 
        null, 
        $this->disabledSwitcherMods);
?>
<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:subject:list:new')):?>
    <?php echo $this->Actions('subject');?>
<?php endif;?>
<div class="tmc-subgridswitcher">
    <?php echo $this->listSwitcher(
          array('past' => _('прошедшие'), 'current' => _('текущие'), 'future' => _('будущие')),
          array('module' => 'subject', 'controller' => 'list', 'action' => 'list'),
          $this->listSwitcher
    );?>
    <?php if (count($this->subjects)):?>
        <?php if (isset($this->is_student) && $this->is_student) : ?>
            <div class="progress_title progress_title-my"><?php echo _('Результат');?></div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if (count($this->subjects)):?>

    <div class="clearfix"></div>
    <?php foreach($this->subjects as $subject):?>
        <?php $fromProgram = in_array($subject->subid, $this->fromProgramArray); ?>
        <?php echo $this->subjectPreview($subject, $this->marks, $this->graduatedList, $this->studentCourseData[$subject->subid], array(), $fromProgram)?>
    <?php endforeach;?>
<?php else:?>
    <div class="clearfix"></div>
    <div><?php echo _('Отсутствуют данные для отображения')?></div>
<?php endif;?>

<?php if ($this->switchRole):?>
<script>
	document.location.href = '/switch/role/<?php echo $this->switchRole;?>';
</script>
<?php endif;?>
