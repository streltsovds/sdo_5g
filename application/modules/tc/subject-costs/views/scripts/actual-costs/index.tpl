<?php if (!$this->isAjaxRequest && Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:subject-costs:actual-costs:new')):?>
    <?php echo $this->Actions('actual-costs', array(
        array(
            'title' => _('Создать запись'),
            'url' => $this->url(array('module' => 'subject-costs', 'controller' => 'actual-costs', 'action' => 'new'))
        )
    ));?>
<?php endif;?>
<?php echo $this->grid;