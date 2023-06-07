<?php if (!$this->gridAjaxRequest):?>
<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css'); ?>
<?php if ($this->baseType != HM_Tc_Subject_SubjectModel::BASETYPE_SESSION && Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:subject:fulltime:new')):?>
    <?php echo $this->actions('fulltime', array(
        array(
            'title' => _('Создать внешний курс'),
            'url'   => $this->url(array('module' => 'subject', 'controller' => 'fulltime', 'action' => 'new', 'subid' => null, 'subject_id' => null))
        )
    ));?>
<?php endif;?>
<?php endif;?>
<?php echo $this->grid?>
<?php if (!$this->gridAjaxRequest):?>
<?php echo $this->footnote();?>
<?php endif;?>
<?php $this->inlineScript()->captureStart(); ?>
    jQuery(document).ready(function(){
        jQuery('#_fdiv [multiple]').attr('size','1');
        jQuery('#_fdiv [multiple]').removeAttr('multiple');
    });
<?php $this->inlineScript()->captureEnd(); ?>
