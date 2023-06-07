<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:hr:reserve:list:new')):?>
    <?php if (!$this->gridAjaxRequest):?>
        <?php echo $this->Actions('reserve', array(array('title' => _('Создать должность КР'), 'url' => $this->url(array('action' => 'new')))));?>
    <?php endif; ?>
<?php endif;?>
<?php echo $this->grid;