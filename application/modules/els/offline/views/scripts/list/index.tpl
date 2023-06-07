<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:offline:list:new')):?>
        <?php
            echo $this->Actions(
                'offline',
                array(
//                     array(
//                         'title' => _('Импорт данных'),
//                         'url' => $this->url(array('action' => 'import', 'controller' => 'list', 'module' => 'offline'))
//                     ),
                )
            )
        ?>
    <?php endif;?>
<?php endif;?>
<?php 
echo $this->grid;
?>
