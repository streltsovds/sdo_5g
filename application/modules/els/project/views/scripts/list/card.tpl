<div class="tmc-project-card-icon">
    <?php echo $this->project->getIconHtml();?>
</div>
<?php
if ($this->project->period == HM_project_projectModel::PERIOD_FREE) {
    $period = array($this->project->getPeriod()  => _('Время проведения'));
} else {
    if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),  HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
        $period = array($this->project->getBeginForParticipant()  => _('Дата начала'), $this->project->getEndForParticipant() => _('Дата окончания'));
    } else {
        if ($this->project->period == HM_project_projectModel::PERIOD_FIXED) {
            $period = array($this->project->getLongtime()  => _('Время проведения'));
        } else { // PERIOD_DATES
            $period = array($this->project->getBegin()  => _('Дата начала'), $this->project->getEnd()    => _('Дата окончания'));
        }
    }
}
?>
<div class="tmc-sub">

    <?php foreach($period as $k => $p): ?>
        <div class="tmc-sub-row">
            <div class="tmc-sub-com"><?php echo $p; ?>:</div>
            <div class="tmc-sub-text"><?php echo $k; ?></div>
        </div>
    <?php endforeach; ?>

</div>

