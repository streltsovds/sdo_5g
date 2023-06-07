<?php if (!$this->gridAjaxRequest && Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:recruit:vacancies:new')):?>
    <?php echo $this->Actions('vacancy');?>
<?php endif;?><?php echo $this->grid;?>