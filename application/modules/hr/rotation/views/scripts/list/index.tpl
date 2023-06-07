<script>
    $(document).ready(function () {
        var debts = [<?php echo '"'.implode('","', $this->debts).'"' ?>];
        debts.forEach(function (id) {
            $('"'+"div.workflowBulbs.grid-workflow[data-workflow_id='"+id+"']"+'"').closest('tr').addClass("highlighted");
        });
    });

</script>
<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:hr:rotation:new')):?>
    <?php if (!$this->gridAjaxRequest):?>
        <?php echo $this->Actions('rotation', array(array('title' => _('Создать сессию ротации'), 'url' => $this->url(array('action' => 'new')))));?>
    <?php endif; ?>
    <?php endif;?>

<?php echo $this->grid;?>
<?php if (!$this->gridAjaxRequest):?>
    <?php echo $this->footnote();?>
<?php endif;?>

<?php if (!$this->isAjaxRequest): ?>
<style>
    .hm-rotation-dolg {
        color: #ffffff;
        background-color: #cc0000;
        font-weight: bold;
        font-size: 14px;
        padding: 1px 6px;
        border-radius: 3px;
    }
</style>
<?php endif; ?>