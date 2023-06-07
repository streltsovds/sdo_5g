<?php if (!$this->isAjaxRequest && Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:recruit:provider:new')):?>
    <?php echo $this->Actions('provider');?>
<?php endif;?>
<?php echo $this->grid;?>