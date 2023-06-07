<?php if (!$this->gridAjaxRequest):?>
    <?php echo $this->headSwitcher(array('module' => 'meeting', 'controller' => 'list', 'action' => 'index', 'switcher' => 'index'));?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:meeting:list:new')):?>
    <?php echo $this->Actions('meetings', array(array('title' => _('Создать мероприятие'), 'url' => $this->url(array('action' => 'new'))), /*array('title' => _('Сгенерировать план мероприятий'), 'url' => $this->url(array('action' => 'generate')))*/));?>
    <?php endif;?>
<?php endif;?>
<?php echo $this->grid?>