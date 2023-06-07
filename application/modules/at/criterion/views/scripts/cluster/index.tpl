<?php echo $this->grid;

if ($this->showDialog): ?>
    <?php $this->inlineScript()->captureStart(); ?>
        jQuery(document).ready(function(){
            var buttons = {};
            buttons['<?php echo _('Да');?>'] = function () {
                $(this).dialog('close');
                document.location.href = '<?php echo $this->redirectUrl; ?>';
            };
            buttons['<?php echo _('Нет');?>'] = function () {
                $(this).dialog('close');
                document.location.href = '<?php echo $this->url(array('module' => 'criterion', 'controller' => 'cluster', 'action' => 'index', 'delete' => null, 'cluster_id' => null)); ?>';
            };

            jQuery('#redirect-dialog').dialog({
                autoOpen: true,
                resizeable: false,
                width: 400,
                modal: true,
                title: '<?php echo _('Подтверждение удаления кластера'); ?>',
                buttons: buttons
            });
        });
    <?php $this->inlineScript()->captureEnd(); ?>
    <div id="redirect-dialog">
        <p><?php echo _('Кластер назначен компетенциям. При удалении кластера связи с компетенциями будут удалены. Продолжить?'); ?></p>
    </div>
<?php endif; ?>
