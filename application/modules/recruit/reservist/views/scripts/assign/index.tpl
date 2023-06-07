<?php if (!$this->isAjaxRequest):?>
     <?php echo $this->Actions('candidate',array( array('title' => _('Создать кандидата'),'url' => $this->url(array('baseUrl' => '', 'module' => 'user', 'controller' => 'list', 'action' => 'new')))));?>
<?php endif;?>
<?php echo $this->grid;?>