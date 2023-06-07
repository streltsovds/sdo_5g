<?php $this->inlineScript()->captureStart(); ?>
jQuery(document).ready(function(){
    var buttons = {};
    buttons['<?php echo _('Да'); ?>'] = function () {
    $(this).dialog('close');
    document.location.href = '<?php echo $this->redirectToSessionUrl; ?>';
    };
    buttons['<?php echo _('Нет'); ?>'] = function () {
    $(this).dialog('close');
    document.location.href = '<?php echo $this->redirectToIndexUrl; ?>';
    };

    jQuery('#redirect-dialog').dialog({
    autoOpen: true,
    resizeable: false,
    width: 400,
    modal: true,
    title: '<?php echo _('Принять в работу'); ?>',
    buttons: buttons
    });
});
<?php $this->inlineScript()->captureEnd(); ?>
<div id="redirect-dialog">
    <p><?php echo _('Вы хотите сразу перейти к созданию сессию подбора и уточнению её параметров?'); ?></p>
</div>