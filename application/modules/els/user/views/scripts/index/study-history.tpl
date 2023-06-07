<?php if (!$this->gridAjaxRequest && Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:user:index:new-study-history')):?>
    <?php echo $this->Actions('index', array(
        array(
            'title' => _('Создать запись в истории обучения'),
            'url' => $this->url(array('module' => 'user', 'controller' => 'index', 'action' => 'new-study-history'))
        )
    ));?>
<?php endif;?>
<?php echo $this->grid;