<?php if(!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:subject:feedback:new')):?>

    <?php echo $this->Actions('feedback', array(
            array(
                'title' => _('Создать анкету'),
                'url' => $this->url(array(
                    'module' => 'subject',
                    'controller' => 'feedback',
                    'action' => 'new',
                    'subject_id' => $this->subjectId
                 ), null, true)
            )
        ));?>
    <?php endif;?>
    <?=$this->grid?>
<?php endif;?>