<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:programm:list:new')):?>
        <?php echo $this->Actions('programm', array(array('title' => _('Создать программу'), 'url' => $this->url(array('module' => 'programm', 'controller' => 'list', 'action' => 'new')))));?>
    <?php endif;?>
<?php endif;?>
<?php 
echo $this->grid;
?>
