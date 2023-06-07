<?php if (!$this->gridAjaxRequest):?>
    <?php if ($this->projectId && Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:assign:participant:index')):?>
        <br>
        <a href="<?php echo $this->url(
            array(
                'module' => 'assign',
                'controller' => 'import-participants',
                'action' => 'index',
                'source' => 'csv'
            )
        );?>" >
            Импорт списка участников конкурса из CSV
        </a>
        <br><br>
    <?php endif; ?>
<?php endif; ?>

<?php echo $this->grid;?>
<?php if (!$this->gridAjaxRequest):?>
<?php echo $this->footnote();?>
<?php endif;?>
<?php $this->inlineScript()->captureStart(); ?>
    jQuery(document).ready(function(){
        jQuery('#_fdiv [multiple]').attr('size','1');
        jQuery('#_fdiv [multiple]').removeAttr('multiple');
    });
<?php $this->inlineScript()->captureEnd(); ?>
