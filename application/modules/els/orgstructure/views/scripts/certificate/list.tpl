<?php if (!$this->gridAjaxRequest): ?>
   <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:orgstructure:certificate:edit')):?>
   <?php 
   echo $this->Actions('certificates', array(
      array(
         'title' => _('Создать сертификат'), 
         'url' => preg_replace('/^\\//', '', $this->url(array('action' => 'new')))
      ), 
   ));
   ?>
   <?php endif?>
<?php endif?>
<?php echo $this->grid?>
