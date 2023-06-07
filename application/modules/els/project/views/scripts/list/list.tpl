<?php
$this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css');
$this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/courses_table.css');
?>
<?php echo $this->headSwitcher(array('module' => 'project', 'controller' => 'list', 'action' => 'index', 'switcher' => 'list'));?>
<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:project:list:new')):?>
    <?php echo $this->Actions('project');?>
<?php endif;?>
<?php echo $this->listSwitcher(
      array('past' => _('прошедшие'), 'current' => _('текущие'), 'future' => _('будущие')),
      array('module' => 'project', 'controller' => 'list', 'action' => 'list'),
      $this->listSwitcher
);?>
<?php if (count($this->projects)):?>
    <div class="clearfix"></div>
    <?php foreach($this->projects as $project):?>
        <?php echo $this->projectPreview($project, $this->marks, $this->graduatedList, $this->participantCourseData[$project->projid])?>
    <?php endforeach;?>
<?php else:?>
    <div class="clearfix"></div>
    <div><?php echo _('Отсутствуют данные для отображения')?></div>
<?php endif;?>