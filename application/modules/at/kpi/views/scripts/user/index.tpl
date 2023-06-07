<?php if ((!$this->gridAjaxRequest) && Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:kpi:user:new')):?>
    <?php echo $this->actions('kpi', array(array('title' => _('Создать показатель эффективности'), 'url' => $this->url(array('action' => 'new')))));?>
<?php endif;?>

<?php echo $this->grid;?>