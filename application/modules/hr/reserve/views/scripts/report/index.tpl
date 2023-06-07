<?php echo $this->reportList($this->lists['general']);?>
<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL))) :?>
    <div class="report-summary clearfix">
        <h2><?php echo _('Результаты последней регулярной оценки');?></h2>
        <?php echo $this->reportColorScale($this->lastSessionId);?>
    </div>
<?php endif;?>