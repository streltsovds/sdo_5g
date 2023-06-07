<div class="lesson">
<a name="lesson_<?php echo $this->project->projid?>"></a>
<div class="lesson_wrapper_1">
<div class="lesson_wrapper_2">
<div  <?php if (strtotime($this->participantCourseData['end']) && $this->showScore): // если обучение закончено - выделить цветом
?> id="lesson_block_active" <?php else: ?> class="lesson_block" <?php endif;?>>
<div class="lesson_table">
<table border="0" cellpadding="0" cellspacing="0" >
  <tr>
    <td width="220" align="center" valign="top" class="lesson_bg">
        <div class="project_icon_container">
            <?php if ($this->project->getIcon()):?>
                <?php if (!$this->project->isAccessible() && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) || (!$this->graduated && !$isStudent && !$this->isTeacher)): ?>
                    <?php echo $this->project->getIconHtml()?>
                <?php else: ?>
                    <a href="<?php echo $this->project->getDefaultUri();?>">
                        <?php echo $this->project->getIconHtml()?>
                    </a>
                <?php endif;?>
            <?php endif;?>
        </div>
    </div>
    <div id="lesson_type"><?php echo _('Конкурс');?></div>

</td>
<td width="350" class="lesson_options">
    <div id="lesson_title">
    <?php if (!$this->project->isAccessible() && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)): ?>
        <p><?php echo $this->escape($this->project->name)?></p>
    <?php else: ?>
        <a href="<?php echo Zend_Registry::get('serviceContainer')->getService('Project')->getDefaultUri($this->project->projid);?>">
            <?php echo $this->escape($this->project->name)?>
        </a>
    <?php endif;?>
    </div>

    <div id="lesson_go">
        <div id="lesson_begin" class="<?php if ((!$this->project->begin) || $this->project->period_restriction_type == HM_Project_ProjectModel::PERIOD_RESTRICTION_DECENT):?>recomended<?php endif;?>">
        <?php if (strtotime($this->participantCourseData['end'])): ?>
        	<p><?php echo _('Дата окончания конкурса');?>: <?php $end = new Zend_Date($this->participantCourseData['end']); echo $end->toString(Zend_Date::DATES);?></p>
        <?php else:?>
	        <?php if ($this->project->period == HM_Project_ProjectModel::PERIOD_FREE):?>
	            <?php echo _('Время конкурса не ограниченно');?>
	        <?php else:?>
	        	<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, HM_Role_Abstract_RoleModel::ROLE_TEACHER))) :?>

                <?php if ($this->project->begin): ?>
                    <p><?php echo _('Дата начала');?>:
                    <?php $begin = new Zend_Date($this->project->begin); echo $begin->toString(Zend_Date::DATES);?></p>
                <?php endif; ?>
                <?php if ($this->project->end): ?>
                    <p><?php echo _('Дата окончания');?>:
                    <?php $end = new Zend_Date($this->project->end); echo $end->toString(Zend_Date::DATES);?></p>
                <?php endif; ?>

		        <?php endif;?>
	        <?php endif;?>
        <?php endif;?>
        </div>
    </div>
    <?php if ($this->graduated) : ?>
    	<div class="lesson_ended"><?php echo _('Конкурс завершён');?></div>
    <?php endif;?>
</td>
<td width="100" align="center" valign="top" class="showscore"></td>
<td width="150" valign="top" class="lesson_descript_td" <?php if ($this->share):?> style="border-left: 1px solid #cccccc;" <?php endif;?>>
</td>
</tr>
</table>



</div>
</div>
</div>
</div>

</div>
